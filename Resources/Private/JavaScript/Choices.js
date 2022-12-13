define(['jquery', 'TYPO3/CMS/Luxletter/Vendor/Choices.min'], function($) {
  'use strict';

  /**
   * @constructor
   */
  function LuxletterChoices($) {
    'use strict';

    /**
     * Initialize
     *
     * @returns {void}
     */
    this.initialize = function() {
      const luxletterReceiversIdentifier = '#luxletter-receivers-dropdown';
      const luxletterReceiversElement = document.querySelector(luxletterReceiversIdentifier);
      if (luxletterReceiversElement === null) return;
      window.luxLetterReceiverChoice = new Choices(luxletterReceiversIdentifier, {
        allowHTML: true
      });
    };


  }


  /**
   * Init
   */
  $(document).ready(function () {
    var LuxletterChoicesObject = new LuxletterChoices($);
    LuxletterChoicesObject.initialize();
  })
});
