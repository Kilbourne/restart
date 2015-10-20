/*!
* gulp
* $ npm install gulp-load-plugins gulp-ruby-sass  gulp-jshint  gulp-notify  browser-sync --save-dev
*/ 
var gulp = require('gulp'),
gulpLoadPlugins = require('gulp-load-plugins'),
browserSync = require('browser-sync'),
plugins = gulpLoadPlugins(),
reload = browserSync.reload,
jade = require('gulp-jade');

// Start the server
gulp.task('browser-sync', function() {
    browserSync({
        server: {
            baseDir: "./"
        }
    });
});

gulp.task('fileinclude', function() {
  gulp.src(['dev/index.html'])
    .pipe(plugins.fileInclude({
      prefix: '@@',
      basepath: '@file'
    }))
    .pipe(gulp.dest('./'));
});

gulp.task('styles', function() {
plugins.rubySass('dev/sass/giank.scss',{ style: 'expanded' })
.pipe(gulp.dest('dev/css'))
.pipe(plugins.notify({ message: 'sass compiltation complete' }));
});

gulp.task('styledoc', function() {
plugins.rubySass('cssdoc/inuit.scss',{ style: 'expanded' })
.pipe(gulp.dest('cssdoc'))
.pipe(plugins.notify({ message: 'sass compiltation complete' }));
});


gulp.task('styles1',['styles'],function() {
return  gulp.src(['dev/css/giank.css'])
.pipe(plugins.autoprefixer())
.pipe(plugins.minifyCss())
.pipe(gulp.dest('dist/css'))
.pipe(reload({stream:true}))
.pipe(plugins.notify({ message: 'Styles task complete' }));
});

gulp.task('styles2',function() {
return  gulp.src(['dev/css/giank.css'])
.pipe(plugins.autoprefixer())
.pipe(plugins.minifyCss())
.pipe(gulp.dest('dist/css'))
.pipe(reload({stream:true}))
.pipe(plugins.notify({ message: 'Styles task complete' }));
});
// Scripts\
gulp.task('uglify', function() {
return gulp.src('dev/js/*.js')
.pipe(plugins.uglify())
.pipe(gulp.dest('dev/js/min'))
});
gulp.task('scripts',['uglify'], function() {
return gulp.src('dev/js/min/*.js')
.pipe(plugins.concat('all.js'))
.pipe(gulp.dest('dist/js'))
.pipe(plugins.notify({ message: 'Scripts task complete' }));
});

gulp.task('ttf2woff', function(){
  gulp.src(['asset/fonts/**/*.ttf'])
    .pipe(plugins.ttf2woff())
    .pipe(gulp.dest('asset/fonts/'));
});
gulp.task('base', function(){
  gulp.src(['asset/fonts/**/*.*','!asset/fonts/**/*.zip'])
    .pipe(plugins.cssfont64())
    .pipe(gulp.dest('asset/fonts/'));
});
gulp.task('concatfont', function(){
  gulp.src(['asset/fonts/**/*.css'])
    .pipe(plugins.concatCss("bundle.css"))
    .pipe(gulp.dest('asset/fonts/'));
});

gulp.task('templates', function() {
  var YOUR_LOCALS = {};
 
  gulp.src('dev/jade/*.jade')
    .pipe(jade({
      locals: YOUR_LOCALS
    }))
    .pipe(gulp.dest('dev/html-partials/'))

});
gulp.task('watchtemp',['templates'],function(){
    gulp.watch('./tmp/*.jade',['templates']);    
})

gulp.task('default', ['browser-sync'], function () {
	gulp.start(['templates','styles1','scripts','fileinclude']);
	gulp.watch("bower_components/**/*").on("change", browserSync.reload);

    gulp.watch("dev/css/*.css",['styles2']);
    gulp.watch("dev/sass/*.scss",['styles']);
	gulp.watch('dev/js/*.js',['scripts']).on("change", browserSync.reload);
    gulp.watch("dev/*.html",['fileinclude']).on("change", browserSync.reload);
    gulp.watch("dev/jade/*.jade",['templates']);
    gulp.watch("dev/html-partials/*.html",['fileinclude']).on("change", browserSync.reload);

});
 


 
