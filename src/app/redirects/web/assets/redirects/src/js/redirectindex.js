/* global Craft */

class RedirectEditLinkSwitcher {

  constructor(settings) {
    this.$menuBtn = $('.sitemenubtn:first').menubtn().data('menubtn');

    if (this.$menuBtn !== undefined) {
      this.initSiteSwitchListener();
    }
  }

  /**
   * Dynamically update the "New Redirect" cpUrl to create a redirect for the current site
   */
  initSiteSwitchListener() {
    let self = this;
    let siteMenu = this.$menuBtn.menu;

    // Change the siteId when on hidden values
    siteMenu.on('optionselect', function() {
      for (let site of Craft.sites) {
        if (site.id === Craft.elementIndex.siteId) {
          let cpEditUrl = Craft.getUrl('sprout/redirects/new/' + site.handle);
          let newRedirectButton = document.getElementById('sprout-base-redirects-new-button');
          newRedirectButton.setAttribute('href', cpEditUrl);
          break;
        }
      }
    });
  }
}

window.RedirectEditLinkSwitcher = RedirectEditLinkSwitcher;