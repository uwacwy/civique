// @ts-check

import { esbuildBundle, printEsbuildResult } from "./build/helpers/esbuild.mjs";
import {
	copyFiles,
	createOutputDirectory,
	zipFiles,
} from "./build/helpers/Files.mjs";
import { trailingSlashIt } from "./build/helpers/Paths.mjs";
import { when } from "./build/helpers/Promises.mjs";
import { thenLog } from "./build/helpers/Thenable.mjs";
import { Curries } from "./build/helpers/Urls.mjs";
import { writeWordPressStylesheet } from "./build/helpers/WordPress.mjs";
import { base } from "./esbuild.base.mjs";
import pkg from "./package.json" with { type: "json" };

export const srcDirUrl = new URL("./civique/", import.meta.url);
export const dstDirUrl = new URL("./dist/civique/", import.meta.url);
export const cwdDirUrl = new URL(
	trailingSlashIt(process.cwd()),
	import.meta.url
);

export const cwdRel = Curries.relativeToUrl(cwdDirUrl);

// Generate CSS/JS Builds
when("Starting WordPress theme build...", process.cwd())
	.then(thenLog("cwd"))
	.then(createOutputDirectory(dstDirUrl, !process.argv.includes("--no-clean")))
	.then(
		esbuildBundle({
			...base,
			entryPoints: [
				{ in: "civique/css/civique.scss", out: "civique" },
				{ in: "civique/ts/civique.ts", out: "civique" },
			],
			outdir: "dist/civique",
			metafile: true,
			sourcemap: true,
			assetNames: "assets/[hash]",
		})
	)
	.then(printEsbuildResult())
	.then(
		esbuildBundle({
			...base,
			entryPoints: [
				{ in: "civique/css/civique.scss", out: "civique.min" },
				{ in: "civique/ts/civique.ts", out: "civique.min" },
			],
			outdir: "dist/civique",
			metafile: true,
			sourcemap: false,
			minify: true,
			treeShaking: true,
			assetNames: "assets/[hash]",
		})
	)
	.then(printEsbuildResult())
	.then(
		copyFiles({
			glob: ["**/*.php"],
			srcDirUrl,
			dstDirUrl,
		})
	)
	.catch((error) => {
		throw new Error(
			`Failed to copy PHP files from ${srcDirUrl} to ${dstDirUrl}`,
			{ cause: error }
		);
	})
	.then(
		copyFiles({
			glob: ["./LICENSE", "./README.md"],
			srcDirUrl: new URL("./", import.meta.url),
			dstDirUrl,
		})
	)
	.catch((error) => {
		throw new Error(`Failed to copy root files to ${dstDirUrl}`, {
			cause: error,
		});
	})
	// .then(
	// 	concatFiles(
	// 		["./build/helpers/*.mjs"],
	// 		new URL("./build/bundle.mjs", import.meta.url)
	// 	)
	// )
	.then(writeWordPressStylesheet(new URL("./style.css", dstDirUrl)))
	.then(
		zipFiles(
			new URL(`./dist/${pkg.name}-${pkg.version}.zip`, import.meta.url),
			dstDirUrl
		)
	)
	.then((r) => {
		console.log("Build complete.", r);
	})
	.catch((error) => {
		console.error("Build failed due to a build error...", error);
		return process.exit(1);
	});
