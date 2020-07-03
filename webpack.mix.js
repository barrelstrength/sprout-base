const mix = require('laravel-mix');

require('laravel-mix-polyfill');

/**
 * @param {Object} mix
 * @param {method} mix.ts
 * @param {method} mix.sass
 * @param {method} mix.polyfill
 */
mix

  // General Resources
  .ts([
    'src/web/assets/src/js/sproutcp/Groups.ts'
  ], 'src/web/assetbundles/groups/dist/js/Groups.js')
  .sass(
    'src/web/assets/src/scss/sproutcp/sprout-cp.scss',
    'src/web/assetbundles/sproutcp/dist/css/sprout-cp.css')
  .sass(
    'src/web/assets/src/scss/sproutcp/landing.scss',
    'src/web/assetbundles/landing/dist/css/landing.css')

  // Address Field
  .js([
      'src/web/assets/src/js/fields/AddressBox.js',
      'src/web/assets/src/js/fields/EditAddressModal.js'
    ],
    'src/web/assetbundles/addressfield/dist/js/AddressField.js')
  .sass(
    'src/web/assets/src/scss/fields/address-field.scss',
    'src/web/assetbundles/addressfield/dist/css/address-field.css')

  // Email Field
  .js([
    'src/web/assets/src/js/fields/EmailField.js'
  ], 'src/web/assetbundles/emailfield/dist/js/EmailField.js')
  .sass(
    'src/web/assets/src/scss/fields/email-field.scss',
    'src/web/assetbundles/emailfield/dist/css/email-field.css')

  // Phone Field
  .js([
    'src/web/assets/src/js/fields/PhoneField.js'
  ], 'src/web/assetbundles/phonefield/dist/js/PhoneField.js')
  .sass(
    'src/web/assets/src/scss/fields/phone-field.scss',
    'src/web/assetbundles/phonefield/dist/css/phone-field.css')

  // Regular Expression Field
  .js([
    'src/web/assets/src/js/fields/RegularExpressionField.js'
  ], 'src/web/assetbundles/regularexpressionfield/dist/js/RegularExpressionField.js')

  // Select Other Field
  .js([
    'src/web/assets/src/js/fields/SelectOtherField.js'
  ], 'src/web/assetbundles/selectotherfield/dist/js/SelectOtherField.js')
  .sass(
    'src/web/assets/src/scss/fields/select-other-field.scss',
    'src/web/assetbundles/selectotherfield/dist/css/select-other-field.css')

  // URL Field
  .js([
    'src/web/assets/src/js/fields/UrlField.js'
  ], 'src/web/assetbundles/urlfield/dist/js/UrlField.js')
  .sass(
    'src/web/assets/src/scss/fields/url-field.scss',
    'src/web/assetbundles/urlfield/dist/css/url-field.css')

  // Reports
  .js([
    'src/web/assets/src/js/reports/Reports.js',
    'src/web/assets/src/js/reports/Visualizations.js',
    'src/web/assets/src/js/reports/VisualizationSettings.js'
  ], 'src/web/assetbundles/reports/dist/js/Reports.js')
  .sass(
    'src/web/assets/src/scss/reports/reports.scss',
    'src/web/assetbundles/reports/dist/css/reports.css')
  .sass(
    'src/web/assets/src/scss/reports/visualizations.scss',
    'src/web/assetbundles/reports/dist/css/visualizations.css')

  // Campaigns
  .ts([
    'src/web/assets/src/js/campaigns/CopyPaste.ts'
  ], 'src/web/assetbundles/copypastemailer/dist/js/CopyPaste.js')
  .sass(
    'src/web/assets/src/scss/campaigns/copy-paste.scss',
    'src/web/assetbundles/copypastemailer/dist/css/copy-paste.css')

  // Email
  .js([
    'src/web/assets/src/js/email/NotificationEvent.js'
  ], 'src/web/assetbundles/email/dist/js/NotificationEvent.js')
  .js([
    'src/web/assets/src/js/email/SproutModal.js'
  ], 'src/web/assetbundles/email/dist/js/SproutModal.js')
  .sass(
    'src/web/assets/src/scss/email/charts-explorer.scss',
    'src/web/assetbundles/email/dist/css/charts-explorer.css')
  .sass(
    'src/web/assets/src/scss/email/modal.scss',
    'src/web/assetbundles/email/dist/css/modal.css')
  .sass(
    'src/web/assets/src/scss/email/sproutemail.scss',
    'src/web/assetbundles/email/dist/css/sproutemail.css')

  // Redirects
  .js([
    'src/web/assets/src/js/redirects/RedirectIndex.js'
  ], 'src/web/assetbundles/redirects/dist/js/RedirectIndex.js')
  .sass(
    'src/web/assets/src/scss/redirects/redirects.scss',
    'src/web/assetbundles/redirects/dist/css/redirects.css')

  // Sitemaps
  .js([
    'src/web/assets/src/js/sitemaps/Sitemaps.js',
    'src/web/assets/src/js/sitemaps/SitemapSettings.js',
  ], 'src/web/assetbundles/sitemaps/dist/js/Sitemaps.js')
  .sass(
    'src/web/assets/src/scss/sitemaps/sitemaps.scss',
    'src/web/assetbundles/sitemaps/dist/css/sitemaps.css')

