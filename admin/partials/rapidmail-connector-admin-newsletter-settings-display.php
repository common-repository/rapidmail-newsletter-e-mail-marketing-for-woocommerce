<!-- wrapper for admin notices -->
<div class="wrap"></div>

<?php

$options = get_option('rapidmail_connector_newsletter', []);
$isEnabled = isset($options['enabled']) && $options['enabled'];
$pluginDir = plugin_dir_url(__DIR__);

?>

<div id="rm-advanced-settings">
    <img src="<?php echo $pluginDir; ?>assets/rapidmail-logo.png" class="rm-logo" alt="">

    <h2><?php _e('Advanced plugin settings', 'rapidmail-connector'); ?></h2>
    <hr>
    <h3><?php _e('Newsletter subscription Opt-In', 'rapidmail-connector'); ?></h3>
    <form action="options.php" method="post">
        <?php settings_fields('rapidmail_connector_settings_newsletter'); ?>
        <div class="opt-in-field">
            <input type="checkbox" name="rapidmail_connector_newsletter[enabled]" id="newsletter_enabled"<?php echo $isEnabled ? ' checked' : '' ?>>
            <div class="text-box">
                <img style="padding-left: 15px;" src="<?php echo $pluginDir ?>assets/email.svg" alt="">
                <div style="padding-left: 10px;">
                    <strong><?php _e('Show a Newsletter Opt-In checkbox', 'rapidmail-connector'); ?></strong>
                    <br>
                    <?php _e(
                        'This option enables your customers to subscribe to you newsletter. Once imported in rapidmail, contacts that opted-in for receiving newsletters will automatically be tagged in your rapidmail subscriber list.',
                        'rapidmail-connector'
                    ); ?>
                </div>
            </div>
        </div>

        <table class="form-table"<?php echo !$isEnabled ? ' style="display:none;"' : '' ?>>
            <tbody>
            <tr>
                <th scope="row">
                    <label>
                        <?php _e('Opt-In checkbox position', 'rapidmail-connector'); ?>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <?php

                    $locations = isset($options['location']) ? $options['location'] : [];

                    $available_options = [
                        'register' => __(
                            'Display in the new account register form, after the email address field',
                            'rapidmail-connector'
                        ),
                        'submit' => __('Display on the checkout page, before the Order button', 'rapidmail-connector'),
                        'billing' => __(
                            'Display on the checkout page, after the Billing information',
                            'rapidmail-connector'
                        ),
                    ];

                    foreach ($available_options as $key => $text) {
                        $checked = in_array($key, $locations) ? 'checked' : '';
                        echo '<label><input class="rm-location-checkbox" type="checkbox" name="rapidmail_connector_newsletter[location][' . $key . ']" ' . $checked . '> ' . $text . '</label><br>';
                    }

                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="rm-newsletter-label">
                        <?php _e('Opt-In checkbox label', 'rapidmail-connector'); ?>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <textarea id="rm-newsletter-label" style="width:100%;" name="rapidmail_connector_newsletter[label]"><?php
                        echo isset($options['label']) ? $options['label'] : ''; ?></textarea>
                    <br>
                    <?php _e(
                        'Leave the Field blank to use language translation files (.po, .mo), translating the string: "Subscribe to our newsletter".',
                        'rapidmail-connector'
                    ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="rm-newsletter-default">
                        <?php _e('Opt-In checkbox default status', 'rapidmail-connector'); ?>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <?php $enabled = isset($options['default']) ? $options['default'] : 0; ?>
                    <select id="rm-newsletter-default" name="rapidmail_connector_newsletter[default]" style="width:100%;max-width:100%;">
                        <option value="0">
                            <?php _e('Unchecked', 'rapidmail-connector'); ?>
                        </option>
                        <option value="1" <?php echo $enabled ? ' selected' : ''; ?>>
                            <?php _e('Checked', 'rapidmail-connector'); ?>
                        </option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <div style="text-align: right;" class="rm-mt-1">
            <a class="rm-button rm-back-button rm-link" href="<?php echo admin_url('admin.php?page=rapidmail-connector') ?>">
                <?php _e('Back', 'rapidmail-connector'); ?>
            </a>
            <button id="rm-submit" type="submit" class="rm-button disabled" style="margin-left: 20px;">
                <?php _e('Save', 'rapidmail-connector'); ?>
            </button>
        </div>
    </form>
</div>