const gulp = require("gulp"),
    scss = require("gulp-sass"),
    preFixer = require("gulp-autoprefixer"),
    uglify = require("gulp-uglifyes"),
    babel = require("gulp-babel"),
    sourceMap = require("gulp-sourcemaps"),
    imgmin = require("gulp-imagemin"),
    concat = require("gulp-concat"),
    browserSync = require("browser-sync").create();

gulp.task("compileScss",async ()=>{
    gulp.src("./src/scss/**/*.scss")
        .pipe(scss({outputStyle:"expended"}))
        .pipe(sourceMap.init())
        .pipe(preFixer({"overrideBrowserslist": ["last 2 version", "> 5%", "IE 10"]}))
        .pipe(sourceMap.write('.'))
        .pipe(gulp.dest('./assets/css/'))
});

gulp.task("compileJs",async ()=>{
   gulp.src("./src/js/main.js")
       .pipe(babel({presets:['@babel/env']})).on("error",(e)=>{console.log(`Error Compiling Js:${e}`)})
       .pipe(gulp.dest("./assets/js/"))
});
gulp.task("compileImgs",async ()=>{
   gulp.src("./src/imgs/**/*")
       .pipe(imgmin())
       .pipe(gulp.dest("./assets/imgs"));
});

gulp.task("compileFonts",async ()=>{
   gulp.src("./src/fonts/")
       .pipe(gulp.dest("./assets/fonts/"));
});

gulp.task('compileHtml',()=>{
    return gulp.src('./src/**.html')
        .pipe(gulp.dest('./dist'))
});
gulp.task("minifyCss",async ()=>{
   gulp.src("./src/css/plugins/*.css")
       .pipe(sourceMap.init())
       .pipe(concat("css-bundle.css"))
       .pipe(sourceMap.write("."))
       .pipe(gulp.dest('./assets/css/'))
});
gulp.task("minifyJs",async ()=>{
    gulp.src(['src/js/jquery.min.js','src/js/migrate.min.js','src/js/popper.min.js','src/js/plugins/*.js'])
        .pipe(concat('js-bundle.js'))
        .pipe(babel())
        .pipe(uglify().on('error',(e)=>{console.log(e)}))
        .pipe(gulp.dest('./assets/js/'))
});

gulp.task("server",async function () {
    browserSync.init({
        server:{
            baseDir:'./'
        }
    });
   gulp.watch("./src/scss/**/*.scss",gulp.series("compileScss")).on("change",browserSync.reload);
   gulp.watch("./src/js/**.js",gulp.series("compileJs")).on("change",browserSync.reload);
   gulp.watch("./src/imgs/**",gulp.series("compileImgs")).on("change",browserSync.reload);
    gulp.watch('./src/fonts/**/*', gulp.series("compileFonts")).on('change',browserSync.reload);
    gulp.watch('./src/*.html', gulp.series("compileHtml")).on('change',browserSync.reload);
});
