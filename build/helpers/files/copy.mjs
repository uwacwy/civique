import cbfs from "fs";
import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { mapLog } from "../Mapable.mjs";
import { formatPath, trailingSlashIt } from "../Paths.mjs";
import { serial, when } from "../Promises.mjs";
import { thenLog } from "../Thenable.mjs";
import { Curries } from "../Urls.mjs";

/**
 * @typedef {Object} CopyFilesConfig
 * @property {string|string[]} globOrGlobs Glob pattern of files to copy from src to dst
 * @property {URL}             srcDirUrl   Source directory URL
 * @property {URL}             dstDirUrl   Destination directory URL
 */

/**
 *
 * @param {CopyFilesConfig} config
 * @return {() => Promise<void>} function that copies files when called
 */

export const copyFiles =
	({ globOrGlobs, srcDirUrl, dstDirUrl }) =>
	() =>
		when(`Copying files...`, {
			globs: Array.isArray(globOrGlobs) ? globOrGlobs : [globOrGlobs],
			urls: {
				src: srcDirUrl,
				dst: dstDirUrl,
				cwd: new URL(trailingSlashIt(process.cwd()), "file://"),
			},
		})
			.then((ctx) => ({
				...ctx,
				relativeToCwd: Curries.relativeToUrl(ctx.urls.cwd),
			}))
			.then((ctx) => ({
				...ctx,
				absolutePaths: {
					src: fileURLToPath(ctx.urls.src),
					dst: fileURLToPath(ctx.urls.dst),
					cwd: fileURLToPath(ctx.urls.cwd),
				},
				relativePaths: {
					src: ctx.relativeToCwd(ctx.urls.src),
					dst: ctx.relativeToCwd(ctx.urls.dst),
				},
			}))
			.then(
				thenLog("copy-files", {
					project: (ctx) =>
						[
							`${formatPath(ctx.absolutePaths.src, ctx.absolutePaths.cwd)}`,
							`${formatPath(ctx.absolutePaths.dst, ctx.absolutePaths.cwd)}`,
						].join(" → "),
				})
			)
			.then((ctx) =>
				new Promise((resolve, reject) =>
					cbfs.glob(
						globOrGlobs,
						{ cwd: ctx.absolutePaths.src },
						(err, files) => {
							if (err) {
								return reject(err);
							}

							return resolve(files);
						}
					)
				).then((/**@type {string[]} */ files) => ({
					...ctx,
					files,
				}))
			)
			.then((ctx) => ({
				...ctx,
				directories: [
					...ctx.files.reduce(
						(
							/** @type {Set<string>} */ dirSet,
							/** @type {string} */ filePath
						) => dirSet.add(path.dirname(filePath)),
						new Set()
					),
				],

				groupedByDirectory: Object.groupBy(ctx.files, (f) =>
					path.dirname(f)
				),
			}))
			.then((ctx) =>
				Promise.resolve(
					ctx.directories.map((dir) =>
						fs.mkdir(path.join(ctx.absolutePaths.dst, dir), {
							recursive: true,
						})
					)
				)
					.then((p) => Promise.all(p))
					.then((dirs) => ({
						...ctx,
						directories: dirs
							.filter((d) => d !== undefined)
							.map(
								mapLog("mkdir", {
									project: (d) => formatPath(d, ctx.absolutePaths.cwd),
								})
							),
					}))
			)
			.then((ctx) =>
				new Promise((resolve) => {
					// figure out column width for logging
					// calculate with unformatted paths
					// then, use .replace(path, formattedPath) to log
					// without messing up alignment
					const joined = ctx.files.map((f) => ({
						src: path.posix.join(ctx.absolutePaths.src, f),
						dst: path.posix.join(ctx.absolutePaths.dst, f),
					}));
					const columnWidth = Math.max(
						...joined.map(({ src }) => src.length)
					);

					/** @type {Array<Promise<void>>} */
					const files = ctx.files
						.map((file) => ({
							srcFilePath: path.join(ctx.absolutePaths.src, file),
							dstFilePath: path.join(ctx.absolutePaths.dst, file),
						}))
						.map(({ srcFilePath, dstFilePath }, idx, all) =>
							fs.copyFile(srcFilePath, dstFilePath).then(
								thenLog("copy-files", {
									idx,
									length: all.length,
									project: () =>
										`${srcFilePath.padEnd(columnWidth, " ")} → ${dstFilePath}`
											.replace(
												srcFilePath,
												formatPath(
													srcFilePath,
													ctx.absolutePaths.cwd
												)
											)
											.replace(
												dstFilePath,
												formatPath(
													dstFilePath,
													ctx.absolutePaths.cwd
												)
											),
								})
							)
						);

					return resolve(files);
				}).then((promises) => serial(promises).then(() => ctx))
			)
			.then(() => Promise.resolve());
