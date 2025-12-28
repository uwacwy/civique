import fs from "node:fs/promises";
import { fileURLToPath } from "node:url";
import { when } from "../Promises.mjs";
import { thenLog } from "../Thenable.mjs";

/**
 *
 * @param {URL}     dirUrl URL to output directory
 * @param {boolean} clean  Delete existing output directory first.
 * @return {() => Promise<undefined | string>} function that creates the output directory when called
 */

export const mkdir = (dirUrl, clean) => () =>
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
