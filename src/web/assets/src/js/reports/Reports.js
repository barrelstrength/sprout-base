/* global Craft */

/**
 * Initialize and style DataTables on the Sprout Reports results page
 */
class SproutReportsDataTables {

  constructor(settings) {
    this.allowHtml = settings.allowHtml ?? false;
    this.defaultPageLength = settings.defaultPageLength ?? 10;
    this.sortOrder = settings.sortOrder ?? null;
    this.sortColumnPosition = settings.sortColumnPosition ?? null;
    this.orderSetting = [];
    this.sproutResultsTable = $('#sprout-results');

    this.initializeDataTable();
  }

  initializeDataTable() {
    let self = this;

    let sortOrder = self.sortOrder;
    let sortColumnPosition = self.sortColumnPosition;

    if (sortOrder && sortColumnPosition) {
      self.orderSetting = [[sortColumnPosition, sortOrder]];
    }

    this.sproutResultsTable.DataTable({
      dom: '<"sprout-results-header"pilf>t',
      responsive: true,
      scrollX: "100vw",
      order: self.orderSetting,
      pageLength: self.defaultPageLength,
      lengthMenu: [
        [10, 25, 50, 100, 250, -1],
        [10, 25, 50, 100, 250, 'All'],
      ],
      pagingType: 'simple',
      language: {
        emptyTable: Craft.t('sprout', 'No results found.'),
        info: Craft.t('sprout', '_START_-_END_ of _MAX_ results'),
        infoEmpty: Craft.t('sprout', 'No results found.'),
        infoFiltered: '',
        lengthMenu: Craft.t('sprout', 'Show rows _MENU_'),
        loadingRecords: Craft.t('sprout', 'Loading...'),
        processing: Craft.t('sprout', 'Processing...'),
        search: '',
        zeroRecords: Craft.t('sprout', 'No results found'),
      },
      columnDefs: [
        {
          targets: '_all',
          render: function(data, type) {

            if (type === 'display' && data.length > 65 && self.allowHtml === false) {
              return data.substr(0, 65) + '… <span class="info" style="margin-right:10px;">' + data + '</span>';
            }

            return data;
          },
        },
      ],
      initComplete: function() {

        let searchInput = document.querySelector('#sprout-results_filter input');
        let sproutResultsFilter = document.getElementById('sprout-results_filter');

        // Style Search Box
        searchInput.setAttribute('placeholder', Craft.t('sprout', 'Search'));
        searchInput.classList.add('text', 'fullwidth');
        searchInput.focus();

        sproutResultsFilter.classList.add('texticon', 'search', 'icon', 'clearable');

        // // Style Results per Page Dropdown
        let resultsLengthDropdown = document.querySelector('#sprout-results_length select');
        let selectWrapper = document.createElement('dig');
        selectWrapper.classList.add('select');
        // Place new element in DOM
        resultsLengthDropdown.parentNode.insertBefore(selectWrapper, resultsLengthDropdown);
        // Move resultsLengthDropdown into wrapper
        selectWrapper.appendChild(resultsLengthDropdown);

        // init info bubbles after search, sort, filter, etc.
        self.sproutResultsTable.on('draw.dt', function() {
          self.stylePagination();
          Craft.initUiElements(self.sproutResultsTable);
        });

        self.stylePagination();

        Craft.initUiElements(self.sproutResultsTable);

        let dataTablesScrollTable = document.querySelector('.dataTables_scroll table');
        dataTablesScrollTable.style.opacity = '1';
        let resultsTable = document.getElementById('sprout-results');

        resultsTable.style.opacity = '1';

        window.addEventListener('resize', function() {
          self.resizeTable()
        });

        self.resizeTable();
      },
    });
  }

  resizeTable() {
    let leftAndRightPadding = 48;
    $('.dataTables_scroll').width($('#header').width() - leftAndRightPadding);
  }

  stylePagination() {
    document.querySelector('#sprout-results_paginate').classList.add('pagination');
    let paginateButtons = document.querySelectorAll('.paginate_button');
    document.querySelector('.paginate_button.previous').innerHTML = '';
    document.querySelector('.paginate_button.next').innerHTML = '';
    document.querySelector('.paginate_button.previous').setAttribute('data-icon', 'leftangle');
    document.querySelector('.paginate_button.next').setAttribute('data-icon', 'rightangle');

    for (let button of paginateButtons) {
      button.classList.add('page-link');
    }

    let $actionButton = $('#action-button');
    $actionButton.prepend($('#sprout-results_paginate'));
    $actionButton.prepend($('#sprout-results_info'));
  }
}

class ReportSettingsToggleButton {
  constructor() {
    this.$modifySettingsPanel = $('#modify-settings-panel');
    this.initSettingsToggle();
  }

  initSettingsToggle() {
    let self = this;
    $('#modify-settings-icon').on('click', function() {

      let isDisplayed = self.$modifySettingsPanel.css('display') === 'block';
      let isInViewport = self.isInViewport(self.$modifySettingsPanel);

      if (isInViewport) {
        self.$modifySettingsPanel.slideToggle('fast');
      } else {
        $('html, body').animate({scrollTop: 0}, 'fast');
        if (!isDisplayed) {
          self.$modifySettingsPanel.slideToggle('fast');
        }
      }
    });
  }

  /**
   * Determine if a given HTML element exists within the current viewport
   *
   * @returns {boolean}
   */
  isInViewport($element) {
    let topOfElement = $element.offset().top;
    let bottomOfElement = $element.offset().top + $element.outerHeight();
    let bottomOfScreen = $(window).scrollTop() + $(window).innerHeight();
    let topOfScreen = $(window).scrollTop();

    return (bottomOfScreen > topOfElement) && (topOfScreen < bottomOfElement);
  }
}

window.SproutReportsDataTables = SproutReportsDataTables;
window.ReportSettingsToggleButton = ReportSettingsToggleButton;