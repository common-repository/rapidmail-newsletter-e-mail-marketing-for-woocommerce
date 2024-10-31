(function ($) {
    'use strict';

    function connectionForm() {
        var $form = $('#rm-connector'),
            preventSubmit = true;

        $form.on('submit', function (e) {
            if (!preventSubmit) {
                return;
            }

            e.preventDefault();

            $.post(ajaxurl, {
                action: 'rapidmail_create_connection',
            }, function (result) {
                if (!result.success) {
                    // some error message?
                    return;
                }

                $('#rm-type').val(result.data.type);
                $('#rm-connection').val(result.data.connection);
                $('#rm-payload-key').attr('value', result.data.payload.accessKey);
                $('#rm-payload-secret').attr('value', result.data.payload.secretAccessKey);
                $('#rm-payload').removeClass('rm-display-none');

                preventSubmit = false;
                $form.submit();
            });
        });

        $('#rm-submit').attr('type', 'submit');
    }

    function removeDisabled() {
        $('#rm-submit').removeClass('disabled');
    }

    function advancedSettingsForm() {
        $('#newsletter_enabled').on('change', function () {
            $('.form-table').toggle($(this).is(':checked'));
        });
        $('#rm-newsletter-default, #newsletter_enabled, .rm-location-checkbox').on('change', removeDisabled);
        $('#rm-newsletter-label').on('keyup', removeDisabled);
    }

    $(function () {
        if ($('#rm-connector').length) {
            connectionForm();
        } else if ($('#rm-advanced-settings').length) {
            advancedSettingsForm();
        }
    });

})(jQuery);
