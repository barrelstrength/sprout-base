/* global Craft */

/**
 * Manages the dynamic updating of Sitemap attributes from the Sitemap page.
 */
class SproutSeoSitemapIndex {

  constructor() {
    const lightswitches = document.querySelectorAll('.sitemap-settings .lightswitch');
    const selectDropdowns = document.querySelectorAll('.sitemap-settings select');
    const customSectionUrls = document.querySelectorAll('.sitemap-settings input.sitemap-custom-url');
    const customPageDeleteLinks = document.querySelectorAll('#custom-pages tbody tr td a.delete');

    for (const lightswitch of lightswitches) {
      lightswitch.addEventListener('click', this.updateSitemap);
    }

    for (const selectDropdown of selectDropdowns) {
      selectDropdown.addEventListener('change', this.updateSitemap);
    }

    for (const customSectionUrl of customSectionUrls) {
      customSectionUrl.addEventListener('change', this.updateSitemap);
    }

    for (const customPageDeleteLink of customPageDeleteLinks) {
      customPageDeleteLink.addEventListener('click', this.deleteCustomPage);
    }
  }

  updateSitemap(event) {
    let changedElement = event.target;
    let $row = $(changedElement).closest('tr');
    let rowId = $row.data('rowid');
    let isNew = $row.data('isNew');
    let enabled = $('input[name="sproutseo[sections][' + rowId + '][enabled]"]').val();
    let siteId = $('input[name="siteId"]').val();
    let uri = $('input[name="sproutseo[sections][' + rowId + '][uri]"]').val();
    let status = $('tr[data-rowid="' + rowId + '"] td span.status');

    let data = {
      "id": $row.data('id'),
      "type": $row.data('type'),
      "urlEnabledSectionId": $row.data('urlEnabledSectionId'),
      "uri": uri,
      "priority": $('select[name="sproutseo[sections][' + rowId + '][priority]"]').val(),
      "changeFrequency": $('select[name="sproutseo[sections][' + rowId + '][changeFrequency]"]').val(),
      "enabled": enabled,
      "siteId": siteId
    };

    Craft.postActionRequest('sprout/sitemaps/save-sitemap-section', data, $.proxy(function(response, textStatus) {
      if (textStatus === 'success') {
        if (response.success) {

          let keys = rowId.split('-');
          let type = keys[0];
          let newRowId = null;

          if (response.sitemapSection.urlEnabledSectionId) {
            newRowId = type + '-' + response.sitemapSection.urlEnabledSectionId;
          } else {
            newRowId = type + '-' + response.sitemapSection.id;
          }

          let $changedElementRow = $(changedElement).closest('tr');
          let $changedElementTitleLink = $changedElementRow.find('a.sproutseo-sectiontitle');

          if ($changedElementRow.data('isNew')) {
            $changedElementTitleLink.attr('href', 'sections/' + response.sitemapSection.id);
            $changedElementRow.removeClass('sitemapsection-isnew');
            $changedElementRow.data('isNew', 0);
            $changedElementRow.data('id', response.sitemapSection.id);

            $changedElementTitleLink.unbind('click');
          }

          let $sectionInputBase = 'input[name="sproutseo[sections][' + rowId + ']';

          $($sectionInputBase + '[id]"]').val(newRowId);
          $($sectionInputBase + '[id]"]').attr('name', 'sproutseo[sections][' + newRowId + '][id]');
          $($sectionInputBase + '[urlEnabledSectionId]"]').attr('name', 'sproutseo[sections][' + newRowId + '][urlEnabledSectionId]');
          $($sectionInputBase + '[priority]"]').attr('name', 'sproutseo[sections][' + newRowId + '][priority]');
          $($sectionInputBase + '[changeFrequency]"]').attr('name', 'sproutseo[sections][' + newRowId + '][changeFrequency]');
          $($sectionInputBase + '[enabled]"]').attr('name', 'sproutseo[sections][' + newRowId + '][enabled]');

          Craft.cp.displayNotice(Craft.t('sprout', 'Sitemap Metadata saved.'));
        } else {
          Craft.cp.displayError(Craft.t('sprout', 'Unable to save Sitemap Metadata.'));
        }
      }
    }, this));

    if (enabled) {
      status.removeClass('disabled');
      status.addClass('live');
    } else {
      status.removeClass('live');
      status.addClass('disabled');
    }
  }

  deleteCustomPage(event) {

    let linkElement = event.target;
    let row = linkElement.parentElement.parentElement;
    let customPageId = row.getAttribute('data-id');

    let data = {
      id: customPageId
    };

    Craft.postActionRequest('sprout/sitemaps/delete-sitemap-by-id', data, $.proxy(function(response, textStatus) {
      if (response.success) {
        row.remove();
      }

      let customPageRows = document.querySelectorAll('#custom-pages tbody tr').length;

      if (customPageRows <= 0) {
        let customPagesTable = document.getElementById('custom-pages');
        customPagesTable.remove();
      }
    }, this));
  }
}

window.SproutSeoSitemapIndex = SproutSeoSitemapIndex;