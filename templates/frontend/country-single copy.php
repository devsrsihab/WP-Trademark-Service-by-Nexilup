<?php
/**
 * Single country trademark page (PIXEL PERFECT NOMINUS LAYOUT)
 */

if (!defined('ABSPATH')) exit;

/* -----------------------------------------
   ORGANIZE PRICE ROWS BY STEP (PREFERRED LOGIC)
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
   ORDER URL BUILDER
-------------------------------- */
$service_form_base = site_url('/trademark-service-form/');

$order_urls = [
    1 => add_query_arg(['country' => $country->iso_code, 'step' => 1, 'type' => 'combined'], $service_form_base),
    2 => add_query_arg(['country' => $country->iso_code, 'step' => 2, 'type' => 'combined'], $service_form_base),
    3 => add_query_arg(['country' => $country->iso_code, 'step' => 3, 'type' => 'combined'], $service_form_base),
];

/* STEP IMAGE (same for all steps) */
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

        // NORMALIZE DATABASE TYPE (critical fix)
        $type = strtolower(trim($row->trademark_type));

        // Convert variations
        if ($type === 'word mark') $type = 'word';
        if ($type === 'figurative mark') $type = 'figurative';
        if ($type === 'combined mark') $type = 'combined';

        if (!isset($steps_filtered[$type])) continue;

        $step = (int)$row->step_number;
        if ($step >= 1 && $step <= 3) {
            $steps_filtered[$type][$step] = $row;
        }
    }
}

/* ============================================================
   NEW STEP VISIBILITY LOGIC (per your requirement)
   Step 1  → Word
   Step 2  → Figurative
   Step 3  → Combined
============================================================ */

$step_type_map = [
    1 => 'word',
    2 => 'figurative',
    3 => 'combined'
];

$visible_steps = [
    1 => false,
    2 => false,
    3 => false,
];

foreach ($step_type_map as $step => $type) {
    $row = $steps_filtered[$type][$step] ?? null;

    if ($row && (float)$row->price_one_class > 0) {
        $visible_steps[$step] = true;
    }
}

$any_step_visible = ($visible_steps[1] || $visible_steps[2] || $visible_steps[3]);


/* ============================================================
   SERVICE CONDITIONS FILTER
   $service_conditions may be:
   - array of DB rows (preferred)
   - string HTML (fallback)
============================================================ */
$sc_items = [];
$has_any_sc = false;

if (!empty($service_conditions)) {
    if (is_array($service_conditions)) {
        foreach ($service_conditions as $sc) {
            $content = isset($sc->content) ? trim($sc->content) : '';
            if ($content !== '') {
                $sc_items[] = $sc;
                $has_any_sc = true;
            }
        }
    } elseif (is_string($service_conditions) && trim($service_conditions) !== '') {
        $has_any_sc = true;
    }
}

/* Default step titles (for admin rows) */
$default_titles = [
    1 => "Step 1 – Comprehensive Study",
    2 => "Step 2 – Registration / Filing",
    3 => "Step 3 – Owner Information",
];

?>

<!-- ===========================================
     MAIN COUNTRY SINGLE PAGE LAYOUT
=========================================== -->
<div class="tm-country-single-page">

<?php if (!$any_step_visible): ?>
    <p class="tm-no-prices" style="text-align:center; margin:40px 0; font-size:16px;">
        No pricing is available for this country at the moment.
    </p>
<?php endif; ?>


