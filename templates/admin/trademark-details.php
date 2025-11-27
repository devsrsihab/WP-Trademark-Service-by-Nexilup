<?php if (!defined('ABSPATH')) exit; ?>

<style>
/* Inline Modal Design */

.tm-admin-modal-body {
    padding: 10px 20px;
}

.tm-admin-details-card {
    background: #fff;
    padding: 18px 22px;
    border: 1px solid #e2e5e9;
    border-radius: 8px;
    margin-bottom: 25px;
}

.tm-admin-subtitle {
    margin: 0 0 15px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1e1e1e;
}

.tm-admin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 28px;
}

.tm-grid-item label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #555d66;
    margin-bottom: 5px;
}

.tm-grid-item span,
.tm-grid-item p {
    font-size: 15px;
    color: #1d2327;
    margin: 0;
    line-height: 1.4;
}

.tm-full {
    grid-column: 1 / -1;
}

.tm-admin-logo-image {
    max-width: 160px;
    border-radius: 6px;
    border: 1px solid #dcdcdc;
    margin-top: 10px;
}

.tm-doc-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e2e5e9;
}

/* Responsive */
@media (max-width: 700px) {
    .tm-admin-grid {
        grid-template-columns: 1fr;
    }

    .tm-admin-details-card {
        padding: 15px;
    }

    .tm-admin-modal-body {
        padding: 10px;
    }
}
</style>

<div class="tm-admin-modal-body">

    <div class="tm-admin-details-card">

        <h3 class="tm-admin-subtitle">Trademark #<?php echo $t->id; ?></h3>

        <div class="tm-admin-grid">

            <div class="tm-grid-item">
                <label>Country</label>
                <span><?php echo esc_html($t->country_name); ?></span>
            </div>

            <div class="tm-grid-item">
                <label>Type</label>
                <span><?php echo ucfirst($t->trademark_type); ?></span>
            </div>

            <div class="tm-grid-item">
                <label>Classes</label>
                <span><?php echo intval($t->class_count); ?></span>
            </div>

            <div class="tm-grid-item">
                <label>Total Price</label>
                <span><?php echo esc_html($t->final_price . ' ' . $t->currency); ?></span>
            </div>

            <div class="tm-grid-item tm-full">
                <label>Goods & Services</label>
                <p><?php echo nl2br(esc_html($t->goods_services)); ?></p>
            </div>

            <?php if (!empty($t->logo_url)): ?>
                <div class="tm-grid-item tm-full">
                    <label>Logo</label>
                    <img src="<?php echo esc_url($t->logo_url); ?>" class="tm-admin-logo-image">
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="tm-admin-details-card">
        <h3 class="tm-admin-subtitle">Documents</h3>

        <div id="tm-admin-doc-list" class="tm-doc-container">
            Loading documents…
        </div>
    </div>

</div>
