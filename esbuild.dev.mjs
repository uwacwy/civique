import * as esbuild from "esbuild";

import { base } from "./esbuild.base.mjs";

// recompile the SCSS files on change

const ctx = await esbuild.context({
	...base,
	outdir: "civique",
	write: true,
	minify: false,
	sourcemap: true,
	assetNames: "assets/[name]",
});

await ctx.watch();

console.log("Watching for changes...");

// catch ctrl+c and call ctx.dispose()
process.on("SIGINT", async () => {
	console.log("Stopping watch...");
	await ctx.dispose();
	console.log("Goodbye!");
	process.exit(0);
});
