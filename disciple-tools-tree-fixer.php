<?php
require_once 'vendor/autoload.php';

/**
 * Plugin Name: Disciple Tools - Tree Fixer
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-tree-fixer
 * Description: Disciple Tools - Tree Fixer is intended to help developers and integrator jumpstart their extension of the Disciple Tools system.
 * Text Domain: disciple-tools-tree-fixer
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-tree-fixer
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Gets the instance of the `Disciple_Tools_Tree_Fixer` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function disciple_tools_tree_fixer() {
    $disciple_tools_tree_fixer_required_dt_theme_version = '1.0';
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;

    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = strpos( $wp_theme->get_template(), 'disciple-tools-theme' ) !== false || $wp_theme->name === 'Disciple Tools';
    if ( $is_theme_dt && version_compare( $version, $disciple_tools_tree_fixer_required_dt_theme_version, '<' ) ) {
        add_action( 'admin_notices', 'Disciple_Tools_Tree_Fixer_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }
    if ( !$is_theme_dt ){
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }

    return Disciple_Tools_Tree_Fixer::instance();
}
add_action( 'after_setup_theme', 'disciple_tools_tree_fixer', 20 );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class Disciple_Tools_Tree_Fixer {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $is_rest = dt_is_rest();

        if ( $is_rest && strpos( dt_get_url_path(), 'dt-tree-fixer' ) !== false ) {
            require_once 'rest-api/rest-api.php'; // adds starter rest api class
        }

        if ( is_admin() ) {
            require_once 'admin/admin-menu-and-tabs.php'; // adds starter admin page and section for plugin
        }

        $this->i18n();
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {
        // add elements here that need to fire on activation
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        // add functions here that need to happen on deactivation
        delete_option( 'dismissed-disciple-tools-tree-fixer' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'disciple-tools-tree-fixer';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'disciple-tools-tree-fixer';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( 'Disciple_Tools_Tree_Fixer::' . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}


// Register activation hook.
register_activation_hook( __FILE__, array( 'Disciple_Tools_Tree_Fixer', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'Disciple_Tools_Tree_Fixer', 'deactivation' ) );


if ( ! function_exists( 'Disciple_Tools_Tree_Fixer_hook_admin_notice' ) ) {
    function disciple_tools_tree_fixer_hook_admin_notice() {
        global $disciple_tools_tree_fixer_required_dt_theme_version;
        $wp_theme = wp_get_theme();
        $current_version = $wp_theme->version;
        $message = "'Disciple Tools - Tree Fixer' plugin requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or make sure it is latest version.";
        if ( $wp_theme->get_template() === 'disciple-tools-theme' ){
            $message .= ' ' . sprintf( esc_html( 'Current Disciple Tools version: %1$s, required version: %2$s' ), esc_html( $current_version ), esc_html( $disciple_tools_tree_fixer_required_dt_theme_version ) );
        }
        // Check if it's been dismissed...
        if ( ! get_option( 'dismissed-disciple-tools-tree-fixer', false ) ) { ?>
            <div class="notice notice-error notice-disciple-tools-tree-fixer is-dismissible" data-notice="disciple-tools-tree-fixer">
                <p><?php echo esc_html( $message );?></p>
            </div>
            <script>
                jQuery(function($) {
                    $( document ).on( 'click', '.notice-disciple-tools-tree-fixer .notice-dismiss', function () {
                        $.ajax( ajaxurl, {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: 'disciple-tools-tree-fixer',
                                security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                            }
                        })
                    });
                });
            </script>
        <?php }
    }
}

/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( ! function_exists( 'dt_hook_ajax_notice_handler' ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST['type'] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}


/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
add_action( 'plugins_loaded', function () {
    if ( is_admin() ){
        // Check for plugin updates
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ){
                require get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php';
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ){
                Puc_v4_Factory::buildUpdateChecker(
                    'https://raw.githubusercontent.com/thecodezone/disciple-tools-tree-fixer/master/version-control.json',
                    __FILE__,
                    'disciple-tools-tree-fixer'
                );

        }
    }
} );
