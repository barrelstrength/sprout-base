/* global Craft */

if (typeof SproutRegularExpressionField === typeof undefined) {
  SproutRegularExpressionField = {};
}

SproutRegularExpressionField = Garnish.Base.extend({

  init: function(id, fieldHandle, fieldContext) {
    this.checkSproutRegularExpressionField(id, fieldHandle, fieldContext);
  },

  checkSproutRegularExpressionField: function(id, fieldHandle, fieldContext) {

    let sproutRegularExpressionFieldId = '#' + id;
    let sproutRegularExpressionClass = '.' + id;

    // We use setTimeout to make sure our function works every time
    setTimeout(function() {
      // Set up data for the controller.
      let data = {
        'fieldHandle': fieldHandle,
        'fieldContext': fieldContext,
        'value': $(sproutRegularExpressionFieldId).val()
      };

      // Query the controller so the regex validation is all done through PHP.
      Craft.postActionRequest('sprout-base-fields/fields/validate-regular-expression', data, function(response) {
        if (response.success) {
          $(sproutRegularExpressionClass).addClass('fade');
        } else {
          $(sproutRegularExpressionClass).removeClass('fade');
        }
      }, []);

    }, 500);
  }
});