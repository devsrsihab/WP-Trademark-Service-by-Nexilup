<?php
if (!defined('ABSPATH')) exit;

$paged  = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$data   = TM_Country_Prices::get_paginated_prices($paged, 10);
$prices = $data['items'];

$nonce  = wp_create_nonce('tm_country_prices_nonce');
?>

<div class="wrap tm-wrap">

    <h1 class="tm-page-title">Country Prices</h1>

    <a href="?page=tm-country-prices&action=add" class="button button-primary">
        + Add Country Price
    </a>

    <table class="wp-list-table widefat fixed striped mt-20">
        <thead>
            <tr>
                <th>Country</th>
                <th>Remark Type</th>
                <th>First Class Fee</th>
                <th>Additional Class Fee</th>
                <th>Priority Claim Fee</th>
                <th>POA Late Fee</th>
                <th>Currency</th>
                <th style="width:130px;">Actions</th>
            </tr>
        </thead>

        <tbody>

        <?php if (!empty($prices)) : ?>
            <?php foreach ($prices as $p): ?>
                <tr>

                    <td><?php echo esc_html($p->country_name); ?></td>
                    <td><?php echo esc_html($p->general_remarks ?: '-'); ?></td>

                    <td><?php echo number_format($p->first_class_fee, 2); ?></td>
                    <td><?php echo number_format($p->additional_class_fee, 2); ?></td>
                    <td><?php echo number_format($p->priority_claim_fee, 2); ?></td>
                    <td><?php echo number_format($p->poa_late_fee, 2); ?></td>
                    <td><?php echo esc_html($p->currency); ?></td>

                    <td>
                        <a href="?page=tm-country-prices&action=edit&id=<?php echo $p->id; ?>"
                           class="button">Edit</a>

                            <a href="?page=tm-country-prices&action=delete&id=<?php echo $p->id; ?>"
                                class="button tm-delete-price"
                                >
                                Delete
                           </a>


                    </td>

                </tr>
            <?php endforeach; ?>

        <?php else: ?>
            <tr>
                <td colspan="8">No pricing data available.</td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>

    <!-- Pagination -->
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
<script>
    
document.addEventListener("click", function(e) {
    let btn = e.target.closest(".tm-delete-price");
    if (!btn) return;

    if (!confirm("Are you sure you want to delete this pricing?")) {
        e.preventDefault(); // STOP delete
    }
});
</script>

</div>
