import path from "node:path";
import { fileURLToPath } from "node:url";
import { when } from "../Promises.mjs";

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

export const defaultConcatenateFilesOpts = {
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
 * @param {string | Array<string>}            globOrGlobs files to fetch, defined by globs.
 * @param {URL}                               dstUrl      destination file URL
 * @param {Partial<ConcatenateFilesOptions> } [opts]      options for concatenation
 * @return {() => Promise<void>} function that concatenates files when called
 */
export const concatFiles =
	(globOrGlobs, dstUrl, opts = {}) =>
	() =>
		when("Concatenating files...", {
			globOrGlobs,
			dstUrl,
			opts: {
				...defaultConcatenateFilesOpts,
				...opts,
			},
		})
			.then((ctx) => ({
				...ctx,
				absoluteDstPath: fileURLToPath(ctx.dstUrl),
				cwdPath: path.resolve(ctx.opts.cwd),
				globs: Array.isArray(globOrGlobs) ? globOrGlobs : [globOrGlobs],
			}))
			.then((ctx) => {
				console.log(ctx);
			});
