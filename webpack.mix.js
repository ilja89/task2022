const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application, as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('plugin/public');

mix.js('plugin/resources/assets/js/pages/instanceForm/instanceForm.js', 'js')
  .js('plugin/resources/assets/js/pages/courseSettings/courseSettings.js', 'js')
  .js('plugin/resources/assets/js/pages/popup/popup.js', 'js')
  .js('plugin/resources/assets/js/assignment.js', 'js')
  .js('plugin/resources/assets/js/packageWrappers/highlightJs.js', 'plugin/public/js/highlight.js')
  .sass('plugin/resources/assets/sass/assignment.scss', 'css')
  .sass('plugin/resources/assets/sass/instanceForm.scss', 'css')
  .sass('plugin/resources/assets/sass/popup/popup.scss', 'css')
  .sass('plugin/resources/assets/sass/courseSettings.scss', 'css')
  .sass('plugin/resources/assets/sass/highlight.scss', 'css')
  .minify([
    'plugin/public/js/instanceForm.js',
    'plugin/public/js/courseSettings.js',
    'plugin/public/js/popup.js',
    'plugin/public/js/assignment.js',
    'plugin/public/css/assignment.css',
    'plugin/public/css/instanceForm.css',
    'plugin/public/css/popup.css',
    'plugin/public/css/courseSettings.css',
    'plugin/public/css/highlight.css',
  ]);

let webpack = require('webpack');

mix.webpackConfig({
  plugins: [
    new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /en-gb/)
  ]
});

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
