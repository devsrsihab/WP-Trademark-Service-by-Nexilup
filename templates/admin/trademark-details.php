<?php
if (!defined('ABSPATH')) exit;

/** @var object $t Loaded trademark row */
?>

<div class="tm-admin-trademark-modal">

    <h2>Trademark #<?php echo $t->id; ?></h2>

    <div class="tm-section">
        <h3>Trademark Info</h3>

        <p><strong>Country:</strong> <?php echo esc_html($t->country_name); ?></p>
        <p><strong>Type:</strong> <?php echo ucfirst($t->trademark_type); ?></p>
        <p><strong>Classes:</strong> <?php echo intval($t->class_count); ?></p>
        <p><strong>Total Price:</strong> <?php echo esc_html($t->final_price . ' ' . $t->currency); ?></p>
        <p><strong>Goods & Services:</strong><br><?php echo nl2br(esc_html($t->goods_services)); ?></p>

        <?php if (!empty($t->logo_url)) : ?>
            <p><strong>Logo:</strong></p>
            <img src="<?php echo esc_url($t->logo_url); ?>"
                 alt="Trademark Logo"
                 style="max-width:150px;border:1px solid #ddd;border-radius:4px;">
        <?php endif; ?>
    </div>


    <!-- STATUS SECTION -->
    <div class="tm-section">
        <h3>Status</h3>

        <div class="tm-field">
            <label>Status</label>
            <select id="tm-admin-status" data-id="<?php echo $t->id; ?>">
                <option value="pending_payment"   <?php selected($t->status, 'pending_payment'); ?>>Pending Payment</option>
                <option value="paid"              <?php selected($t->status, 'paid'); ?>>Paid</option>
                <option value="in_process"        <?php selected($t->status, 'in_process'); ?>>In Process</option>
                <option value="completed"         <?php selected($t->status, 'completed'); ?>>Completed</option>
                <option value="cancelled"         <?php selected($t->status, 'cancelled'); ?>>Cancelled</option>
            </select>

            <button class="button button-primary" id="tm-admin-save-status">
                Update Status
            </button>
        </div>

        <div id="tm-admin-status-msg"></div>
    </div>



    <!-- DOCUMENTS SECTION -->
    <div class="tm-section">
        <h3>Documents</h3>

        <div id="tm-admin-doc-list">
            <p>Loading documents…</p>
        </div>

        <hr>

        <h4>Upload a New Document</h4>

        <input type="file" id="tm-doc-file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">

        <select id="tm-doc-type">
            <option value="certificate">Registration Certificate</option>
            <option value="poa">Power of Attorney</option>
            <option value="exam_report">Examination Report</option>
            <option value="other">Other Document</option>
        </select>

        <button class="button button-primary" id="tm-doc-upload-btn">
            Upload Document
        </button>

        <div id="tm-doc-msg"></div>
    </div>

</div>
