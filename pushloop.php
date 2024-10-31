<?php
/**
 * Plugin Name: Pushloop
 * Plugin URI: https://app.pushloop.io
 * Description: Pushloop is a programmatic self-service web push notification platform. Send push from WP backend!
 * Version: 1.4.11
 * Author: Valorize
 * Author URI: https://valorize.io
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Pushloop
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PUSHLOOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'PUSHLOOP_URL', plugin_dir_url( __FILE__ ) );

// Includes.
require PUSHLOOP_PATH . '/js/pushloop-enqueue-scripts.php';
require PUSHLOOP_PATH . '/admin/pushloop-functions.php';
require PUSHLOOP_PATH . '/admin/pushloop-menu.php';
require PUSHLOOP_PATH . '/admin/pushloop-settings.php';
require PUSHLOOP_PATH . '/admin/pushloop-ecommerce.php';
require PUSHLOOP_PATH . '/admin/pushloop-send.php';
require PUSHLOOP_PATH . '/admin/pushloop-dashboard.php';
require PUSHLOOP_PATH . '/admin/pushloop-monetization.php';
require PUSHLOOP_PATH . '/amp/pushloop-amp.php';
require PUSHLOOP_PATH . '/includes/class-pushlooplogger.php';

if ( ! function_exists( 'get_filesystem_method' ) ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
}

// Define Hooks.
add_action( 'plugins_loaded', 'pushloop_define_version' );
add_action( 'plugins_loaded', 'pushloop_install_db' );
add_action( 'admin_init', 'pushloop_admin_init' );
add_action( 'admin_notices', 'pushloop_warn_onactivate' );
add_action( 'add_meta_boxes', 'pushloop_push_notification_box_init' );
add_action( 'admin_enqueue_scripts', 'pushloop_load_pushloop_assets' );
add_action( 'wp_enqueue_scripts', 'pushloop_enqueue_scripts' );
add_action( 'wp_ajax_pushloop_save_user_token', 'pushloop_save_user_token' );
add_action( 'wp_ajax_nopriv_pushloop_save_user_token', 'pushloop_save_user_token' );
add_action( 'wp', 'pushloop_schedule_jobs' );
add_action( 'pushloop_daily_send_log_file', 'PushloopLogger::send_file' );
add_action( 'pushloop_folder_clean_event', 'PushloopLogger::clean_log_folder' );
add_action( 'pushloop_folder_clean_event', 'pushloop_clean_db' );
add_action( 'pushloop_check_abandoned_cart', 'pushloop_abandoned_cart' );
add_action( 'pushloop_check_enabled_site', 'pushloop_check_site_is_enabled' );

if ( get_option( 'pushloop_default_for_post' ) === 'on' ) {
	add_action( 'transition_post_status', 'pushloop_save_push', 10, 3 );
} else {
	add_action( 'wp_insert_post', 'pushloop_new_post', 10, 3 );
}

if ( get_option( 'pushloop_ecommerce_order_is_changed' ) ) {
	add_action( 'woocommerce_order_status_changed', 'pushloop_order_status_update' );
}
if ( get_option( 'pushloop_ecommerce_order_is_failed' ) ) {
	add_action( 'woocommerce_order_status_failed', 'pushloop_order_status_update' );
}
if ( get_option( 'pushloop_ecommerce_order_is_completed' ) ) {
	add_action( 'woocommerce_order_status_completed', 'pushloop_order_status_update' );
}
if ( get_option( 'pushloop_ecommerce_order_is_refunded' ) ) {
	add_action( 'woocommerce_order_status_refunded', 'pushloop_order_status_update' );
}
if ( get_option( 'pushloop_ecommerce_order_payment_complete' ) ) {
	add_action( 'woocommerce_payment_complete', 'pushloop_order_status_update' );
}
if ( get_option( 'pushloop_ecommerce_order_is_cancelled' ) ) {
	add_action( 'woocommerce_order_status_cancelled', 'pushloop_order_status_update' );
}
if ( get_option( 'pushloop_ecommerce_product_update' ) || get_option( 'pushloop_ecommerce_product_in_discount' ) ) {
	add_action( 'woocommerce_update_product', 'pushloop_product_update', 10, 1 );
}
if ( get_option( 'pushloop_ecommerce_cart_abandoned' ) ) {
	add_action( 'woocommerce_cart_updated', 'pushloop_track_cart_activity' );
}
register_activation_hook( __FILE__, 'pushloop_init_options' );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pushloop_plugin_settings_link' );

/**
 * Include custom JS & CSS.
 *
 * @param string $hook_suffix hook suffix.
 */
