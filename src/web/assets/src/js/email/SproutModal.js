/* global Craft */

class SproutModal {

  constructor() {
    let self = this;

    self.initEventListeners();
    self.initEmailPreview();

    $('.prepare').on('click', function(e) {
      e.preventDefault();

      let $t = $(e.target);

      let modalLoader = null;

      if ($t.data('mailer') === 'copypaste') {
        modalLoader = self.createLoadingModal();
      }

      self.postToControllerAction($t.data(), function handle(error, response) {
        if (error) {
          return self.createErrorModal(error);
        }

        if (!response.success) {
          return self.createErrorModal(response.message);
        }

        // Close error loading modal if no error on request
        if (modalLoader != null) {
          modalLoader.hide();
          modalLoader.destroy();
        }

        self.create(response.content);
      });
    });

    // return this;
  }

  /**
   * Gives us the ability to post to a controller action and register a callback a la NodeJS
   *
   * @example
   * let payload = {action: 'plugin/controller/action'};
   * let callback = function(error, data) {};
   *
   * @note
   * The action is required and must be provided in the payload
   *
   * @param object payload
   * @param function callback
   */
  postToControllerAction(payload, callback) {
    let request = {
      url: window.location,
      type: "POST",
      data: payload,
      cache: false,
      dataType: "json",

      error: function handleFailedRequest(xhr, status, error) {
        callback(error);
      },

      success: function handleSuccessfulRequest(response) {
        callback(null, response);
      },
    };

    request.data[Craft.csrfTokenName] = Craft.csrfTokenValue;

    $.ajax(request);
  }

  /**
   * Creates a modal window instance from content returned from server and does so recursively
   *
   * @param string content
   * @returns {Garnish.Modal}
   */
  create(content) {
    // For later reference within different scopes
    let self = this;

    // Modal setup
    let $modal = $('#sproutmodal').clone();
    let $content = $modal.html(content);
    let $spinner = $('.spinner', $modal);
    let $actions = $('.actions', $modal);

    // Gives mailers a chance to add their own event handlers
    $(document).trigger('sproutModalBeforeRender', $content);

    $modal.removeClass('hidden');

    // Instantiate and show
    let modal = new Garnish.Modal($modal);

    self.initEmailPreview();

    $('#close', $modal).on('click', function() {
      Craft.elementIndex.updateElements();

      modal.hide();
      modal.destroy();
    });

    $('#cancel', $modal).on('click', function() {
      Craft.elementIndex.updateElements();

      modal.hide();
      modal.destroy();
    });

    $actions.on('click', function(e) {
      e.preventDefault();

      let $self = $(e.target);

      if ($self.hasClass('preventAction')) {
        $self.removeClass('preventAction');

        return;
      }

      $spinner.removeClass('hidden');

      let data = $self.data();

      if ($('#recipients').val() !== '') {
        let recipients = {recipients: $('#recipients').val()};

        data = $.extend(data, recipients);
      }
      let $spin = $self.parents('.footer').find('.send-spinner');
      $spin.show();
      self.postToControllerAction(data, function handleResponse(error, response) {
        $spin.hide();

        // Close previous modal
        modal.hide();
        modal.destroy();

        if (error) {
          return self.createErrorModal(error);
        }

        if (!response.success) {
          return self.createErrorModal(response.message);
        }

        $spinner.addClass('hidden');

        modal = self.create(response.content);

        modal.updateSizeAndPosition();
      });
    });

    return modal;
  }

  createErrorModal(error) {
    let $content = $('#sproutmodal-error').clone();

    $('.innercontent', $content).html(error);

    let modal = new SproutModal();

    modal.create($content.html());
  }

  createLoadingModal() {
    let $content = $('#sproutmodal-loading').clone();

    $('.innercontent', $content);

    let modal = new SproutModal();

    return modal.create($content.html());

  }

  initEventListeners() {
    document.addEventListener('sproutModalBeforeRender', function(event, content) {

      alert('registering sproutModalBeforeRender');
      $('.btnSelectAll', content).off().on('click', function(event) {
        event.preventDefault();

        let $this = $(event.target);
        let $target = '#' + $this.data('clipboard-target-id');
        let $message = $this.data('success-message');

        let $content = $($target).select();

        // Copy our selected text to the clipboard
        document.execCommand('copy');

        Craft.cp.displayNotice($message);
      });
    });
  }

  initEmailPreview() {
    $('.email-preview').on('click', function(e) {

      e.preventDefault();

      let $this = $(e.target);
      let $previewUrl = $this.data('preview-url');

      window.open($previewUrl, 'newwindow', 'width=920, height=600');

      return false;
    });
  }
}

window.SproutModal = SproutModal;