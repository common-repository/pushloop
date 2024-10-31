<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

/**
 * Filter all options
 *
 * @param string $pushloop_keyword Keyword to search.
 */
function pushloop_delete_options_by_keyword( $pushloop_keyword ) {
	$pushloop_all_options = wp_load_alloptions();

	foreach ( $pushloop_all_options as $pushloop_option_name => $pushloop_option_value ) {
		if ( false !== strpos( $pushloop_option_name, $pushloop_keyword ) ) {
			delete_option( $pushloop_option_name );
		}
	}
}
pushloop_delete_options_by_keyword( 'pushloop' );

/**
 * Delete all scheduled jobs.
 */
function unschedule_daily_send_file() {
	$send_file = wp_next_scheduled( 'pushloop_daily_send_log_file' );
	if ( $send_file ) {
		wp_unschedule_event( $send_file, 'pushloop_daily_send_log_file' );
	}
	$clean_folder = wp_next_scheduled( 'pushloop_folder_clean_event' );
	if ( $clean_folder ) {
		wp_unschedule_event( $clean_folder, 'pushloop_folder_clean_event' );
	}
	$check_abandoned_cart = wp_next_scheduled( 'pushloop_check_abandoned_cart' );
	if ( $check_abandoned_cart ) {
		wp_unschedule_event( $check_abandoned_cart, 'pushloop_check_abandoned_cart' );
	}
	$check_enabled_site = wp_next_scheduled( 'pushloop_check_enabled_site' );
	if ( $check_enabled_site ) {
		wp_unschedule_event( $check_enabled_site, 'pushloop_check_enabled_site' );
	}
}
unschedule_daily_send_file();

/**
 * Delete all pushloop user meta.
 */
function pushloop_remove_all_user_meta() {
	$users = get_users( array( 'fields' => 'ID' ) );

	foreach ( $users as $user_id ) {
		delete_user_meta( $user_id, 'pushloop_user_token' );
		delete_user_meta( $user_id, 'pushloop_last_cart_activity_time' );
	}
}
pushloop_remove_all_user_meta();

/**
 * Delete pushloop table.
 */
function pushloop_delete_table() {
	global $wpdb;
	$table_name = esc_sql( $wpdb->prefix . 'pushloop_cart' );
	$sql        = $wpdb->prepare( "DROP TABLE IF EXISTS $table_name;" );
	$wpdb->query( $sql );
}
pushloop_delete_table();
