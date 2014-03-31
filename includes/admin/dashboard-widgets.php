<?php
/**
 * Dashboard Widgets
 *
 * @package     EDD
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the dashboard widgets
 *
 * @author Sunny Ratilal
 * @since 1.2.2
 * @return void
 */
function edd_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'edd_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		wp_add_dashboard_widget( 'edd_dashboard_sales', __('Easy Digital Downloads Sales Summary', 'edd'), 'edd_dashboard_sales_widget' );
	}
}
add_action('wp_dashboard_setup', 'edd_register_dashboard_widgets', 10 );

/**
 * Sales Summary Dashboard Widget
 *
 * Builds and renders the Sales Summary dashboard widget. This widget displays
 * the current month's sales and earnings, total sales and earnings best selling
 * downloads as well as recent purchases made on your EDD Store.
 *
 * @author Sunny Ratilal
 * @since 1.2.2
 * @return void
 */
function edd_dashboard_sales_widget() {
	$stats = new EDD_Payment_Stats; ?>
	<div class="edd_dashboard_widget">
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Current Month', 'edd' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first b b-earnings"><?php echo edd_currency_filter( edd_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ); ?></td>
						<td class="t monthly_earnings"><?php _e( 'Earnings', 'edd' ); ?></td>
					</tr>
					<tr>
						<?php $monthly_sales = $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ); ?>
						<td class="first b b-sales"><?php echo $monthly_sales; ?></td>
						<td class="t monthly_sales"><?php echo _n( 'Sale', 'Sales', $monthly_sales, 'edd' ); ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Last Month', 'edd' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first b b-last-month-earnings"><?php echo edd_currency_filter( edd_format_amount( $stats->get_earnings( 0, 'last_month' ) ) ); ?></td>
						<td class="t earnings"><?php echo __( 'Earnings', 'edd' ); ?></td>
					</tr>
					<tr>
						<td class="first b b-last-month-sales">
							<?php $last_month_sales = $stats->get_sales( 0, 'last_month', false, array( 'publish', 'revoked' ) ); ?>
							<?php echo $last_month_sales; ?>
						</td>
						<td class="t sales">
							<?php echo _n( 'Sale', 'Sales', $last_month_sales, 'edd' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Totals', 'edd' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="b b-earnings"><?php echo edd_currency_filter( edd_format_amount( edd_get_total_earnings() ) ); ?></td>
						<td class="last t earnings"><?php _e( 'Total Earnings', 'edd' ); ?></td>
					</tr>
					<tr>
						<td class="b b-sales"><?php echo edd_format_amount( edd_get_total_sales(), false ); ?></td>
						<td class="last t sales"><?php _e( 'Total Sales', 'edd' ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php
			$best_selling = $stats->get_best_selling( 1 );
			if ( ! empty( $best_selling ) ) : ?>
				<table>
					<thead>
						<tr>
							<td colspan="2">
								<?php _e('Lifetime Best Selling', 'edd') ?>
							</td>
						</tr>
					</thead>
					<tbody>
					<?php foreach( $best_selling as $top_seller ) : ?>
						<tr>
							<td class="edd_order_label">
								<a href="<?php echo get_permalink( $top_seller->download_id ); ?>"><?php echo get_the_title( $top_seller->download_id ); ?></a>
							</td>
							<td class="edd_order_price">
								<span class="edd_price_label"><?php echo $top_seller->sales; ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<div style="clear: both"></div>
		<?php
		$p_query = new EDD_Payments_Query( array(
			'number'   => 5,
			'status'   => 'publish'
		) );

		$payments = $p_query->get_payments();

		if ( $payments ) { ?>
		<div class="table recent_purchases">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Recent Purchases', 'edd' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $payments as $payment ) { ?>
						<tr>
							<td class="edd_order_label">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ); ?>" title="<?php printf( __( 'Purchase Details for Payment #%s', 'edd' ), $payment->ID ); ?> ">
									<?php echo get_the_title( $payment->ID ) ?> - (<?php echo $payment->user_info['email'] ?>)
								</a>
							</td>
							<td class="edd_order_price">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ); ?>" title="<?php printf( __( 'Purchase Details for Payment #%s', 'edd' ), $payment->ID ); ?> ">
									<span class="edd_price_label"><?php echo edd_currency_filter( edd_format_amount( $payment->total ) ); ?></span>
								</a>
							</td>
						</tr>
						<?php
					} // End foreach ?>
				</tbody>
			</table>
		</div>
		<?php } // End if ?>
	</div>
	<?php
}
