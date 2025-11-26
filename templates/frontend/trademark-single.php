<?php
if (!defined('ABSPATH')) exit;
?>

<div class="tm-single">
    <h2>Trademark Details</h2>

    <div class="tm-card">
        <p><strong>Country:</strong> <?php echo esc_html($t->country_id); ?></p>
        <p><strong>Mark Text:</strong> <?php echo esc_html($t->mark_text); ?></p>
        <p><strong>Type:</strong> <?php echo ucfirst($t->trademark_type); ?></p>
        <p><strong>Classes:</strong> <?php echo esc_html($t->class_count); ?></p>
        <p><strong>Status:</strong> <span class="tm-badge tm-status-<?php echo $t->status; ?>"><?php echo ucfirst($t->status); ?></span></p>
        <p><strong>Goods & Services:</strong><br><?php echo nl2br(esc_html($t->goods_services)); ?></p>

        <p><strong>Price:</strong> <?php echo $t->final_price . ' ' . $t->currency; ?></p>

        <p><strong>Order ID:</strong> #<?php echo $t->woo_order_id; ?></p>

        <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($t->created_at)); ?></p>
    </div>

    <br>
    <a href="?page=my-trademarks" class="tm-btn-primary">← Back to Dashboard</a>
</div>
