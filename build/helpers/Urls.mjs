// @ts-check
import path, { relative } from "node:path";
import { fileURLToPath } from "node:url";

/**
 * @typedef {(to: URL) => string} InnerRelative
 */

/**
 * @typedef {(from: URL) => InnerRelative} OuterRelative
 */

export const Curries = {
	/**
	 * @type {OuterRelative}
	 */
	relativeToUrl: (from) => {
		const fromDirname = path.dirname(
			fileURLToPath(new URL(from, import.meta.url))
		);
		return (to) => {
			return relative(fromDirname, fileURLToPath(to));
		};
	},
};
/**
 *
 * @param {URL} from url to start from
 * @param {URL} to   url navigating to
 * @return {string} relative path from "from" to "to"
 */
export const urlRelative = (from, to) => Curries.relativeToUrl(from)(to);
