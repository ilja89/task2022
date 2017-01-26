let mix = require('laravel-mix').mix;

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix.setPublicPath('plugin/public');

mix.js('plugin/resources/assets/js/app.js', 'js')
   .js('plugin/resources/assets/js/instanceForm.js', 'js')
    .js('plugin/resources/assets/js/courseSettings.js', 'js')
   .sass('plugin/resources/assets/sass/app.scss', 'css')
   .sass('plugin/resources/assets/sass/assignment.scss', 'css')
   .sass('plugin/resources/assets/sass/instanceForm.scss', 'css')
   .minify([
       'plugin/public/js/app.js',
       'plugin/public/css/app.css',
       'plugin/public/css/assignment.css',
       'plugin/public/css/instanceForm.css'
   ]);

// Full API
// mix.js(src, output);
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.combine(files, destination);
// mix.copy(from, to);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public'); <-- Useful for Node apps.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
