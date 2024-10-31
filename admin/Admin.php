<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.rapidmail.de/
 * @since      1.0.0
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/admin
 */

namespace Rapidmail\Connector\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/admin
 * @author     rapidmail GmbH <ebess@rapidmail.de>
 */
class Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Check if woocommerce is installed and activated.
     */
    public function admin_init()
    {
        if (!defined('WC_VERSION')) {
            deactivate_plugins('rapidmail-connector/rapidmail-connector.php');

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['activate'])) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                unset($_GET['activate']);
            }
        }
    }

    /**
     * @return Encrypter
     */
    public function getEncrypter()
    {
        require_once __DIR__ . '/Encrypter.php';

        return new Encrypter();
    }

    public function add_menu()
    {
        add_submenu_page(
            "woocommerce",
            'Rapidmail Connector',
            'Rapidmail',
            'manage_woocommerce',
            $this->plugin_name,
            [$this, 'connection_page']
        );

        $settings_page = new SettingsPage($this->plugin_name, $this->version);
        add_options_page(
            'Rapidmail Connector Settings',
            'Rapidmail',
            'manage_options',
            $this->plugin_name . '-settings',
            [$settings_page, 'render']
        );

        $newsletter_settings_page = new NewsletterSettingsPage(
            $this->plugin_name, $this->version
        );
        add_submenu_page(
            null,
            'Rapidmail Newsletter Settings',
            'Rapidmail Newsletter',
            'manage_options',
            $this->plugin_name . '-newsletter-settings',
            [$newsletter_settings_page, 'render']
        );

        add_action('admin_init', [$settings_page, 'register_settings']);
        add_action('admin_init', [$newsletter_settings_page, 'register_settings']);
    }

    public function create_connection()
    {
        global $wpdb;

        if (!current_user_can('manage_woocommerce')) {
            wp_die(-1);
        }

        $consumerKey = get_option('rapidmail_consumer_key');
        $secretKey = get_option('rapidmail_secret_key');

        // check if key still exists
        if ($secretKey && $consumerKey) {

            $key = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT consumer_key, consumer_secret
				FROM {$wpdb->prefix}woocommerce_api_keys
				WHERE description LIKE 'rapidmail%' AND consumer_secret = %s",
                    $secretKey
                ),
                ARRAY_A
            );

            // reset secret/consumer key if key does not exists anymore
            if ($key === null) {
                $secretKey = $consumerKey = null;
            }
        }

        if (!$consumerKey || !$secretKey) {
            require_once(plugin_dir_path(dirname(__FILE__)) . 'admin/Auth.php');
            $auth = new Auth();
            $data = $auth->create_key();
            $consumerKey = $data['consumer_key'];
            $secretKey = $data['consumer_secret'];

            update_option('rapidmail_consumer_key', $consumerKey);
            update_option('rapidmail_secret_key', $secretKey);
        }

        $plugins = get_plugins();
        $url = rest_url();

        $payload = [
            'accessKey' => $consumerKey,
            'secretAccessKey' => $secretKey,
        ];

        $shop = [
            'type' => 'woo_commerce',
            'version' => $GLOBALS['wp_version'],
            'woocommerceVersion' => $plugins['woocommerce/woocommerce.php']['Version'],
            'url' => $url,
        ];

        $data = [
            'type' => $shop['type'],
            'connection' => $this->getEncrypter()->encrypt(compact('payload', 'shop')),
            'url' => $url,
            'payload' => $payload,
        ];

        wp_send_json_success($data);
    }

    public function connection_page()
    {
        $options = get_option('rapidmail_connector');

        if (!is_array($options) || empty($options['connect_url']) || empty($options['overview_url'])) {
            wp_redirect(admin_url('options-general.php?page=rapidmail-connector-settings'));
        }

        require_once 'partials/' . $this->plugin_name . '-admin-connection-display.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/rapidmail-connector-admin.css',
            [],
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/rapidmail-connector-admin.js',
            ['jquery'],
            $this->version,
            false
        );
    }

}
