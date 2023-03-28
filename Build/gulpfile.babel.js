'use strict';

import gulp from 'gulp';
import log from 'gulplog';
import path from 'path';

import browserify from 'browserify';
import buffer from 'vinyl-buffer';
import rename from 'gulp-rename';
import source from 'vinyl-source-stream';
import sourcemaps from 'gulp-sourcemaps';
import tsify from 'tsify';
import ts from 'gulp-typescript';
import terser from 'gulp-terser';

import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import sass from 'gulp-sass';

import stylelint from 'gulp-stylelint';
import eslint from 'gulp-eslint';

const paths = {
	src: './Sources/',
	dest: '../Resources/Public/'
};

const tasks = {
	typescript: {
		src: 'TypeScript',
		dest: 'JavaScript'
	},
	scss: {
		src: 'Scss/*.scss',
		dest: 'Stylesheet'
	}
};

gulp.task('typescript-gm', () => {
	let b = browserify({
		entries: [path.join(paths.src, tasks.typescript.src, 'FrontendGoogleMap.ts')],
		debug: true
	});

	return b
		.plugin(tsify)
		.bundle()
		.pipe(source('FrontendGoogleMap.js'))
		.pipe(buffer())
		.pipe(sourcemaps.init({loadMaps: true}))
		// This will output the non-minified version
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)))
		// Add transformation tasks to the pipeline here.
		.pipe(terser())
		.on('error', log.error)
		.pipe(rename({ extname: '.min.js' }))
		.pipe(sourcemaps.write('./', {
			mapFile: function(mapFilePath) {
				// source map files are named *.map instead of *.js.map
				return mapFilePath.replace('.min.js.map', '.js.map');
			}
		}))
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)));
});

gulp.task('typescript-osm', () => {
	let b = browserify({
		entries: [path.join(paths.src, tasks.typescript.src, 'FrontendOsmMap.ts')],
		debug: true
	});

	return b
		.plugin(tsify)
		.bundle()
		.pipe(source('FrontendOsmMap.js'))
		.pipe(buffer())
		.pipe(sourcemaps.init({loadMaps: true}))
		// This will output the non-minified version
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)))
		// Add transformation tasks to the pipeline here.
		.pipe(terser())
		.on('error', log.error)
		.pipe(rename({ extname: '.min.js' }))
		.pipe(sourcemaps.write('./', {
			mapFile: function(mapFilePath) {
				// source map files are named *.map instead of *.js.map
				return mapFilePath.replace('.min.js.map', '.js.map');
			}
		}))
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)));
});

gulp.task('typescript-backend', () => {
	let tsProject = ts.createProject('tsconfig.json', {module: 'amd'});

	return gulp.src(path.join(paths.src, tasks.typescript.src, 'BackendOsmMap.ts'))
		.pipe(sourcemaps.init())
		.pipe(tsProject())
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest, 'FormEngine/Element')));
});

gulp.task('typescript', gulp.series('typescript-gm', 'typescript-osm', 'typescript-backend'));

gulp.task('scss', () => {
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

gulp.task('eslint', () => {
  return gulp
    .src(['Sources/TypeScript/*.ts'])
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError());
});

gulp.task('stylelint', () => {
  return gulp
    .src('Sources/Scss/*.scss')
    .pipe(stylelint({
      reporters: [
        { formatter: 'string', console: true }
      ]
    }));
});
