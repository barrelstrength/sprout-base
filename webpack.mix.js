const mix = require('laravel-mix');

require('laravel-mix-polyfill');

/**
 * @param {Object} mix
 * @param {method} mix.sass
 * @param {method} mix.polyfill
 */
mix

  // base
  .sass(
    'src/config/web/assets/cp/src/scss/sproutcp.scss',
    'src/config/web/assets/cp/dist/css/sproutcp.css')
  .sass(
    'src/config/web/assets/landing/src/scss/landing.scss',
    'src/config/web/assets/landing/dist/css/landing.css')
  .js([
    'src/config/web/assets/groups/src/js/groups.js'
  ], 'src/web/assets/groups/dist/js/groups.js')
  .copy('src/config/web/assets/cp/src/images',
    'src/config/web/assets/cp/dist/images');

// fields
if (mix.inProduction()) {
  // we can import this file directly from node_modules but then
  // scss variables fail to autocomplete. So, we import this file
  // from a location that is available to the plugin, but only
  // want to copy it once.
  mix.copy(
    'node_modules/craftcms-sass/src/_mixins.scss',
    'lib/craftcms-sass/_mixins.scss'
  );
}

// fields
mix
  // Address
  .js([
      'src/app/fields/web/assets/address/src/js/AddressBox.js',
      'src/app/fields/web/assets/address/src/js/EditAddressModal.js'
    ],
    'src/app/fields/web/assets/address/dist/js/addressfield.js')
  .sass(
    'src/app/fields/web/assets/address/src/scss/addressfield.scss',
    'src/app/fields/web/assets/address/dist/css/addressfield.css'
  )

  // Email
  .js([
    'src/app/fields/web/assets/email/src/js/emailfield.js'
  ], 'src/app/fields/web/assets/email/dist/js/emailfield.js')
  .sass(
    'src/app/fields/web/assets/email/src/scss/emailfield.scss',
    'src/app/fields/web/assets/email/dist/css/emailfield.css'
  )

  // Phone
  .js([
    'src/app/fields/web/assets/phone/src/js/phonefield.js'
  ], 'src/app/fields/web/assets/phone/dist/js/phonefield.js')
  .sass(
    'src/app/fields/web/assets/phone/src/scss/phonefield.scss',
    'src/app/fields/web/assets/phone/dist/css/phonefield.css'
  )

  // Regular Expression
  .js([
    'src/app/fields/web/assets/regularexpression/src/js/regularexpressionfield.js'
  ], 'src/app/fields/web/assets/regularexpression/dist/js/regularexpressionfield.js')

  // Select Other
  .js([
    'src/app/fields/web/assets/selectother/src/js/SelectOtherField.js'
  ], 'src/app/fields/web/assets/selectother/dist/js/selectotherfield.js')
  .sass(
    'src/app/fields/web/assets/selectother/src/scss/select-other.scss',
    'src/app/fields/web/assets/selectother/dist/css/select-other.css'
  )

  // URL
  .js([
    'src/app/fields/web/assets/url/src/js/urlfield.js'
  ], 'src/app/fields/web/assets/url/dist/js/urlfield.js')
  .sass(
    'src/app/fields/web/assets/url/src/scss/urlfield.scss',
    'src/app/fields/web/assets/url/dist/css/urlfield.css'
  );

// reports
mix
  .sass(
    'src/app/reports/web/assets/reports/src/scss/reports.scss',
    'src/app/reports/web/assets/reports/dist/css/reports.css',
  )
  .sass(
    'src/app/reports/web/assets/reports/src/scss/visualizations.scss',
    'src/app/reports/web/assets/reports/dist/css/visualizations.css',
  )
  .options({
    processCssUrls: false
  })
  .js([
    'src/app/reports/web/assets/reports/src/js/reports.js',
    'src/app/reports/web/assets/reports/src/js/visualizations.js',
    'src/app/reports/web/assets/reports/src/js/visualization-settings.js'
  ], 'src/app/reports/web/assets/reports/dist/js/reports.js')
  .copy('src/app/reports/web/assets/reports/src/images',
    'src/app/reports/web/assets/reports/dist/images')
  .copy('node_modules/apexcharts/dist',
    'lib/apexcharts')
  .copy('node_modules/datatables.net/js',
    'lib/datatables.net');

// sent email
mix
  .sass(
    'src/app/sentemail/web/assets/sentemail/src/scss/sent-email.scss',
    'src/app/sentemail/web/assets/sentemail/dist/css/sent-email.css',
  )
  .copy('src/app/sentemail/web/assets/sentemail/src/images',
    'src/app/sentemail/web/assets/sentemail/dist/images');

