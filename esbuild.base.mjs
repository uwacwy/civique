// @ts-check

import autoprefixer from "autoprefixer";
import { sassPlugin } from "esbuild-sass-plugin";
import postcss from "postcss";

/**
 * @type {Partial<import("esbuild").BuildOptions>}
 */
export const base = {
	entryPoints: [
		{
			in: "civique/css/civique.scss",
			out: "civique",
		},
		{
			in: "civique/ts/civique.ts",
			out: "civique",
		},
	],
	bundle: true,
	target: ["es2017"],
	loader: {
		".woff": "file",
		".woff2": "file",
		".png": "file",
		".jpg": "file",
		".svg": "file",
		".eot": "file",
		".ttf": "file",
	},
	plugins: [
		sassPlugin({
			transform: (source) =>
				postcss([autoprefixer])
					.process(source, { from: undefined })
					.then((result) => result.css),
		}),
	],
};
