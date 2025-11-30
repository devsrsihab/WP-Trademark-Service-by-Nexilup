<?php
/**
 * Prices & Service Conditions Modal
 */
if (!defined('ABSPATH')) exit;

global $wpdb;

/* =======================================================
   1) FETCH PRICE ROW FOR THIS COUNTRY
======================================================= */
$prices_table = TM_Database::table_name('country_prices');

$p = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $prices_table WHERE country_id = %d LIMIT 1", $country->id)
);

if (!$p) {
    $base_one = 0;
    $base_add = 0;
    $currency = "USD";
} else {
    $base_one = floatval($p->first_class_fee);
    $base_add = floatval($p->additional_class_fee);
    $currency = $p->currency ?: 'USD';
}

/* =======================================================
   2) FETCH SERVICE CONDITIONS (only 1 allowed per country)
======================================================= */
$sc_table = TM_Database::table_name('service_conditions');

$sc_item = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $sc_table WHERE country_id = %d LIMIT 1", $country->id)
);

// Determine if available
$has_any_sc = ($sc_item && trim($sc_item->content) !== "");
?>

<!-- =====================================================
     PRICES MODAL — FLAT PRICE MODEL
====================================================== -->
<div id="tm-prices-modal" class="tm-modal" aria-hidden="true">
    <div class="tm-modal-backdrop"></div>

    <div class="tm-modal-dialog" role="dialog" aria-modal="true">

        <span class="tm-modal-close" aria-label="Close">&times;</span>

        <h2 class="tm-modal-title">Trademark Registration Prices</h2>

        <p class="tm-modal-subtitle">
            Prices for the trademark registration process in
            <strong><?php echo esc_html($country->country_name); ?></strong>.
        </p>

        <p class="tm-modal-currency-note">
            Prices are in <?php echo esc_html($currency); ?> U.S. Dollar
        </p>

        <!-- Tabs -->
        <div class="tm-modal-tabs">
            <span class="tm-prices-tab is-active" data-type="word">WORD MARK</span>
            <span class="tm-prices-tab" data-type="figurative">FIGURATIVE MARK</span>
            <span class="tm-prices-tab" data-type="combined">COMBINED MARK</span>
        </div>

        <!-- Panels -->
        <div class="tm-modal-body">

            <?php $types = ['word', 'figurative', 'combined']; ?>

            <?php foreach ($types as $type): ?>
                <?php
                $multiplier = ($type === 'combined') ? 2 : 1;

                $s1_one = $base_one * $multiplier;
                $s1_add = $base_add * $multiplier;

                $s2_one = $base_one * $multiplier;
                $s2_add = $base_add * $multiplier;

                $s3_one = $base_one * $multiplier;
                $s3_add = $base_add * $multiplier;
                ?>
            
                <div class="tm-prices-panel <?php echo $type === 'word' ? 'is-active' : ''; ?>"
                     data-type="<?php echo esc_attr($type); ?>">

                    <!-- STEP 1 -->
                    <div class="tm-prices-step-card">
                        <h3>Step 1 — Comprehensive Trademark Study</h3>
                        <div class="tm-prices-step-table">
                            <div class="tm-prices-row">
                                <span>One Class</span>
                                <strong><?php echo tm_format_price($s1_one); ?></strong>
                            </div>
                            <div class="tm-prices-row">
                                <span>Add. Class</span>
                                <strong><?php echo tm_format_price($s1_add); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="tm-prices-step-card">
                        <h3>Step 2 — Trademark Application Filing</h3>
                        <div class="tm-prices-step-table">
                            <div class="tm-prices-row">
                                <span>One Class</span>
                                <strong><?php echo tm_format_price($s2_one); ?></strong>
                            </div>
                            <div class="tm-prices-row">
                                <span>Add. Class</span>
                                <strong><?php echo tm_format_price($s2_add); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3 -->
                    <!-- <div class="tm-prices-step-card">
                        <h3>Step 3 — Registration Certificate</h3>
                        <div class="tm-prices-step-table">
                            <div class="tm-prices-row">
                                <span>One Class</span>
                                <strong><?php echo tm_format_price($s3_one); ?></strong>
                            </div>
                            <div class="tm-prices-row">
                                <span>Add. Class</span>
                                <strong><?php echo tm_format_price($s3_add); ?></strong>
                            </div>
                        </div>
                    </div> -->

                </div>

            <?php endforeach; ?>

        </div>


        <!-- Show only if SC exists -->
        <?php if ($has_any_sc): ?>
            <p class="tm-modal-footnote">
                Please review 
                <a href="#" class="tm-open-service-conditions" 
                   style="color:#0066cc; text-decoration:underline; font-weight:600;">
                    Service Conditions
                </a>.
                Note that prices include official fees.
            </p>
        <?php endif; ?>

    </div>
</div>


<!-- =====================================================
     SERVICE CONDITIONS MODAL
====================================================== -->
<div id="tm-service-conditions-modal" class="tm-modal" aria-hidden="true">
    <div class="tm-modal-backdrop"></div>

    <div class="tm-modal-dialog" role="dialog" aria-modal="true">

        <button class="tm-modal-close" aria-label="Close">&times;</button>

        <h2 class="tm-modal-title">Service Conditions</h2>

        <div class="tm-service-conditions-body">
            <?php if ($has_any_sc): ?>
                <div class="tm-sc-block">
                    <?php echo wp_kses_post($sc_item->content); ?>
                </div>
            <?php else: ?>
                <p>No service conditions available for this country.</p>
            <?php endif; ?>
        </div>

        <div class="tm-service-conditions-footer">
            <button class="tm-btn-primary tm-close-service-conditions">OK</button>
        </div>

    </div>
</div>
