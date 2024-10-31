<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send Notification callback
 */
function pushloop_send_notifications_callback() {
	try {
		$markets           = get_option( 'pushloop_markets' );
		$pushloop_gmt      = get_option( 'pushloop_gmt' );
		$pushloop_gmt_sign = get_option( 'pushloop_gmt_sign' );

		global $title;

		echo "<h2 class='top-margin'>" . esc_html( $title ) . "</h2> <a href='https://help.pushloop.io' class='button pushloop-button help' target='_blank'>Help</a>";
		if ( isset( $_POST['pl-send-submit'] ) || isset( $_POST['pl-schedule-submit'] ) ) {
			if ( ! isset( $_POST['pushloop-submenu-page-save-nonce'] ) || ( ! wp_verify_nonce( sanitize_key( $_POST['pushloop-submenu-page-save-nonce'] ), plugin_basename( __FILE__ ) ) ) ) {
				echo '<div class="error"><p>Something went wrong!</p></div>';
			} else {
				$success                 = true;
				$notification_title      = isset( $_POST['pushloop_send_notification_title'] ) ? sanitize_text_field( wp_unslash( $_POST['pushloop_send_notification_title'] ) ) : '';
				$notification_message    = isset( $_POST['pushloop_send_notification_message'] ) ? sanitize_text_field( wp_unslash( $_POST['pushloop_send_notification_message'] ) ) : '';
				$notification_market     = filter_input( INPUT_POST, 'pushloop_send_notification_market', FILTER_SANITIZE_STRING );
				$notification_url        = filter_input( INPUT_POST, 'pushloop_send_notification_url', FILTER_VALIDATE_URL );
				$notification_img_url    = filter_input( INPUT_POST, 'pushloop_send_notification_image_url', FILTER_VALIDATE_URL );
				$notification_recurrence = filter_input( INPUT_POST, 'pushloop_send_notification_recurrence', FILTER_SANITIZE_STRING );
				if ( is_null( $notification_recurrence ) ) {
					$notification_recurrence = 0;
				}
				$notification_send_time = gmdate( 'Y-m-d H:i:s', strtotime( gmdate( 'Y-m-d H:i:s' ) . ' ' . $pushloop_gmt_sign . $pushloop_gmt . ' hours' ) );
				$is_send_now            = true;
				if ( filter_input( INPUT_POST, 'when-send', FILTER_SANITIZE_STRING ) === '1' ) {
					$notification_send_time = filter_input( INPUT_POST, 'pushloop_send_notification_datetime', FILTER_SANITIZE_STRING );
					$is_send_now            = false;
				}
				$utm_source       = get_option( 'pushloop_utm_source', '' );
				$utm_medium       = get_option( 'pushloop_utm_medium', '' );
				$utm_campaign     = get_option( 'pushloop_utm_campaign', '' );
				$notification_url = pushloop_set_utm( $notification_url, 'utm_source', $utm_source );
				$notification_url = pushloop_set_utm( $notification_url, 'utm_medium', $utm_medium );
				$notification_url = pushloop_set_utm( $notification_url, 'utm_campaign', $utm_campaign );

				$push = array(
					'title'      => $notification_title,
					'message'    => $notification_message,
					'url'        => $notification_url,
					'img'        => $notification_img_url,
					'market'     => $notification_market,
					'send_time'  => $notification_send_time,
					'recurrence' => $notification_recurrence,
					'when'       => $is_send_now ? 1 : 0,
				);

				try {
					$success = pushloop_send_notification_curl( $push, false );

				} catch ( \Throwable $th ) {
					PushloopLogger::error( 'pushloop_send_notifications_callback:' . $th->__toString() );
					$success = false;
				}
				if ( $success && $is_send_now ) {
					echo '<div class="updated"><p>Notification sent successfully!</p></div>';
				} elseif ( $success && ! $is_send_now ) {
					echo '<div class="updated"><p>Notification will be sent at ' . esc_html( $notification_send_time ) . '!</p></div>';
				} else {
					echo '<div class="error"><p>An error has occured!</p></div>';
				}
			}
		}
		echo '<form method="POST" action="">
		<table class="form-table">';

		echo '   <tr valign="top">
		<th scope="row" class="titledesc">
		<label for="pushloop_send_notification_title">Notification Title</label>
		</th>
		<td>
		<input type="text" name="pushloop_send_notification_title" id="pushloop_send_notification_title" placeholder="Notification Title" class="pushloop-text" required maxlength="100">
		</td>
		</tr>
		<tr valign="top">
		<th scope="row" class="titledesc">
		<label for="pushloop_send_notification_message">Notification Message</label>
		</th>
		<td>
		<textarea name="pushloop_send_notification_message" rows="3" id="pushloop_send_notification_message" placeholder="Notification Message" class="pushloop-text" required maxlength="255"></textarea>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row" class="titledesc">
		<label for="pushloop_send_notification_url">Target URL</label>
		</th>
		<td>
		<input type="text" name="pushloop_send_notification_url" id="pushloop_send_notification_url" placeholder="https://example.com" class="pushloop-text" pattern="[Hh][Tt][Tt][Pp][Ss]?:\/\/(?:(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)(?:\.(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)*(?:\.(?:[a-zA-Z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:\/[^\s]*)?" required>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row" class="titledesc">
		<label for="pushloop_send_notification_image_url">Image Url</label>
		</th>
		<td>
		<input type="text" name="pushloop_send_notification_image_url" id="pushloop_send_notification_image_url" placeholder="https://example.com/image.jpg" class="pushloop-text" pattern="[Hh][Tt][Tt][Pp][Ss]?:\/\/(?:(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)(?:\.(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)*(?:\.(?:[a-zA-Z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:\/[^\s]*)?" required>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row" class="titledesc">
		<label for="pushloop_send_notification_market">Destination Country</label>
		</th>
		<td> <select name="pushloop_send_notification_market" id="pushloop_send_notification_market" max-length= "2" class="pushloop-text" required>';
		if ( ! $markets || gettype( $markets ) === 'string' ) {
			echo "<option value='IT' selected>IT</option>";
		} else {
			echo "<option value='IT' selected>IT</option>";
			foreach ( $markets as $market ) {
				if ( 'IT' !== $market ) {
					echo "<option value='" . esc_attr( $market ) . "'>" . esc_html( $market ) . '</option>';
				}
			}
		}
		$time = gmdate( 'Y-m-d' ) . 'T' . gmdate( 'H:i', strtotime( gmdate( 'H:i' ) . ' ' . $pushloop_gmt_sign . $pushloop_gmt . ' hours 11 minutes' ) );

		echo '</select>
		<br><small>Select in which country you want to send this push notification</small>
		</td>
		</tr>
		<br>
		<tr valign="top">
		<th scope="row" class="titledesc">
		<label for="when-send-id">Schedule Push</label>
		</th>
		<td>
		<input type="checkbox" class="pushloop-ui-toggle" id="when-send-id" name="when-send" value="1">
		</td>
		<tr valign="top">
		<th scope="row" class="titledesc" id="send-time-label">
		<label>Set date and time:</label>
		</th>
		<td>
			<input id="send-time-input" type="datetime-local" name="pushloop_send_notification_datetime" value=' . esc_attr( $time ) . ' />
		</td>
		<tr valign="top">
		<th scope="row" class="titledesc date-time">
		<label class="custom-control-label" for="recurrence">Daily Recurrence </label>
		</th>
		<td>
			<input class="pushloop-ui-toggle" id="recurrence" type="checkbox" name="pushloop_send_notification_recurrence" value="1">
		</td>
		</table>
		';

		submit_button( 'Send Push', 'pushloop-button', 'pl-send-submit' );
		submit_button( 'Schedule Push', 'pushloop-button', 'pl-schedule-submit' );
		wp_nonce_field( plugin_basename( __FILE__ ), 'pushloop-submenu-page-save-nonce' );
		echo '</form>';

	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_send_notifications_callback: ' . $th->__toString() );
	}
}
