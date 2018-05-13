if (typeof Craft.SproutEmail === typeof undefined) {
    Craft.SproutEmail = {};
}


Craft.SproutEmail.SentEmailElementEditor = Craft.BaseElementEditor.extend({
    showHud: function(response, textStatus) {
        this.onEndLoading();

        if (textStatus === 'success') {
            var $hudContents = $();

            if (response.sites) {
                var $header = $('<div class="hud-header"/>'),
                    $siteSelectContainer = $('<div class="select"/>').appendTo($header);

                this.$siteSelect = $('<select/>').appendTo($siteSelectContainer);
                this.$siteSpinner = $('<div class="spinner hidden"/>').appendTo($header);

                for (var i = 0; i < response.sites.length; i++) {
                    var siteInfo = response.sites[i];
                    $('<option value="' + siteInfo.id + '"' + (siteInfo.id == response.siteId ? ' selected="selected"' : '') + '>' + siteInfo.name + '</option>').appendTo(this.$siteSelect);
                }

                this.addListener(this.$siteSelect, 'change', 'switchSite');

                $hudContents = $hudContents.add($header);
            }

            this.$form = $('<div/>');
            this.$fieldsContainer = $('<div class="fields"/>').appendTo(this.$form);

            this.updateForm(response);

            this.onCreateForm(this.$form);

            var $footer = $('<div class="hud-footer"/>').appendTo(this.$form),
                $buttonsContainer = $('<div class="buttons right"/>').appendTo($footer);
            this.$cancelBtn = $('<div class="btn">' + Craft.t('app', 'Cancel') + '</div>').appendTo($buttonsContainer);
            this.$spinner = $('<div class="spinner hidden"/>').appendTo($buttonsContainer);

            $hudContents = $hudContents.add(this.$form);

            if (!this.hud) {
                var hudTrigger = (this.settings.hudTrigger || this.$element);

                this.hud = new Garnish.HUD(hudTrigger, $hudContents, {
                    bodyClass: 'body elementeditor',
                    closeOtherHUDs: false,
                    onShow: $.proxy(this, 'onShowHud'),
                    onHide: $.proxy(this, 'onHideHud'),
                    onSubmit: $.proxy(this, 'saveElement')
                });

                this.hud.$hud.data('elementEditor', this);

                this.hud.on('hide', $.proxy(function() {
                    delete this.hud;
                }, this));
            }
            else {
                this.hud.updateBody($hudContents);
                this.hud.updateSizeAndPosition();
            }

            // Focus on the first text input
            $hudContents.find('.text:first').focus();

            this.addListener(this.$cancelBtn, 'click', function() {
                this.hud.hide();
            });
        }
    }
});