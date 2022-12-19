/* jshint node: true */
'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var uglify = require('gulp-uglify');
var plumber = require('gulp-plumber');
var rename = require('gulp-rename');

var project = {
	base: __dirname + '/../../Public',
	css: __dirname + '/../../Public/Css',
	js: __dirname + '/../../Public/JavaScript/Luxletter',
	images: __dirname + '/../../Public/Images'
};

// SCSS zu css
gulp.task('css', function() {
	var config = {};
	config.outputStyle = 'compressed';

	gulp.src(__dirname + '/../Sass/*.scss')
		.pipe(plumber())
		.pipe(sass(config))
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest(project.css));
});

gulp.task('js', function() {
	gulp.src([__dirname + '/../JavaScript/*.js'])
		.pipe(plumber())
		.pipe(uglify())
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest(project.js));
});

/*********************************
 *         Watch Tasks
 *********************************/
gulp.task('default', function() {
	gulp.watch(__dirname + '/../Sass/*.scss', ['css']);
	gulp.watch(__dirname + '/../JavaScript/*.js', ['js']);
});
