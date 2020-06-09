/* global Craft */

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

    init: function(settings) {

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

        menuBtn.settings.onOptionSelect = $.proxy(function(elem) {

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
      const name = this.promptForGroupName('');

      if (name) {
        const data = {
          name: name
        };

        Craft.postActionRequest(settings.newGroupAction, data, $.proxy(function(response) {
          if (response.success) {
            location.href = Craft.getUrl(settings.newGroupOnSuccessUrlBase);
          } else {
            const errors = this.flattenErrors(response.errors);
            alert(Craft.t('sprout', settings.newGroupOnErrorMessage) + "\n\n" + errors.join("\n"));
          }

        }, this));
      }
    },

    renameSelectedGroup: function() {
      const oldName = this.$selectedGroup.text(),
        newName = this.promptForGroupName(oldName);

      if (newName && newName !== oldName) {
        const data = {
          id: this.$selectedGroup.data('id'),
          name: newName
        };

        Craft.postActionRequest(settings.renameGroupAction, data, $.proxy(function(response) {
          if (response.success) {
            this.$selectedGroup.text(response.group.name);
            Craft.cp.displayNotice(Craft.t('sprout', (settings.renameGroupOnSuccessMessage)));
          } else {
            const errors = this.flattenErrors(response.errors);
            alert(Craft.t('sprout', settings.renameGroupOnErrorMessage) + "\n\n" + errors.join("\n"));
          }

        }, this));
      }
    },

    promptForGroupName: function(oldName) {
      return prompt(Craft.t('sprout', settings.promptForGroupNameMessage), oldName);
    },

    deleteSelectedGroup: function() {
      if (confirm(Craft.t('sprout', settings.deleteGroupConfirmMessage))) {
        const data = {
          id: this.$selectedGroup.data('id')
        };

        Craft.postActionRequest(settings.deleteGroupAction, data, $.proxy(function(response) {
          if (response.success) {
            location.href = Craft.getUrl(settings.deleteGroupOnSuccessUrl);
          } else {
            alert(Craft.t('sprout', settings.deleteGroupOnErrorMessage));
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

    flattenErrors: function(responseErrors) {
      let errors = [];

      for (let attribute in responseErrors) {
        errors = errors.concat(response.errors[attribute]);
      }

      return errors;
    }
  });

})();
