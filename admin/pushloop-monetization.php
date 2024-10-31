<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monetization Stats
 */
function pushloop_monetization_stats() {
	try {
		global $title;

		echo "<h2 class='top-margin'>" . esc_html( $title ) . "</h2> <a href='https://help.pushloop.io' class='button pushloop-button help' target='_blank'>Help</a>";
		$api_key = get_option( 'pushloop_api_key' );
		$resp    = pushloop_validate_token( $api_key );
		$site_id = get_option( 'pushloop_web_id' );

		if ( $api_key && $site_id && $resp ) {
			?>
			<input type="text" id="monetizationAnalyticsDaterange" name="pushesAnalyticsDaterange" class="pushloop-text" />
			<div class="table-container">
				<table id="monetizationAnalyticsTable" class="display pushloop-datatables">
					<thead>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<th>TOTAL</th>
						<th></th>
						<th></th>
						<th></th>
					</tfoot>
				</table>
			</div>
	
			<script>
			jQuery(document).ready (function() {
				jQuery('#monetizationAnalyticsDaterange').daterangepicker({
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
				var table = jQuery('#monetizationAnalyticsTable').DataTable({
					dom: 'ftip',
					deferRender: true,
					paging: true,
					searching: true,
					ordering: true,
					order: [[0, 'desc']],
					columns: [
						{ data: 'date', title: 'Date', className: 'dt-center' },
						{ data: 'revenue', title: 'Revenue', className: 'dt-center' }, 
						{ data: 'click', title: 'Click', className: 'dt-center' },
						{ data: 'epc', title: 'EPC', className: 'dt-center' }
					],
					columnDefs: [
						{
							targets: 1,
							data: 'revenue',
							render: function (data, type, row, meta) {
								return data.toFixed(2) + '€';
							}
						},
						{
							targets: 3,
							data: 'epc',
							render: function (data, type, row, meta) {
								return data.toFixed(2) + '€';
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
						var Col1 = api
							.column(1, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var totalCol1 = api
							.column(1)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var Col2 = api
							.column(2, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var totalCol2 = api
							.column(2)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var Col3 = api
							.column(3, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var countCol3 = api
							.column(3, { page: 'current' })
							.data()
							.length;
						var avgCol3 = countCol3 ? Col3 / countCol3 : 0;
	
						var totalCol3 = api
							.column(3)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						var totalCountCol3 = api
							.column(3)
							.data()
							.length;
						var totAvgCol3 = totalCountCol3 ? totalCol3 / totalCountCol3 : 0;
						jQuery(api.column(1).footer()).html(Col1.toFixed(2)+ '€' + ' ('+totalCol1.toFixed(2)+'€ total)');
						jQuery(api.column(2).footer()).html(Col2+' ('+totalCol2+' total)');
						jQuery(api.column(3).footer()).html(avgCol3.toFixed(2) + '€' + ' ('+totAvgCol3.toFixed(2)+'€ total)');
					},
					ajax: {
						url: 'https://api.pushloop.io/api/v2/analytics/monetization',
						type: "POST",
						headers: { 'ApiKey': api_key },
						data: function (d) {
							var dateRange = jQuery('#monetizationAnalyticsDaterange').val().split(' - ');
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
	
				jQuery('#monetizationAnalyticsDaterange').on('apply.daterangepicker', function (ev, picker) {
					table.ajax.reload();
				});
			});
			</script>
			<?php
		}
	} catch ( \Throwable $th ) {
		PushloopLogger::error( 'pushloop_monetization_stats: ' . $th->__toString() );
	}
}