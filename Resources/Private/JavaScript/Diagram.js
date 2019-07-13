define(['jquery', 'TYPO3/CMS/Luxletter/Vendor/Chart.min'], function($) {
	'use strict';

	/**
	 * @constructor
	 */
	function LuxletterDiagram($) {
		'use strict';

		/**
		 * Initialize
		 *
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
			for (var i = 0; i < diagrams.length; i++) {
				var type = diagrams[i].getAttribute('data-chart');
				if (type === 'doughnut') {
					diagramDoughnut(diagrams[i]);
				} else if (type === 'bar') {
					diagramBar(diagrams[i]);
				}
			}
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
							'rgba(77, 231, 255, 1)'
						]
					}],
					labels: element.getAttribute('data-chart-labels').split(',')
				},
				options: {
					legend: {
						display: false,
						position: 'right',
						labels: {
							fontSize: 14
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
							'rgba(77, 231, 255, 1)',
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
						yAxes: [{
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}
			});
		};
	}


	/**
	 * Init
	 */
	$(document).ready(function () {
		var LuxletterDiagramObject = new LuxletterDiagram($);
		LuxletterDiagramObject.initialize();
	})
});
