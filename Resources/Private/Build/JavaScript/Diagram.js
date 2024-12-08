import { Chart, registerables } from "@in2code/luxletter/vendor/chartjs.js";

const setDefaultChartColor = () => {
  const colorScheme = document.documentElement.getAttribute('data-color-scheme') || 'auto';
  const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (colorScheme === 'light') {
    Chart.defaults.color = '#1A1A1A';
  } else if (colorScheme === 'dark' || (colorScheme === 'auto' && prefersDarkMode)) {
    Chart.defaults.color = '#D9D9D9';
  } else {
    Chart.defaults.color = '#1A1A1A';
  }
};

const IS_TYPO3_12 = document.querySelector('.luxletter--typo3-12') !== null;

if (!IS_TYPO3_12) {
  setDefaultChartColor();
}

Chart.register(...registerables);

const LuxletterDiagram = function() {
  'use strict';

  /**
   * @returns {void}
   */
  this.initialize = function() {
    diagramListener();
  };

  /**
   * @returns {void}
   */
  var diagramListener = function() {
    var diagrams = document.querySelectorAll('[data-chart]');
    diagrams.forEach(function(diagram) {
      const existingChart = Chart.getChart(diagram);
      if (existingChart !== undefined) {
        existingChart.destroy();
      }

      const type = diagram.getAttribute('data-chart');
      if (type === 'doughnut') {
        diagramDoughnut(diagram);
      } else if (type === 'bar') {
        diagramBar(diagram);
      }
    });
  };

  /**
   * @returns {void}
   */
  var diagramDoughnut = function(element) {
    new Chart(element.getContext('2d'), {
      type: 'doughnut',
      data: {
        datasets: [{
          data: element.getAttribute('data-chart-data').split(','),
          backgroundColor: [
            'rgba(221, 221, 221, 1)',
            'rgba(2, 122, 202, 1)'
          ]
        }],
        labels: element.getAttribute('data-chart-labels').split(',')
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
            position: 'right',
            labels: {
              fontSize: 14
            }
          }
        }
      }
    });
  };

  /**
   * @returns {void}
   */
  var diagramBar = function(element) {
    new Chart(element.getContext('2d'), {
      type: 'bar',
      data: {
        datasets: [{
          label: element.getAttribute('data-chart-label'),
          data: element.getAttribute('data-chart-data').split(','),
          backgroundColor: [
            'rgba(2, 122, 202, 1)',
            'rgba(221, 221, 221, 1)'
          ]
        }],
        labels: element.getAttribute('data-chart-labels').split(',')
      },
      options: {
        legend: {
          display: false,
          position: 'right',
          labels: {
            fontSize: 18
          }
        },
        scales: {
          x: {
            ticks: {
              autoSkip: false
            }
          },
          y: {
            ticks: {
              beginAtZero: true
            }
          }
        }
      }
    });
  };
}

const LuxletterDiagramObject = new LuxletterDiagram();
LuxletterDiagramObject.initialize();
