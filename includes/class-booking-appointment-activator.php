<?php

/**
 * Fired during plugin activation
 *
 * @link       https://stridedge.com
 * @since      1.0.0
 *
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/includes
 * @author     StridEdge Technology Company <irfan@stridedge.com>
 */
class Booking_Appointment_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$ba_entries = $wpdb->prefix . 'ba_entries_check';
		$ba_holidays = $wpdb->prefix . 'ba_holidays_check';
		
		$charset_collate = $wpdb->get_charset_collate();
		if($wpdb->get_var($wpdb->prepare("show tables like %s", $ba_entries) ) != $ba_entries ){
			$sql = "CREATE TABLE `$ba_entries` (
			  `id` int(11) NOT NULL auto_increment,
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `slot` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `status` tinyint(1) NOT NULL DEFAULT '0',
			  `date` date NOT NULL,
			  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY id (id)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		if($wpdb->get_var($wpdb->prepare("show tables like %s", $ba_holidays) ) != $ba_holidays ){
			$sql = "CREATE TABLE `$ba_holidays` (
			  `id` int(11) NOT NULL auto_increment,
			  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `description` text COLLATE utf8_unicode_ci NOT NULL,
			  `status` tinyint(1) NOT NULL DEFAULT '1',
			  `date` date NOT NULL,
			  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY id (id)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

}
