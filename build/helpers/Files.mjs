// @ts-check

/**
 * @typedef {Object} CopyFilesConfig
 * @property {string|string[]} glob      Glob pattern of files to copy from src to dst
 * @property {URL}             srcDirUrl Source directory URL
 * @property {URL}             dstDirUrl Destination directory URL
 */
// import Archiver from "archiver";

import Archiver from "archiver";
import chalk from "chalk";
import cbfs from "fs";
import fs from "node:fs/promises";
import path from "path";

import { fileURLToPath } from "node:url";

import { mapLog } from "./Mapable.mjs";
import { formatBasename, formatPath, trailingSlashIt } from "./Paths.mjs";
import { serial, when } from "./Promises.mjs";
import { thenLog } from "./Thenable.mjs";
import { Curries } from "./Urls.mjs";

const formatBase = (base) => chalk.bold.white(base);
// replace ** with bold magenta **
// replace * with magenta *
// regular segments white
// sep in dim white
const formatGlob = (g) =>
	g
		.split(path.posix.sep)
		.map((seg, idx, arr) => {
			if (seg === "**") {
				return chalk.bold.magenta(seg);
			}

			if (seg === "*") {
				return chalk.magenta(seg);
			}

			// last segment - format as basename
			if (idx === arr.length - 1) {
				return formatBasename(seg);
			}

			return chalk.white(seg);
		})
		.join(chalk.dim.white(path.posix.sep));

/**
 *
 * @param {CopyFilesConfig} config
 * @return {() => Promise<void>} function that copies files when called
 */
export const copyFiles =
	({ glob, srcDirUrl, dstDirUrl }) =>
	() =>
		when(`Copying files...`, {
			globs: Array.isArray(glob) ? glob : [glob],
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
					cbfs.glob(glob, { cwd: ctx.absolutePaths.src }, (err, files) => {
						if (err) {
							return reject(err);
						}

						return resolve(files);
					})
				).then((/**@type {string[]} */ files) => ({
					...ctx,
					files,
				}))
			)
			.then((ctx) => {
				return {
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
				};
			})
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
				new Promise(() => {
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
					return ctx.files.map((file, idx, files) => {
						const srcFilePath = path.join(ctx.absolutePaths.src, file);
						const dstFilePath = path.join(ctx.absolutePaths.dst, file);
						return fs.copyFile(srcFilePath, dstFilePath).then(
							thenLog("copy-files", {
								idx,
								length: files.length,
								project: () =>
									`${srcFilePath.padEnd(columnWidth, " ")} → ${dstFilePath}`
										.replace(
											srcFilePath,
											formatPath(srcFilePath, ctx.absolutePaths.cwd)
										)
										.replace(
											dstFilePath,
											formatPath(dstFilePath, ctx.absolutePaths.cwd)
										),
							})
						);
					});
				}).then((promises) => serial(promises).then(() => ctx))
			)
			.then((ctx) => {
				return console.log({ ctx });
			});

/**
 * @param {URL} zipFileUrl Destination for the zip archive.
 * @param {URL} dirUrl     Directory to zip.
 * @return {() => Promise<string>} function that zips the directory when called
 */
export const zipFiles = (zipFileUrl, dirUrl) => () =>
	new Promise((resolve, reject) => {
		console.log("Creating zip archive...");
		const zipFilePath = fileURLToPath(zipFileUrl);
		const output = cbfs.createWriteStream(zipFilePath);
		const archive = Archiver("zip", {
			zlib: { level: 9 },
		});

		output.on("close", () => {
			resolve(
				`${zipFilePath} (${archive.pointer().toLocaleString()} total bytes)`
			);
		});

		archive.on("error", (err) => {
			console.error("Error creating zip archive:", err);
			reject(err);
		});

		archive.pipe(output);

		archive.directory(
			fileURLToPath(dirUrl),
			"civique" // include the civique/ folder in the zip
		);

		return archive.finalize();
	});

/**
 *
 * @param {URL}     dirUrl URL to output directory
 * @param {boolean} clean  Delete existing output directory first.
 * @return {() => Promise<undefined | string>} function that creates the output directory when called
 */
