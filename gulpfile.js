const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

elixir.config.publicPath = 'plugin/public';
elixir.config.assetsPath = 'plugin/resources/assets';

elixir((mix) => {
    mix.sass('app.scss')
       .sass('assignment.scss')
       .sass('instanceForm.scss')
       .webpack('app.js')
       .version([
           'css/app.css',
           'css/assignment.css',
           'css/instanceForm.css'
       ]);
});
