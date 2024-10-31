<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook-activated function Product
 *
 * @param int $product_id Product id.
 */
function pushloop_product_update( $product_id ) {

	if ( pushloop_check_one_time_execution( 'pushloop_product_update' ) ) {
		$push = pushloop_get_product_details_for_push( $product_id );

		if ( false !== $push ) {
			pushloop_send_notification_curl( $push );
		}
	}
}

/**
 * Product details to create the push
 *
 * @param int $product_id Product id.
 */
function pushloop_get_product_details_for_push( $product_id ) {
	try {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			PushloopLogger::warning( 'pushloop_get_product_details_for_push: no product found' );
			return false;
		}

		$large_image = '';
		if ( get_option( 'pushloop_large_image', 0 ) && has_post_thumbnail( $product_id ) ) {
			$large_image = wp_get_attachment_image_src( $product->get_image_id() );
			$large_image = $large_image[0];
		}

		$push = array(
			'title'      => 'Buy now ' . $product->get_title(),
			'message'    => $product->get_description(),
			'url'        => get_permalink( $product_id ),
			'img'        => $large_image,
			'when'       => 1,
			'send_time'  => gmdate( 'Y-m-d H:i:s' ),
			'recurrence' => 0,
		);

		if ( get_option( 'pushloop_ecommerce_product_in_discount' ) ) {
			if ( '' !== $product->get_sale_price() ) {
				$push['message'] = 'This product is now on discount.';
				if ( null !== $product->get_date_on_sale_from() ) {
					$push['send_time'] = $product->get_date_on_sale_from()->format( 'Y-m-d H:i:s' );
					$push['when']      = 0;
				}
			}
		}

		return $push;

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_get_product_details_for_push: ' . $th->__toString() );
		return false;
	}
}

/**
 * Hook-activated function Order Status
 *
 * @param int $order_id Order id.
 */
function pushloop_order_status_update( $order_id ) {

	if ( pushloop_check_one_time_execution( 'pushloop_order_status_update' ) ) {
		$push = pushloop_get_order_details_for_push( $order_id );

		if ( false !== $push ) {
			pushloop_send_notification_curl( $push );
		}
	}
}

/**
 * Order details to create the push
 *
 * @param int $order_id Order id.
 */
function pushloop_get_order_details_for_push( $order_id ) {
	try {
		$order  = wc_get_order( $order_id );
		$tokens = get_user_meta( $order->get_user_id(), 'pushloop_user_token', false );
		if ( get_option( 'pushloop_ecommerce_push_default_image' ) ) {
			$img = get_option( 'pushloop_ecommerce_push_default_image_url' );
		} else {
			$img = plugin_dir_url( __DIR__ ) . 'media/pushloop_logo_istituzionale_ecommerce_campanella.svg';
		}

		if ( false === $tokens || empty( $tokens ) ) {
			PushloopLogger::warning( 'pushloop_get_order_details_for_push: no user tokens found' );
			return false;
		}

		$push = array(
			'title'      => 'The status of order #' . $order->get_id() . ' has changed',
			'message'    => 'The status of your order has changed in "' . $order->get_status() . '"',
			'url'        => $order->get_view_order_url(),
			'img'        => $img,
			'when'       => 1,
			'send_time'  => gmdate( 'Y-m-d H:i:s' ),
			'recurrence' => 0,
			'tokens'     => $tokens,
		);

		return $push;

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_get_order_details_for_push: ' . $th->__toString() );
		return false;
	}
}

/**
 * Check cart activity.
 */
function pushloop_track_cart_activity() {
	try {
		$cart     = WC()->cart;
		$meta_key = 'pushloop_last_cart_activity_time';

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			if ( ! $cart->is_empty() ) {
				update_user_meta(
					$user_id,
					$meta_key,
					time()
				);

			} else {
				delete_user_meta( $user_id, $meta_key );
			}
		} else {
			if ( '' === session_id() ) {
				session_start();
			}
			$customer_id = session_id();
			if ( ! $cart->is_empty() ) {
				pushloop_update_or_insert_from_db(
					array(
						'customer_id' => $customer_id,
					),
					array(
						'last_update' => time(),
					)
				);

			} else {
				if ( $customer_id ) {
					$check_customer_id = pushloop_get_from_db(
						array(
							'customer_id' => $customer_id,
						)
					);

					if ( $check_customer_id ) {
						pushloop_update_or_insert_from_db(
							array(
								'customer_id' => $customer_id,
							),
							array(
								'last_update' => null,
							)
						);
					}
				}
			}
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_track_cart_activity: ' . $th->__toString() );
	}
}

/**
 * Check if the cart has been abandoned.
 */
