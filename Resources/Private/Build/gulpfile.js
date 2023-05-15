/* jshint node: true */
'use strict';

const { src, dest, watch, series, parallel } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const rollup = require('rollup').rollup;
const rollupConfig = require('./rollup.config');
const uglify = require('gulp-uglify');
const plumber = require('gulp-plumber');
const rename = require('gulp-rename');

const project = {
  base: __dirname + '/../../Public',
  css: __dirname + '/../../Public/Css',
  js: __dirname + '/../../Public/JavaScript/Luxletter'
};

// SCSS zu css
function css() {
  const config = {};
  config.outputStyle = 'compressed';

  return src(__dirname + '/../Sass/*.scss')
    .pipe(plumber())
    .pipe(sass(config))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(dest(project.css));
};

function js(done) {
  rollup(rollupConfig).then(bundle => {
    rollupConfig.output.plugins = rollupConfig
    bundle.write(rollupConfig.output).then(() => done());
  });
};

function jsBackend() {
  return src([__dirname + '/JavaScript/*.js'])
    .pipe(plumber())
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(dest(project.js));
};

// "npm run build"
const build = series(js, jsBackend, css);

// "npm run watch"
const def = parallel(
  function watchSCSS() { return watch(__dirname + '/../Sass/**/*.scss', series(css)) },
  function watchJS() { return watch(__dirname + '/JavaScript/*.js', series(js, jsBackend)) }
);

module.exports = {
  default: def,
  build,
  css,
  jsBackend,
  js
};
