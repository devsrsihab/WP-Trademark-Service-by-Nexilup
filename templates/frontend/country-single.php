<?php
/**
 * Single country trademark page (Pixel Perfect Nominus Layout)
 */

if (!defined('ABSPATH')) exit;

/* -----------------------------------------
   ORGANIZE PRICE ROWS BY STEP
------------------------------------------ */
$by_step = [1 => null, 2 => null, 3 => null];

if (!empty($steps)) {
    foreach ($steps as $row) {
        $step = (int)$row->step_number;
        if ($step < 1 || $step > 3) continue;

        if ($by_step[$step] === null) {
            $by_step[$step] = $row;
        }

        if ($row->trademark_type === 'combined') {
            $by_step[$step] = $row;
        } elseif ($row->trademark_type === 'word' && $by_step[$step]->trademark_type !== 'combined') {
            $by_step[$step] = $row;
        }
    }
}

/* -------------------------------
   PRICE FORMATTER
-------------------------------- */
function tm_format_price($v) {
    if ($v === null || $v === '' || (float)$v <= 0) return 'N/A';
    return number_format((float)$v, 2);
}

/* -------------------------------
   DETECT CURRENCY
-------------------------------- */
$currency = 'USD';
foreach ($by_step as $s) {
    if ($s && !empty($s->currency)) {
        $currency = $s->currency;
        break;
    }
}

/* -------------------------------
   ORDER URL BUILDER (SEO URLs)
-------------------------------- */

// Step 1 → Comprehensive Study
$step1_url = add_query_arg(
    ['country' => $country->iso_code],
    site_url('/tm/trademark-choose/order-form')
);

// Step 2 → Registration / Filing
$step2_url = add_query_arg(
    ['country' => $country->iso_code],
    site_url('/tm/trademark-choose/order-form?tm_additional_class=1')
);

// Step 3 → Dashboard - Active Trademarks
$step3_url = add_query_arg(
    ['country' => $country->iso_code],
    site_url('/myaccount/my-trademarks/active-trademarks')
);

$order_urls = [
    1 => $step1_url,
    2 => $step2_url,
    3 => $step3_url,
];

/* STEP IMAGE */
$step_image = esc_url(trailingslashit(wp_upload_dir()['baseurl']) . '2025/11/step_2.webp');

/* ============================================================
   BUILD FILTERED PRICE MATRIX FOR MODAL
============================================================ */
$steps_filtered = [
    'word'       => [1 => null, 2 => null, 3 => null],
    'figurative' => [1 => null, 2 => null, 3 => null],
    'combined'   => [1 => null, 2 => null, 3 => null],
];

if (!empty($steps)) {
    foreach ($steps as $row) {

        $type = strtolower(trim($row->trademark_type));

        if ($type === 'word mark')       $type = 'word';
        if ($type === 'figurative mark') $type = 'figurative';
        if ($type === 'combined mark')   $type = 'combined';

        if (!isset($steps_filtered[$type])) continue;

        $step = (int)$row->step_number;
        if ($step >= 1 && $step <= 3) {
            $steps_filtered[$type][$step] = $row;
        }
    }
}

/* ============================================================
   STEP VISIBILITY LOGIC
============================================================ */

$step_type_map = [
    1 => 'word',
    2 => 'figurative',
    3 => 'combined'
];

$visible_steps = [1 => false, 2 => false, 3 => false];

foreach ($step_type_map as $step => $type) {

    $row = $steps_filtered[$type][$step] ?? null;

    if ($row && (float)$row->price_one_class > 0) {
        $visible_steps[$step] = true;
    }
}

$any_step_visible = ($visible_steps[1] || $visible_steps[2] || $visible_steps[3]);

/* ============================================================
   SERVICE CONDITIONS (filter empty)
============================================================ */

$sc_items = [];
$has_any_sc = false;

if (!empty($service_conditions)) {
    foreach ($service_conditions as $sc) {
        if (!empty(trim($sc->content))) {
            $sc_items[] = $sc;
            $has_any_sc = true;
        }
    }
}

$default_titles = [
    1 => "Step 1 – Comprehensive Study",
    2 => "Step 2 – Registration / Filing",
    3 => "Step 3 – Owner Information",
];

?>

<!-- ============================
     MAIN PAGE LAYOUT
============================ -->
<div class="tm-country-single-page">

<?php if (!$any_step_visible): ?>
    <p class="tm-no-prices" style="text-align:center;margin:40px 0;font-size:16px;">
        No pricing is available for this country at the moment.
    </p>
<?php endif; ?>

<?php if ($visible_steps[1]): ?>
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
                <li>Avoid time-consuming office actions</li>
            </ul>

            <div class="tm-nominus-step-actions">
                <a href="<?php echo esc_url($order_urls[1]); ?>" class="tm-nominus-order-btn">Order</a>
                <a href="#" class="tm-nominus-prices-link tm-open-prices-modal">>> Prices</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($visible_steps[2]): ?>
<section class="tm-nominus-step">
    <h2 class="tm-nominus-step-title">Step 2 - Trademark Application Filing</h2>

    <div class="tm-nominus-step-inner">
        <div class="tm-nominus-step-image">
            <img src="<?php echo $step_image; ?>" alt="Step Image">
        </div>

        <div class="tm-nominus-step-content">
            <p class="tm-nominus-top-text">
                Our attorneys file your application in <strong><?php echo esc_html($country->country_name); ?></strong>.
            </p>

            <ul class="tm-nominus-bullets">
                <li>Full application drafting & filing</li>
                <li>Classification of goods/services</li>
                <li>Official filing receipt included</li>
                <li>Monitoring of application</li>
            </ul>

            <div class="tm-nominus-step-actions">
                <a href="<?php echo esc_url($order_urls[2]); ?>" class="tm-nominus-order-btn">Order</a>
                <a href="#" class="tm-nominus-prices-link tm-open-prices-modal">>> Prices</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($visible_steps[3]): ?>
<section class="tm-nominus-step">
    <h2 class="tm-nominus-step-title">Step 3 - Registration & Certificate</h2>

    <div class="tm-nominus-step-inner">
        <div class="tm-nominus-step-image">
            <img src="<?php echo $step_image; ?>" alt="Step Image">
        </div>

        <div class="tm-nominus-step-content">
            <p class="tm-nominus-top-text">
                After approval, we secure your official registration certificate.
            </p>

            <ul class="tm-nominus-bullets">
                <li>Monitoring until registration</li>
                <li>Handling formalities</li>
                <li>Certificate delivery</li>
                <li>Renewal reminders</li>
            </ul>

            <div class="tm-nominus-step-actions">
                <a href="<?php echo esc_url($order_urls[3]); ?>" class="tm-nominus-order-btn">Order</a>
                <a href="#" class="tm-nominus-prices-link tm-open-prices-modal">>> Prices</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?php include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/partials/prices-and-conditions-modals.php'; ?>

</div>
