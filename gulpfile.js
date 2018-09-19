var gulp = require('gulp');
var uglify = require('gulp-uglify');
var minify = require('gulp-minify');

gulp.task('default', function () {
    // 将你的默认的任务代码放在这
    gulp.src('public/js/meTables/*.js')
        .pipe(minify({
            ext: {
                min: ".min.js"
            }
        }))
        .pipe(gulp.dest('public/js/meTables/'));
});


