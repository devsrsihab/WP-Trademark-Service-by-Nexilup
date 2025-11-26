<?php
if (!defined('ABSPATH')) exit;

/** @var object $country */
/** @var array  $prices */
/** @var string $order_page */

$price_map = [
    'word'      => [],
    'figurative'=> [],
    'combined'  => [],
];

foreach ($prices as $row) {
    $type = $row->trademark_type;
    $step = intval($row->step_number);
    if (!isset($price_map[$type])) {
        $price_map[$type] = [];
    }
    $price_map[$type][$step] = [
        'one'      => $row->price_one_class,
        'add'      => $row->price_add_class,
        'currency' => $row->currency,
    ];
}
?>

<div class="tm-country-single-wrap">

    <h1>Trademark Registration in <?php echo esc_html($country->country_name); ?></h1>

    <div class="tm-country-header">
        <div class="tm-flag flag-shadowed-<?php echo esc_attr($country->iso_code); ?>"></div>
        <p>Select your trademark type and step to start the order.</p>
    </div>

    <div class="tm-type-selector">
        <label><strong>Trademark Type:</strong></label>
        <label><input type="radio" name="tm_type" value="word" checked> Word Mark</label>
        <label><input type="radio" name="tm_type" value="figurative"> Figurative Mark</label>
        <label><input type="radio" name="tm_type" value="combined"> Combined Mark</label>
    </div>

    <div class="tm-steps-grid">
        <?php for ($step = 1; $step <= 3; $step++): ?>
            <div class="tm-step-card" data-step="<?php echo $step; ?>">
                <h3>
                    <?php if ($step == 1) echo 'Step 1 – Comprehensive Study';
                          elseif ($step == 2) echo 'Step 2 – Application Filing';
                          else echo 'Step 3 – Registration / Maintenance'; ?>
                </h3>

                <p class="tm-step-price" data-step="<?php echo $step; ?>">
                    <span class="tm-price-main"></span><br>
                    <small class="tm-price-extra"></small>
                </p>

                <?php if ($order_page): ?>
                    <a href="#"
                       class="button button-primary tm-order-btn"
                       data-step="<?php echo $step; ?>"
                       data-country="<?php echo esc_attr($country->iso_code); ?>">
                        Order this Step
                    </a>
                <?php else: ?>
                    <p><em>Order page not configured in shortcode.</em></p>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<script>
(function(){
    const priceMap = <?php echo wp_json_encode($price_map); ?>;
    const orderPage = "<?php echo esc_js($order_page); ?>";
    const countryIso = "<?php echo esc_js($country->iso_code); ?>";

    function updatePrices() {
        const type = document.querySelector('input[name="tm_type"]:checked').value;

        for (let step = 1; step <= 3; step++) {
            const priceBox = document.querySelector('.tm-step-price[data-step="'+step+'"]');
            if (!priceBox) continue;

            const data = priceMap[type] && priceMap[type][step] ? priceMap[type][step] : null;

            if (!data) {
                priceBox.querySelector('.tm-price-main').textContent  = 'Not available for this type.';
                priceBox.querySelector('.tm-price-extra').textContent = '';
            } else {
                priceBox.querySelector('.tm-price-main').textContent =
                    data.one.toFixed(2) + ' ' + data.currency + ' (1 class)';

                priceBox.querySelector('.tm-price-extra').textContent =
                    'Each additional class: ' +
                    data.add.toFixed(2) + ' ' + data.currency;
            }
        }
    }

    document.querySelectorAll('input[name="tm_type"]').forEach(function(radio){
        radio.addEventListener('change', updatePrices);
    });

    updatePrices();

    // Order buttons → redirect to service form
    document.querySelectorAll('.tm-order-btn').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            if (!orderPage) return;

            const step = this.getAttribute('data-step');
            const type = document.querySelector('input[name="tm_type"]:checked').value;

            const url = orderPage
                + '?country=' + encodeURIComponent(countryIso)
                + '&step=' + encodeURIComponent(step)
                + '&type=' + encodeURIComponent(type);

            window.location.href = url;
        });
    });

})();
</script>
