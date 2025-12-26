// @ts-check

import chalk from "chalk";

/**
 * @overload
 * @param {string} message string to log
 * @return {Promise<void>} promise that resolves after logging message
 */

/**
 * @template T
 * @overload
 * @param {string} message string to log
 * @param {T}      ctx     context object to pass through
 * @return {Promise<T>} promise that resolves to ctx after logging message
 */

/**
 * @template {Record<string, unknown> } T
 * @param {string} message string to log
 * @param {T}      [ctx]   context object to pass through
 * @return {Promise<void | T>} promise that resolves to ctx after logging message
 */
export const when = (message, ctx) =>
	Promise.resolve(chalk.bold.whiteBright(message))
		.then(console.log)
		.then(() => ctx);

/**
 * @template T
 * @param {Array<Promise<T>>} promises
 * @return {Promise<T[]>} promise that resolves when all promises have resolved in series
 */
export const serial = async (promises) => {
	/** @type {T[]} */
	const results = [];

	for await (const promise of promises) {
		const result = await promise;
		results.push(result);
	}

	return results;
};