function pushloop_abandoned_cart() {
	try {
		$meta_key                   = 'pushloop_last_cart_activity_time';
		$time                       = time() - ( get_option( 'pushloop_ecommerce_cart_abandoned_waiting_time' ) * MINUTE_IN_SECONDS );
		$users_with_abandoned_carts = get_users(
			array(
				'meta_key'     => $meta_key, // phpcs:ignore
				'meta_value'   => $time, // phpcs:ignore
				'meta_compare' => '<',
				'number'       => 10,
				'fields'       => array( 'ID' ),
			)
		);

		foreach ( $users_with_abandoned_carts as $user ) {
			$tokens = get_user_meta( $user->id, 'pushloop_user_token', false );
			$push   = pushloop_get_cart_details_for_push( $tokens );

			if ( false !== $push ) {
				pushloop_send_notification_curl( $push );
			}
			delete_user_meta( $user->id, $meta_key );
		}

		$anonymous_with_abandoned_carts = pushloop_get_from_db(
			array(
				'last_update' => array(
					'<',
					$time,
				),
			)
		);

		foreach ( $anonymous_with_abandoned_carts as $anonymous ) {
			// log anonymous
			if ( null !== $anonymous['token'] ) {
				$push = pushloop_get_cart_details_for_push( array( $anonymous['token'] ) );
				if ( false !== $push ) {
					pushloop_send_notification_curl( $push );
				}
			} else {
				PushloopLogger::error( 'pushloop_abandoned_cart: no token found' );
			}

			pushloop_delete_from_db(
				array(
					'id' => $anonymous['id'],
				)
			);

		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_abandoned_cart: ' . $th->__toString() );
	}
}

/**
 * Clean the pushloop table of old records.
 */
function pushloop_clean_db() {
	global $wpdb;
	$table_name = esc_sql( $wpdb->prefix . 'pushloop_cart' );

	$wpdb->query(
		$query = $wpdb->prepare(
			"DELETE FROM $table_name 
			WHERE last_update IS NULL
			OR token IS NULL"
		)
	);
}

/**
 * Check if site is enabled.
 */
function pushloop_check_site_is_enabled() {
	try {
		$api_key = get_option( 'pushloop_api_key' );
		$site_id = get_option( 'pushloop_web_id' );
		if ( ! $api_key || ! $site_id ) {
			return false;
		}

		$headers = array(
			'ApiKey'       => $api_key,
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		$url      = 'https://api.pushloop.io/api/v2/sites/' . $site_id . ':active';
		$response = pushloop_api_request( $url, $headers, false, 'GET', true );

		if ( $response ) {
			$enabled = json_decode( $response )->success;
			update_option( 'pushloop_site_enabled', $enabled );

			if ( $enabled ) {
				$check_enabled_site = wp_next_scheduled( 'pushloop_check_enabled_site' );
				if ( $check_enabled_site ) {
					wp_unschedule_event( $check_enabled_site, 'pushloop_check_enabled_site' );
				}
			}
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_check_site_is_enabled: ' . $th->__toString() );
	}
}

/**
 * Get abandoned cart details for push.
 *
 * @param array $tokens arrays of tokens.
 */
function pushloop_get_cart_details_for_push( $tokens ) {
	try {

		if ( false === $tokens || empty( $tokens ) || ! is_array( $tokens ) ) {
			PushloopLogger::warning( 'pushloop_get_cart_details_for_push: no user tokens found or not array' );
			return false;
		}

		if ( get_option( 'pushloop_ecommerce_push_default_image' ) ) {
			$img = get_option( 'pushloop_ecommerce_push_default_image_url' );
		} else {
			$img = plugin_dir_url( __DIR__ ) . 'media/pushloop_logo_istituzionale_ecommerce_campanella.svg';
		}
		$push = array(
			'title'      => get_option( 'pushloop_ecommerce_cart_abandoned_title', 'Hi! You forgot your cart!' ),
			'message'    => get_option( 'pushloop_ecommerce_cart_abandoned_description', 'Continue shopping' ),
			'url'        => wc_get_cart_url(),
			'img'        => $img,
			'when'       => 1,
			'send_time'  => gmdate( 'Y-m-d H:i:s' ),
			'recurrence' => 0,
			'tokens'     => $tokens,
		);

		return $push;

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_get_cart_details_for_push: ' . $th->__toString() );
		return false;
	}
}

/**
 * Control for one-time execution
 *
 * @param string $process_name Name of the process to be controlled.
 */
function pushloop_check_one_time_execution( $process_name ) {

	if ( get_transient( $process_name ) ) {
		return false;
	} else {
		set_transient( $process_name, true, 10 );
		return true;
	}
}

// phpcs:disable
/**
 * Insert into pushloop table cart data.
 *
 * @param array $where Params to search.
 * @param array $data Params to insert.
 */
function pushloop_update_or_insert_from_db( $where, $data ) {
	try {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'pushloop_cart' );
	
		$existing_record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE " . implode(
					' AND ',
					array_map(
						function ( $key ) {
							return "$key = %s";
						},
						array_keys( $where )
					)
				),
				array_values( $where )
			)
		);
	
		if ( $existing_record ) {
			return $wpdb->update(
				$table_name,
				$data,
				$where
			);
		} else {
			return $wpdb->insert(
				$table_name,
				array_merge( $data, $where )
			);
		}

	} catch (\Throwable $th) {
		PushloopLogger::error( 'pushloop_update_or_insert_from_db: ' . $th->__toString() );
	}
}

