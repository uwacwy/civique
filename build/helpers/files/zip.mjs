import Archiver from "archiver";
import cbfs from "fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { prettyCwdRelPath } from "../Paths.mjs";
import { when } from "../Promises.mjs";
import { thenLog } from "../Thenable.mjs";

/**
 * @param {URL} zipFileUrl Destination for the zip archive.
 * @param {URL} dirUrl     Directory to zip.
 * @return {() => Promise<string>} function that zips the directory when called
 */

export const zipFiles = (zipFileUrl, dirUrl) => () =>
	when(
		`Creating zip archive of ${prettyCwdRelPath(fileURLToPath(dirUrl))} at ${prettyCwdRelPath(fileURLToPath(zipFileUrl))}...`
	).then(() =>
		new Promise((resolve, reject) => {
			const zipFilePath = fileURLToPath(zipFileUrl);
			const output = cbfs.createWriteStream(zipFilePath);
			const archive = Archiver("zip", {
				zlib: { level: 9 },
			});

			output.on("close", () => {
				resolve(
					`${zipFilePath} (${archive.pointer().toLocaleString()} total bytes)`
				);
			});

			archive.on("error", (err) => {
				reject(err);
			});

			archive.pipe(output);

			const dirpath = fileURLToPath(dirUrl);
			const basename = path.basename(dirpath);
			archive.directory(dirpath, basename);

			return archive.finalize();
		}).then(
			thenLog("zip", {
				project: () => fileURLToPath(zipFileUrl),
			})
		)
	);