export const createOutputDirectory = (dirUrl, clean) => () =>
	when(`Creating output directory: ${fileURLToPath(dirUrl)}...`)
		.then(() =>
			clean
				? fs
						.rm(fileURLToPath(dirUrl), { recursive: true, force: true })
						.then(
							thenLog("clean", {
								project: () =>
									`Cleaned output directory: ${fileURLToPath(dirUrl)}`,
							})
						)
				: Promise.resolve()
		)
		.then(() => fs.mkdir(fileURLToPath(dirUrl), { recursive: true }));

/**
 * @typedef {Object} ConcatenateFilesOptions
 * @property {string}        beforeAll    String to insert before all concatenated content.
 * @property {string}        afterAll     String to insert after all concatenated content.
 * @property {string}        join         String to insert between each file's content.
 * @property {string}        beforeEach   String to insert before each file's content.
 * @property {string}        afterEach    String to insert after each file's content.
 * @property {string}        cwd          Current working directory for resolving files.
 * @property {Array<string>} stripPragmas List of pragmas to strip from files.
 */

/** @type {ConcatenateFilesOptions} */
const defaultConcatenateFilesOpts = {
	beforeAll: "",
	beforeEach: "",
	afterAll: "",
	join: "\n",
	afterEach: "",
	cwd: process.cwd(),
	stripPragmas: ["<?php"],
};

/**
 *
 * @param {string | readonly string[]}        globOrGlobs files to fetch, defined by globs.
 * @param {URL}                               dstUrl      destination file URL
 * @param {Partial<ConcatenateFilesOptions> } [opts]      options for concatenation
 * @return {() => Promise<void>} function that concatenates files when called
 */
export const concatFiles =
	(globOrGlobs, dstUrl, opts = {}) =>
	() => {
		const globs = Array.isArray(globOrGlobs) ? globOrGlobs : [globOrGlobs];
		const {
			cwd,
			beforeAll,
			afterAll,
			join,
			beforeEach,
			afterEach,
			stripPragmas,
		} = {
			...defaultConcatenateFilesOpts,
			...opts,
		};

		/** @type {(null|import('./Strings.mjs').Serializable)[]} */
		const args = [
			null, // reserved for current file path
			new Date().toISOString(),
			process.cwd(), // actual cwd
			cwd, // working directory
			globs.join(", "),
			fileURLToPath(dstUrl),
		];

		// console.log({ args, cwd, globs })

		return when("Concatenating files...").then(
			() =>
				new Promise((resolve, reject) => {
					const filePaths = cbfs.globSync(globs, { cwd });

					console.log(`Found ${filePaths.length} files to concatenate.`);

					filePaths.map(
						mapLog("concat-file", {
							project: (filePath) => formatPath(filePath, cwd),
						})
					);
				})
		);

		// });
	};
// .then(async () => {
// 	const filePaths = cbfs.globSync(globs, { cwd });

// 	console.log(`Found ${filePaths.length} files to concatenate.`);

// 	output.write(beforeAll);
// 	for (const filePath of filePaths) {
// 		formatPath(filePath);
// 		console.log({ file: formatPath(filePath) });

// 		const input = cbfs.createReadStream(filePath, { encoding: "utf8" });

// 		input.on("data", (chunk) => {
// 			const content = chunk.toString();

// 			// TODO: strip pragmas
// 			// if (opts.stripPragmas) {
// 			// 	for (const pragma of opts.stripPragmas) {
// 			// 		const pragmaRegex = new RegExp(`^\\s*${pragma}\\s*`, "gm");
// 			// 		content = content.replace(pragmaRegex, "");
// 			// 	}
// 			// }

// 			output.write(interpolate(beforeEach, ...args));
// 			output.write(content);
// 			output.write(interpolate(afterEach, ...args));
// 			output.write(interpolate(join, ...args));
// 		})
// 		input.on("error", (err) => {
// 			console.error(`Error reading file ${filePath}:`, err);
// 		});
// 	}
// 	output.write(afterAll);
// 	output.end();
// 	console.log(`Concatenated files written to ${fileURLToPath(dstUrl)}`);
