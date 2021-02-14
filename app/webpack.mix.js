const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------§------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    //accelerate watch by ignoring node_modules:
    //https://laracasts.com/discuss/channels/elixir/laravel-mix-extremly-slow?page=0#
    watchOptions: {
        ignored: /node_modules/
    }
});

mix.js('resources/js/app.js', 'public/js')
    //http://randy.works/how-to-insert-svg-files-directly-into-your-vue-components
    .override(config => {
        config.module.rules.find(rule =>
            rule.test.test('.svg')
        ).exclude = /\.svg$/;

        config.module.rules.push({
            test: /\.svg$/,
            use: [{ loader: 'html-loader' }]
        })
    })
    //.sourceMaps()
;

mix.version();
