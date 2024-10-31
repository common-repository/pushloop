<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Append Js
 */
function pushloop_enqueue_scripts() {
	$pushloop_site_domain = get_option( 'pushloop_site_domain' );
	$pushloop_web_id      = get_option( 'pushloop_web_id' );
	$pushloop_popup_type  = get_option( 'pushloop_popup_type', 0 );
	$pushloop_amp         = get_option( 'pushloop_amp' );
	$pushloop_site_enable = get_option( 'pushloop_site_enabled' );

	if ( $pushloop_web_id && $pushloop_site_domain && $pushloop_site_enable ) {
		try {
			wp_register_script( 'pushloop-sdk', 'https://cdn.pushloop.io/code/sdk/?code=' . $pushloop_site_domain . '&site_id=' . $pushloop_web_id . '&tpl=tpl_' . $pushloop_popup_type . '&script_prefix=https://cdn.pushloop.io&swLocalPath=' . plugins_url( '/pushloop_sw.js', __FILE__ ) . '&type=wordpress', array(), PUSHLOOP_PLUGIN_VERSION, false ); // phpcs:ignore
			wp_enqueue_script( 'pushloop-sdk' );

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				wp_register_script( 'pushloop_front_js', plugins_url( 'pushloop_front.js', __FILE__ ), array( 'jquery', 'pushloop-sdk' ), PUSHLOOP_PLUGIN_VERSION, true );
				wp_enqueue_script( 'pushloop_front_js' );
				wp_localize_script(
					'pushloop_front_js',
					'pushloop_ajax_object',
					array(
						'ajax_url'            => admin_url( 'admin-ajax.php' ),
						'pushloop_ajax_nonce' => wp_create_nonce( 'pushloop_ajax_nonce' ),
					)
				);
			}
		} catch ( \Throwable $th ) {
			PushloopLogger::error( 'pushloop_enqueue_scripts: ' . $th->__toString() );
		}
	}

	if ( $pushloop_amp ) {
		try {
			wp_register_script( 'amp-web-push', 'https://cdn.ampproject.org/v0/amp-web-push-0.1.js', array(), PUSHLOOP_PLUGIN_VERSION, true );

			add_filter(
				'script_loader_tag',
				function ( $tag, $handle ) {
					if ( 'amp-web-push' === $handle ) {
						return str_replace( ' src', ' async="async" src', $tag );
					}
					return $tag;
				},
				10,
				2
			);

		} catch ( \Throwable $th ) {
			PushloopLogger::error( 'pushloop_enqueue_scripts_amp: ' . $th->__toString() );
		}
	}
}
