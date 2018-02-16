/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

(function($) {

    Craft.PhoneInputMask = Garnish.Base.extend(
        {
            init: function(namespaceInputId, countryId) {

                var sproutPhoneFieldId = '#' + namespaceInputId;
                var sproutPhoneCountryId = '#' + countryId;

                // We use setTimeout to make sure our function works every time
                setTimeout(function() {

                    var phoneNumber = $(sproutPhoneFieldId).val();
                    var country = $(sproutPhoneCountryId).val();

                    var data = {
                        'country': country,
                        'phone': phoneNumber
                    };

                    // Determine if we should show Phone link on initial load
                    validatePhoneNumber($(sproutPhoneFieldId), phoneNumber, data);
                }, 500);

                $(sproutPhoneFieldId).on('input', function() {
                    var currentPhoneField = this;
                    var phoneNumber = $(this).val();
                    var country = $(sproutPhoneCountryId).val();
                    var data = {
                        'country': country,
                        'phone': phoneNumber
                    };
                    validatePhoneNumber(currentPhoneField, phoneNumber, data);
                });

                function validatePhoneNumber(currentPhoneField, phoneNumber, data) {

                    Craft.postActionRequest('sprout-base/fields/phone-validate', data, function(response) {
                        if (response) {
                            showCallText(phoneNumber, currentPhoneField);
                        }
                        else {
                            $('.sprout-phone-button').html('');
                        }
                    })
                }

                // Show Call Phone Text
                function showCallText(phoneNumber, currentPhoneField) {

                    if (phoneNumber == '') {
                        return;
                    }
                    $('.sprout-phone-button').addClass('fade');

                    $('.sprout-phone-button').html('<a href="tel:' + phoneNumber +
                        '" target="_blank" class="fontello-icon">&#xe802;</a>');

                    $(currentPhoneField).addClass('complete');
                }
            }
        });

})(jQuery);


