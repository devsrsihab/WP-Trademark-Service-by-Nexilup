<?php
global $wpdb;
$prices_table = TM_Database::table_name('country_prices');
$symbol = '$';
?>

<table class="tm-pricing-table-pro">
    <thead>
        <tr>
            <th rowspan="2" class="tm-col-country">Country</th>
            <th colspan="2" class="tm-step-head">Step 1</th>
            <th colspan="2" class="tm-step-head">Step 2</th>
            <th colspan="2" class="tm-step-head">Step 3</th>
            <th colspan="2" class="tm-step-head">Total</th>
        </tr>

        <tr class="tm-subhead-row">
            <th>One Class</th><th>Add. Class</th>
            <th>One Class</th><th>Add. Class</th>
            <th>One Class</th><th>Add. Class</th>
            <th>One Class</th><th>Add. Class</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($countries as $c):

        // Fetch prices for selected type
        $steps = [];
        for ($s = 1; $s <= 3; $s++) {
            $steps[$s] = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT price_one_class, price_add_class
                     FROM $prices_table
                     WHERE country_id = %d AND trademark_type = %s AND step_number = %d
                     LIMIT 1",
                    $c->id,
                    $selected_type,
                    $s
                )
            );
        }

        $tot_one = array_sum(array_column($steps, 'price_one_class'));
        $tot_add = array_sum(array_column($steps, 'price_add_class'));
    ?>

        <tr>
            <td class="tm-country-cell">
                <span class="flag-shadowed flag-shadowed-<?php echo esc_attr($c->iso_code); ?>"></span>
                <strong><?php echo esc_html($c->country_name); ?></strong>
            </td>

            <?php for ($s = 1; $s <= 3; $s++): ?>
                <td><?php echo $steps[$s] ? $symbol . number_format($steps[$s]->price_one_class, 2) : "—"; ?></td>
                <td><?php echo $steps[$s] ? $symbol . number_format($steps[$s]->price_add_class, 2) : "—"; ?></td>
            <?php endfor; ?>

            <td><strong><?php echo $symbol . number_format($tot_one, 2); ?></strong></td>
            <td><strong><?php echo $symbol . number_format($tot_add, 2); ?></strong></td>
        </tr>

    <?php endforeach; ?>

    </tbody>
</table>
