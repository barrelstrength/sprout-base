class SproutEmailNotificationEventToggle {

  constructor() {
    let self = this;

    let notificationEventsSelectInput = document.getElementById('notificationEvent');

    notificationEventsSelectInput.addEventListener('change', function(event) {
      self.toggleNotificationEventSettings(event.target);
    });

    this.toggleNotificationEventSettings(notificationEventsSelectInput);
  }

  toggleNotificationEventSettings(eventDropdown) {

    let eventOptions = document.querySelectorAll('.event-options');
    for (let eventOption of eventOptions) {
      eventOption.classList.add('hidden');
    }

    let selectedNotificationEvent = eventDropdown.options[eventDropdown.selectedIndex].value;

    if (selectedNotificationEvent !== undefined && selectedNotificationEvent !== '') {
      let eventClass = selectedNotificationEvent.replace(/\\/g, '-').toLowerCase();

      let eventSettingsWrapper = document.querySelector('.' + eventClass);
      console.log(eventSettingsWrapper);
      eventSettingsWrapper.classList.remove('hidden');
    }
  }
}

window.SproutEmailNotificationEventToggle = SproutEmailNotificationEventToggle;
