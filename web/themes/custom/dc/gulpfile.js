var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var reload = browserSync.reload;
var connect = require('gulp-connect-php');
var sass = require('gulp-sass')(require('sass'));
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var bulkSass = require('gulp-sass-bulk-import');


gulp.task('sass', done => {
    gulp.src('./scss/init.scss')
    .pipe(sourcemaps.init())
    .pipe(bulkSass())
    .pipe(sass())
    .pipe(autoprefixer({
        cascade: false
    }))
    .pipe(concat('style.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./css'))
    .pipe(browserSync.stream());
});

gulp.task('watch', done => {
    gulp.watch('./scss/**/*.scss').on('change', gulp.series('sass'));
    gulp.watch('./templates/**/*.scss').on('change', gulp.series('sass'));

    gulp.watch('/templates/**/*.html.twig').on('change', browserSync.reload);
    
    connect.server({}, function() {
        browserSync.init({
            proxy: 'https://127.0.0.1:61842'
        });
    });
});


gulp.task('build', done => {
    gulp.src('./scss/init.scss')
    .pipe(sourcemaps.init())
    .pipe(bulkSass())
    .pipe(sass())
    .pipe(autoprefixer({
        cascade: false
    }))
    .pipe(concat('style.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./css'));
    done();
});