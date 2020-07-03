declare var Garnish: any;

interface Window {
  settings: any;
}

/**
 * Manage groups based off the Craft fields.js file
 */
if (typeof Craft.SproutBase === typeof undefined) {
  Craft.SproutBase = {};
}

(function() {

  Craft.SproutBase.GroupsAdmin = Garnish.Base.extend({

    $groups: null,
    $selectedGroup: null,
    $groupSettingsBtn: null,

    init: function(settings: any) {

      // Make settings globally available
      window.settings = settings;

      // Ensure that 'menubtn' classes get registered
      Craft.initUiElements();

      this.$groups = $(settings.groupsSelector);
      this.$selectedGroup = this.$groups.find('a.sel:first');
      this.addListener($(settings.newGroupButtonSelector), 'activate', 'addNewGroup');

      this.$groupSettingsBtn = $(settings.groupSettingsSelector);

      // Should we display the Groups Setting Selector or not?
      this.toggleGroupSettingsSelector();
      this.addListener(this.$groups, 'click', 'toggleGroupSettingsSelector');

      if (this.$groupSettingsBtn.length) {

        const menuBtn = this.$groupSettingsBtn.data('menubtn');

        menuBtn.settings.onOptionSelect = $.proxy(function(elem: any) {

          const $elem = $(elem);

          if ($elem.hasClass('disabled')) {
            return;
          }

          switch ($(elem).data('action')) {
            case 'rename': {
              this.renameSelectedGroup();
              break;
            }
            case 'delete': {
              this.deleteSelectedGroup();
              break;
            }
          }
        }, this);
      }
    },

    addNewGroup: function() {
      let self = this;
      const name = this.promptForGroupName('');

      if (name) {
        const data = {
          name: name,
        };

        Craft.postActionRequest(window.settings.newGroupAction, data, $.proxy(function(response: any) {

          if (response.success) {
            location.href = Craft.getUrl(window.settings.newGroupOnSuccessUrlBase);
          } else {
            const errors = this.flattenErrors(response.errors);
            alert(Craft.t('sprout', window.settings.newGroupOnErrorMessage) + "\n\n" + errors.join("\n"));
          }

        }, this));
      }
    },

    renameSelectedGroup: function() {
      let self = this;

      const oldName = this.$selectedGroup.text(),
        newName = this.promptForGroupName(oldName);

      if (newName && newName !== oldName) {
        const data = {
          id: this.$selectedGroup.data('id'),
          name: newName,
        };

        Craft.postActionRequest(window.settings.renameGroupAction, data, $.proxy(function(response: any) {
          if (response.success) {
            this.$selectedGroup.text(response.group.name);
            Craft.cp.displayNotice(Craft.t('sprout', (window.settings.renameGroupOnSuccessMessage)));
          } else {
            const errors = this.flattenErrors(response.errors);
            alert(Craft.t('sprout', window.settings.renameGroupOnErrorMessage) + "\n\n" + errors.join("\n"));
          }

        }, this));
      }
    },

    promptForGroupName: function(oldName: any) {
      return prompt(Craft.t('sprout', window.settings.promptForGroupNameMessage), oldName);
    },

    deleteSelectedGroup: function() {
      let self = this;
      if (confirm(Craft.t('sprout', window.settings.deleteGroupConfirmMessage))) {
        const data = {
          id: this.$selectedGroup.data('id'),
        };

        Craft.postActionRequest(window.settings.deleteGroupAction, data, $.proxy(function(response: any) {
          if (response.success) {
            location.href = Craft.getUrl(window.settings.deleteGroupOnSuccessUrl);
          } else {
            alert(Craft.t('sprout', window.settings.deleteGroupOnErrorMessage));
          }
        }, this));
      }
    },

    toggleGroupSettingsSelector: function() {
      this.$selectedGroup = this.$groups.find('a.sel:first');

      if (this.$selectedGroup.data('key') === '*' || this.$selectedGroup.data('readonly')) {
        $(this.$groupSettingsBtn).addClass('hidden');
      } else {
        $(this.$groupSettingsBtn).removeClass('hidden');
      }
    },

    flattenErrors: function(responseErrors: any) {
      let errors: any[] = [];

      for (let attribute in responseErrors) {
        errors = errors.concat(this.response.errors[attribute]);
      }

      return errors;
    },
  });

})();