/**
 * Delete data in pushloop table cart.
 *
 * @param array $where Params to search.
 */
function pushloop_delete_from_db( $where ) {
	try {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'pushloop_cart' );
	
		$where_clause = implode(
			' AND ',
			array_map(
				function ( $key ) {
					return "$key = %s";
				},
				array_keys( $where )
			)
		);
	
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table_name WHERE $where_clause",
				array_values( $where )
			)
		);

	} catch (\Throwable $th) {
		PushloopLogger::error( 'pushloop_delete_from_db: ' . $th->__toString() );
	}
}

/**
 * Get data in pushloop table cart.
 *
 * @param array $where Params to search.
 */
function pushloop_get_from_db( $where ) {
	try {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'pushloop_cart' );
	
		$where_clause = implode(
			' AND ',
			array_map(
				function ( $key, $value ) {
					if ( is_array( $value ) ) {
						return "$key $value[0] %s";
					} else {
						return "$key = %s";
					}
				},
				array_keys( $where ),
				$where
			)
		);
	
		$values = array_map(
			function ( $value ) {
				return is_array( $value ) ? $value[1] : $value;
			},
			$where
		);
	
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE $where_clause",
				$values
			),
			ARRAY_A
		);

	} catch (\Throwable $th) {
		PushloopLogger::error( 'pushloop_get_from_db: ' . $th->__toString() );
		return false;
	}
}
// phpcs:enable

/**
 * Save token in user_meta or WC session.
 */
function pushloop_save_user_token() {
	try {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pushloop_ajax_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed', 400 );
			wp_die();
		}

		if (
			( isset( $_POST['curr_tok'] ) && null !== $_POST['curr_tok'] ) &&
			( isset( $_POST['old_tok'] ) && null !== $_POST['old_tok'] )
		) {
			$curr_tok = $_POST['curr_tok']; // phpcs:ignore
			$old_tok  = $_POST['old_tok']; // phpcs:ignore

			if ( is_user_logged_in() ) {
				$user_id        = get_current_user_id();
				$existing_token = get_user_meta( $user_id, 'pushloop_user_token', false );

				if ( false !== $existing_token && in_array( $old_tok, $existing_token, true ) ) {
					if ( update_user_meta( $user_id, 'pushloop_user_token', $curr_tok, $old_tok ) ) {
						echo 'update';
					} else {
						echo 'update error';
					}
					wp_die();

				} elseif ( false !== $existing_token && ! in_array( $curr_tok, $existing_token, true ) ) {
					if ( add_user_meta( $user_id, 'pushloop_user_token', $curr_tok, false ) ) {
						echo 'added';
					} else {
						echo 'added error';
					}
					wp_die();
				} elseif ( false !== $existing_token && in_array( $curr_tok, $existing_token, true ) ) {
					echo 'ok';
				}
			} else {
				if ( get_option( 'pushloop_ecommerce_cart_abandoned' ) ) {
					if ( '' === session_id() ) {
						session_start();
					}
					$customer_id = session_id();
					if ( $customer_id ) {
						$check_customer_id = pushloop_get_from_db(
							array(
								'customer_id' => $customer_id,
							)
						);

						if ( $check_customer_id ) {
							$saved = pushloop_update_or_insert_from_db(
								array(
									'customer_id' => $customer_id,
								),
								array(
									'token' => $curr_tok,
								)
							);
						} else {
							$check_token = pushloop_get_from_db(
								array(
									'token' => $curr_tok,
								)
							);

							if ( $check_token ) {
								$saved = pushloop_update_or_insert_from_db(
									array(
										'token' => $curr_tok,
									),
									array(
										'customer_id' => $customer_id,
									)
								);
							} else {
								$saved = pushloop_update_or_insert_from_db(
									array(
										'customer_id' => $customer_id,
									),
									array(
										'token' => $curr_tok,
									)
								);
							}
						}
					}
					if ( false !== $saved ) {
						echo 'saved';
					} else {
						echo 'saved error';
					}
					wp_die();
				}
			}

			wp_die();
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_save_user_token: ' . $th->__toString() );
	}
}
