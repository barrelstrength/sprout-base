/* global Craft */

if (typeof Craft.SproutFields === typeof undefined) {
  Craft.SproutFields = {};
}

(function($) {

// Set all the standard Craft.SproutFields.* stuff
  $.extend(Craft.SproutFields,
    {
      initFields: function($container) {
        $('.sprout-selectother', $container).sproutSelectOther();
      }
    });

// -------------------------------------------
//  Custom jQuery plugins
// -------------------------------------------

  $.extend($.fn, {
    sproutSelectOther: function() {
      return this.each(function() {
        if (!$.data(this, 'sprout-selectother')) {
          new Craft.SproutFields.SelectOtherField(this);
        }
      });
    }
  });

  Craft.SproutFields.SelectOtherField = Garnish.Base.extend({
    $container: null,

    $dropdownField: null,
    $textField: null,
    $clearIcon: null,

    init: function(container) {
      this.$container = $(container);

      this.$dropdownField = this.$container.find('.sprout-selectotherdropdown select');
      this.$textField = this.$container.find('.sprout-selectothertext input');
      this.$clearIcon = this.$container.find('.sprout-selectothertext .clear');

      this.addListener(this.$dropdownField, 'change', 'handleSelectOtherChange');
      this.addListener(this.$clearIcon, 'click', 'handleCancelOther');
    },

    handleSelectOtherChange: function() {
      let selectedValue = this.$dropdownField.val();

      if (selectedValue === 'custom') {
        // Hide the Select Field and it's wrapping div
        this.$dropdownField.parent().addClass('hidden');

        // Show the Text Field and display the existing value for editing
        this.$textField.parent().removeClass('hidden');
        if (this.$textField.val().indexOf('{') > -1) {
          // If the setting is using custom Twig syntax, don't clear the field
          this.$textField.focus().select();
        } else {
          // If the setting is not using Twig syntax, clear the field so the user sees the placeholder example
          this.$textField.val('').focus().select();
        }

      } else {
        // Store the selected value in the other field, as it takes precedence
        this.$textField.val(selectedValue);
      }

    },

    handleCancelOther: function() {

      // Hide our Custom text field
      this.$textField.parent().addClass('hidden');

      // Show our dropdown again
      this.$dropdownField.parent().removeClass('hidden');

    }

  });

  // Add support to Sprout Forms edit modal window
  let content = $("#sprout-content");
  if (content.length === 0) {
    content = $("#content");
  }

  // Initialize the SelectOther Field
  Craft.SproutFields.initFields($(content));

})(jQuery);