// SEO
  .js([
    'src/web/assets/src/js/metadata/EditableTable.js',
    'src/web/assets/src/js/metadata/General.js',
    'src/web/assets/src/js/metadata/MetaDetailsToggle.js',
    'src/web/assets/src/js/metadata/Schema.js',
    'src/web/assets/src/js/metadata/WebsiteIdentity.js'
  ], 'src/web/assetbundles/metadata/dist/js/Metadata.js')
  .sass(
    'src/web/assets/src/scss/metadata/metadata.scss',
    'src/web/assetbundles/metadata/dist/css/metadata.css')

  // Forms
  .js([
    'src/web/assets/src/js/forms/EditableTable.js',
    'src/web/assets/src/js/forms/FormSettings.js',
    'src/web/assets/src/js/forms/FieldLayoutEditor.js',
    'src/web/assets/src/js/forms/FieldModal.js',
    'src/web/assets/src/js/forms/IntegrationModal.js',
    'src/web/assets/src/js/forms/Integrations.js',
    'src/web/assets/src/js/forms/RuleModal.js',
  ], 'src/web/assetbundles/forms/dist/js/Forms.js')
  .sass('src/web/assets/src/scss/forms/forms-ui.scss',
    'src/web/assetbundles/forms/dist/css/forms-ui.css')
  .sass('src/web/assets/src/scss/forms/forms.scss',
    'src/web/assetbundles/forms/dist/css/forms.css')

  // Form Templates
  .js([
    'src/web/assets/src/js/formtemplates/Accessibility.js',
  ], 'src/web/public/formtemplates/dist/js/Accessibility.js')
  .js([
    'src/web/assets/src/js/formtemplates/AddressField.js',
  ], 'src/web/public/formtemplates/dist/js/AddressField.js')
  .js([
    'src/web/assets/src/js/formtemplates/DisableSubmitButton.js',
  ], 'src/web/public/formtemplates/dist/js/DisableSubmitButton.js')
  .js([
    'src/web/assets/src/js/formtemplates/Rules.js',
  ], 'src/web/public/formtemplates/dist/js/Rules.js')
  .js([
    'src/web/assets/src/js/formtemplates/SubmitHandler.js',
  ], 'src/web/public/formtemplates/dist/js/SubmitHandler.js')

  // Move assets to a location where they can referenced in asset bundles
  // A bit overkill to do this every time, but so goes for now.
  .copy('node_modules/apexcharts/dist',
    'lib/apexcharts')
  .copy('node_modules/datatables.net/js',
    'lib/datatables.net')
  .copy('node_modules/craftcms-sass/src/_mixins.scss',
    'lib/craftcms-sass/_mixins.scss')
  .copy('src/web/assets/images/reports',
    'src/web/assetbundles/reports/dist/images')

  // Make CSS URL references work
  .options({
    processCssUrls: false
  })

  // Improve compatibility for front-end form resources
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
