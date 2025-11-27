<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$prices_table = TM_Database::table_name('country_prices');

/**
 * Helper: Build pagination URL
 */
function tm_country_page_link($page) {
    $args = $_GET;
    $args['tm_page'] = $page;
    return esc_url(add_query_arg($args));
}

wp_enqueue_style('tm-country-table-pro');

/** Determine selected type (normal page load OR ajax load) **/
$selected_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'word';
?>

<div class="tm-country-table-pro-wrap">


    <!-- ================= FILTER BAR ================== -->
<form id="tm-filter-form" class="tm-filter-bar">

    <div class="tm-filter-left">
        <label class="tm-filter-label">Country:</label>
        <select id="tm-country" name="country" class="tm-select">
            <option value="">All Countries</option>
            <?php foreach ($countries as $c): ?>
                <option value="<?php echo esc_attr($c->iso_code); ?>">
                    <?php echo esc_html($c->country_name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="tm-btn-primary">Search</button>
    </div>

    <div class="tm-filter-right">
        <label class="tm-filter-label">Trademark Type:</label>

        <label class="tm-radio"><input type="radio" name="type" value="word" checked> Word Mark</label>
        <label class="tm-radio"><input type="radio" name="type" value="figurative"> Figurative Mark</label>
        <label class="tm-radio"><input type="radio" name="type" value="combined"> Combined Mark</label>
    </div>

</form>



    <!-- ================= TABLE ================== -->
    <div class="tm-scroll-x">
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

            // Fetch pricing for selected mark type
            $steps = [];
            for ($s = 1; $s <= 3; $s++) {
                $steps[$s] = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT price_one_class, price_add_class
                         FROM $prices_table
                         WHERE country_id = %d AND trademark_type = %s AND step_number = %d LIMIT 1",
                        $c->id,
                        $selected_type,
                        $s
                    )
                );
            }

            $symbol = '$';

            // Totals
            $tot_one = 0;
            $tot_add = 0;

            foreach ($steps as $st) {
                if ($st) {
                    $tot_one += (float)$st->price_one_class;
                    $tot_add += (float)$st->price_add_class;
                }
            }

            // Single link
            $country_url = $single_page 
                ? esc_url(add_query_arg(['country' => $c->iso_code], $single_page)) 
                : '#';
        ?>

            <tr>

                <!-- Country -->
                <td class="tm-country-cell tm-country-flag-wraper">
                    <span class="flag-shadowed flag-shadowed-<?php echo esc_attr($c->iso_code); ?>"></span>

                    <a href="<?php echo $country_url; ?>" class="tm-country-link">
                        <strong class="tm-country-name"><?php echo esc_html($c->country_name); ?></strong>
                    </a>
                </td>

                <!-- Prices by Step -->
                <?php for ($s = 1; $s <= 3; $s++): ?>
                    <td><?php echo $steps[$s] ? $symbol . number_format($steps[$s]->price_one_class, 2) : '—'; ?></td>
                    <td><?php echo $steps[$s] ? $symbol . number_format($steps[$s]->price_add_class, 2) : '—'; ?></td>
                <?php endfor; ?>

                <!-- Totals -->
                <td><strong><?php echo $symbol . number_format($tot_one, 2); ?></strong></td>
                <td><strong><?php echo $symbol . number_format($tot_add, 2); ?></strong></td>

            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>
    </div>


    <!-- ================= PAGINATION ================== -->
    <?php if ($max_pages > 1): ?>
        <div class="tm-ct-pagination">
            <?php for ($i = 1; $i <= $max_pages; $i++): ?>
                <a href="<?php echo tm_country_page_link($i); ?>"
                   class="tm-page <?php echo ($i == $paged ? 'active' : ''); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>