// email
mix
  .sass(
    'src/app/email/web/assets/email/src/scss/charts-explorer.scss',
    'src/app/email/web/assets/email/dist/css/charts-explorer.css')
  .sass(
    'src/app/email/web/assets/email/src/scss/modal.scss',
    'src/app/email/web/assets/email/dist/css/modal.css')
  .sass(
    'src/app/email/web/assets/email/src/scss/sproutemail.scss',
    'src/app/email/web/assets/email/dist/css/sproutemail.css')
  .js([
    'src/app/email/web/assets/email/src/js/notification.js'
  ], 'src/app/email/web/assets/email/dist/js/notification.js')
  .js([
    'src/app/email/web/assets/email/src/js/sprout-modal.js'
  ], 'src/app/email/web/assets/email/dist/js/sprout-modal.js')
  .copy('src/app/email/web/assets/email/src/images',
    'src/app/email/web/assets/email/dist/images');

// redirects
mix
  .sass(
    'src/app/redirects/web/assets/redirects/src/scss/redirects.scss',
    'src/app/redirects/web/assets/redirects/dist/css/redirects.css',
  )
  .copy('src/app/redirects/web/assets/redirects/src/images',
    'src/app/redirects/web/assets/redirects/dist/images')
  .js([
    'src/app/redirects/web/assets/redirects/src/js/redirectindex.js'
  ], 'src/app/redirects/web/assets/redirects/dist/js/redirectindex.js');

// sitemaps
mix
  .sass(
    'src/app/sitemaps/web/assets/sitemaps/src/scss/sitemaps.scss',
    'src/app/sitemaps/web/assets/sitemaps/dist/css/sitemaps.css',
  )
  .js([
    'src/app/sitemaps/web/assets/sitemaps/src/js/settings.js',
    'src/app/sitemaps/web/assets/sitemaps/src/js/sitemaps.js',
  ], 'src/app/sitemaps/web/assets/sitemaps/dist/js/sitemaps.js');

// SEO
mix
  .sass(
    'src/app/metadata/web/assets/seo/src/scss/sproutseo.scss',
    'src/app/metadata/web/assets/seo/dist/css/sproutseo.css',
  )
  .js([
    'src/app/metadata/web/assets/seo/src/js/editable-table.js',
    'src/app/metadata/web/assets/seo/src/js/general.js',
    'src/app/metadata/web/assets/seo/src/js/meta-details-toggle.js',
    'src/app/metadata/web/assets/seo/src/js/schema.js',
    'src/app/metadata/web/assets/seo/src/js/website-identity.js'
  ], 'src/app/metadata/web/assets/seo/dist/js/sproutseo.js')
  .copy('src/app/metadata/web/assets/seo/src/images',
    'src/app/metadata/web/assets/seo/dist/images');

mix
  // Forms
  .js([
    'src/app/forms/web/assets/cp/src/js/editable-table.js',
    'src/app/forms/web/assets/cp/src/js/form-settings.js',
    'src/app/forms/web/assets/cp/src/js/field-layout-editor.js',
    'src/app/forms/web/assets/cp/src/js/field-modal.js',
    'src/app/forms/web/assets/cp/src/js/integration-modal.js',
    'src/app/forms/web/assets/cp/src/js/integrations.js',
    'src/app/forms/web/assets/cp/src/js/rule-modal.js',
  ], 'src/app/forms/web/assets/cp/dist/js/sproutforms-cp.js')

  // Entries Index
  .js([
    'src/app/forms/web/assets/cp/src/js/entries-index.js',
    'src/app/forms/web/assets/cp/src/js/entries-table-view.js',
  ], 'src/app/forms/web/assets/cp/dist/js/sprout-entries-index.js')
  .sass('src/app/forms/web/assets/cp/src/scss/charts.scss',
    'src/app/forms/web/assets/cp/dist/css/sproutforms-charts.css')
  .sass('src/app/forms/web/assets/cp/src/scss/forms-ui.scss',
    'src/app/forms/web/assets/cp/dist/css/sproutforms-forms-ui.css')
  .sass('src/app/forms/web/assets/cp/src/scss/cp.scss',
    'src/app/forms/web/assets/cp/dist/css/sproutforms-cp.css')
  .copy('src/app/forms/web/assets/cp/src/images',
    'src/app/forms/web/assets/cp/dist/images')

  // Form Templates
  .js([
    'src/app/forms/web/assets/formtemplates/src/js/accessibility.js',
  ], 'src/app/forms/web/assets/formtemplates/dist/js/accessibility.js')
  .js([
    'src/app/forms/web/assets/formtemplates/src/js/addressfield.js',
  ], 'src/app/forms/web/assets/formtemplates/dist/js/addressfield.js')
  .js([
    'src/app/forms/web/assets/formtemplates/src/js/disable-submit-button.js',
  ], 'src/app/forms/web/assets/formtemplates/dist/js/disable-submit-button.js')
  .js([
    'src/app/forms/web/assets/formtemplates/src/js/rules.js',
  ], 'src/app/forms/web/assets/formtemplates/dist/js/rules.js')
  .js([
    'src/app/forms/web/assets/formtemplates/src/js/submit-handler.js',
  ], 'src/app/forms/web/assets/formtemplates/dist/js/submit-handler.js')
  .polyfill({
    targets: '> 0.5%, last 2 versions, Firefox ESR'
  });

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.dump(); <-- Dump the generated webpack config object to the console.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   terser: {}, // Terser-specific options. https://github.com/webpack-contrib/terser-webpack-plugin#options
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
