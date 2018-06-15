var gulp = require('gulp');

gulp.task('default', function () {
    // 将你的默认的任务代码放在这
    gulp.src('public/js/layer/layer.js')
        .pipe(uglify())
        .pipe(minify())
        .pipe(gulp.dest('public/js/layer/layer.min.js'));
});


