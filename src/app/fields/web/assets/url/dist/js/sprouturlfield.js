/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

function checkSproutUrlField(namespaceInputId, id, fieldHandle, fieldContext) {

    var sproutUrlFieldId = '#' + namespaceInputId;
    var sproutUrlButtonClass = '.' + id;

    // We use setTimeout to make sure our function works every time
    setTimeout(function() {
        // Set up data for the controller.
        var data = {
            'fieldHandle': fieldHandle,
            'fieldContext': fieldContext,
            'value': $(sproutUrlFieldId).val()
        };

        // Query the controller so the regex validation is all done through PHP.
        Craft.postActionRequest('sprout/fields/url-validate', data, function(response) {
            if (response) {
                $(sproutUrlButtonClass).addClass('fade');
                $(sproutUrlButtonClass + ' a').attr("href", data.value);
            }
            else {
                $(sproutUrlButtonClass).removeClass('fade');
            }
        });

    }, 500);
}