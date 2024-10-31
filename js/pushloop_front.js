window.addEventListener(
	'load',
	function () {
		if (typeof window.PushloopSw !== 'undefined' && typeof window.PushloopSw.getToken === 'function') {
			var cookieDeny = window.PushloopSw.getCookie( 'PushloopDenypushCookie' );

			if (Notification.permission === 'granted' && (cookieDeny === undefined || cookieDeny === null)) {
				window.PushloopSw.getDataFromIndexDB( 'pushloop', 'pushloopReferences' ).then(
					function (tokenData) {
						jQuery.post(
							pushloop_ajax_object.ajax_url,
							{
								action: 'pushloop_save_user_token',
								nonce: pushloop_ajax_object.pushloop_ajax_nonce,
								curr_tok: tokenData.token,
								old_tok: tokenData.oldToken,
							}
						).done(
							function (response) {
								console.log( response );
							}
						);
					}
				);
			}
		}
	}
);
