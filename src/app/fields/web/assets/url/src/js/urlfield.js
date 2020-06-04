/* global Craft */

if (typeof SproutUrlField === typeof undefined) {
  SproutUrlField = {};
}

SproutUrlField = Garnish.Base.extend({

  init: function(namespaceInputId, id, elementId, fieldHandle, fieldContext) {
    this.checkSproutUrlField(namespaceInputId, id, elementId, fieldHandle, fieldContext);
  },

  checkSproutUrlField: function(namespaceInputId, id, elementId, fieldHandle, fieldContext) {

    let sproutUrlFieldId = '#' + namespaceInputId;
    let sproutUrlButtonClass = '.' + id;

    // We use setTimeout to make sure our function works every time
    setTimeout(function() {
      // Set up data for the controller.
      let data = {
        'elementId': elementId,
        'fieldHandle': fieldHandle,
        'fieldContext': fieldContext,
        'value': $(sproutUrlFieldId).val()
      };

      // Query the controller so the regex validation is all done through PHP.
      Craft.postActionRequest('sprout-base-fields/fields/validate-url', data, function(response) {
        if (response.success) {
          $(sproutUrlButtonClass).addClass('fade');
          $(sproutUrlButtonClass + ' a').attr("href", data.value);
        } else {
          $(sproutUrlButtonClass).removeClass('fade');
        }
      }, []);

    }, 500);
  }
});