// @ts-check
import fs from "node:fs/promises";
import pkg from "../../package.json" with { type: "json" };
import { when } from "./Promises.mjs";
import { thenLog } from "./Thenable.mjs";

/**
 *
 * @param {Object<string, string>} record key-value pairs to convert to stylesheet comment
 * @return {string} WordPress stylesheet comment block
 */
export const buildThemeStylesheet = (record) =>
	["/*", ...Object.keys(record).map((k) => `${k}: ${record[k]}`), "*/"].join(
		"\n"
	);

/**
 *
 * @param {URL} styleCssUrl URL to write the stylesheet comment to
 * @return {() => Promise<void>} function that writes the stylesheet when called
 */
export const writeWordPressStylesheet = (styleCssUrl) => () =>
	when(`Writing WordPress stylesheet to ${styleCssUrl}...`).then(() =>
		fs
			.writeFile(
				styleCssUrl,
				buildThemeStylesheet({
					"Theme Name": pkg.name,
					"Theme URI": pkg.repository,
					Author: pkg.author,
					"Author URI": "https://unitedwayalbanycounty.org",
					Version: pkg.version,
					License: pkg.license || "MIT",
					Description: pkg.description,
					"Text Domain": "civique",
				})
			)
			.then(
				thenLog("writeFile", {
					project: () => styleCssUrl.pathname,
				})
			)
	);
