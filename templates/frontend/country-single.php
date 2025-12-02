<?php if (!defined('ABSPATH')) exit; ?>

<?php
/* -----------------------------------
   Helper function
----------------------------------- */
function tm_format_price($v) {
    if (!$v || $v <= 0) return 'N/A';
    return number_format((float)$v, 2);
}

$step_image = trailingslashit(WP_TMS_NEXILUP_PLUGIN_URL) . 'assets/img/step.webp';

$step1_url = add_query_arg(['country' => $country->iso_code], site_url('/tm/trademark-choose/order-form'));
$step2_url = add_query_arg(['country' => $country->iso_code], site_url('/tm/trademark-choose/order-form?tm_additional_class=1'));
$step3_url = add_query_arg(['country' => $country->iso_code], site_url('/myaccount/my-trademarks/active-trademarks'));
?>

<div class="tm-country-single-page">

<!-- =============================
     STEP 1
============================= -->
<section class="tm-nominus-step">
    <h2 class="tm-nominus-step-title">Step 1 - Comprehensive Trademark Study</h2>

    <div class="tm-nominus-step-inner">
        <div class="tm-nominus-step-image">
            <img src="<?php echo $step_image; ?>" alt="Step Image">
        </div>

        <div class="tm-nominus-step-content">
            <p class="tm-nominus-top-text">
                Planning to file a trademark in <strong><?php echo esc_html($country->country_name); ?></strong>?
                Our Comprehensive Study identifies issues earlier.
            </p>

            <ul class="tm-nominus-bullets">
                <li>Check for conflicting trademarks</li>
                <li>Attorney review of registrability</li>
                <li>Avoid office actions</li>
            </ul>

            <div class="tm-nominus-step-actions">
                <a href="<?php echo esc_url($step1_url); ?>" class="tm-nominus-order-btn">Order</a>
                <a href="#" class="tm-nominus-prices-link tm-open-prices-modal">>> Prices</a>
            </div>
        </div>
    </div>
</section>

<!-- =============================
     STEP 2
============================= -->
<section class="tm-nominus-step">
    <h2 class="tm-nominus-step-title">Step 2 - Application Filing</h2>

    <div class="tm-nominus-step-inner">
        <div class="tm-nominus-step-image">
            <img src="<?php echo $step_image; ?>" alt="Step Image">
        </div>

        <div class="tm-nominus-step-content">
            <p class="tm-nominus-top-text">
                We file and prepare the trademark application in
                <strong><?php echo esc_html($country->country_name); ?></strong>.
            </p>

            <ul class="tm-nominus-bullets">
                <li>Application drafting & filing</li>
                <li>Class selection & review</li>
                <li>Official filing receipt</li>
            </ul>

            <div class="tm-nominus-step-actions">
                <a href="<?php echo esc_url($step2_url); ?>" class="tm-nominus-order-btn">Order</a>
                <a href="#" class="tm-nominus-prices-link tm-open-prices-modal">>> Prices</a>
            </div>
        </div>
    </div>
</section>

<!-- =============================
     STEP 3
============================= -->
<section class="tm-nominus-step">
    <h2 class="tm-nominus-step-title">Step 3 - Registration Certificate</h2>

    <div class="tm-nominus-step-inner">
        <div class="tm-nominus-step-image">
            <img src="<?php echo $step_image; ?>" alt="Step Image">
        </div>

        <div class="tm-nominus-step-content">
            <p class="tm-nominus-top-text">
                We complete your registration and deliver the certificate.
            </p>

            <ul class="tm-nominus-bullets">
                <li>Monitoring registration</li>
                <li>Handling office actions</li>
                <li>Certificate issuance</li>
            </ul>

            <div class="tm-nominus-step-actions">
                <a href="<?php echo esc_url($step3_url); ?>" class="tm-nominus-order-btn">Order</a>
                <a href="#" class="tm-nominus-prices-link tm-open-prices-modal">>> Prices</a>
            </div>
        </div>
    </div>
</section>

<?php include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/partials/prices-and-conditions-modals.php'; ?>

</div>
