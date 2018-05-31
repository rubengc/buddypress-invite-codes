<?php
/**
 * Plugin Name: BuddyPress Invite Codes
 * Plugin URI:
 * Description: Add members to groups based on invite codes.
 * Tags: buddypress, invite codes, invite, groups, invitation
 * Author: Ruben Garcia
 * Version: 1.0.0
 * Author URI: https://wordpress.org/
 * License: GNU AGPLv3
 * License URI: http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @package BuddyPress Invite Codes
 * @version 1.0.0
 */

class BuddyPress_Invite_Codes {

	public $version = '1.0.0';

	public $basename;
	public $directory_path;
	public $directory_url;

	function __construct() {

		// Define plugin constants
		$this->basename = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );
		$this->directory_url = plugin_dir_url( __FILE__ );

		// Load translations
		load_plugin_textdomain( 'bp-invite-codes', false, 'bp-invite-codes/languages' );

		// Run our activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// If BuddyPress is unavailable, deactivate our plugin
		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );

		add_action( 'bp_include', array( $this, 'bp_include' ) );

		// Load custom js and css
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 999 );

	}

	/**
	 * Files to include with BuddyPress
	 *
	 * @since 1.0.0
	 */
	public function bp_include() {

		if ( $this->meets_requirements() ) {

			require_once( $this->directory_path . 'cmb2/init.php' );

			if ( is_admin() ) {
				require_once( $this->directory_path . 'includes/settings.php' );
			}

			require_once( $this->directory_path . 'includes/functions.php' );

		}
	}

	/**
	 * Enqueue custom scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		// If we're on a BP group page
		global $bp;

		if ( isset( $bp->current_component ) && 'groups' == $bp->current_component ) {
			wp_enqueue_script( 'bp-invite-codes', $this->directory_url . 'js/bp-invite-codes.js', array( 'jquery' ) );

			wp_localize_script(
				'bp-invite-codes',
				'bp_invite_codes',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
					'prompt' => __( 'You must enter an invite code to join this group.', 'bp-invite-codes' )
				)
			);
		}

	}

	/**
	 * Enqueue custom scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {

		// If need to load css for admin pages
		if ( isset( $_GET[ 'page' ] ) && 'bp_invite_codes_settings' == $_GET[ 'page' ] ) {
			wp_enqueue_style( 'bp-invite-codes-admin', $this->directory_url . 'css/admin.css', array(), '1.0' );
		}

	}

	/**
	 * Activation hook for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function activate() {

	}

	/**
	 * Check if BuddyPress is available
	 *
	 * @since  1.0.0
	 * @return bool True if BuddyPress is available, false otherwise
	 */
	public static function meets_requirements() {

		if ( class_exists( 'BuddyPress' ) && class_exists( 'BP_Groups_Group' ) ) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Generate a custom error message and deactivates the plugin if we don't meet requirements
	 *
	 * @since 1.0.0
	 */
	public function maybe_disable_plugin() {

		if ( !$this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';

			if ( !class_exists( 'BuddyPress' ) ) {
				echo '<p>' . sprintf( __( 'BuddyPress Invite Codes requires BuddyPress and has been <a href="%s">deactivated</a>. Please install and activate BuddyPress and then reactivate this plugin.', 'bp-invite-codes' ), admin_url( 'plugins.php' ) ) . '</p>';
			}
			elseif ( !class_exists( 'BP_Groups_Group' ) || !bp_is_active( 'groups' ) ) {
				echo '<p>' . sprintf( __( 'BuddyPress Invite Codes requires BuddyPress Groups be enabled and has been <a href="%s">deactivated</a>. Please activate <a href="%s">BuddyPress Groups</a> and then reactivate this plugin.', 'bp-invite-codes' ), admin_url( 'plugins.php' ), admin_url( 'options-general.php?page=bp-components' ) ) . '</p>';
			}

			echo '</div>';

			// Deactivate our plugin
			deactivate_plugins( $this->basename );
		}

	}
}

global $buddypress_invite_codes;
$buddypress_invite_codes = new BuddyPress_Invite_Codes;