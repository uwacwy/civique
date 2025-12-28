// @ts-check
import chalk from "chalk";
import cbfs from "node:fs";
import path from "node:path";

/**
 * Normalize/resolve a file path to an absolute POSIX-style path.
 *
 * @param {string} filePath
 * @return {string} Absolute POSIX-style file path
 */
export const resolveAbsPosix = (filePath) =>
	(path.isAbsolute(filePath) ? filePath : path.resolve(filePath))
		.split(path.sep)
		.join(path.posix.sep);

/**
 * Ensure a path has a trailing slash.
 *
 * @param {string} s path that needs a trailing slash
 * @return {string} a path that has a trailing slash.
 */
export const trailingSlashIt = (s) => (s.endsWith("/") ? s : s + "/");

/**
 * Formats a file basename for display.
 *
 * @param {string} basename The file basename to format.
 * @return {string} The formatted file basename.
 */
export const formatBasename = (basename) => {
	// dotfiles => bold.white
	if (basename.startsWith(".")) {
		return chalk.bold.white(basename);
	}

	return basename
		.split(".")
		.map((seg, idx, all) => {
			if (seg === "**") {
				return chalk.bold.magenta(seg);
			}

			if (seg === "*") {
				return chalk.magenta(seg);
			}
			if (idx === 0) {
				return chalk.cyan(seg);
			}

			// last
			if (idx === all.length - 1) {
				return chalk.dim.white(seg);
			}

			return chalk.dim.cyan(seg);
		})
		.join(chalk.dim.white("."));
};

/**
 * Formats a file path for display.
 *
 * @param {string} filePath The file path to format.3
 * @param {string} [cwd]    The current working directory to make the path relative to.
 * @return {string} The formatted file path.
 */
export const formatPath = (filePath, cwd = process.cwd()) => {
	const cwdAbs = resolveAbsPosix(cwd);
	const filePathAbs = resolveAbsPosix(filePath);

	const stats = cbfs.statSync(filePathAbs);
	const isDirectory = stats.isDirectory();
	const isFile = stats.isFile();
	// eslint-disable-next-line no-bitwise
	const isExecutable = isFile && (stats.mode & 0o111) !== 0;

	/** @type {string[]} */
	const chunks = [chalk.dim.white(".")];

	chunks.push(
		...filePathAbs
			.slice(cwdAbs.length)
			.split(path.posix.sep)
			.filter(Boolean)
			.map((seg, idx, all) => {
				if (seg === "**") {
					return chalk.bold.magenta(seg);
				}

				if (seg === "*") {
					return chalk.magenta(seg);
				}

				// if not first or last segment, color as dim white
				if (idx < all.length - 1) {
					return chalk.white(seg);
				}

				// if last, use basename formatting
				if (idx === all.length - 1) {
					if (isDirectory) {
						return chalk.green(seg) + chalk.white(path.posix.sep);
					}
					const fmted = formatBasename(seg);
					return isExecutable ? chalk.bold.green(fmted) : fmted;
				}

				return seg;
			})
	);

	return chunks.join(chalk.bold.dim(path.posix.sep));
};

/**
 * Print a path relative to cwd, formatted.
 * @param {string} filePath a path
 * @return {string} the path, shown relative to cwd and formatted
 */
export const prettyCwdRelPath = (filePath) => {
	const cwd = process.cwd();
	const rel = path.relative(cwd, filePath);
	return formatPath(rel, cwd);
};