function pushloop_load_pushloop_assets( $hook_suffix ) {
	PushloopLogger::info( 'Version ' . PUSHLOOP_PLUGIN_VERSION );
	$apl               = get_option( 'active_plugins' );
	$plugins           = get_plugins();
	$activated_plugins = array();
	foreach ( $apl as $p ) {
		if ( isset( $plugins[ $p ] ) ) {
			array_push( $activated_plugins, $plugins[ $p ] );
		}
	}
	foreach ( $activated_plugins as $plugin ) {
		PushloopLogger::info( 'Plugin: ' . esc_html( $plugin['Name'] ) . ' Ver. ' . esc_html( $plugin['Version'] ) );
	}

	try {
		wp_register_style( 'pushloop_style_css', plugins_url( '/css/pushloop.css', __FILE__ ), array(), PUSHLOOP_PLUGIN_VERSION );
		wp_register_script( 'pushloop_javascript_js', plugins_url( '/js/pushloop.js', __FILE__ ), array(), PUSHLOOP_PLUGIN_VERSION, true );
		wp_enqueue_style( 'pushloop_style_css' );
		wp_enqueue_script( 'pushloop_javascript_js' );

		if ( str_contains( $hook_suffix, 'page_pushloop' ) ) {
			wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css', array(), PUSHLOOP_PLUGIN_VERSION, false );
			wp_enqueue_script( 'datatables-js', 'https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js', array( 'jquery' ), PUSHLOOP_PLUGIN_VERSION, false );
			wp_enqueue_script( 'moment-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array(), PUSHLOOP_PLUGIN_VERSION, false );
			wp_enqueue_style( 'daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array(), PUSHLOOP_PLUGIN_VERSION, false );
			wp_enqueue_script( 'daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array( 'moment-js' ), PUSHLOOP_PLUGIN_VERSION, false );
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_load_pushloop_assets: ' . $th->__toString() );
	}
}

/**
 * Define Pushloop version constant.
 */
function pushloop_define_version() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$pushloop_plugin_data = get_plugin_data( __FILE__ );
	if ( isset( $pushloop_plugin_data['Version'] ) ) {
		define( 'PUSHLOOP_PLUGIN_VERSION', $pushloop_plugin_data['Version'] );
	}
}

/**
 * Install DB.
 */
function pushloop_install_db() {
	try {
		if ( get_option( '_pushloop_version', '1.0.0' ) !== PUSHLOOP_PLUGIN_VERSION ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'pushloop_cart';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				customer_id char(100) NOT NULL,
				last_update varchar(100) DEFAULT NULL,
				token varchar(191) DEFAULT NULL,
				data json DEFAULT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			update_option( '_pushloop_version', PUSHLOOP_PLUGIN_VERSION );
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_install_db: ' . $th->__toString() );
	}
}

/**
 * Pushloop initialisation.
 */
function pushloop_admin_init() {
	register_setting(
		'pushloop',
		'pushloop_web_id'
	);

	register_setting(
		'pushloop',
		'pushloop_api_key'
	);

	register_setting(
		'pushloop',
		'pushloop_utm_source'
	);

	register_setting(
		'pushloop',
		'pushloop_utm_medium'
	);

	register_setting(
		'pushloop',
		'pushloop_utm_campaign'
	);

	register_setting(
		'pushloop',
		'pushloop_default_title'
	);

	register_setting(
		'pushloop',
		'pushloop_large_image'
	);

	register_setting(
		'pushloop',
		'pushloop_enable_for'
	);

	register_setting(
		'pushloop',
		'pushloop_default_for_post'
	);

	register_setting(
		'pushloop',
		'pushloop_popup_type'
	);

	register_setting(
		'pushloop',
		'pushloop_site_domain'
	);

	register_setting(
		'pushloop',
		'pushloop_amp'
	);

	register_setting(
		'pushloop',
		'pushloop_amp_custom_css'
	);

	register_setting(
		'pushloop',
		'pushloop_gmt'
	);

	register_setting(
		'pushloop',
		'pushloop_gmt_sign'
	);

	if ( is_admin() && get_option( 'pushloop_plugin_activation' ) === 'just-activated' ) {
		delete_option( 'pushloop_plugin_activation' );
	}
}

/**
 * Custom scheduling time
 *
 * @param array $schedules Schedules.
 */
function pushloop_custom_cron_schedules_send_file( $schedules ) {
	try {
		$site_id = get_option( 'pushloop_web_id' );
		if ( $site_id ) {
			$interval                   = 86400 + ( abs( $site_id * $site_id ) % 3601 );
			$schedules['custom_daily']  = array(
				'interval' => $interval,
				'display'  => __( 'Custom Daily' ),
			);
			$schedules['pushloop_1min'] = array(
				'interval' => 60,
				'display'  => __( 'Once every 1 minutes' ),
			);
		}
		return $schedules;

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_custom_cron_schedules_send_file: ' . $th->__toString() );
	}
}
add_filter( 'cron_schedules', 'pushloop_custom_cron_schedules_send_file' );

/**
 * Schedule jobs.
 */
function pushloop_schedule_jobs() {
	try {
		if ( get_option( 'pushloop_web_id' ) ) {
			if ( ! wp_next_scheduled( 'pushloop_daily_send_log_file' ) ) {
				$timestamp = strtotime( 'tomorrow' );
				wp_schedule_event( $timestamp, 'custom_daily', 'pushloop_daily_send_log_file' );
			}
			if ( ! wp_next_scheduled( 'pushloop_folder_clean_event' ) ) {
				$timestamp = strtotime( 'next Sunday 00:00' );
				wp_schedule_event( $timestamp, 'weekly', 'pushloop_folder_clean_event' );
			}
			if ( ! wp_next_scheduled( 'pushloop_check_abandoned_cart' ) ) {
				$timestamp = strtotime( '+1 minutes' );
				wp_schedule_event( $timestamp, 'pushloop_1min', 'pushloop_check_abandoned_cart' );
			}
	
			$pushloop_api_key     = get_option( 'pushloop_api_key' );
			$pushloop_web_id      = get_option( 'pushloop_web_id' );
			$pushloop_site_enable = get_option( 'pushloop_site_enabled' );
			if ( $pushloop_api_key && $pushloop_web_id && ! $pushloop_site_enable && ! wp_next_scheduled( 'pushloop_check_enabled_site' ) ) {
				wp_schedule_event( time(), 'hourly', 'pushloop_check_enabled_site' );
			}
		}

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_schedule_jobs: ' . $th->__toString() );
	}
}

/**
 * Notification box init
 */
function pushloop_push_notification_box_init() {
	$pushloop_api_key = get_option( 'pushloop_api_key' );
	if ( $pushloop_api_key ) {
		wp_enqueue_style( 'pushloop_style_css' );
		wp_enqueue_script( 'pushloop_javascript_js' );

		if ( get_option( 'pushloop_default_for_post' ) !== 'on' ) {
			$screen = get_current_screen();
			// phpcs:disable
			// if ( $screen && 'add' == $screen->action ) {
				$post_types = explode( ',', get_option( 'pushloop_enable_for', 'post,page' ) );
				if ( in_array( $screen->post_type, $post_types, true ) ) {
				add_meta_box( 'pushloop_push_notification', __( 'Pushloop Notification', 'pushloop' ), 'pushloop_push_notification_box', $screen->post_type, 'side', 'high' );
				}
			// }
			// phpcs:enable
		}
	}
}

/**
 * Validate Token
 *
 * @param string $token Plugin Token.
 */
function pushloop_validate_token( $token ) {
	$headers = array(
		'Accept'       => 'application/json',
		'Content-Type' => 'application/json',
	);

	$body = array(
		'pluginToken' => $token,
	);

	return pushloop_api_request( 'https://api.pushloop.io/api/sync', $headers, $body, 'POST', true );
}

/**
 * Check id
 *
 * @param string $api_key Api Key.
 * @param int    $site    Site Id.
 */
function pushloop_check_web_id( $api_key, $site ) {
	$headers = array(
		'ApiKey'       => $api_key,
		'Accept'       => 'application/json',
		'Content-Type' => 'application/json',
	);

	$url = 'https://api.pushloop.io/api/pub/sites/' . $site;

	return pushloop_api_request( $url, $headers, false, 'GET' );
}

/**
 * Activate
 */
function pushloop_warn_onactivate() {
	if ( is_admin() ) {
		$pushloop_api_key     = get_option( 'pushloop_api_key' );
		$pushloop_web_id      = get_option( 'pushloop_web_id' );
		$pushloop_site_enable = get_option( 'pushloop_site_enabled' );

		if ( ! $pushloop_api_key || ! $pushloop_web_id ) {
			echo '<div class="notice notice-warning"><p><strong>Pushloop:</strong> ' . esc_html__( 'Plugin Token and Website ID are required. Update ', 'pushloop' ) . '<a href="' . esc_url( admin_url( 'admin.php?page=pushloop-general-settings' ) ) . '">' . esc_html__( 'settings', 'pushloop' ) . '</a> ' . esc_html__( 'now!', 'pushloop' ) . '</p></div>';
		} elseif ( $pushloop_api_key && $pushloop_web_id && ! $pushloop_site_enable ) {
			echo '<div class="notice notice-warning"><p><strong>Pushloop:</strong> ' . esc_html__( 'Your site is being activated. It is usually active in 24 hours from the time you created it.', 'pushloop' ) . '</p></div>';
		}
	}
}

/**
 * Get Post
 *
 * @param bool $key_value Key value.
 */
function pushloop_get_post_types_allowed( $key_value = false ) {
	$custom_post_types = get_post_types(
		array(
			'public'   => true,
			'_builtin' => false,
		),
		'names',
		'and'
	);

	if ( $key_value ) {
		$post_types_allowed = array_merge(
			$custom_post_types,
			array(
				'post' => 'post',
				'page' => 'page',
			)
		);
	} else {
		$post_types_allowed = array_merge( array_values( $custom_post_types ), array( 'post', 'page' ) );
	}

	return $post_types_allowed;
}

/**
 * Settings link
 *
 * @param array $links Links.
 */
function pushloop_plugin_settings_link( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=pushloop-general-settings' ) . '">' . __( 'Settings', 'Pushloop' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

// HELPERS.

/**
 * Helper to sanitize inputs.
 *
 * @param string $str String to be sanitized.
 */
function pushloop_sanitize_text_field( $str ) {
	$filtered = wp_check_invalid_utf8( $str ); // html tags are fine.
	$filtered = trim( preg_replace( '/[\r\n\t ]+/', '', $filtered ) );

	$found = false;
	while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
		$filtered = str_replace( $match[0], '', $filtered );
		$found    = true;
	}

	if ( $found ) {
		// Strip out the whitespace that may now exist after removing the octets.
		$filtered = trim( preg_replace( '/ +/', '', $filtered ) );
	}

	return $filtered;
}

/**
 * Adds get parameters to urls.
 *
 * @param string $url         Url.
 * @param string $param_name  Param name.
 * @param string $param_value Param value.
 */
function pushloop_set_utm( $url, $param_name, $param_value ) {
	if ( '' === $param_value ) {
		return $url;
	}
	if ( strpos( $url, $param_name . '=' ) !== false ) {
		$prefix = substr( $url, 0, strpos( $url, $param_name ) );
		$suffix = substr( $url, strpos( $url, $param_name ) );
		$suffix = substr( $suffix, strpos( $suffix, '=' + 1 ) );
		$suffix = ( strpos( $suffix, '&' ) !== false ) ? substr( $suffix, strpos( $suffix, '&' ) ) : '';
		$url    = $prefix . $param_name . '=' . $param_value . $suffix;
	} elseif ( strpos( $url, '?' ) ) {
		$url = $url . '&' . $param_name . '=' . $param_value;
	} else {
		$url = $url . '?' . $param_name . '=' . $param_value;
	}

	return $url;
}

/**
 * New post
 *
 * @param int    $post_id Post id.
 * @param object $post    Post.
 */
function pushloop_new_post( $post_id, $post ) {
	if ( 'publish' === $post->post_status && empty( get_post_meta( $post_id, 'check_if_run_once' ) ) ) {

		if ( isset( $_POST['pushloop_nonce_field'] ) || wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pushloop_nonce_field'] ) ), 'pushloop_push_notification_box' ) ) {
			if ( isset( $_POST['pushloop_notification_enable'] ) && '1' === $_POST['pushloop_notification_enable'] ) {
				if ( isset( $_POST['pushloop_notification_title'] ) ) {
					$title = sanitize_text_field( wp_unslash( $_POST['pushloop_notification_title'] ) );
				}
				if ( isset( $_POST['pushloop_notification_message'] ) ) {
					$mess = sanitize_text_field( wp_unslash( $_POST['pushloop_notification_message'] ) );
				}

				pushloop_send_notification_next( $post_id, true, $title, $mess );
				update_post_meta( $post_id, 'check_if_run_once', true );
			}
		}
	}
}

/**
 * Notification box
 *
 * @param object $post Post.
 */
function pushloop_push_notification_box( $post ) {
	$title = get_post_meta( $post->ID, 'pushloop_notification_title', true );
	if ( '' === $title ) {
		$title = get_option( 'pushloop_default_title' );
	}
	$message     = get_post_meta( $post->ID, 'pushloop_notification_message', true );
	$enable      = get_post_meta( $post->ID, 'pushloop_notification_enable', true );
	$post_status = $post->post_status;

	echo '<input type="text" maxlength="100" id="pushloop_notification_title" name="pushloop_notification_title" placeholder="Title" value="" >';
	echo '<textarea id="pushloop_notification_message" maxlength="255" name="pushloop_notification_message" rows="4" value="" placeholder="Your message here...">' . esc_textarea( $message ) . '</textarea>';
	wp_nonce_field( 'pushloop_push_notification_box', 'pushloop_nonce_field' );
	echo '<label class="pushloop_enable_label"><input type="checkbox" name="pushloop_notification_enable" id="pushloop_notification_enable" value="1"> Push notification on publish</label>';
	echo '<small>If you leave text fields empty, push notification will be autogenerated from your content</small>';
}

/**
 * Init Options
 */
function pushloop_init_options() {
	add_option( 'pushloop_plugin_activation', 'just-activated' );
}

/**
 * Get site
 */
function pushloop_get_site() {
	$api_key = get_option( 'pushloop_api_key' );
	$resp    = pushloop_validate_token( $api_key );
	$site_id = get_option( 'pushloop_web_id' );
	if ( ! $api_key || ! $site_id || ! $resp ) {
		return false;
	}

	$headers = array(
		'ApiKey'       => $api_key,
		'Accept'       => 'application/json',
		'Content-Type' => 'application/json',
	);

	return pushloop_api_request( 'https://api.pushloop.io/api/pub/sites/' . $site_id, $headers, false, 'GET', true );
}

/**
 * Curl send Notification
 *
 * @param array $data    Data.
 * @param bool  $is_auto Auto.
 */
function pushloop_send_notification_curl( $data, bool $is_auto = true ) {
	$api_key = get_option( 'pushloop_api_key' );
	$resp    = pushloop_validate_token( $api_key );
	$site_id = get_option( 'pushloop_web_id' );
	if ( ! $api_key || ! $site_id || ! $resp ) {
		return false;
	}
	if ( $is_auto ) {
		$recurrence = 0;
	} else {
		$recurrence = $data['recurrence'];
	}

	if ( '' === $data['title'] || '' === $data['message'] || '' === $data['url'] ) {
		return false;
	}

	if ( '' === $data['img'] ) {
		$data['img'] = PUSHLOOP_URL . 'media/bell_notification.jpg';
	}

	if ( ! array_key_exists( 'market', $data ) ) {
		$data['market'] = 'IT';
	}
	$headers = array(
		'ApiKey'       => $api_key,
		'Accept'       => 'application/json',
		'Content-Type' => 'application/json',
	);

	$body = array(
		'title'       => $data['title'],
		'description' => $data['message'],
		'site_id'     => $site_id,
		'market'      => $data['market'],
		'img'         => $data['img'],
		'url'         => $data['url'],
		'send_time'   => $data['send_time'],
		'recurrence'  => $recurrence,
		'when'        => isset( $data['when'] ) ? $data['when'] : 0,
	);
	if ( ! empty( $data['tokens'] ) ) {
		$body['tokens'] = $data['tokens'];
	}

	return pushloop_api_request( 'https://api.pushloop.io/api/pub/pushes', $headers, $body );
}

/**
 * Get markets
 *
 * @param int $site_id Site id.
 */
function pushloop_get_markets( $site_id ) {
	$api_key = get_option( 'pushloop_api_key' );
	$resp    = pushloop_validate_token( $api_key );
	$site_id = get_option( 'pushloop_web_id' );
	if ( ! $api_key || ! $site_id || ! $resp ) {
		return false;
	}

	$headers = array(
		'ApiKey'       => $api_key,
		'Accept'       => 'application/json',
		'Content-Type' => 'application/json',
	);

	$url      = 'https://api.pushloop.io/api/pub/market/' . $site_id;
	$response = pushloop_api_request( $url, $headers, false, 'GET' );

	if ( $response ) {
		return json_decode( $response )->markets;
	} else {
		return false;
	}
}

/**
 * Autosend.
 *
 * @param string $new_status New status.
 * @param string $old_status Old status.
 * @param object $post       Post.
 */
function pushloop_save_push( $new_status, $old_status, $post ) {
	// phpcs:ignore
	if ( isset( $_POST['action_performed'] ) ) {
		// Prevent running the action twice.
		return;
	}
	if ( 'publish' === $new_status && 'publish' !== $old_status ) {

		if ( ! in_array( get_post_type( $post->ID ), explode( ',', get_option( 'pushloop_enable_for', 'post,page' ) ), true ) ) {
			return false;
		}
		pushloop_send_notification_next( $post->ID );
		$_POST['action_performed'] = true;
	}
}

/**
 * Send Notification
 *
 * @param int    $id         Id.
 * @param bool   $enable     Enabled.
 * @param string $meta_title Title.
 * @param string $meta_desc  Description.
 */
function pushloop_send_notification_next( $id, $enable = false, $meta_title = false, $meta_desc = false ) {
	if ( get_option( 'pushloop_default_for_post' ) === 'on' || $enable ) {
		if ( $meta_title ) {
			$notification_title = $meta_title;
		} else {
			$notification_title = get_post_meta( $id, 'pushloop_notification_title', true );
		}
		if ( '' === $notification_title || empty( $notification_title ) || ! isset( $notification_title ) ) {
			$notification_title = substr( wp_strip_all_tags( get_the_title( $id ) ), 0, 100 );
			$notification_title = html_entity_decode( $notification_title );
		}

		if ( $meta_desc ) {
			$notification_message = $meta_desc;
		} else {
			$notification_message = get_post_meta( $id, 'pushloop_notification_message', true );
		}
		if ( '' === $notification_message || empty( $notification_message ) || ! isset( $notification_message ) ) {
			$notification_message = substr( wp_strip_all_tags( get_the_content( null, false, $id ) ), 0, 250 ) . '...';
			$notification_message = html_entity_decode( $notification_message );
		}
		$utm_source   = get_post_meta( $id, 'pushloop_utm_source', true );
		$utm_medium   = get_post_meta( $id, 'pushloop_utm_medium', true );
		$utm_campaign = get_post_meta( $id, 'pushloop_utm_campaign', true );

		$large_image = '';
		if ( get_option( 'pushloop_large_image', 0 ) && has_post_thumbnail( $id ) ) {
			$large_image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
			$large_image = $large_image[0];
		}

		$notification_url  = get_permalink( $id );
		$notification_url  = pushloop_set_utm( $notification_url, 'utm_source', $utm_source );
		$notification_url  = pushloop_set_utm( $notification_url, 'utm_medium', $utm_medium );
		$notification_url  = pushloop_set_utm( $notification_url, 'utm_campaign', $utm_campaign );
		$pushloop_gmt      = get_option( 'pushloop_gmt' );
		$pushloop_gmt_sign = get_option( 'pushloop_gmt_sign' );

		$push = array(
			'title'      => trim( $notification_title ),
			'message'    => trim( $notification_message ),
			'url'        => $notification_url,
			'img'        => $large_image,
			'send_time'  => gmdate( 'Y-m-d H:i:s', strtotime( gmdate( 'Y-m-d H:i:s' ) . ' ' . $pushloop_gmt_sign . $pushloop_gmt . ' hours' ) ),
			'when'       => 1,
			'recurrence' => 0,
		);
		pushloop_send_notification_curl( $push );
	}
}

/**
 * Api Request.
 *
 * @param string $pushloop_api_end_point Endpoin.
 * @param array  $pushloop_headers       Headers.
 * @param bool   $pushloop_body          Body.
 * @param string $type                   Type.
 * @param bool   $no_status_check        Check.
 */
function pushloop_api_request( string $pushloop_api_end_point, array $pushloop_headers, $pushloop_body = false, string $type = 'POST', bool $no_status_check = false ) {
	try {
		$request['headers'] = $pushloop_headers;
		if ( $pushloop_body ) {
			$request['body'] = wp_json_encode( $pushloop_body );
		}
		if ( 'POST' === $type ) {
			$result = wp_remote_post( $pushloop_api_end_point, $request );
		} elseif ( 'GET' === $type ) {
			$result = wp_remote_get( $pushloop_api_end_point, $request );
		}

		PushloopLogger::info( 'Request Endpoint ' . $pushloop_api_end_point );
		PushloopLogger::info( 'Request Header ' . wp_json_encode( $pushloop_headers ) );
		PushloopLogger::info( 'Request Body ' . wp_json_encode( $pushloop_body ) );

		$result_code = wp_remote_retrieve_response_code( $result );
		if ( 200 === $result_code ) {
			if ( ! is_wp_error( $result ) ) {
				$response = wp_remote_retrieve_body( $result );

				PushloopLogger::info( 'Response ' . $response );

				$status = false;
				if ( isset( $response ) && ( $decoded_response = json_decode( $response ) ) !== null ) {
					if ( isset($decoded_response->status ) ) {
						$status = $decoded_response->status;
					}
				}
				if ( $status || $no_status_check ) {
					if ( '' === $response ) {
						return true;
					} else {
						return $response;
					}
				}
			}
		}
		PushloopLogger::error( 'pushloop_api_request ' . wp_json_encode( $result ) );
		return false;

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_general_settings_callback: ' . $th->__toString() );
		return false;
	}
}
