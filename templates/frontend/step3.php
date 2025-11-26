<?php
if ( ! defined('ABSPATH') ) exit;

if ( empty($order_id) ) {
    echo "<div class='tm-error'>Invalid order.</div>";
    return;
}

$order = wc_get_order($order_id);

if ( ! $order ) {
    echo "<div class='tm-error'>Order not found.</div>";
    return;
}

$order_number  = $order->get_order_number();
$order_date    = $order->get_date_created();
$billing_email = $order->get_billing_email();
$total         = $order->get_total();
$currency      = $order->get_currency();
$payment_title = $order->get_payment_method_title();
$billing_addr  = $order->get_formatted_billing_address();
?>

<div class="tm-order-page tm-step3">

    <!-- 3-step bar -->
    <div class="tm-progress">
        <div class="tm-progress-line"></div>

        <div class="tm-progress-step is-active">
            <span class="dot"></span>
            <span>Trademark Information</span>
        </div>

        <div class="tm-progress-step is-active">
            <span class="dot"></span>
            <span>Confirm Order</span>
        </div>

        <div class="tm-progress-step is-active">
            <span class="dot"></span>
            <span>Order Receipt</span>
        </div>
    </div>

    <div class="tm-step3-container">
        <!-- Success headline -->
        <div class="tm-success-message">
            <h2>🎉 Order Received</h2>
            <p>Thank you for your order. We’ve sent a confirmation email with your order details.</p>
        </div>

        <!-- Top order info (like WooCommerce thank you) -->
        <div class="tm-order-box">
            <h3 class="tm-box-title">Order summary</h3>
            <div class="tm-order-meta-grid">
                <div class="tm-meta-row">
                    <span class="tm-meta-label">Order number</span>
                    <span class="tm-meta-value"><?php echo esc_html( $order_number ); ?></span>
                </div>
                <div class="tm-meta-row">
                    <span class="tm-meta-label">Date</span>
                    <span class="tm-meta-value">
                        <?php echo esc_html( wc_format_datetime( $order_date ) ); ?>
                    </span>
                </div>
                <?php if ( $billing_email ) : ?>
                <div class="tm-meta-row">
                    <span class="tm-meta-label">Email</span>
                    <span class="tm-meta-value"><?php echo esc_html( $billing_email ); ?></span>
                </div>
                <?php endif; ?>
                <div class="tm-meta-row">
                    <span class="tm-meta-label">Total</span>
                    <span class="tm-meta-value">
                        <?php echo wp_kses_post( wc_price( $total, [ 'currency' => $currency ] ) ); ?>
                    </span>
                </div>
                <?php if ( $payment_title ) : ?>
                <div class="tm-meta-row">
                    <span class="tm-meta-label">Payment method</span>
                    <span class="tm-meta-value"><?php echo esc_html( $payment_title ); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trademark items -->
        <div class="tm-order-items">
            <h3 class="tm-box-title">Trademark details</h3>

            <?php foreach ( $order->get_items() as $item ) : ?>
                <div class="tm-item">
                    <div class="tm-item-main">
                        <div class="tm-item-text">
                            <div class="tm-item-title">
                               <?php echo esc_html( $item->get_meta( 'tm_mark_text' ) ); ?>
                            </div>

                            <div class="tm-item-row">
                                <span class="label">Type:</span>
                                <span class="value">
                                    <?php echo esc_html( $item->get_meta( 'tm_type' ) ); ?>
                                </span>
                            </div>

                            <div class="tm-item-row">
                                <span class="label">Mark:</span>
                                <span class="value">
                                    <?php echo esc_html( $item->get_meta( 'tm_mark_text' ) ); ?>
                                </span>
                            </div>

                            <div class="tm-item-row">
                                <span class="label">Classes:</span>
                                <span class="value">
                                    <?php echo esc_html( $item->get_meta( 'tm_classes' ) ); ?>
                                </span>
                            </div>

                            <div class="tm-item-row">
                                <span class="label">Goods / Services:</span>
                                <span class="value">
                                    <?php echo esc_html( $item->get_meta( 'tm_goods' ) ); ?>
                                </span>
                            </div>

                            <div class="tm-item-row">
                                <span class="label">Country:</span>
                                <span class="value">
                                    <?php echo esc_html( $item->get_meta( 'tm_country_iso' ) ); ?>
                                </span>
                            </div>

                            <div class="tm-item-row">
                                <span class="label">Line total:</span>
                                <span class="value">
                                    <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ( $item->get_meta( 'tm_logo_url' ) ) : ?>
                            <div class="tm-item-logo">
                                <img
                                    src="<?php echo esc_url( $item->get_meta( 'tm_logo_url' ) ); ?>"
                                    alt="Trademark logo"
                                />
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Totals + billing -->
        <div class="tm-bottom-grid">
            <div class="tm-total-box">
                <h3 class="tm-box-title">Order totals</h3>
                <table class="tm-totals-table">
                    <tbody>
                        <tr>
                            <th>Subtotal</th>
                            <td><?php echo wp_kses_post( wc_price( $order->get_subtotal(), [ 'currency' => $currency ] ) ); ?></td>
                        </tr>
                        <?php if ( $order->get_total_tax() > 0 ) : ?>
                        <tr>
                            <th>Tax</th>
                            <td><?php echo wp_kses_post( wc_price( $order->get_total_tax(), [ 'currency' => $currency ] ) ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( $order->get_shipping_total() > 0 ) : ?>
                        <tr>
                            <th>Shipping</th>
                            <td><?php echo wp_kses_post( wc_price( $order->get_shipping_total(), [ 'currency' => $currency ] ) ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="tm-grand-total">
                            <th>Total</th>
                            <td><?php echo wp_kses_post( wc_price( $total, [ 'currency' => $currency ] ) ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="tm-address-box">
                <h3 class="tm-box-title">Billing address</h3>
                <div class="tm-address">
                    <?php
                    if ( $billing_addr ) {
                        echo wp_kses_post( $billing_addr );
                    } else {
                        echo '<p>Not provided.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Next steps -->
        <div class="tm-next-steps">
            <h3 class="tm-box-title">What happens next?</h3>
            <p>
                Our team will now review your order and start the comprehensive trademark study
                for the selected jurisdiction(s). You will be notified by email once the report
                is ready or if we need any additional information from you.
            </p>
        </div>
    </div>
</div>
