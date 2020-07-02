declare var Craft: any;

$(document).on('sproutModalBeforeRender', function (event, content) {
    $('.btnSelectAll', content).off().on('click', function (event) {

        event.preventDefault();

        let $this = $(event.target);
        let $target = '#' + $this.data('clipboard-target-id');
        let $message = $this.data('success-message');

        let $content = $($target).select();

        // Copy our selected text to the clipboard
        document.execCommand("copy");

        Craft.cp.displayNotice($message);
    });
});