(function() {
  const multilingualToggleDiv = document.getElementById('settings-enableMultilingualSitemaps');

  if (multilingualToggleDiv) {
    // Callback function to execute when mutations are observed
    const toggleMultisiteSettingsCallback = function(mutationsList, observer) {
      for (let mutation of mutationsList) {
        if (mutation.type === 'attributes') {

          let siteWrapper = document.getElementById('settings-siteWrapper');
          let groupWrapper = document.getElementById('settings-groupWrapper');
          let value = document.querySelector('[name="settings[enableMultilingualSitemaps]"').value;

          if (value === "1") {
            groupWrapper.classList.remove('hidden');
            siteWrapper.classList.add('hidden');
          } else {
            groupWrapper.classList.add('hidden');
            siteWrapper.classList.remove('hidden');
          }
        }
      }
    };

    // Create an observer instance linked to the callback function
    const toggleMultisiteObserver = new MutationObserver(toggleMultisiteSettingsCallback);

    // Start observing the target node for configured mutations
    toggleMultisiteObserver.observe(multilingualToggleDiv, {
      attributes: true
    });
  }
})();
