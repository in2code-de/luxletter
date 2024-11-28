import "@in2code/luxletter/vendor/choices.js";

function LuxletterChoices() {
  this.initialize = function() {
    const luxletterReceiversIdentifier = '#luxletter-receivers-dropdown';
    const luxletterReceiversElement = document.querySelector(luxletterReceiversIdentifier);
    if (luxletterReceiversElement === null) return;
    window.luxLetterReceiverChoice = new Choices(luxletterReceiversIdentifier, {
      allowHTML: true,
      searchResultLimit: 100
    });
  };
}

var LuxletterChoicesObject = new LuxletterChoices();
LuxletterChoicesObject.initialize();
