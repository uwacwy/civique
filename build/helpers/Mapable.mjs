import { progressTag } from "./Tags.mjs";

/**
 * @template T
 * @typedef {Object} MapLogConfig
 * @property {(item: T, idx?: number, arr?: T[]) => any}     [project]   function to project/transform each item
 * @property {(item: T, idx?: number, arr?: T[]) => boolean} [predicate] function to filter/log each item
 */

/**
 * @template T
 * @type {MapLogConfig<T>}
 */
export const defaultMapLogConfig = {
	project: (i) => i,
	predicate: () => true,
};

/**
 * @template T
 * @overload
 * @param {string} tag string to tag/label each item
 * @return {(item: T, idx: number, arr: T[]) => any} function that logs each item and returns the original item
 */

/**
 * @template T
 * @overload
 * @param {string}                   tag    string to tag/label each item
 * @param {Partial<MapLogConfig<T>>} config optional object containing an optional project and predicate.
 * @return {(item: T, idx: number, arr: T[]) => any} function that logs each item and returns the original item
 */

/**
 * @template T
 * @param {string}                   tag      string to tag/label each item
 * @param {Partial<MapLogConfig<T>>} [config] optional object containing an optional project and predicate.
 * @return {(item: T, idx: number, arr: T[]) => any} function that logs each item and returns the original item
 */
export const mapLog =
	(tag, config = {}) =>
	(originalItem, idx, arr) => {
		const { project, predicate } = {
			...defaultMapLogConfig,
			...config,
		};
		const projectedItem = project(originalItem);
		if (predicate(originalItem, idx, arr)) {
			console.log(` [${progressTag(tag, idx, arr.length)}]`, projectedItem);
		}
		return originalItem;
	};
