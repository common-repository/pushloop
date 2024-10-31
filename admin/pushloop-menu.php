<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu
 */
function pushloop_menu_page() {
	try {
		add_menu_page( 'Pushloop - Web Push Notifications', 'Pushloop', 'manage_options', 'pushloop-web-push-notifications', 'pushloop_stats_callback', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBMaWNlbnNlOiBNSVQuIE1hZGUgYnkgVHdpdHRlcjogaHR0cHM6Ly9naXRodWIuY29tL3R3aXR0ZXIvdHdlbW9qaSAtLT4KPHN2ZyB3aWR0aD0iODAwcHgiIGhlaWdodD0iODAwcHgiIHZpZXdCb3g9IjAgMCAzNiAzNiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgYXJpYS1oaWRkZW49InRydWUiIHJvbGU9ImltZyIgY2xhc3M9Imljb25pZnkgaWNvbmlmeS0tdHdlbW9qaSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQgbWVldCI+PHBhdGggZmlsbD0iI0ZGQUMzMyIgZD0iTTI4IDEzYzAgMTEgNSAxMCA1IDE1YzAgMCAwIDItMiAySDVjLTIgMC0yLTItMi0yYzAtNSA1LTQgNS0xNUM4IDcuNDc4IDEyLjQ3NyAzIDE4IDNzMTAgNC40NzggMTAgMTB6Ij48L3BhdGg+PGNpcmNsZSBmaWxsPSIjRkZBQzMzIiBjeD0iMTgiIGN5PSIzIiByPSIzIj48L2NpcmNsZT48cGF0aCBmaWxsPSIjRkZBQzMzIiBkPSJNMTggMzZhNCA0IDAgMCAwIDQtNGgtOGE0IDQgMCAwIDAgNCA0eiI+PC9wYXRoPjwvc3ZnPg==', 30 );
		add_submenu_page( 'pushloop-web-push-notifications', 'Dashboard - Pushloop', 'Dashboard', 'manage_options', 'pushloop-web-push-notifications', 'pushloop_stats_callback' );
		if ( get_option( 'pushloop_web_id' ) ) {
			add_submenu_page( 'pushloop-web-push-notifications', 'Send Notification - Pushloop', 'Send Push', 'manage_options', 'pushloop-send-notification', 'pushloop_send_notifications_callback' );
			if ( get_option( 'pushloop_site_monetization' ) ) {
				add_submenu_page( 'pushloop-web-push-notifications', 'Monetization Stats - Pushloop', 'Monetization Stats', 'manage_options', 'pushloop-monetization-stats', 'pushloop_monetization_stats' );
			}
			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				add_submenu_page( 'pushloop-web-push-notifications', 'E-commerce Settings - Pushloop', 'E-commerce Settings', 'manage_options', 'pushloop-ecommerce-settings', 'pushloop_ecommerce_settings_callback' );
			}
		}
		add_submenu_page( 'pushloop-web-push-notifications', 'General Settings - Pushloop', 'General Settings', 'manage_options', 'pushloop-general-settings', 'pushloop_general_settings_callback' );

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_menu_page: ' . $th->__toString() );
	}
}

add_action( 'admin_menu', 'pushloop_menu_page' );
