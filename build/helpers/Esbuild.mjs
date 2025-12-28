import fs from "node:fs/promises";

import esbuild from "esbuild";
import { setCause } from "./Errors.mjs";
import { mapLog } from "./Mapable.mjs";
import { formatPath } from "./Paths.mjs";
import { when } from "./Promises.mjs";
import { thenLog } from "./Thenable.mjs";

/**
 * @typedef {import("esbuild")} esbuild
 */

/**
 * Write esbuild build result to console.
 * @template {esbuild.BuildOptions} T
 * @return {(result: esbuild.BuildResult<T>)=>esbuild.BuildResult<T>} same build object
 */
export const printEsbuildResult = () => (result) => {
	Object.entries(result.metafile.outputs).map(
		mapLog("esbuild", {
			project: ([outfile, info]) =>
				`${formatPath(outfile)} (${info.bytes.toLocaleString()} bytes)`,
		})
	);

	return result;
};

/**
 *
 * @template {Partial<esbuild.BuildOptions>} T
 * @param {esbuild.SameShape<esbuild.BuildOptions, T>} buildOptions
 * @return {() => Promise<esbuild.BuildResult<T>>} function that builds when called
 */
export const esbuildBundle = (buildOptions) => () =>
	when("Building with esbuild...").then(() =>
		esbuild.build({
			...buildOptions,
		})
	);

export const esbuildMinify = (loader, src, dst) => () =>
	when(`Minifying ${formatPath(src)} to ${formatPath(dst)}...`).then(() =>
		esbuild
			.transform(src, {
				minify: true,
				loader,
			})
			.then(thenLog("minify"))
			.then((result) => fs.writeFile(dst, result.code))
			.catch(setCause(`Failed to minify ${src} to ${dst}`))
	);
