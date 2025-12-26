import { gulp } from "gulp";

const build: gulp.TaskFunction = (cb) => {
	console.log("Gulp is running!");
	cb();
};

export default build;
