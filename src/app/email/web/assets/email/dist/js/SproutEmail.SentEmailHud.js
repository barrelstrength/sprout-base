if (typeof Craft.SproutEmail === typeof undefined) {
    Craft.SproutEmail = {};
}


Craft.SproutEmail.SentEmailHud = Craft.BaseElementSelectInput.extend({
    loadHud: function() {
        $icon = $('.info-hud.element');

        this.addListener($icon, 'click', this.handleHud);
    },
    handleHud: function(ev) {
        var $element = $(ev.currentTarget);
        var func;
        var elementType = this.settings.elementType;
        var settings = [];
        func = Craft.SproutEmail.SentEmailElementEditor;

        return new func($element, settings);
    },
    showHud: function(response, textStatus) {

    }
});

var sentEmailHud = new Craft.SproutEmail.SentEmailHud;
sentEmailHud.loadHud();