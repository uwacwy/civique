import chalk from "chalk";

const formatters = [
	// chalk.red, // red looks like errors
	chalk.green,
	chalk.yellow,
	chalk.blue,
	chalk.magenta,
	chalk.cyan,

	// black on <color>
	// chalk.bgRed.black,

	chalk.green.bold,
	chalk.yellow.bold,
	chalk.blue.bold,
	chalk.magenta.bold,
	chalk.cyan.bold,
];

const tags = new Map();

export const getFormatterForTag = (tag) => {
	if (!tags.has(tag)) {
		const formatter = formatters[tags.size % formatters.length];
		tags.set(tag, formatter);
	}
	return tags.get(tag);
};

/**
 *
 * @param {string} tag    tag to format and prepend
 * @param {number} idx    current index
 * @param {number} length total length
 * @return {string} formatted progress tag
 */
export const progressTag = (tag, idx = NaN, length = NaN) => {
	const formatTag = getFormatterForTag(tag);
	const chunks = [formatTag(tag)];
	if (!isNaN(idx) && !isNaN(length)) {
		// use math to figure out how many digits are in length
		const digits = Math.floor(Math.log10(length)) + 1;
		const idxString = `${(idx + 1).toString().padStart(digits, " ")}/${length}`;
		chunks.push(formatTag.dim(idxString));
	}
	return chunks.join(" ");
};
