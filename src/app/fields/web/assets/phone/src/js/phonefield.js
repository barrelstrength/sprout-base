/* global Craft */

if (typeof SproutPhoneField === typeof undefined) {
  SproutPhoneField = {};
}

SproutPhoneField = Garnish.Base.extend({

  init: function(namespaceInputId, countryId) {
    let sproutPhoneFieldId = '#' + namespaceInputId;
    let sproutPhoneCountryId = '#' + countryId;
    let sproutPhoneFieldButtonClass = sproutPhoneFieldId + '-field .compoundSelectText-text .sprout-phone-button';

    // We use setTimeout to make sure our function works every time
    setTimeout(function() {

      let phoneNumber = $(sproutPhoneFieldId).val();
      let country = $(sproutPhoneCountryId).val();

      let data = {
        value: {
          'country': country,
          'phone': phoneNumber
        }
      };

      // Determine if we should show Phone link on initial load
      validatePhoneNumber($(sproutPhoneFieldId).get(0), data);
    }, 500);

    $(sproutPhoneFieldId).on('input', function() {
      let currentPhoneField = this;
      let phoneNumber = $(this).val();
      let country = $(sproutPhoneCountryId).val();
      let data = {
        value: {
          'country': country,
          'phone': phoneNumber
        }
      };
      validatePhoneNumber(currentPhoneField, data);
    });

    function validatePhoneNumber(currentPhoneField, data) {
      Craft.postActionRequest('sprout-base-fields/fields/validate-phone', data, function(response) {
        if (response.success) {
          $(sproutPhoneFieldButtonClass).addClass('fade');
          $(sproutPhoneFieldButtonClass + ' a').attr("href", "tel:" + data.phone);
        } else {
          $(sproutPhoneFieldButtonClass).removeClass('fade');
        }
      }, [])
    }
  }
});


