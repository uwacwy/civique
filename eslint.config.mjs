import path from "node:path";
import { fileURLToPath } from "node:url";

import { FlatCompat } from "@eslint/eslintrc";
import js from "@eslint/js";
import prettier from "eslint-plugin-prettier";
import globals from "globals";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const compat = new FlatCompat({
	baseDirectory: __dirname,
});

export default [
	{
		ignores: [
			"node_modules/**",
			"vendor/**",
			"dist/**",
			"civique/**/*.min.css",
			"civique-0.5.zip",
		],
	},
	js.configs.recommended,
	...compat.extends("plugin:@wordpress/eslint-plugin/recommended"),
	{
		files: ["*.mjs", "build/**/*.mjs"],
		rules: {
			"no-console": ["off"],
		},
	},
	{
		files: ["civique/**/*.js", "civique/**/*.ts", "build/**/*.mjs"],
		languageOptions: {
			sourceType: "module",
			globals: {
				...globals.browser,
				...globals.jquery,
				wp: "readonly",
			},
		},
		plugins: {
			prettier,
		},
		rules: {
			// ...(configPrettier.rules ?? {}),
			// "max-len": [
			// 	"warn",
			// 	{
			// 		code: 100,
			// 		ignoreComments: true,
			// 		ignoreUrls: true,
			// 		ignoreStrings: true,
			// 		ignoreTemplateLiterals: true,
			// 	},
			// ],
			"prettier/prettier": ["warn"],
		},
	},
];
