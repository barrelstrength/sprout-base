
(function($)
{
    var RedirectIndex = Garnish.Base.extend({
        $menu: null,
        $form: null,

        /**
         * The constructor.
         */
        init: function()
        {
            var $siteMenu = $('.sitemenubtn:first').menubtn().data('menubtn').menu;

            // Upload file on click
            $('.translations-upload-button').click(function() {
                $('input[name="translations-upload"]').click().change(function() {
                    $(this).parent('form').submit();
                });
            });

            // Init the form
            if(Craft.getLocalStorage('BaseElementIndex.siteId')) {
                //$siteIdInput.val(Craft.getLocalStorage('BaseElementIndex.siteId'));
            }

            // Change the siteId when on hidden values
            $siteMenu.on('optionselect', function(ev) {
                var uri = '';
                for (var i = 0; i < Craft.sites.length; i++) {
                    if (Craft.sites[i].id == Craft.elementIndex.siteId) {
                        uri += 'sprout-seo/redirects/new/'+Craft.sites[i].handle;
                        uri = Craft.getUrl(uri);
                        $("#sprout-seo-new-button").attr("href", uri);
                    }
                }
               // $siteIdInput.val($(ev.selectedOption).data('siteId'));
            });
        },
    });

    window.RedirectIndex = RedirectIndex;

})(jQuery);


