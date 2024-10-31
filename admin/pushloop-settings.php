<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generale Settings
 */
function pushloop_general_settings_callback() {
	try {
		global $title;

		echo "<h2 class='top-margin'>" . esc_html( $title ) . ' v' . esc_attr( PUSHLOOP_PLUGIN_VERSION ) . "</h2> <a href='https://help.pushloop.io' class='button pushloop-button help' target='_blank'>Help</a>";

		if ( isset( $_POST['pl-save-changes'] ) ) {
			if ( ! isset( $_POST['pushloop-submenu-page-save-nonce'] ) || ( ! wp_verify_nonce( sanitize_key( $_POST['pushloop-submenu-page-save-nonce'] ), plugin_basename( __FILE__ ) ) ) ) {
				echo '<div class="error"><p>Something went wrong!</p></div>';
			} else {
				$success           = true;
				$pushloop_web_id   = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_web_id', FILTER_SANITIZE_NUMBER_INT ) );
				$pushloop_api_key  = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_api_key', FILTER_SANITIZE_STRING ) );
				$pushloop_gmt      = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_gmt', FILTER_SANITIZE_NUMBER_INT ) );
				$pushloop_gmt_sign = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_gmt_sign', FILTER_SANITIZE_STRING ) );

				$pushloop_utm_source   = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_utm_source', FILTER_SANITIZE_STRING ) );
				$pushloop_utm_medium   = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_utm_medium', FILTER_SANITIZE_STRING ) );
				$pushloop_utm_campaign = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_utm_campaign', FILTER_SANITIZE_STRING ) );

				$pushloop_enable_for = '';
				if ( isset( $_POST['enable_pushloop'] ) ) {
					$pushloop_enable_for = array_map( 'sanitize_text_field', wp_unslash( $_POST['enable_pushloop'] ) );
					if ( gettype( $pushloop_enable_for ) !== 'string' ) {
						$pushloop_enable_for = pushloop_sanitize_text_field( implode( ',', $pushloop_enable_for ) );
					}
				}
				$pushloop_default_for_post = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_default_for_post', FILTER_SANITIZE_STRING ) );
				$pushloop_popup_type       = pushloop_sanitize_text_field( filter_input( INPUT_POST, 'pushloop_popup_type', FILTER_SANITIZE_STRING ) );

				if ( isset( $_POST['pushloop_large_image'] ) && is_numeric( $_POST['pushloop_large_image'] ) && '1' === $_POST['pushloop_large_image'] ) {
					$pushloop_large_image = 1;
				} else {
					$pushloop_large_image = 0;
				}

				if ( isset( $_POST['pushloop_amp'] ) && is_numeric( $_POST['pushloop_amp'] ) && '1' === $_POST['pushloop_amp'] ) {
					$pushloop_amp = 1;

					$pushloop_amp_custom_css              = isset( $_POST['pushloop_amp_custom_css'] ) ? sanitize_text_field( wp_unslash( $_POST['pushloop_amp_custom_css'] ) ) : '';
					$pushloop_amp_custom_text_subscribe   = isset( $_POST['pushloop_amp_custom_text_subscribe'] ) ? sanitize_text_field( wp_unslash( $_POST['pushloop_amp_custom_text_subscribe'] ) ) : '';
					$pushloop_amp_custom_text_unsubscribe = isset( $_POST['pushloop_amp_custom_text_unsubscribe'] ) ? sanitize_text_field( wp_unslash( $_POST['pushloop_amp_custom_text_unsubscribe'] ) ) : '';

				} else {
					$pushloop_amp = 0;
				}

				$resp         = pushloop_validate_token( $pushloop_api_key );
				$check_web_id = pushloop_check_web_id( $pushloop_api_key, $pushloop_web_id );
				if ( ! $resp ) {
					echo '<div class="error"><p>Invalid Plugin Token!</p></div>';
					return;
				} else {
					if ( $check_web_id ) {
						update_option( 'pushloop_web_id', $pushloop_web_id );
						$markets = pushloop_get_markets( $pushloop_web_id );
						update_option( 'pushloop_markets', wp_parse_args( $markets ) );
						pushloop_check_site_is_enabled();
					} else {
						echo '<div class="error"><p>Invalid Website ID!</p></div>';
						return;
					}
					update_option( 'pushloop_gmt', $pushloop_gmt );
					update_option( 'pushloop_gmt_sign', $pushloop_gmt_sign );
					update_option( 'pushloop_api_key', $pushloop_api_key );

					update_option( 'pushloop_utm_source', $pushloop_utm_source );
					update_option( 'pushloop_utm_medium', $pushloop_utm_medium );
					update_option( 'pushloop_utm_campaign', $pushloop_utm_campaign );

					update_option( 'pushloop_large_image', $pushloop_large_image );
					update_option( 'pushloop_enable_for', $pushloop_enable_for );
					update_option( 'pushloop_default_for_post', $pushloop_default_for_post );
					update_option( 'pushloop_popup_type', $pushloop_popup_type );
					update_option( 'pushloop_default_title', 'New notification!' );

					update_option( 'pushloop_amp', $pushloop_amp );

					if ( 1 === $pushloop_amp ) {
						if ( '' === $pushloop_amp_custom_text_subscribe ) {
							$pushloop_amp_custom_text_subscribe = 'Subscribe to updates';
						}
						if ( '' === $pushloop_amp_custom_text_unsubscribe ) {
							$pushloop_amp_custom_text_unsubscribe = 'Unsubscribe to updates';
						}
						if ( '' === $pushloop_amp_custom_css ) {
							$pushloop_amp_custom_css = '
							amp-web-push-widget button.subscribe,
							amp-web-push-widget button.unsubscribe {
								z-index: 100;
								display: inline-flex;
								margin: 0;
								cursor: pointer;
								outline: 0;
								font-size: 15px;
								font-weight: 400;
								-webkit-tap-highlight-color: transparent;
							}
							amp-web-push-widget button.subscribe {
								align-items: center;
								border-radius: 2px;
								border: 0;
								box-sizing: border-box;
								padding: 10px 15px;
								background: #4a90e2;
								color: #fff;
								box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.5);
							}
							amp-web-push-widget button.subscribe .subscribe-icon {
								margin-right: 10px;
							}
							amp-web-push-widget button.subscribe:active {
								transform: scale(0.99);
							}
							amp-web-push-widget button.unsubscribe {
								align-items: center;
								justify-content: center;
								height: 45px;
								border: 0;
								background: 0 0;
								color: #b1b1b1;
							}
						';
						}

						update_option( 'pushloop_amp_custom_css', sanitize_text_field( $pushloop_amp_custom_css ) );
						update_option( 'pushloop_amp_custom_text_subscribe', $pushloop_amp_custom_text_subscribe );
						update_option( 'pushloop_amp_custom_text_unsubscribe', $pushloop_amp_custom_text_unsubscribe );

						remove_action( 'amp_post_template_css', 'pushloop_amp_widget_css', 11 );
						add_action( 'amp_post_template_css', 'pushloop_amp_widget_css', 11 );
					}

					try {
						$site = json_decode( pushloop_get_site() );
						update_option( 'pushloop_site_monetization', $site->automation );
						update_option( 'pushloop_site_domain', $site->topic );

					} catch ( \Throwable $th ) {
						echo '<div class="error"><p>Error, could not retrieve site domain!</p></div>';
						PushloopLogger::error( 'pushloop_general_settings_callback retrieve site domain: ' . $th->__toString() );
					}

					echo '<div class="updated"><p>Changes saved successfully!</p></div>';
				}
			}
		}
		if ( ! get_option( 'pushloop_web_id' ) ) {
			?>
		<p>Configure options for Pushloop, you can get your Plugin Token from your Pushloop Profile Page. If you're not registered, signup for FREE at <a target="_blank" href="https://app.pushloop.io/register">app.pushloop.io/register</a>.</p>
			<?php
		}
		?>

	<form method="post" action="">
		<?php settings_fields( 'pushloop' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<h3>Website Settings</h3>
				</th>
			</tr>
			<tr>
				<th scope="row">Website ID</th>
				<td><input type="text" required name="pushloop_web_id" size="6" value="<?php echo esc_attr( get_option( 'pushloop_web_id' ) ); ?>" placeholder="ID" /><br>
					<small>You can retrieve Website ID from your Pushloop "Manage Sites" section</small>
				</td>
			</tr>
			<tr>
				<th scope="row">Plugin Token</th>
				<td>
					<input type="text" required name="pushloop_api_key" size="64" value="<?php echo esc_attr( get_option( 'pushloop_api_key' ) ); ?>" placeholder="Plugin Token" />
				</td>
			</tr>
			<tr>
				<th scope="row">GMT Modifier</th>
				<td>
					<select type="number" required name="pushloop_gmt_sign" value="<?php echo esc_attr( get_option( 'pushloop_gmt' ) ); ?>" placeholder="Insert a value" />
					<option
					<?php
					if ( esc_attr( get_option( 'pushloop_gmt_sign' ) ) === '+' ) {
						echo 'selected';
					}
					?>
					value="+">+</option>
					<option
					<?php
					if ( esc_attr( get_option( 'pushloop_gmt_sign' ) ) === '-' ) {
						echo 'selected';
					}
					?>
					value="-">-</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">GMT Hours</th>
				<td>
					<input type="number" required name="pushloop_gmt" size="2" value="<?php echo esc_attr( get_option( 'pushloop_gmt' ) ); ?>" placeholder="Insert a value" />
				</td>
			</tr>

			<tr>
				<th scope="row">
					<h3>UTM Params</h3>
				</th>
			</tr>
			<tr>
				<th scope="row">Source</th>
				<td><input type="text" name="pushloop_utm_source" size="64" maxlength="32" value="<?php echo esc_attr( get_option( 'pushloop_utm_source' ) ); ?>" placeholder="pushloop" /></td>
			</tr>
			<tr>
				<th scope="row">Medium</th>
				<td><input type="text" name="pushloop_utm_medium" size="64" maxlength="32" value="<?php echo esc_attr( get_option( 'pushloop_utm_medium' ) ); ?>" placeholder="push_notification" /></td>
			</tr>
			<tr>
				<th scope="row">Content</th>
				<td><input type="text" name="pushloop_utm_campaign" size="64" maxlength="32" value="<?php echo esc_attr( get_option( 'pushloop_utm_campaign' ) ); ?>" placeholder="pushloop_campaign" /></td>
			</tr>

			<tr>
				<th scope="row">
					<h3>Automation</h3>
				</th>
			</tr>
			<tr>
				<th scope="row">Enable Pushloop for</th>
				<td>
					<?php
					$post_types_all      = pushloop_get_post_types_allowed( true );
					$pushloop_enable_for = explode( ',', get_option( 'pushloop_enable_for', 'post,page' ) );
					foreach ( $post_types_all as $key => $value ) {
						echo '<label class="pushloop_enable_for_cb"><input class="pushloop-ui-toggle" type="checkbox" name="enable_pushloop[]" value="' . esc_attr( $value ) . '" ' . ( in_array( $value, $pushloop_enable_for, true ) ? 'checked' : '' ) . '> ' . esc_html( ucwords( $key ) ) . '</label>';
						?>
						<?php
					}
					?>
				</td>
			</tr>

			<tr>
				<th scope="row">Send condition</th>
				<td>
					<label><input class="pushloop-ui-toggle" type="checkbox" name="pushloop_default_for_post" id="pushloop_default_for_post"
					<?php
					if ( get_option( 'pushloop_default_for_post' ) === 'on' ) {
						?>
						checked="checked"
						<?php
					}
					?>
					/> Automatically send push notification when a new post or page is published</label><br>
					<small>Automatic pushes will be sent after at least 10 minutes from the article generation</small>
				</td>
			</tr>

			<tr>
				<th scope="row" colspan="2">
					<label><input class="pushloop-ui-toggle" type="checkbox" name="pushloop_large_image"
					<?php
					if ( get_option( 'pushloop_large_image', 0 ) ) {
						echo 'checked';
					}
					?>
					value="1" /> Add featured image as a large image in notifications</label>
				</th>
			</tr>

			<tr>
				<th scope="row">
					<h3>Popup</h3>
				</th>
			</tr>
			<tr>
				<th scope="row">Popup type</th>
				<td>
					<select class="form-select" name="pushloop_popup_type">
						<option
						<?php
						if ( get_option( 'pushloop_popup_type' ) === '0' || null !== get_option( 'pushloop_popup_type' ) ) {
							echo 'selected';
						}
						?>
						value="0">Direct</option>
						<option
						<?php
						if ( get_option( 'pushloop_popup_type' ) === '1' ) {
							echo 'selected';
						}
						?>
						value="1">Popup (BETA)</option>
						<option
						<?php
						if ( get_option( 'pushloop_popup_type' ) === '2' ) {
							echo 'selected';
						}
						?>
						value="2">Custom popup (BETA)</option>
					</select><br>
					<small>If you want to make popup changes immediately effective you need to disable and then re-enable the plugin</small>
					<br><small><i>"Direct"</i> is the best option for optimizing the subscribtion rate. We are working for finding a solution for the other two options.</small>
				</td>
			</tr>

			<tr id="tableAMP">
				<th scope="row">
					<h3>AMP</h3>
				</th>
				<td>
					<label><input id="checkAMP" type="checkbox" class="pushloop-ui-toggle" name="pushloop_amp"
					<?php
					if ( get_option( 'pushloop_amp', 0 ) ) {
						echo 'checked';
					}
					?>
					value="1" />Enable Pushloop for AMP </label><br>
					<small> If you've any kind of URL Redirects on, you should manually install Pushloop files by following <i>"AMP integration"->"Step 2"</i> guide 
					<?php
					if ( get_option( 'pushloop_web_id' ) ) {
						?>
						<a href="
						<?php
						$pushloop_web_id = esc_attr( get_option( 'pushloop_web_id' ) );
						echo esc_url( 'https://app.pushloop.io/site/install/' . $pushloop_web_id );
						?>
						" target="_blank"> here</a> 
						<?php
					}
					?>
					</small>
					<br><br>
					<div id="AMPcustom">
						<label>Customize Button CSS style</label><br>
						<textarea cols="40" rows="15" name="pushloop_amp_custom_css" id="pushloop_amp_custom_css">
						<?php
							$custom_css = get_option( 'pushloop_amp_custom_css' );
						if ( $custom_css ) {
							echo esc_textarea( $custom_css );
						}
						?>
						</textarea><br><br>
						<label>Customize Subscribe Button Text</label><br>
						<input type="text" name="pushloop_amp_custom_text_subscribe" size="64" maxlength="64" value="
						<?php
							$custom_text = get_option( 'pushloop_amp_custom_text_subscribe' );
						if ( $custom_text ) {
							echo esc_html( $custom_text );
						}
						?>
						" /><br><br>
						<label>Customize Unsubscribe Button Text</label><br>
						<input type="text" name="pushloop_amp_custom_text_unsubscribe" size="64" maxlength="64" value="
						<?php
							$custom_text = get_option( 'pushloop_amp_custom_text_unsubscribe' );
						if ( $custom_text ) {
							echo esc_html( $custom_text );
						}
						?>
						" />
					</div>
				</td>
			</tr>

		</table>
		<?php
		submit_button( 'Save Changes', 'pushloop-button', 'pl-save-changes' );
		wp_nonce_field( plugin_basename( __FILE__ ), 'pushloop-submenu-page-save-nonce' );
		?>
	</form>
		<?php

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_general_settings_callback: ' . $th->__toString() );
	}
}