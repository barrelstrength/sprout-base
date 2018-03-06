$(document).ready(function() {
    SproutEmail.init();
});

var SproutEmail = {
    button: $('#notificationEvent'),
    init: function() {
        this.button.change(function() {
            SproutEmail.selectNotificationEvent();
        });

        $('#mailer').change(function() {
            SproutEmail.onCampaignMailerSelect();
        });

        SproutEmail.selectNotificationEvent();
        SproutEmail.onCampaignMailerSelect();
    },

    selectNotificationEvent: function() {
        $('.event-options').hide();
        var notificationVal = this.button.val();

        if (notificationVal !== '') {
            var eventVal = notificationVal.replace(/\\/g, '-').toLowerCase();

            $('.' + eventVal).show();
        }
    },

    /**
     * Event handler for mailer selection on campaign settings
     */
    onCampaignMailerSelect: function() {

    }
};
