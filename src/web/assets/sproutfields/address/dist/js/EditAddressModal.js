/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

if (typeof Craft.SproutBase === typeof undefined) {
    Craft.SproutBase = {};
}

Craft.SproutBase.EditAddressModal = Garnish.Modal.extend(
    {
        id: null,
        init: function($addressForm, settings, target) {

            this.setSettings(settings, Garnish.Modal.defaults);

            this.$form = $('<form class="sproutaddress-modal modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
            this.$body = $('<div class="body sproutaddress-body"></div>').appendTo(this.$form);
            this.$bodyMeta = $('<div class="meta"></div>').appendTo(this.$body);

            this.$addressForm = $addressForm;
            this.$addressFormHtml = $addressForm.html();

            $(this.$addressFormHtml).appendTo(this.$bodyMeta);

            this.modalTitle = Craft.t('sprout-base', 'Update Address');
            this.submitLabel = Craft.t('sprout-base', 'Update');

            // Footer and buttons
            var $footer = $('<div class="footer"/>').appendTo(this.$form);
            var $btnGroup = $('<div class="btngroup left"/>').appendTo($footer);
            var $mainBtnGroup = $('<div class="btngroup right"/>').appendTo($footer);
            this.$updateBtn = $('<input type="button" class="btn submit" value="' + this.submitLabel + '"/>').appendTo($mainBtnGroup);
            this.$footerSpinner = $('<div class="spinner right hidden"/>').appendTo($footer);
            this.$cancelBtn = $('<input type="button" class="btn" value="' + Craft.t('sprout-base', 'Cancel') + '"/>').appendTo($btnGroup);

            this.addListener(this.$cancelBtn, 'click', 'hide');
            this.addListener(this.$updateBtn, 'click', $.proxy(function(ev) {
                ev.preventDefault();

                this.updateAddress();
            }, this));

            var self = this;

            this.addListener('.sproutaddress-country-select select', 'change', function(ev) {
                this.changeFormInput(ev.currentTarget);
            });

            // Select the country dropdown again for some reason it does not get right value at the form box
            this.$form.find(".sproutaddress-country-select select").val(this.settings.countryCode);

            this.base(this.$form, settings);
        },
        changeFormInput: function(target) {
            var $target = $(target);
            var countryCode = $(target).val();
            var $parents = $target.parents('.sproutaddress-body');

            var self = this;
            Craft.postActionRequest('sprout-base/address/change-form', {
                countryCode: countryCode,
                namespace: this.settings.namespace
            }, $.proxy(function(response) {
                $parents.find('.field-address-input').remove();

                var $addressIdInput = $parents.find('.field-address-id');
                $parents.find('.field-address-id').remove();

                if (response.html) {
                    $parents.find('.meta').append(response.html);
                }

                // Add our input back at the bottom
                $parents.find('.meta').append($addressIdInput);
            }, this))
        },
        enableUpdateBtn: function() {
            this.$updateBtn.removeClass('disabled');
        },
        disableUpdateBtn: function() {
            this.$updateBtn.addClass('disabled');
        },
        showFooterSpinner: function() {
            this.$footerSpinner.removeClass('hidden');
        },

        hideFooterSpinner: function() {
            this.$footerSpinner.addClass('hidden');
        },
        updateAddress: function() {

            var namespace = this.settings.namespace;

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

            var formValues = {};

            var self = this;

            $.each(formKeys, function(index, el) {
                formValues[el] = self.$form.find("[name='" + namespace + "[" + el + "]']").val()
            });

            formValues.id = this.settings.addressInfoId;

            var data = {
                formValues: formValues
            };

            if (this.settings.source != null) {
                data.source = this.settings.source;
            }

            data.namespace = this.settings.namespace;

            this.settings.onSubmit(data, $.proxy(function(errors) {

                $.each(errors, function(index, val) {

                    var errorHtml = "<ul class='errors'>";

                    var $element = self.$form.find("[name='" + namespace + "[" + index + "]']");
                    $element.parent().addClass('errors');

                    errorHtml += "<li>" + val + "</li>";
                    errorHtml += "</ul>";

                    if ($element.parent().find('.errors') != null) {
                        $element.parent().find('.errors').remove();
                    }

                    $element.after(errorHtml)
                })
            }))
        },
        defaults: {
            onSubmit: $.noop,
            onUpdate: $.noop
        }
    });
