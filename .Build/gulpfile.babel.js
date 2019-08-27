'use strict';

import gulp from 'gulp';
import ts from 'gulp-typescript';
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import sourcemaps from 'gulp-sourcemaps';
import sass from 'gulp-sass';
import path from 'path';

const paths = {
	src: './Sources/',
	dest: '../Resources/Public/'
};

const tasks = {
	typescript: {
		src: 'TypeScript/*.ts',
		dest: 'JavaScript'
	},
	scss: {
		src: 'Scss/*.scss',
		dest: 'Stylesheet'
	}
};

gulp.task('typescript', function () {
	return gulp.src(path.join(paths.src, tasks.typescript.src))
		.pipe(sourcemaps.init())
		.pipe(ts({
			//target: 'es5',
			module: 'amd',
			alwaysStrict: true,
			downlevelIteration: true,
			experimentalDecorators: true,
			noImplicitAny: true,
			noImplicitThis: true,
			noImplicitReturns: true,
			pretty: true,
			typeRoots: ['./node_modules/@types/']
		}))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)));
});

gulp.task('scss', function () {
	return gulp.src(path.join(paths.src, tasks.scss.src))
		.pipe(sourcemaps.init())
		.pipe(
			sass({
				includePaths: require('node-normalize-scss').includePaths
			}).on('error', sass.logError)
		)
		.pipe(postcss([autoprefixer()]))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(path.join(paths.dest, tasks.scss.dest)));
});

gulp.task('build', gulp.series('typescript', 'scss'));
