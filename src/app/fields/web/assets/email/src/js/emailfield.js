/* global Craft */

if (typeof SproutEmailField === typeof undefined) {
  SproutEmailField = {};
}

SproutEmailField = Garnish.Base.extend({

  init: function(namespaceInputId, id, elementId, fieldHandle, fieldContext) {
    this.checkSproutEmailField(namespaceInputId, id, elementId, fieldHandle, fieldContext);
  },

  checkSproutEmailField: function(namespaceInputId, id, elementId, fieldHandle, fieldContext) {

    let sproutEmailFieldId = '#' + namespaceInputId;
    let sproutEmailButtonClass = '.' + id;

    // We use setTimeout to make sure our function works every time
    setTimeout(function() {
      // Set up data for the controller.
      let data = {
        'elementId': elementId,
        'fieldContext': fieldContext,
        'fieldHandle': fieldHandle,
        'value': $(sproutEmailFieldId).val()
      };

      // Query the controller so the regex validation is all done through PHP.
      Craft.postActionRequest('sprout-base-fields/fields/validate-email', data, function(response) {
        if (response.success) {
          $(sproutEmailButtonClass).addClass('fade');
          $(sproutEmailButtonClass + ' a').attr("href", "mailto:" + data.value);
        } else {
          $(sproutEmailButtonClass).removeClass('fade');
        }
      }, []);

    }, 500);
  }
});