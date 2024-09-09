const mix = require('laravel-mix');

require('laravel-mix-polyfill');

mix.options({
    manifest: `../mix-manifest.json`
})
    .webpackConfig({
        devtool: "inline-source-map"
    })
    .copy('resources/images', 'public/dist/img')
    .copy('resources/js', 'public/dist/js')
    .copy('resources/js/libraries', 'public/dist/js/libraries')
    .copy('resources/css/libraries', 'public/dist/css/libraries')
    // .js('resources/js/app.js', 'public/dist/js')
    .js('resources/js/helpers/*', 'public/dist/js/helpers')
    .js('resources/js/data-table.js', 'public/dist/js')
    .js('resources/js/manager-product.js', 'public/dist/js/')
    .js('resources/js/manager-menu.js', 'public/dist/js/')
    .js('resources/js/new-staff.js', 'public/dist/js/')
    .js('resources/js/printer.js', 'public/dist/js/')
    .js('resources/js/slug.js', 'public/dist/js/')
    .js('resources/js/manager.js', 'public/dist/js/')
    .js('resources/js/products.js', 'public/dist/js/')
    .js('resources/js/manager/dashboard.js', 'public/dist/js/manager')
    .js('resources/js/manager/food-types.js', 'public/dist/js/manager')
    .js('resources/js/manager/settings.js', 'public/dist/js/manager')
    .js('resources/js/admin/*', 'public/dist/js/admin')
    .js('resources/js/staff/*', 'public/dist/js/staff')
    .js('resources/js/general.js', 'public/dist/js/')
    // Customer
    .js('resources/js/customer/cart.js', 'public/dist/js/customer')
    .js('resources/js/customer/menu.js', 'public/dist/js/customer')
    .js('resources/js/customer/order.js', 'public/dist/js/customer')
    .postCss('resources/css/app.css', 'public/dist/css', [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ])
    .sass('resources/sass/app.scss', 'public/scss')
    .sass('resources/sass/new-staff.scss', 'public/scss')
    .copyDirectory('resources/fonts', 'public/dist/fonts')
    .version()
    .polyfill({
        enabled: true,
        useBuiltIns: "entry",
        targets: "iOS 11, > 0.25%, not dead",
    });
