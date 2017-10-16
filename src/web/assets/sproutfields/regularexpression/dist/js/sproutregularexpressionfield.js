/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

function checkSproutRegularExpressionField(id, fieldHandle, fieldContext) {

	var sproutRegularExpressionFieldId = '#' + id;
	var sproutRegularExpressionClass = '.' + id;

	// We use setTimeout to make sure our function works every time
	setTimeout(function()
	{
		// Set up data for the controller.
		var data = {
			'fieldHandle': fieldHandle,
			'fieldContext': fieldContext,
			'value': $(sproutRegularExpressionFieldId).val()
		};

		// Query the controller so the regex validation is all done through PHP.
		Craft.postActionRequest('sprout-core/fields/regular-expression-validate', data, function(response) {
			if (response)
			{
				$(sproutRegularExpressionClass).addClass('fade');
			}
			else
			{
				$(sproutRegularExpressionClass).removeClass('fade');
			}
		});

	}, 500);
}