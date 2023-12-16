const mix = require('laravel-mix');
const fs = require('fs');
const webp = require('laravel-mix-webp');

const dirRaiz = './app-assets';

mix.options({
  publicPath: './',
});

mix.webpackConfig({
  resolve: {
    modules: ['node_modules'],
    alias: {
      jquery: 'jquery/src/jquery',
    },
  },
});

/**
 * Javascripts
 */
mix.js('resources/js/app.js', dirRaiz + '/js').sourceMaps();

/**
 * Folhas de Estilo
 */
mix.sass('resources/scss/app.scss', dirRaiz + '/css').options({
  processCssUrls: false,
});

/**
 * Arquivos de Imagens
 */
mix.ImageWebp({
  from: 'resources/img',
  to: dirRaiz + '/images',
});

mix.copyDirectory('resources/img', dirRaiz + '/images');

/**
 * Slick Images
 */
mix.copyDirectory('node_modules/slick-carousel/slick/fonts/', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/slick-carousel/slick/ajax-loader.gif', dirRaiz + '/img');

/**
 *
 * Arquivos de fontes
 */
mix.copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts/', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/@phosphor-icons/web/src/bold/*', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/@phosphor-icons/web/src/fill/*', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/@phosphor-icons/web/src/light/*', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/@phosphor-icons/web/src/regular/*', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/@phosphor-icons/web/src/thin/*', dirRaiz + '/fonts');
mix.copyDirectory('node_modules/@phosphor-icons/web/src/duotone/*', dirRaiz + '/fonts');