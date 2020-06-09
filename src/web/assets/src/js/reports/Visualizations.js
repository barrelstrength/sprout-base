(function($) {

  /** global ApexCharts */

  /**
   * Manage available subclasses
   */
  class SproutReportsViz {
    constructor(type, settings) {
      switch (type) {
        case 'barChart':
          return new BarChart(settings);
        case 'lineChart':
          return new LineChart(settings);
        case 'pieChart':
          return new PieChart(settings);
        case 'timeSeriesChart':
          return new TimeSeriesChart(settings);
      }
    }
  }

  /**
   * Base Visualization class with shared methods.
   */
  class SproutReportsChart {

    constructor(settings) {

      this.chartSelector = settings.chartSelector ?? '#chart';
      this.labels = settings.labels ?? [];
      this.dataSeries = settings.dataSeries ?? [];
      this.options = settings.options ?? [];

      this.chart = null;
      this.defaultStartDate = settings.startDate ?? 0;
      this.defaultEndDate = settings.endDate ?? 0;

      //setup listeners for date ranges
      this.startDate = $('input[name="reportDateFrom[date]"]');
      this.endDate = $('input[name="reportDateTo[date]"]');

      this.startDate.on('change', this.updateVisualizationDate.bind(this));
      this.endDate.on('change', this.updateVisualizationDate.bind(this));
    }

    updateVisualizationDate(event) {
      event.preventDefault();
      let currentStartDate = $('input[name="reportDateFrom[date]"]').val();
      let currentEndDate = $('input[name="reportDateTo[date]"]').val();

      currentStartDate = currentStartDate !== '' ? currentStartDate : this.defaultStartDate;
      currentEndDate = currentEndDate !== '' ? currentEndDate : this.defaultEndDate;

      this.chart.zoomX(
        new Date(currentStartDate).getTime(),
        new Date(currentEndDate).getTime()
      );

      return false;
    }

    create() {
      return null;
    }

    draw(settings) {
      this.chart = new ApexCharts(document.querySelector(this.chartSelector), settings);
      this.chart.render();
    }
  }

  class BarChart extends SproutReportsChart {
    create() {
      const settings = {
        series: this.dataSeries,
        chart: {
          height: 380,
          type: 'bar',
          zoom: {
            enabled: false
          }
        },
        plotOptions: {
          bar: {
            horizontal: true,
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          },
        },
        xaxis: {
          categories: this.labels
        }
      };

      console.log('CREATE');
      console.log(this.dataSeries);

      this.draw(settings);
    }
  }

  class LineChart extends SproutReportsChart {
    create() {
      const settings = {
        series: this.dataSeries,
        chart: {
          height: 380,
          type: 'line',
          zoom: {
            enabled: false
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          },
        },
        xaxis: {
          categories: this.labels
        }
      };

      this.draw(settings);
    }
  }

  class PieChart extends SproutReportsChart {
    create() {
      const settings = {
        series: this.dataSeries[0].data,
        chart: {
          width: 380,
          type: 'pie',
        },
        labels: this.labels,
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: 200
              },
              legend: {
                position: 'bottom'
              }
            }
          }
        ]
      };

      this.draw(settings);
    }
  }

  class TimeSeriesChart extends SproutReportsChart {

    create() {
      const settings = {
        series: this.dataSeries,
        chart: {
          height: 380,
          type: 'line',
          zoom: {
            enabled: true
          },
          toolbar: {
            show: true,
            offsetX: 0,
            offsetY: 0,
            tools: {
              download: false,
              selection: true,
              zoom: true,
              zoomin: true,
              zoomout: true,
              pan: true,
              reset: true,
              customIcons: []
            },
            autoSelected: 'zoom'
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          },
        },
        xaxis: {
          type: 'datetime',
          min: this.defaultStartDate,
          max: this.defaultEndDate
        }
      };

      this.draw(settings);
    }
  }

  window.SproutReportsViz = SproutReportsViz;

})(jQuery);