<?php if ($visible_steps[1]): ?>
    <!-- ===========================
         STEP 1
    ============================ -->
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
    <!-- ===========================
         STEP 2
    ============================ -->
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
    <!-- ===========================
         STEP 3
    ============================ -->
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


    <!-- =====================================================
         PRICES MODAL (FULL DYNAMIC NOMINUS STYLE)
    ====================================================== -->
    <div id="tm-prices-modal" class="tm-modal" aria-hidden="true">
        <div class="tm-modal-backdrop"></div>

        <div class="tm-modal-dialog" role="dialog" aria-modal="true">

            <span class="tm-modal-close" aria-label="Close">&times;</span>

            <h2 class="tm-modal-title">Trademark Registration Prices</h2>

            <p class="tm-modal-subtitle">
                Prices for trademark registration in
                <strong><?php echo esc_html($country->country_name); ?></strong>
            </p>

            <p class="tm-modal-currency-note">
                Prices are in <?php echo esc_html($currency); ?>
            </p>

            <!-- Tabs -->
            <div class="tm-modal-tabs">
                <span class="tm-prices-tab is-active" data-type="word">WORD MARK</span>
                <span class="tm-prices-tab" data-type="figurative">FIGURATIVE MARK</span>
                <span class="tm-prices-tab" data-type="combined">COMBINED MARK</span>
            </div>

            <!-- Panels -->
            <div class="tm-modal-body">

                <?php foreach (['word','figurative','combined'] as $type): ?>

                    <div class="tm-prices-panel <?php echo $type === 'word' ? 'is-active' : ''; ?>"
                        data-type="<?php echo esc_attr($type); ?>">

                        <?php
                        $has_price = false;

                        for ($step = 1; $step <= 3; $step++):
                            $row = $steps_filtered[$type][$step] ?? null;
                            if (!$row) continue;

                            // Hide step card if both prices <= 0
                            $one = (float)$row->price_one_class;
                            $add = (float)$row->price_add_class;

                            if ($one <= 0 && $add <= 0) continue;

                            $has_price = true;
                        ?>

                        <div class="tm-prices-step-card">
                            <h3>
                                Step <?php echo $step; ?> —
                                <?php echo esc_html($by_step[$step]->step_title ?? 'Trademark Step'); ?>
                            </h3>

                            <div class="tm-prices-step-table">

                                <div class="tm-prices-row">
                                    <span>One Class</span>
                                    <strong><?php echo tm_format_price($row->price_one_class); ?></strong>
                                </div>

                                <div class="tm-prices-row">
                                    <span>Add. Class</span>
                                    <strong><?php echo tm_format_price($row->price_add_class); ?></strong>
                                </div>

                            </div>
                        </div>

                        <?php endfor; ?>

                        <?php if (!$has_price): ?>
                            <p class="tm-no-prices">No price data available for this mark type.</p>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php if ($has_any_sc): ?>
                <p class="tm-modal-footnote">
                    Please review <a href="#" class="tm-open-service-conditions">Service Conditions</a>
                </p>
            <?php endif; ?>

        </div>
    </div>



    <!-- ===========================================
         SERVICE CONDITIONS MODAL (DYNAMIC)
    =========================================== -->
    <div id="tm-service-conditions-modal" class="tm-modal" aria-hidden="true">
        <div class="tm-modal-backdrop"></div>

        <div class="tm-modal-dialog" role="dialog" aria-modal="true">

            <button class="tm-modal-close" aria-label="Close">&times;</button>

            <h2 class="tm-modal-title">Service Conditions</h2>

            <div class="tm-service-conditions-body">

                <?php if ($has_any_sc): ?>

                    <?php if (is_array($sc_items)): ?>
                        <?php foreach ($sc_items as $sc): ?>
                            <?php $sn = (int)$sc->step_number; ?>
                            <h3><?php echo esc_html($default_titles[$sn] ?? ("Step " . $sn)); ?></h3>
                            <div class="tm-sc-block">
                                <?php echo wp_kses_post($sc->content); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php echo wp_kses_post($service_conditions); ?>
                    <?php endif; ?>

                <?php else: ?>
                    <p>No service conditions available for this country.</p>
                <?php endif; ?>

            </div>

            <div class="tm-service-conditions-footer">
                <button class="tm-btn-primary tm-close-service-conditions">OK</button>
            </div>

        </div>
    </div>

</div>
