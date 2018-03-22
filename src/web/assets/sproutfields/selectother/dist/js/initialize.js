$(document).ready(function() {
    // Add support to Sprout Forms edit modal window
    var content = $("#sprout-content");
    if(content.length == 0) {
        content = $("#content");
    }
    Craft.SproutFields.initFields($(content));
});