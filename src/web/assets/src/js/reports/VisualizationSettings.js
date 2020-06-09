(function($) {

  class SproutReportsVisualizationSettings {

    constructor() {
      this.$toggle = $('select[name="visualizationType"]');
      this.$toggle.on("change", this.onVisualizationChange.bind(this));
      $('.js-add-data-series').on("click", this.addDataSeries.bind(this));
      $('table').on('click', '.js-remove-data-series', this.removeDataSeries.bind(this));

      this.findTarget();
    }

    addDataSeries(event) {
      event.preventDefault();
      let $row = $(event.currentTarget).parent().find('[name*="dataColumn"]').last().closest('tr');

      this.$target.find('[id$="visualizationDataColumn-field"]').first();

      let $clone = $row.clone();
      $clone.find('textarea').val('');
      $row.after($clone);

      return false;
    }

    removeDataSeries(event) {
      event.preventDefault();
      let $row = $(event.currentTarget).closest('tr');

      if ($row.siblings().length === 0) {
        alert('You must defined at least one data column');
      } else {
        $row.remove();
      }
      return false;

    }

    onVisualizationChange() {
      this.hideTarget(this.$target);
      this.findTarget();
      this.showTarget(this.$target);
    }

    findTarget() {
      let targetSelector = this.$toggle.val();
      if (targetSelector.length) {
        targetSelector = '#' + this.getToggleVal();
        this.$target = $(targetSelector);
      } else {
        this.$target = false;
      }
    }

    hideTarget($target) {
      if ($target && $target.length) {
        $target.addClass('hidden');
      }
    }

    showTarget($target) {
      if ($target && $target.length) {
        $target.removeClass('hidden');
      }
    }

    getToggleVal() {
      if (this.type === 'lightswitch') {
        return this.$toggle.children('input').val();
      } else {
        const postVal = this.$toggle.find(':selected').val();
        return postVal === null ? null : postVal.replace(/[\[\]\\\/]+/g, '-');
      }
    }
  }

  window.SproutReportsVisualizationSettings = SproutReportsVisualizationSettings;

})(jQuery);

