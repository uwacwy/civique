import { progressTag } from "./Tags.mjs";

/**
 * @template T
 * @typedef {Object} ThenLogConfig
 * @property {number}               idx       index of the item in the array
 * @property {number}               length    length of the array
 * @property {(item: T) => string}  project   function to project/transform the item
 * @property {(item: T) => boolean} predicate function to filter/log the item
 */

/**
 * @template T
 * @return {ThenLogConfig<T>} default configuration for thenLog
 */
export const defaultThenLogConfig = () => ({
	idx: NaN,
	length: NaN,
	project: (i) => i,
	// eslint-disable-next-line no-unused-vars
	predicate: (_i) => true,
});

/**
 * @template T
 * @overload
 * @param {string} tag
 * @return {(item: T) => T} function that logs the item and returns it
 */

/**
 * @template T
 * @overload
 * @param {string}                    tag
 * @param {Partial<ThenLogConfig<T>>} config optional object containing an optional project and predicate.
 * @return {(item: T) => T} function that logs the item and returns it
 */

/**
 * @template T
 * @param {string}           tag
 * @param {ThenLogConfig<T>} [config] optional object containing an optional project and predicate.
 * @return {(item: T) => T} function that logs the item and returns it
 */
export const thenLog = (tag, config = {}) => {
	return (originalItem) => {
		const { project, predicate, idx, length } = {
			...defaultThenLogConfig(),
			...config,
		};

		if ((predicate && predicate(originalItem)) || !predicate) {
			console.log(
				` [${progressTag(tag, idx, length)}]`,
				project ? project(originalItem) : originalItem
			);
		}
		return originalItem;
	};
};
