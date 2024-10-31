<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.rapidmail.de/
 * @since      1.0.0
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/includes
 */

namespace Rapidmail\Connector;

use Rapidmail\Connector\Admin\Admin;
use Rapidmail\Connector\Admin\NewsletterSettingsPage;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/includes
 * @author     rapidmail GmbH <ebess@rapidmail.de>
 */
class Connector
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('RAPIDMAIL_CONNECTOR_VERSION')) {
            $this->version = RAPIDMAIL_CONNECTOR_VERSION;
        } else {
            $this->version = '1.0.1';
        }
        $this->plugin_name = 'rapidmail-connector';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->load_filter();

        $this->extend_register_form();
        $this->extend_checkout_form();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Rapidmail_Connector_Loader. Orchestrates the hooks of the plugin.
     * - Rapidmail_Connector_i18n. Defines internationalization functionality.
     * - Rapidmail_Connector_Admin. Defines all hooks for the admin area.
     * - Rapidmail_Connector_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * load all rapidmail-connector classes
         */

        foreach (glob(plugin_dir_path(dirname(__FILE__)) . '**/*.php') as $file) {
            // ignore auth class, WC_Auth is not available at this time, class will be loaded when its required
            if (stristr($file, 'Auth.php') || stristr($file, 'index.php')) {
                continue;
            }

            require_once $file;
        }

        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Rapidmail_Connector_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new I18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_menu', 71);
        $this->loader->add_action('wp_ajax_rapidmail_create_connection', $plugin_admin, 'create_connection');
        $this->loader->add_action('admin_init', $plugin_admin, 'admin_init');

        add_action(
            'rest_api_init',
            function () {
                $this->define_rest_api();
            }
        );
    }

    private function extend_register_form()
    {
        if (!$this->is_newsletter_enabled() || !$this->is_newsletter_location_enabled('register')) {
            return;
        }

        // maybe add checkbox
        add_action(
            'woocommerce_register_form',
            function () {
                $value = $this->get_newsletter_default() ? 1 : 0;
                if (!empty($_POST)) {
                    $value = isset($_POST['rapidmail_newsletter']) && (int)$_POST['rapidmail_newsletter'] === 1 ? 1 : 0;
                }

                woocommerce_form_field(
                    'rapidmail_newsletter',
                    [
                        'type' => 'checkbox',
                        'value' => 1,
                        'label' => $this->get_newsletter_label(),
                        'default' => $this->get_newsletter_default() ? 1 : 0,
                    ],
                    $value
                );
            }
        );

        // save user meta
        add_action(
            'woocommerce_created_customer',
            function ($customer_id) {
                $this->user_save_newsletter(
                    $customer_id,
                    isset($_POST['rapidmail_newsletter']) && (int)$_POST['rapidmail_newsletter'] === 1
                );
            }
        );
    }

    private function extend_checkout_form()
    {
        if (!$this->is_newsletter_enabled()) {
            return;
        }

        add_filter(
            'woocommerce_checkout_fields',
            function ($checkout_fields) {
                $option = [
                    'type' => 'checkbox',
                    'label' => $this->get_newsletter_label(),
                    'default' => $this->get_newsletter_default() ? 1 : 0,
                ];

                if ($this->is_newsletter_location_enabled('billing')) {
                    $checkout_fields['billing']['rapidmail_newsletter'] = $option;
                }

                return $checkout_fields;
            }
        );

        add_action(
            'woocommerce_review_order_before_submit',
            function () {
                if ($this->is_newsletter_location_enabled('submit')) {
                    ?>
                    <p id="rapidmail_newsletter_field" class="form-row terms woocommerce-validated col2-set"
                       style="clear:both;width:100%;">
                        <label class="checkbox">
                            <input type="checkbox" class="input-checkbox" name="rapidmail_newsletter" value="1"
                            <?php echo $this->get_newsletter_default() ? 'checked' : ''; ?>>
                            <?php echo $this->get_newsletter_label(); ?>
                        </label>
                    </p>
                    <?php
                }
            }
        );

        // save
        add_action(
            'woocommerce_checkout_update_order_meta',
            function ($order_id) {
                if (
                    $this->is_newsletter_location_enabled('billing') ||
                    $this->is_newsletter_location_enabled('submit')
                ) {
                    $this->user_save_newsletter(
                        wc_get_order($order_id)->get_customer_id(),
                        isset($_POST['rapidmail_newsletter']) && (int)$_POST['rapidmail_newsletter'] === 1 ? 1 : 0
                    );
                }
            }
        );
    }

    private function get_newsletter_label($escape = true)
    {
        $options = get_option('rapidmail_connector_newsletter', []);

        $label = isset($options['label']) && !empty($options['label'])
            ? $options['label']
            : NewsletterSettingsPage::get_default_label();

        return $escape ? esc_html($label) : $label;
    }

    private function get_newsletter_default()
    {
        $user_option = get_user_option('rapidmail_newsletter');
        if ($user_option !== false) {
            return (int)$user_option === 1;
        }

        $options = get_option('rapidmail_connector_newsletter', []);

        return isset($options['default']) && $options['default'];
    }

    private function is_newsletter_enabled()
    {
        $options = get_option('rapidmail_connector_newsletter', []);

        return isset($options['enabled']) && $options['enabled'];
    }

    private function is_newsletter_location_enabled($location)
    {
        $options = get_option('rapidmail_connector_newsletter', []);
        $locations = isset($options['location']) ? $options['location'] : [];

        return in_array($location, $locations);
    }

    private function user_save_newsletter($customer_id, $value)
    {
        update_user_meta($customer_id, 'rapidmail_newsletter', $value ? 1 : 0);
        $this->update_user('update', $customer_id);
    }

    private function update_user($type, $id)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT id FROM ' . $wpdb->prefix . 'rapidmail_changes WHERE type = %s AND model = "customer" AND entity_id = %d;',
                [$type, $id]
            )
        );

        if ($row === null) {
            $wpdb->insert(
                $wpdb->prefix . 'rapidmail_changes',
                [
                    'model' => 'customer',
                    'type' => $type,
                    'entity_id' => $id,
                    'created_at' => current_time('mysql', true),
                ]
            );
        } else {
            $wpdb->update(
                $wpdb->prefix . 'rapidmail_changes',
                ['created_at' => current_time('mysql', true)],
                ['id' => $row->id]
            );
        }
    }

    private function load_filter()
    {
        // woocommerce_delete_customer
        add_filter(
            'deleted_user',
            function ($id) {
                $this->update_user('delete', $id);
            }
        );
        // woocommerce_update_customer
        add_filter(
            'profile_update',
            function ($id) {
                $this->update_user('update', $id);
            }
        );
        add_filter(
            'woocommerce_created_customer',
            function ($id) {
                $this->update_user('create', $id);
            }
        );

        add_filter('plugin_action_links_' . $this->plugin_main_file(), [$this, 'admin_plugin_links']);
    }

    private function plugin_main_file()
    {
        return plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');
    }

    public function admin_plugin_links($links)
    {
        array_unshift(
            $links,
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings') . '</a>'
        );

        return $links;
    }

    /**
     * Register rest api endpoints
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_rest_api()
    {
        register_rest_route(
            'wc/v3',
            'rapidmail',
            [
                'methods' => 'GET',
                'permission_callback' => function () {
                    if (!wc_rest_check_user_permissions('read')) {
                        return new \WP_Error(
                            'woocommerce_rest_cannot_view',
                            __('Sorry, you cannot list resources.', 'woocommerce'),
                            ['status' => rest_authorization_required_code()]
                        );
                    }

                    return true;
                },
                'callback' => function ($request) {
                    global $wpdb;

                    $parameters = $request->get_params();

                    $where = $whereParameters = [];

                    if (isset($parameters['type']) && in_array($parameters['type'], ['delete', 'update', 'create'])) {
                        $where[] = 'type = %s';
                        $whereParameters[] = $parameters['type'];
                    }
                    if (isset($parameters['model']) && in_array($parameters['model'], ['customer'])) {
                        $where[] = 'model = %s';
                        $whereParameters[] = $parameters['model'];
                    }
                    if (isset($parameters['start'])) {
                        $dt = \DateTime::createFromFormat("Y-m-d", $parameters['start']);
                        if ($dt !== false) {
                            $where[] = 'created_at >= %s';
                            $whereParameters[] = $parameters['start'];
                        }
                    }
                    if (isset($parameters['end'])) {
                        $dt = \DateTime::createFromFormat("Y-m-d", $parameters['end']);
                        if ($dt !== false) {
                            $where[] = 'created_at <= %s';
                            $whereParameters[] = $parameters['end'];
                        }
                    }

                    $page = (isset($parameters['page']) ? $parameters['page'] : 1) - 1;
                    $per_page = isset($parameters['per_page']) ? $parameters['per_page'] : 100;

                    $whereString = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

                    $total_changes = (int)$wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT COUNT(*) as count FROM {$wpdb->prefix}rapidmail_changes" . $whereString,
                            $whereParameters
                        )
                    )[0];

                    $results = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}rapidmail_changes" . $whereString . ' LIMIT ' . ($page * $per_page) . ',' . $per_page,
                            $whereParameters
                        )
                    );

                    $max_pages = ceil($total_changes / $per_page);

                    $response = rest_ensure_response($results);
                    $response->header('X-WP-Total', (int)$total_changes);
                    $response->header('X-WP-TotalPages', (int)$max_pages);

                    return $response;
                },
            ]
        );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

}
