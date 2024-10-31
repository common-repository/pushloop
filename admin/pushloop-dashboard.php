<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stats callback.
 */
function pushloop_stats_callback() {
	try {
		$api_key = get_option( 'pushloop_api_key' );
		$resp    = pushloop_validate_token( $api_key );
		$site_id = get_option( 'pushloop_web_id' );
		if ( ! $api_key || ! $site_id || ! $resp ) {
			?>
			<h2 class='top-margin'>Follow these steps to add Pushloop to your blog:</h2><br>
			<p>1) Create a Pushloop account or log in to your existing account.<br>
				2) Get your Plugin Token from your Pushloop Profile Page.<br>
				3) Get your Website ID from your Pushloop Sites Page.<br>
				4) Update settings in General Settings.<br>
				5) Enjoy with push by Pushloop!</p>
				<?php
		} else {

			$site = json_decode( pushloop_get_site() );

			global $title;
			?>
	
			<h2 class='top-margin'><?php echo esc_html( $title ); ?></h2> <a href='https://help.pushloop.io' class='button pushloop-button help' target='_blank'>Help</a>
			<?php
			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				echo "<div><img src='" . esc_url( plugin_dir_url( __DIR__ ) ) . "media/pushloop_logo_istituzionale_ecommerce.png' width='500'></img></div><br><br>";
			} else {
				echo "<div><img src='" . esc_url( plugin_dir_url( __DIR__ ) ) . "media/pushloop_logo_istituzionale.png' width='500'></img></div><br><br>";
			}
			?>
				<br><div><h2>Total Subscribers: <?php echo esc_html( $site->subscribers ); ?></h2></div><br><br>
				<input type="text" id="pushesAnalyticsDaterange" name="pushesAnalyticsDaterange" class="pushloop-text" />
				<div class="table-container">
					<table id="pushesAnalyticsTable" class="display pushloop-datatables">
						<thead>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<th></th>
							<th></th>
							<th>TOTAL</th>
							<th></th>
							<th></th>
							<th></th>
						</tfoot>
					</table>
				</div>
			<br><br><p>You can view all your pushes stats from your <a href="https://app.pushloop.io/" target="_blank">Pushloop Dashboard</a></p>
	
			<script>
			jQuery(document).ready (function() {
				jQuery('#pushesAnalyticsDaterange').daterangepicker({
					locale: {
						format: 'YYYY-MM-DD'
					},
					startDate: moment(),
					endDate: moment(),
					ranges: {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						}
				});
				const site_id = <?php echo esc_attr( $site_id ); ?>;
				const api_key = '<?php echo esc_attr( $api_key ); ?>';
				var table = jQuery('#pushesAnalyticsTable').DataTable({
					dom: 'ftip',
					deferRender: true,
					paging: true,
					searching: true,
					ordering: true,
					order: [[2, 'desc']],
					columns: [
						{ data: 'title', title: 'Title', className: 'dt-center' },
						{ data: 'description', title: 'Description', className: 'dt-center' },
						{ data: 'send_time', title: 'Sent Time', className: 'dt-center' },
						{ data: 'click', title: 'Click', className: 'dt-center' },
						{ data: 'impression', title: 'Impression', className: 'dt-center' },
						{ data: 'ctr', title: 'CTR', className: 'dt-center' }
					],
					columnDefs: [
						{
							targets: 5,
							data: 'ctr',
							render: function (data, type, row, meta) {
								return data.toFixed(2) + '%';
							}
						}
					],
					footerCallback: function (row, data, start, end, display) {
						var api = this.api();
						var intVal = function (i) {
							return typeof i === 'string' ?
								i.replace(/[\$,]/g, '') * 1 :
								typeof i === 'number' ?
									i : 0;
						};
						var Col3 = api
							.column(3, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var totalCol3 = api
							.column(3)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var Col4 = api
							.column(4, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var totalCol4 = api
							.column(4)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var Col5 = api
							.column(5, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var countCol5 = api
							.column(5, { page: 'current' })
							.data()
							.length;
						var avgCol5 = countCol5 ? Col5 / countCol5 : 0;
	
						var totalCol5 = api
							.column(5)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var totalCountCol5 = api
							.column(5)
							.data()
							.length;
						var totAvgCol5 = totalCountCol5 ? totalCol5 / totalCountCol5 : 0;
						jQuery(api.column(3).footer()).html(Col3+' ('+totalCol3+' total)');
						jQuery(api.column(4).footer()).html(Col4+' ('+totalCol4+' total)');
						jQuery(api.column(5).footer()).html(avgCol5.toFixed(2) + '%'+' ('+totAvgCol5.toFixed(2)+'% total)');
					},
					ajax: {
						url: 'https://api.pushloop.io/api/v2/analytics/pushes',
						type: "POST",
						headers: { 'ApiKey': api_key },
						data: function (d) {
							var dateRange = jQuery('#pushesAnalyticsDaterange').val().split(' - ');
							d.site_id = site_id;
							d.start_date = dateRange[0];
							d.end_date = dateRange[1];
						},
						error: function(jqXHR, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + jqXHR.statusText + "\r\n" + jqXHR.responseText + "\r\n" + ajaxOptions.responseText);
						},
						dataSrc: "data"
					},
				});
	
				jQuery('#pushesAnalyticsDaterange').on('apply.daterangepicker', function (ev, picker) {
					table.ajax.reload();
				});
			});
			</script>
			<?php
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_stats_callback: ' . $th->__toString() );
	}
}
