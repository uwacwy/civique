/**
 * @typedef {Object} Serializable
 * @property {function(): string} toString Method that returns a string representation.
 */

/**
 * csharp-style string value replacement
 *
 * @param {string}                   templateString string including {0}, {1}, etc. placeholders
 * @param {Array<Serializable|null>} args           values to replace placeholders
 * @return {string} formatted string with tokens replaced (if possible)
 */
export const format = (templateString, ...args) =>
	templateString.replace(
		/\{(\d+)\}/g,
		(match, index) => args[index]?.toString() ?? match
	);
