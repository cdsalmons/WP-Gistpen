var gulp = require('gulp');
var clean = require('gulp-clean');
var jshint = require('gulp-jshint');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var composer = require('gulp-composer');
var bower = require('gulp-bower');
var runs  = require('run-sequence');

var paths = {
	pubjs: [
		'public/assets/vendor/SyntaxHighlighter/scripts/XRegExp.js',
		'public/assets/vendor/SyntaxHighlighter/scripts/shLegacy.js',
		'public/assets/vendor/SyntaxHighlighter/scripts/shCore.js',
		'public/assets/vendor/SyntaxHighlighter/scripts/shAutoloader.js',
		'public/assets/js/*.js'],
	adminjs: ['admin/assets/js/wp-gistpen-admin.js'],
	add: ['**/*.php',
		'**/*.png',
		'**/*.pot',
		'.*',
		'*.txt',
		'*.php',
		'*.json',
		'*.lock',
		'!node_modules/**',
		'!includes/**',
		'!public/assets/vendor/**'],
	build: 'build/'
}

gulp.task('dev', function () {
	// Public Javascript Files
	gulp.src(paths.pubjs)
		// Concatenate the Javascript
		.pipe(concat('wp-gistpen.min.js'))
		.pipe(gulp.dest('public/assets/js/'));
	// Admin Javascript Files
	gulp.src(paths.adminjs)
		// Concatenate the Javascript
		.pipe(concat('wp-gistpen-admin.min.js'))
		.pipe(gulp.dest('admin/assets/js/'));
	// Install composer dependencies
	composer({ bin: 'composer' });
	//bower();
})

// Watch and regen
gulp.task('watch', ['dev'], function () {
	gulp.watch('**/*.js', ['dev']);
})

// Delete the build directory
gulp.task('clean', function() {
	return gulp.src(paths.build)
		.pipe(clean());
});

// Copy source files
gulp.task('copy', function() {
	return gulp.src(paths.add)
		.pipe(gulp.dest(paths.build));
});

gulp.task('minify', function () {
	// Public Javascript Files
	gulp.src(paths.pubjs)
		.pipe(concat('wp-gistpen.min.js'))
		.pipe(uglify('.'))
		.pipe(gulp.dest(paths.build + 'public/assets/js/'));
	// Admin Javascript Files
	gulp.src(paths.adminjs)
		.pipe(concat('wp-gistpen-admin.min.js'))
		.pipe(uglify('.'))
		.pipe(gulp.dest(paths.build + 'admin/assets/js/'));
})

gulp.task('install', function() {
	// Install composer dependencies
	composer({bin: 'composer', cwd: process.cwd()+'/'+paths.build});
	// Install bower dependencies
	bower({cwd: paths.build});
});

gulp.task('build', function(done) {
	runs(
		'clean',
		'copy',
		['minify', 'install'],
		done);
});