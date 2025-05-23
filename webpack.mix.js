const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ])
    .sass('resources/sass/app.scss', 'public/css')
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve('resources/js'),
            },
        },
        output: {
            chunkFilename: 'js/chunks/[name].js',
        },
    })
    .disableNotifications()
    .version();

// Copiar imágenes y fuentes
mix.copy('resources/images', 'public/images')
   .copy('resources/fonts', 'public/fonts');

// En modo de desarrollo
if (!mix.inProduction()) {
    mix.sourceMaps();
}
