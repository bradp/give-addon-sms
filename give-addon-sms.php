<?php
/**
 * Plugin Name: Give - Addon SMS
 * Plugin URI:  https://github.com/impress-org/give-addon-sms
 * Description: .
 * Version:     1.0
 * Author:      WordImpress, LLC
 * Author URI:  https://wordimpress.com
 * License:     GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: give-addon-sms
 */


/**
 * Our Globals for easy Reference.
 * You'll want to make sure you replace "GIVE_ADDON_SMS"
 * with your own prefix throughout this whole plugin.
 *
 * Functions are prefixed with "give_boilerplate" and should be replaced as well.
 *
 * The text domain is give-addon-sms and should be replaced as well.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Addon_SMS
 */
final class Give_Addon_SMS {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Addon_SMS
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return Give_Addon_SMS
	 * @since
	 * @access public
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Addon_SMS ) ) {
			self::$instance = new Give_Addon_SMS();
			self::$instance->setup();
		}

		return self::$instance;
	}


	/**
	 * Setup
	 *
	 * @since
	 * @access private
	 */
	private function setup() {
		self::$instance->setup_constants();

		register_activation_hook( GIVE_ADDON_SMS_FILE, array( $this, 'install' ) );
		add_action( 'give_init', array( $this, 'init' ), 10, 1 );
		add_action( 'plugins_loaded', array( $this, 'check_environment' ), 999 );
		add_filter( 'give-settings_get_settings_pages', array( $this, 'register_setting_page' ) );
	}


	/**
	 * Setup constants
	 *
	 * Defines useful constants to use throughout the add-on.
	 *
	 * @since
	 * @access private
	 */
	private function setup_constants() {

		// Defines addon version number for easy reference.
		if ( ! defined( 'GIVE_ADDON_SMS_VERSION' ) ) {
			define( 'GIVE_ADDON_SMS_VERSION', '1.0' );
		}

		// Set it to latest.
		if ( ! defined( 'GIVE_ADDON_SMS_MIN_GIVE_VERSION' ) ) {
			define( 'GIVE_ADDON_SMS_MIN_GIVE_VERSION', '2.3.0' );
		}

		if ( ! defined( 'GIVE_ADDON_SMS_FILE' ) ) {
			define( 'GIVE_ADDON_SMS_FILE', __FILE__ );
		}

		if ( ! defined( 'GIVE_ADDON_SMS_DIR' ) ) {
			define( 'GIVE_ADDON_SMS_DIR', plugin_dir_path( GIVE_ADDON_SMS_FILE ) );
		}

		if ( ! defined( 'GIVE_ADDON_SMS_URL' ) ) {
			define( 'GIVE_ADDON_SMS_URL', plugin_dir_url( GIVE_ADDON_SMS_FILE ) );
		}

		if ( ! defined( 'GIVE_ADDON_SMS_BASENAME' ) ) {
			define( 'GIVE_ADDON_SMS_BASENAME', plugin_basename( GIVE_ADDON_SMS_FILE ) );
		}
	}


	/**
	 * Plugin installation
	 *
	 * @since
	 * @access public
	 */
	public function install() {
		// Bailout.
		if ( ! self::$instance->check_environment() ) {
			return;
		}
	}

	/**
	 * Plugin installation
	 *
	 * @param Give $give
	 *
	 * @return void
	 * @since
	 * @access public
	 *
	 */
	public function init( $give ) {
		if ( ! self::$instance->check_environment() ) {
			return;
		}

		require_once __DIR__ . '/vendor/autoload.php';

		self::$instance->load_files();
		self::$instance->setup_hooks();
		self::$instance->load_license();
	}


	/**
	 * Check plugin environment
	 *
	 * @return bool|null
	 * @since
	 * @access public
	 *
	 */
	public function check_environment() {
		// Bailout.
		if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
			return null;
		}

		// Load plugin helper functions.
		if ( ! function_exists( 'deactivate_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Load helper functions.
		require_once GIVE_ADDON_SMS_DIR . 'includes/misc-functions.php';

		// Flag to check whether deactivate plugin or not.
		$is_deactivate_plugin = false;

		// Verify dependency cases.
		switch ( true ) {
			case doing_action( 'give_init' ):
				if (
					defined( 'GIVE_VERSION' ) &&
					version_compare( GIVE_VERSION, GIVE_ADDON_SMS_MIN_GIVE_VERSION, '<' )
				) {
					/* Min. Give. plugin version. */

					// Show admin notice.
					add_action( 'admin_notices', '__give_addon_sms_dependency_notice' );

					$is_deactivate_plugin = true;
				}

				break;

			case doing_action( 'activate_' . GIVE_ADDON_SMS_BASENAME ):
			case doing_action( 'plugins_loaded' ) && ! did_action( 'give_init' ):
				/* Check to see if Give is activated, if it isn't deactivate and show a banner. */

				// Check for if give plugin activate or not.
				$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

				if ( ! $is_give_active ) {
					add_action( 'admin_notices', '__give_addon_sms_inactive_notice' );

					$is_deactivate_plugin = true;
				}

				break;
		}

		// Don't let this plugin activate.
		if ( $is_deactivate_plugin ) {

			// Deactivate plugin.
			deactivate_plugins( GIVE_ADDON_SMS_BASENAME );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return false;
		}

		return true;
	}

	/**
	 * Register setting page.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	function register_setting_page( $settings ) {
		require_once GIVE_ADDON_SMS_DIR . 'includes/admin/settings.php';

		$settings[] = new Give_SMS_Admin_Settings();

		return $settings;
	}

	/**
	 * Load plugin files.
	 *
	 * @since
	 * @access private
	 */
	private function load_files() {
		require_once GIVE_ADDON_SMS_DIR . 'includes/misc-functions.php';
		require_once GIVE_ADDON_SMS_DIR . 'includes/sms.php';

		if ( is_admin() ) {
			require_once GIVE_ADDON_SMS_DIR . 'includes/admin/setting-examples.php';
		}
	}


	/**
	 * Setup hooks
	 *
	 * @since
	 * @access private
	 */
	private function setup_hooks() {
		// Filters
		add_filter( 'plugin_action_links_' . GIVE_ADDON_SMS_BASENAME, '__give_addon_sms_plugin_row_meta', 10, 2 );

		// Actions
		add_action( 'admin_init', '__give_addon_sms_activation_banner' );
	}


	/**
	 * Load license
	 *
	 * @since
	 * @access private
	 */
	private function load_license() {
		new Give_License(
			GIVE_ADDON_SMS_FILE,
			'Give Addon Boilerplate',
			GIVE_ADDON_SMS_VERSION,
			'WordImpress',
			'give_addon_sms_license_key'
		);
	}
}

/**
 * The main function responsible for returning the one true Give_Addon_SMS instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $recurring = Give_Addon_SMS(); ?>
 *
 * @return Give_Addon_SMS|bool
 * @since 1.0
 *
 */
function Give_Addon_SMS() {
	return Give_Addon_SMS::get_instance();
}

Give_Addon_SMS();
