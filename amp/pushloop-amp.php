<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'pushloop_amp' ) ) {
	add_action( 'amp_post_template_css', 'pushloop_amp_widget_css', 11 );
	add_action( 'amp_post_template_footer', 'pushloop_amp_widget_footer' );
}

/**
 * Add AMP button.
 */
function pushloop_amp_widget_footer() {
	$pushloop_site_domain = get_option( 'pushloop_site_domain' );
	$pushloop_web_id      = get_option( 'pushloop_web_id' );
	$pushloop_popup_type  = get_option( 'pushloop_popup_type', 0 );

	$helper_iframe_url     = PUSHLOOP_URL . 'amp/amp-helper-frame.html';
	$permission_dialog_url = PUSHLOOP_URL . 'amp/amp-permission-dialog.html';
	$service_worker_url    = PUSHLOOP_URL . 'js/pushloop_sw.js';
	?>
	<script src="https://cdn.pushloop.io/code/sdk/?code=<?php echo esc_html( $pushloop_site_domain ); ?>&site_id=<?php echo esc_html( $pushloop_web_id ); ?>&tpl=tpl_<?php echo esc_html( $pushloop_popup_type ); ?>&script_prefix=https://cdn.pushloop.io&swLocalPath=<?php echo esc_html( $service_worker_url ); ?>"></script>
	<amp-web-push id="amp-web-push" layout="nodisplay" helper-iframe-url="<?php echo esc_html( $helper_iframe_url ); ?>" permission-dialog-url="<?php echo esc_html( $permission_dialog_url ); ?>" service-worker-url="<?php echo esc_html( $service_worker_url ); ?>"></amp-web-push>

	<!-- A subscription widget -->
	<amp-web-push-widget visibility="unsubscribed" layout="fixed" width="245" height="45">
		<button class="subscribe" on="tap:amp-web-push.subscribe">
			<amp-img class="subscribe-icon" width="19" height="24" layout="fixed" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAYCAQAAACyng6EAAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAACYktHRAD/h4/MvwAAAAlwSFlzAAAHYgAAB2IBOHqZ2wAAAAd0SU1FB+cHBQojK13ppk8AAAGVSURBVCjPfdMxSFVhGMbx/3fvcSgRLtnlEvciZC6CghAKUbjcvc0maRFagiAoouni2p6ioIu4uEgNTS5Bi9ASROGgCLcWXZS0OPd83/s0nGOdPOf2jIcfHy/v8x7HpRiiAoDLfa1cZkI3eMoweDwBSgAi4Kd0aNNL9CaSK0mRGWHIz9n1XsveJzO6rw82YmXManpjz0TcOovshT0yjIA4y88pPGpwtRq5MTdov/SJ2N3SfvU491Y3nayuV+qqp6Cf+qyP+qEdG9UFOkUI39S2CvHzGYsRRmja2yLStzAhOMIwhDVVhqTvNm4gbDCM64F2VB4L8yLSHbfoJqlTpTyOBkRM0i5W9k9iwNf0Wqb+OQ9tpRVp6T/wXTKUkK6iP/xit4XAMJKBUC+FX3VXnHOSnqEeayWMFuCe7okEn11ZRVuSNm1Myzl4oFnhU5Qd4xMFSRvW0uoFtMUcIgLBmoto0+VUz12FBSBmF9zfnXeyTjWQvmw1rUs69Dc9nfySOygtP2WEa3qph3E16d/LUfbjiPBnLoDfbYvFP7eXdSEAAAA7dEVYdENvbW1lbnQAeHI6ZDpEQUZud3RZbTA5ODozLGo6NTgxNjc5NDQ3ODQzNTQ5NjYwNyx0OjIzMDcwNTEwHdg+jAAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAyMy0wNy0wNVQxMDozNDo0MiswMDowMBtQpTEAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMjMtMDctMDVUMTA6MzQ6NDIrMDA6MDBqDR2NAAAAKHRFWHRkYXRlOnRpbWVzdGFtcAAyMDIzLTA3LTA1VDEwOjM1OjQzKzAwOjAwdK1c2AAAAABJRU5ErkJggg==" >
			</amp-img>
			<?php
				echo esc_html( get_option( 'pushloop_amp_custom_text_subscribe' ) );
			?>
		</button>
	</amp-web-push-widget>

	<!-- An unsubscription widget -->
	<amp-web-push-widget visibility="subscribed" layout="fixed" width="230" height="45">
		<button class="unsubscribe" on="tap:amp-web-push.unsubscribe">
			<?php
				echo esc_html( get_option( 'pushloop_amp_custom_text_unsubscribe' ) );
			?>
		</button>
	</amp-web-push-widget>
	<?php
}

/**
 * Get css options
 */
function pushloop_amp_widget_css() {
	echo esc_html( get_option( 'pushloop_amp_custom_css' ) );
}