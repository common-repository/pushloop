function afterLoad()
{

	if (document.getElementById( "pl-send-submit" )) {
		const checkbox = document.getElementById( 'when-send-id' );

		const boxLabel = document.getElementById( 'send-time-label' );
		const boxInput = document.getElementById( 'send-time-input' );

		const send_btn             = document.getElementById( 'pl-send-submit' );
		const schedule_btn         = document.getElementById( 'pl-schedule-submit' );
		schedule_btn.style.display = 'none';
		boxLabel.style.display     = 'none';
		boxInput.style.display     = 'none';

		checkbox.addEventListener(
			'click',
			function handleClick()
			{
			if (checkbox.checked) {
				boxLabel.style.display     = 'block';
				boxInput.style.display     = 'block';
				send_btn.style.display     = 'none';
				schedule_btn.style.display = 'block';
			} else {
				schedule_btn.style.display = 'none';
				send_btn.style.display     = 'block';
				boxLabel.style.display     = 'none';
				boxInput.style.display     = 'none';
			}
			}
		);
	}

	if (document.getElementById( 'tableAMP' )) {
		const ampCheckbox = document.getElementById( 'checkAMP' );
		const ampCustom   = document.getElementById( 'AMPcustom' );

		if (ampCheckbox.checked) {
			ampCustom.style.display = 'block';
		} else {
			ampCustom.style.display = 'none';
		}

		ampCheckbox.addEventListener(
			'click',
			function handleClick()
			{

			if (ampCheckbox.checked) {
				ampCustom.style.display = 'block';
			} else {
				ampCustom.style.display = 'none';
			}
			}
		);
	}

	if (document.getElementById( 'pushloopEcommerceSettings' )) {
		const CartAbandonedCheckbox = document.getElementById( 'checkCartAbandoned' );
		const CartAbandonedCustom   = document.getElementById( 'divCartAbandonedCustom' );

		if (CartAbandonedCheckbox.checked) {
			CartAbandonedCustom.style.display = 'block';
		} else {
			CartAbandonedCustom.style.display = 'none';

		}

		CartAbandonedCheckbox.addEventListener(
			'click',
			function handleClick()
			{

			if (CartAbandonedCheckbox.checked) {
				CartAbandonedCustom.style.display = 'block';
			} else {
				CartAbandonedCustom.style.display = 'none';
			}
			}
		);

		const imageCheckbox = document.getElementById( 'pushloop_ecommerce_push_default_image' );
		const imageInput    = document.getElementById( 'pushloop_ecommerce_push_default_image_url' );

		if (imageCheckbox.checked) {
			imageInput.style.display = 'block';
		} else {
			imageInput.style.display = 'none';

		}

		imageCheckbox.addEventListener(
			'click',
			function handleClick()
			{

			if (imageCheckbox.checked) {
				imageInput.style.display = 'block';
			} else {
				imageInput.style.display = 'none';
			}
			}
		);
	}
}

window.addEventListener( 'load', afterLoad );
