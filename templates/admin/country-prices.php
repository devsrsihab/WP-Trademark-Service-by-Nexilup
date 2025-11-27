<?php
if (!defined('ABSPATH')) exit;

$paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$data = TM_Country_Prices::get_paginated_prices($paged, 10);
$prices = $data['items'];

$countries = TM_Database::get_countries(['active_only' => false]);
$nonce     = wp_create_nonce('tm_country_prices_nonce');
?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin-prices.css'; ?>">
<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin.css'; ?>">

<div class="wrap tm-wrap">

    <h1 class="tm-page-title">Country Prices</h1>

    <button id="tm-add-price-btn" class="button button-primary">+ Add Price</button>

    <table class="wp-list-table widefat fixed striped mt-20">
        <thead>
        <tr>
            <th>Country</th>
            <th>Type</th>
            <th>Step</th>
            <th>One Class Fee</th>
            <th>Additional Class Fee</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody id="tm-price-list">
        <?php foreach ($prices as $p): ?>
            <tr class="tm-price-row"
                data-country="<?php echo $p->country_id; ?>"
                data-type="<?php echo $p->trademark_type; ?>">

                <td><?php echo esc_html($p->country_name); ?></td>
                <td><?php echo ucfirst($p->trademark_type); ?></td>
                <td><?php echo $p->step_number; ?></td>
                <td><?php echo number_format($p->price_one_class, 2); ?></td>
                <td><?php echo number_format($p->price_add_class, 2); ?></td>

                <td>
                    <?php if ($p->step_number == 1): ?>
                        <button class="button tm-edit-price"
                            data-country="<?php echo $p->country_id; ?>"
                            data-type="<?php echo $p->trademark_type; ?>">
                            Edit
                        </button>
                        <button class="button tm-delete-price"
                            data-country="<?php echo $p->country_id; ?>"
                            data-type="<?php echo $p->trademark_type; ?>">
                            Delete
                        </button>
                    <?php endif; ?>

        
                </td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($data['max_pages'] > 1): ?>
    <div class="tm-pagination">
        <?php for ($i = 1; $i <= $data['max_pages']; $i++): ?>
            <a class="tm-page-link <?php echo $i == $data['current'] ? 'active' : ''; ?>"
               href="?page=tm-country-prices&paged=<?php echo $i; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

</div>



<!-- ==============================
    COUNTRY PRICES MODAL
================================== -->
<div id="tm-price-modal" class="tm-modal">
    <div class="tm-modal-content">
        <h2>Add Country Price</h2>

        <input type="hidden" id="tm-edit-mode" value="0">

        <div class="tm-field">
            <label>Country</label>
            <select id="tm-price-country" class="tm-input">
                <option value="">Select Country</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo $c->id; ?>">
                        <?php echo esc_html($c->country_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="tm-field">
            <label>Trademark Type</label>
            <select id="tm-price-type" class="tm-input">
                <option value="word">Word Mark</option>
                <option value="figurative">Figurative Mark</option>
                <option value="combined">Combined Mark</option>
            </select>
        </div>

        <div class="tm-step-box">
            <h3>Step 1</h3>
            <div class="tm-price-inputs">
                <div class="tm-field">
                    <label>One Class Fee</label>
                    <input type="number" id="s1_one" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="tm-field">
                    <label>Additional Class Fee</label>
                    <input type="number" id="s1_add" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>

                <div class="tm-field">
                    <label>Priority Claim Fee</label>
                    <input type="number" id="priority_claim_fee" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>

                <div class="tm-field">
                    <label>POA Late Fee</label>
                    <input type="number" id="poa_late_fee" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>
                
            </div>
        </div>

        <div class="tm-step-box">
            <h3>Step 2</h3>
            <div class="tm-price-inputs">
                <div class="tm-field">
                    <label>One Class Fee</label>
                    <input type="number" id="s2_one" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="tm-field">
                    <label>Additional Class Fee</label>
                    <input type="number" id="s2_add" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>
        </div>

        <div class="tm-step-box">
            <h3>Step 3</h3>
            <div class="tm-price-inputs">
                <div class="tm-field">
                    <label>One Class Fee</label>
                    <input type="number" id="s3_one" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="tm-field">
                    <label>Additional Class Fee</label>
                    <input type="number" id="s3_add" class="tm-small" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>
        </div>

        <div class="tm-buttons">
            <button class="button button-primary" id="tm-save-price">Save</button>
            <button class="button" id="tm-close-price">Cancel</button>
        </div>
    </div>
</div>


<script>
    const TM_PRICE_AJAX  = "<?php echo admin_url('admin-ajax.php'); ?>";
    const TM_PRICE_NONCE = "<?php echo $nonce; ?>";
</script>

<script src="<?php echo WP_TMS_NEXILUP_URL . 'assets/js/admin-prices.js'; ?>"></script>
