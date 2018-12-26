/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

if (typeof Craft.SproutBase === typeof undefined) {
    Craft.SproutBase = {};
}
(function($) {

    // Set all the standard Craft.SproutBase.* stuff
    $.extend(Craft.SproutBase,
        {
            initFields: function($container) {
                $('.sproutaddressinfo-box', $container).SproutAddressBox();
            }
        });

    // -------------------------------------------
    //  Custom jQuery plugins
    // -------------------------------------------

    $.extend($.fn,
        {
            SproutAddressBox: function() {
                var $container = $(this);
                return this.each(function() {
                    new Craft.SproutBase.AddressBox($container);
                });
            }
        });

    Craft.SproutBase.AddressBox = Garnish.Base.extend({

        $addressBox: null,

        $addButtons: null,
        $editButtons: null,
        $addressFormat: null,

        $addButton: null,
        $updateButton: null,
        $clearButton: null,
        $queryButton: null,

        addressInfoId: null,
        addressInfo: null,
        $addressForm: null,
        countryCode: null,
        actionUrl: null,
        $none: null,
        modal: null,

        init: function($addressBox, settings) {

            this.$addressBox = $addressBox;

            this.$addButton = this.$addressBox.find('.address-add-button a');
            this.$updateButton = this.$addressBox.find('.address-edit-buttons a.update-button');
            this.$clearButton = this.$addressBox.find('.address-edit-buttons a.clear-button');
            this.$queryButton = $('.query-button');

            this.$addButtons = this.$addressBox.find('.address-add-button');
            this.$editButtons = this.$addressBox.find('.address-edit-buttons');
            this.$addressFormat = this.$addressBox.find('.address-format');

            this.settings = settings;

            if (this.settings.namespace == null) {
                this.settings.namespace = 'address';
            }

            this.addressInfoId = this.$addressBox.data('addressinfoid');

            this.renderAddress();

            this.addListener(this.$addButton, 'click', 'editAddressBox');
            this.addListener(this.$updateButton, 'click', 'editAddressBox');
            this.addListener(this.$clearButton, 'click', 'clearAddressBox');
            this.addListener(this.$queryButton, 'click', 'queryAddressCoordinatesFromGoogleMaps');
        },

        renderAddress: function() {

            if (this.addressInfoId == '' || this.addressInfoId == null) {
                this.$addButtons.removeClass('hidden');
                this.$editButtons.addClass('hidden');
                this.$addressFormat.addClass('hidden');
            }
            else {

                this.$addButtons.addClass('hidden');
                this.$editButtons.removeClass('hidden');
                this.$addressFormat.removeClass('hidden');
            }

            this.$addressForm = this.$addressBox.find('.sproutaddressfield-form-fields');

            this.getAddressFormFields();

            this.actionUrl = Craft.getActionUrl('sprout/fields-address/update-address-form-html');
        },

        editAddressBox: function(ev) {

            ev.preventDefault();

            var source = null;

            if (this.settings.source != null) {
                source = this.settings.source;
            }

            this.$target = $(ev.currentTarget);

            var countryCode = this.$addressForm.find('.sproutaddressfield-country-select select').val();

            this.modal = new Craft.SproutBase.EditAddressModal(this.$addressForm, {
                onSubmit: $.proxy(this, 'getAddressDisplayHtml'),
                countryCode: countryCode,
                actionUrl: this.actionUrl,
                addressInfoId: this.addressInfoId,
                namespace: this.settings.namespace,
                source: source
            }, this.$target);

        },

        getAddressDisplayHtml: function(data) {

            var self = this;

            Craft.postActionRequest('sprout/fields-address/get-address-display-html', data, $.proxy(function(response) {
                if (response.result == true) {

                    this.$addressBox.find('.address-format').html(response.html);
                    self.$addressForm.empty();
                    self.$addressForm.append(response.countryCodeHtml);
                    self.$addressForm.append(response.addressFormHtml);

                    self.$addButtons.addClass('hidden');
                    self.$editButtons.removeClass('hidden');
                    self.$addressFormat.removeClass('hidden');

                    this.modal.hide();
                    this.modal.destroy();
                }
                else {
                    Garnish.shake(this.modal.$form);
                    var errors = response.errors;
                    $.each(errors, function(key, value) {
                        $.each(value, function(key2, value2) {
                            Craft.cp.displayError(Craft.t('sprout-base', value2));
                        });
                    });
                }

            }, this));
        },

        getAddressFormFields: function() {

            var self = this;

            var defaultCountryCode = this.$addressBox.data('defaultcountrycode');
            var showCountryDropdown = this.$addressBox.data('showcountrydropdown');

            Craft.postActionRequest('sprout/fields-address/get-address-form-fields-html', {
                addressInfoId: this.addressInfoId,
                defaultCountryCode: defaultCountryCode,
                showCountryDropdown: showCountryDropdown,
                namespace: this.settings.namespace
            }, $.proxy(function(response) {

                this.$addressBox.find('.address-format .spinner').remove();
                self.$addressBox.find('.address-format').empty();
                self.$addressBox.find('.address-format').append(response.html);

            }, this));
        },

        clearAddressBox: function(ev) {
            ev.preventDefault();

            var self = this;

            this.$addButtons.removeClass('hidden');
            this.$editButtons.addClass('hidden');
            this.$addressFormat.addClass('hidden');

            this.$addressForm.find("[name='" + this.settings.namespace + "[delete]']").val(1);

            self.addressInfoId = null;

            this.emptyForm();
            this.getAddressFormFields();
        },
        emptyForm: function() {

            var formKeys = [
                'countryCode',
                'administrativeArea',
                'locality',
                'dependentLocality',
                'postalCode',
                'sortingCode',
                'address1',
                'address2'
            ];

            var self = this;

            $.each(formKeys, function(index, el) {
                self.$addressBox.find("[name='" + self.settings.namespace + "[" + el + "]']").attr('value', '')
            });
        },
        queryAddressCoordinatesFromGoogleMaps: function(ev) {

            ev.preventDefault();

            var self = this;
            var spanValues = [];

            $(".address-format").each(function() {
                spanValues.push($(this).text());
            });

            self.addressInfo = spanValues.join("|");

            if ($('.address-format').is(':hidden')) {
                Craft.cp.displayError(Craft.t('sprout-base', 'Please add an address'));
                return false;
            }

            var data = {
                addressInfo: self.addressInfo
            };

            Craft.postActionRequest('sprout/fields-address/query-address-coordinates-from-google-maps', data, $.proxy(function(response) {
                if (response.result == true) {
                    var latitude = response.geo.latitude;
                    var longitude = response.geo.longitude;
                    // @todo - add generic name?
                    $("input[name='sproutseo[globals][identity][latitude]']").val(latitude);
                    $("input[name='sproutseo[globals][identity][longitude]']").val(longitude);

                    Craft.cp.displayNotice(Craft.t('sprout-base', 'Latitude and Longitude updated.'));
                }
                else {
                    Craft.cp.displayError(Craft.t('sprout-base', 'Unable to find the address: ' + response.errors));
                }
            }, this))
        }
    })
})(jQuery);