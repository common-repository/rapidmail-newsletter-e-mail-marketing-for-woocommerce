<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.rapidmail.de/
 * @since      1.0.0
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/admin/partials
 */

$options = get_option('rapidmail_connector');
$plugin_dir_url = plugin_dir_url(__DIR__);
?>

<form id="rm-connector" target="_blank" action="<?php echo esc_attr($options['connect_url']) ?>">
    <a class="rm-button advanced-button" href="<?php echo admin_url('admin.php?page=rapidmail-connector-newsletter-settings') ?>">
        <img src="<?php echo $plugin_dir_url ?>assets/settings.svg" alt="">
        <?php echo __('Advanced plugin settings', 'rapidmail-connector'); ?>
    </a>
    <img src="<?php echo $plugin_dir_url ?>assets/rapidmail-logo.png" class="rm-logo" alt="">
    <img src="<?php echo $plugin_dir_url ?>assets/rapidmail-banner.png" class="rm-banner" alt="">
    <input id="rm-type" type="hidden" name="type">
    <input id="rm-connection" type="hidden" name="connection">

    <button id="rm-submit" type="button" class="rm-button rm-mb-1 rm-mt-2">
        <?php _e('Create a new connection to rapidmail', 'rapidmail-connector') ?>
    </button>
    <div class="rm-mb-1"><?php _e('or', 'rapidmail-connector'); ?></div>
    <a id="rm-overview-url" class="rm-mb-1"
       href="<?php echo esc_attr($options['overview_url']); ?>"><?php _e('Log in to rapidmail', 'rapidmail-connector') ?></a>

    <div id="rm-payload" class="rm-mb-1 rm-display-none">
        <p class="rm-mb-1"><?php _e('API settings (In case you need to setup your shop manually in rapidmail)', 'rapidmail-connector'); ?></p>
        <div class="rm-field">
            <label for="rm-payload-key"><?php _e('Access key ID', 'rapidmail-connector'); ?></label>
            <div class="rm-flex-grow"><input type="text" id="rm-payload-key" disabled value="<?php echo get_option('rapidmail_consumer_key'); ?>"></div>
        </div>
        <div class="rm-field">
            <label for="rm-payload-secret"><?php _e('Secret access key', 'rapidmail-connector'); ?></label>
            <div class="rm-flex-grow"><input type="text" id="rm-payload-secret" disabled value="<?php echo get_option('rapidmail_secret_key'); ?>"></div>
        </div>
    </div>
</form>