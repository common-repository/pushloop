<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-commerce Settings
 */
function pushloop_ecommerce_settings_callback() {
	try {
		if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			global $title;

			echo "<h2 class='top-margin'>" . esc_html( $title ) . "</h2> <a href='https://help.pushloop.io' class='button pushloop-button help' target='_blank'>Help</a>";

			if ( isset( $_POST['pl-save-changes'] ) ) {
				if ( ! isset( $_POST['pushloop-submenu-page-save-nonce'] ) || ( ! wp_verify_nonce( sanitize_key( $_POST['pushloop-submenu-page-save-nonce'] ), plugin_basename( __FILE__ ) ) ) ) {
					echo '<div class="error"><p>Something went wrong!</p></div>';
				} else {

					if ( isset( $_POST['pushloop_ecommerce_product_update'] ) && is_numeric( $_POST['pushloop_ecommerce_product_update'] ) && '1' === $_POST['pushloop_ecommerce_product_update'] ) {
						$pushloop_ecommerce_product_update = 1;
					} else {
						$pushloop_ecommerce_product_update = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_product_in_discount'] ) && is_numeric( $_POST['pushloop_ecommerce_product_in_discount'] ) && '1' === $_POST['pushloop_ecommerce_product_in_discount'] ) {
						$pushloop_ecommerce_product_in_discount = 1;
					} else {
						$pushloop_ecommerce_product_in_discount = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_order_is_changed'] ) && is_numeric( $_POST['pushloop_ecommerce_order_is_changed'] ) && '1' === $_POST['pushloop_ecommerce_order_is_changed'] ) {
						$pushloop_ecommerce_order_is_changed = 1;
					} else {
						$pushloop_ecommerce_order_is_changed = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_order_is_failed'] ) && is_numeric( $_POST['pushloop_ecommerce_order_is_failed'] ) && '1' === $_POST['pushloop_ecommerce_order_is_failed'] ) {
						$pushloop_ecommerce_order_is_failed = 1;
					} else {
						$pushloop_ecommerce_order_is_failed = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_order_is_completed'] ) && is_numeric( $_POST['pushloop_ecommerce_order_is_completed'] ) && '1' === $_POST['pushloop_ecommerce_order_is_completed'] ) {
						$pushloop_ecommerce_order_is_completed = 1;
					} else {
						$pushloop_ecommerce_order_is_completed = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_order_is_refunded'] ) && is_numeric( $_POST['pushloop_ecommerce_order_is_refunded'] ) && '1' === $_POST['pushloop_ecommerce_order_is_refunded'] ) {
						$pushloop_ecommerce_order_is_refunded = 1;
					} else {
						$pushloop_ecommerce_order_is_refunded = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_order_payment_complete'] ) && is_numeric( $_POST['pushloop_ecommerce_order_payment_complete'] ) && '1' === $_POST['pushloop_ecommerce_order_payment_complete'] ) {
						$pushloop_ecommerce_order_payment_complete = 1;
					} else {
						$pushloop_ecommerce_order_payment_complete = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_order_is_cancelled'] ) && is_numeric( $_POST['pushloop_ecommerce_order_is_cancelled'] ) && '1' === $_POST['pushloop_ecommerce_order_is_cancelled'] ) {
						$pushloop_ecommerce_order_is_cancelled = 1;
					} else {
						$pushloop_ecommerce_order_is_cancelled = 0;
					}

					if ( isset( $_POST['pushloop_ecommerce_cart_abandoned'] ) && is_numeric( $_POST['pushloop_ecommerce_cart_abandoned'] ) && '1' === $_POST['pushloop_ecommerce_cart_abandoned'] ) {
						$pushloop_ecommerce_cart_abandoned = 1;
						if ( isset( $_POST['pushloop_ecommerce_cart_abandoned_waiting_time'] ) && is_numeric( $_POST['pushloop_ecommerce_cart_abandoned_waiting_time'] ) && 5 <= $_POST['pushloop_ecommerce_cart_abandoned_waiting_time'] && $_POST['pushloop_ecommerce_cart_abandoned_waiting_time'] <= 2160 ) {
							$pushloop_ecommerce_cart_abandoned_waiting_time = sanitize_text_field( wp_unslash( $_POST['pushloop_ecommerce_cart_abandoned_waiting_time'] ) );
						} else {
							$pushloop_ecommerce_cart_abandoned_waiting_time = 60;
						}

						if ( isset( $_POST['pushloop_ecommerce_cart_abandoned_title'] ) && '' !== $_POST['pushloop_ecommerce_cart_abandoned_title'] ) {
							$pushloop_ecommerce_cart_abandoned_title = sanitize_text_field( wp_unslash( $_POST['pushloop_ecommerce_cart_abandoned_title'] ) );
						} else {
							$pushloop_ecommerce_cart_abandoned_title = 'Hi! You forgot your cart!';
						}

						if ( isset( $_POST['pushloop_ecommerce_cart_abandoned_description'] ) && '' !== $_POST['pushloop_ecommerce_cart_abandoned_description'] ) {
							$pushloop_ecommerce_cart_abandoned_description = sanitize_text_field( wp_unslash( $_POST['pushloop_ecommerce_cart_abandoned_description'] ) );
						} else {
							$pushloop_ecommerce_cart_abandoned_description = 'Continue shopping';
						}
					} else {
						$pushloop_ecommerce_cart_abandoned              = 0;
						$pushloop_ecommerce_cart_abandoned_waiting_time = 60;
						$pushloop_ecommerce_cart_abandoned_title        = 'Hi! You forgot your cart!';
						$pushloop_ecommerce_cart_abandoned_description  = 'Continue shopping';
					}

					if ( isset( $_POST['pushloop_ecommerce_push_default_image'] )
					&& is_numeric( $_POST['pushloop_ecommerce_push_default_image'] )
					&& 5 <= $_POST['pushloop_ecommerce_cart_abandoned_waiting_time']
					&& $_POST['pushloop_ecommerce_cart_abandoned_waiting_time'] <= 2160
					&& isset( $_POST['pushloop_ecommerce_push_default_image_url'] )
					&& '' !== $_POST['pushloop_ecommerce_push_default_image_url']
					) {
						$pushloop_ecommerce_push_default_image     = sanitize_text_field( wp_unslash( $_POST['pushloop_ecommerce_push_default_image'] ) );
						$pushloop_ecommerce_push_default_image_url = sanitize_text_field( wp_unslash( $_POST['pushloop_ecommerce_push_default_image_url'] ) );
					} else {
						$pushloop_ecommerce_push_default_image     = 0;
						$pushloop_ecommerce_push_default_image_url = '';
					}

					$pushloop_api_key = get_option( 'pushloop_api_key' );
					$pushloop_web_id  = get_option( 'pushloop_web_id' );
					$resp             = pushloop_validate_token( $pushloop_api_key );
					$check_web_id     = pushloop_check_web_id( $pushloop_api_key, $pushloop_web_id );
					if ( ! $resp ) {
						echo '<div class="error"><p>Invalid Plugin Token!</p></div>';
						return;
					} else {
						if ( $check_web_id ) {
							update_option( 'pushloop_web_id', $pushloop_web_id );
							$markets = pushloop_get_markets( $pushloop_web_id );
							update_option( 'pushloop_markets', wp_parse_args( $markets ) );
						} else {
							echo '<div class="error"><p>Invalid Website ID!</p></div>';
							return;
						}
						update_option( 'pushloop_ecommerce_product_update', $pushloop_ecommerce_product_update );
						update_option( 'pushloop_ecommerce_product_in_discount', $pushloop_ecommerce_product_in_discount );
						update_option( 'pushloop_ecommerce_order_is_changed', $pushloop_ecommerce_order_is_changed );
						update_option( 'pushloop_ecommerce_order_is_failed', $pushloop_ecommerce_order_is_failed );
						update_option( 'pushloop_ecommerce_order_is_completed', $pushloop_ecommerce_order_is_completed );
						update_option( 'pushloop_ecommerce_order_is_refunded', $pushloop_ecommerce_order_is_refunded );
						update_option( 'pushloop_ecommerce_order_payment_complete', $pushloop_ecommerce_order_payment_complete );
						update_option( 'pushloop_ecommerce_order_is_cancelled', $pushloop_ecommerce_order_is_cancelled );
						update_option( 'pushloop_ecommerce_cart_abandoned', $pushloop_ecommerce_cart_abandoned );
						update_option( 'pushloop_ecommerce_cart_abandoned_waiting_time', $pushloop_ecommerce_cart_abandoned_waiting_time );
						update_option( 'pushloop_ecommerce_cart_abandoned_title', $pushloop_ecommerce_cart_abandoned_title );
						update_option( 'pushloop_ecommerce_cart_abandoned_description', $pushloop_ecommerce_cart_abandoned_description );
						update_option( 'pushloop_ecommerce_push_default_image', $pushloop_ecommerce_push_default_image );
						update_option( 'pushloop_ecommerce_push_default_image_url', $pushloop_ecommerce_push_default_image_url );

						echo '<div class="updated"><p>Changes saved successfully!</p></div>';
					}
				}
			}
			?>
	
			<form method="post" action="">
			<?php settings_fields( 'pushloop' ); ?>
			<table id="pushloopEcommerceSettings" style="width:100%">
	
				<tr>
					<td>
						<h4 scope="row">Image</h4>
					</td>
				</tr>
				<tr>
					<td>
						<label><input id="pushloop_ecommerce_push_default_image" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_push_default_image"
						<?php
						if ( get_option( 'pushloop_ecommerce_push_default_image', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Use custom image </label><br>
						<small>use a customised image for some pushes.</small>
					</td>
	
					<td>
						<input type="text" name="pushloop_ecommerce_push_default_image_url" id="pushloop_ecommerce_push_default_image_url" 
						value="<?php echo esc_attr( get_option( 'pushloop_ecommerce_push_default_image_url' ) ); ?>" size="64" placeholder="https://example.com" class="pushloop-text" pattern="[Hh][Tt][Tt][Pp][Ss]?:\/\/(?:(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)(?:\.(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)*(?:\.(?:[a-zA-Z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:\/[^\s]*)?">
					</td>
				</tr>
	
	
				<tr>
					<td>
						<h4 scope="row">Products</h4>
					</td>
				</tr>
				<tr>
					<td>
						<input id="checkProductUpdate" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_product_update"
						<?php
						if ( get_option( 'pushloop_ecommerce_product_update', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />
	
						<label>Notification when a product is updated </label><br>
						<small>Useful for sending notifications when a product is updated.</small>
						<br><br>
	
						<input id="checkProductInDiscount" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_product_in_discount"
						<?php
						if ( get_option( 'pushloop_ecommerce_product_in_discount', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />
	
						<label>Notification of discounted product </label><br>
						<small>Useful for sending notifications when a product is discounted.</small>
					</td>
					<td>
					</td>
				</tr>
	
	
				<tr>
					<td>
						<h4 scope="row">Orders</h4>
					</td>
				</tr>
				<tr>
					<td>
						<label><input id="checkStatusChanged" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_order_is_changed"
						<?php
						if ( get_option( 'pushloop_ecommerce_order_is_changed', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification when it changes </label><br>
						<small>Can be used to send notifications when order status changes.</small>
						<br><br>
	
						<label><input id="checkStatusFailed" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_order_is_failed"
						<?php
						if ( get_option( 'pushloop_ecommerce_order_is_failed', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification when it is fails </label><br>
						<small>Useful for sending notifications when an order payment fails.</small>
						<br><br>
	
						<label><input id="checkStatusCompleted" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_order_is_completed"
						<?php
						if ( get_option( 'pushloop_ecommerce_order_is_completed', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification when it is completed </label><br>
						<small>Useful for sending notifications when an order is successfully completed.</small>
						<br><br>
	
						<label><input id="checkStatusRefunded" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_order_is_refunded"
						<?php
						if ( get_option( 'pushloop_ecommerce_order_is_refunded', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification when it is reimbursed </label><br>
						<small>Useful for sending notifications when a refund is made.</small>
						<br><br>
	
						<label><input id="checkStatusPaymentComplete" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_order_payment_complete"
						<?php
						if ( get_option( 'pushloop_ecommerce_order_payment_complete', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification of payment completion </label><br>
						<small>Useful for sending specific notifications for completed payments.</small>
						<br><br>
	
						<label><input id="checkStatusCancelled" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_order_is_cancelled"
						<?php
						if ( get_option( 'pushloop_ecommerce_order_is_cancelled', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification when it is cancelled </label><br>
						<small>Useful for sending notifications when an order is cancelled.</small>
					</td>
					<td>
					</td>
				</tr>
	
	
				<tr>
					<td>
						<h4 scope="row">Cart</h4>
					</td>
				</tr>
				<tr>
					<td>
						<div id="divCheckCartAbandoned">
						<label><input id="checkCartAbandoned" type="checkbox" class="pushloop-ui-toggle" name="pushloop_ecommerce_cart_abandoned"
						<?php
						if ( get_option( 'pushloop_ecommerce_cart_abandoned', 0 ) ) {
							echo 'checked';
						}
						?>
						value="1" />Notification when it is abandoned </label><br>
						<small>Useful for sending notifications when a cart is abandoned.</small><br><br>
					</td>
					<td>
						<div id="divCartAbandonedCustom">
							<table>
								<tr>
									<td>
										<label> Waiting time </label>
									</td>
									<td>
										<input id="pushloop_ecommerce_cart_abandoned_waiting_time" type="number" size="6" class="pushloop-ui-toggle" name="pushloop_ecommerce_cart_abandoned_waiting_time" 
										value="<?php echo esc_attr( get_option( 'pushloop_ecommerce_cart_abandoned_waiting_time' ) ); ?>" placeholder="60" min="5" max="2160" />
										<label> minutes </label>
										<small> (from 5 to 2160) </small>
									</td>
								</tr>
								<tr>
									<td>
										<label> Title </label>
									</td>
									<td>
										<input id="pushloop_ecommerce_cart_abandoned_title" type="text"  size="64" class="pushloop-ui-toggle" name="pushloop_ecommerce_cart_abandoned_title"
										value="<?php echo esc_attr( get_option( 'pushloop_ecommerce_cart_abandoned_title' ) ); ?>" maxlength="64" placeholder="Hi! You forgot your cart!" />
									</td>
								</tr>
								<tr>
									<td>
										<label> Description </label>
									</td>
									<td>
										<textarea id="pushloop_ecommerce_cart_abandoned_description" type="text" cols="64" class="pushloop-ui-toggle" name="pushloop_ecommerce_cart_abandoned_description"
										maxlength="192" placeholder="Continue shopping"><?php echo esc_attr( get_option( 'pushloop_ecommerce_cart_abandoned_description' ) ); ?></textarea>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				</div>
				</table>
	
			<?php
			submit_button( 'Save Changes', 'pushloop-button', 'pl-save-changes' );
			wp_nonce_field( plugin_basename( __FILE__ ), 'pushloop-submenu-page-save-nonce' );
			?>
			</form>
			<?php
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_ecommerce_settings_callback: ' . $th->__toString() );
	}
}