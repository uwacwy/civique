import chalk from "chalk";

const formatters = [
	// chalk.red, // red looks like errors
	chalk.green,
	chalk.yellow,
	chalk.blue,
	chalk.magenta,
	chalk.cyan,
	chalk.white,

	// black on <color>
	// chalk.bgRed.black,

	chalk.greenBright.black,
	chalk.yellowBright.black,
	chalk.blueBright.black,
	chalk.magentaBright.black,
	chalk.cyanBright.black,
	chalk.whiteBright.black,
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
 * @return
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
