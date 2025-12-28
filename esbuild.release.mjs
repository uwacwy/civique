// @ts-check

import { base } from "./esbuild.base.mjs";

import { esbuildBundle, printEsbuildResult } from "./build/helpers/Esbuild.mjs";
import { copyFiles } from "./build/helpers/files/copy.mjs";
import { mkdir } from "./build/helpers/files/mkdir.mjs";
import { zipFiles } from "./build/helpers/files/zip.mjs";
import { trailingSlashIt } from "./build/helpers/Paths.mjs";
import { when } from "./build/helpers/Promises.mjs";
import { thenLog } from "./build/helpers/Thenable.mjs";
import { Curries } from "./build/helpers/Urls.mjs";
import { writeWordPressStylesheet } from "./build/helpers/WordPress.mjs";

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
	.then(mkdir(dstDirUrl, !process.argv.includes("--no-clean")))
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
			globOrGlobs: ["**/*.php"],
			srcDirUrl,
			dstDirUrl,
		})
	)
	.then(
		copyFiles({
			globOrGlobs: ["./LICENSE", "./README.md"],
			srcDirUrl: new URL("./", import.meta.url),
			dstDirUrl,
		})
	)
	.then(writeWordPressStylesheet(new URL("./style.css", dstDirUrl)))
	.then(
		zipFiles(
			new URL(`./dist/${pkg.name}-${pkg.version}.zip`, import.meta.url),
			dstDirUrl
		)
	)
	.then((artifact) =>
		when("Build complete.", artifact).then(thenLog("artifact"))
	)
	.catch((error) => {
		console.error("Build failed due to a build error...", error);
		return process.exit(1);
	});
