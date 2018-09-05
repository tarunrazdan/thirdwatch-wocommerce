<?php

defined( 'ABSPATH' ) || exit;

/**
 * TW_Install Class.
 */
class TW_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_filter( 'plugin_action_links_' . TW_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
	}

	public static function install() {
		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'tw_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'tw_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		wc_maybe_define_constant( 'WC_INSTALLING', true );

		self::create_tables();
		delete_transient( 'wc_installing' );
	}

	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
CREATE TABLE {$wpdb->prefix}tw_orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id char(100) NOT NULL,
  status char(100) NOT NULL,
  flag char(10) NULL,
  action char(10) NULL,
  message longtext NULL,
  score char(10) NULL DEFAULT NULL,
  date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  UNIQUE KEY order_id (order_id)
) $collate;";
		return $tables;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param   mixed $links Plugin Action links.
	 * @return  array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=woocommerce-thirdwatch' ) . '" aria-label="' . esc_attr__( 'View Thirdwatch settings', 'thirdwatch' ) . '">' . esc_html__( 'Settings', 'thirdwatch' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}
}

TW_Install::init();
