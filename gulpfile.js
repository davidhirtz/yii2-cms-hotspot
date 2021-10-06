"use strict";

// Requirements.
var gulp=require('gulp');

var rename=require('gulp-rename');
var uglify=require('gulp-uglify');


// Options.
var options={
    autoprefixer:[
        'last 2 versions',
        'ie >= 11'
    ],
    sass:{
        errLogToConsole:true,
        outputStyle:'expanded'
    }
};


// JS.
function scripts()
{
    // noinspection JSUnresolvedFunction
    return gulp.src(['assets/admin/js/admin.js'], {base:'./'})
        .pipe(uglify())
        .pipe(rename({suffix:'.min'}))
        .pipe(gulp.dest('.'));
}

// Watcher.
function watch()
{
    gulp.watch(['assets/admin/js/admin.js'], scripts);
}

// Tasks.
gulp.task('scripts', scripts);

gulp.task('build', gulp.parallel(scripts));
gulp.task('default', gulp.series('build', watch));