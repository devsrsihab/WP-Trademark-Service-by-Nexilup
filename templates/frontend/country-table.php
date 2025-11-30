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
<?php
global $wpdb;

$prices_table = TM_Database::table_name('country_prices');

// SORT COUNTRIES ALPHABETICALLY
usort($countries, function($a, $b) {
    return strcmp($a->country_name, $b->country_name);
});

// Determine selected type
$selected_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'word';

function tm_resolve_prices($p, $selected_type)
{
    $first = floatval($p->first_class_fee);
    $add   = floatval($p->additional_class_fee);
    $remark = $p->general_remarks;

    $has_filing = (strpos($remark, 'filing_') === 0);
    $has_reg    = (strpos($remark, 'registration_') === 0);

    /** ================================
     *  CASE 1 — Filing exists
     *  (Filing = normal logic)
     * ================================= */
    if ($has_filing) {
        $s1_one = $first;
        $s1_add = $add;

        $s2_one = $add;
        $s2_add = $add;

        $s3_one = $add;
        $s3_add = $add;
    }

    /** ==========================================
     *  CASE 2 — Registration exists BUT no filing
     *  (Use first_class_fee ONLY for Step 1 one class)
     * =========================================== */
    elseif ($has_reg) {
        $s1_one = $first;   // <-- special rule
        $s1_add = $add;

        $s2_one = $add;
        $s2_add = $add;

        $s3_one = $add;
        $s3_add = $add;
    }

    /** ======================================
     *  CASE 3 — No remark
     * ======================================= */
    else {
        $s1_one = $first;
        $s1_add = $add;

        $s2_one = $add;
        $s2_add = $add;

        $s3_one = $add;
        $s3_add = $add;
    }

    /** ======================
     *  Combined = Double price
     * ====================== */
    if ($selected_type === 'combined') {
        $s1_one *= 2;  $s1_add *= 2;
        $s2_one *= 2;  $s2_add *= 2;
        $s3_one *= 2;  $s3_add *= 2;
    }

    return compact('s1_one','s1_add','s2_one','s2_add','s3_one','s3_add');
}

?>

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

    <?php foreach ($countries as $c): ?>

        <?php

        
        // Fetch ALL prices for this country
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $prices_table WHERE country_id = %d", $c->id)
        );

        if (!$rows) continue;

        // Default = first record
        $p = $rows[0];

        // --- PRIORITY GROUP 1: Filing ---
        $filing_basic = null;
        $filing_other = null;

        foreach ($rows as $r) {

            if (strpos($r->general_remarks, 'filing_basic') === 0) {
                $filing_basic = $r;
                break;
            }

            if (strpos($r->general_remarks, 'filing_') === 0) {
                $filing_other = $r; // ex: filing_20_goods
            }
        }

        // If any filing exists → choose the correct one
        if ($filing_basic) {
            $p = $filing_basic;
        } elseif ($filing_other) {
            $p = $filing_other;
        } else {

            // --- PRIORITY GROUP 2: Registration ---
            $reg_basic = null;
            $reg_mid = null;
            $reg_other = null;

            foreach ($rows as $r) {

                if (strpos($r->general_remarks, 'registration_basic') === 0) {
                    $reg_basic = $r;
                    break;
                }

                if (strpos($r->general_remarks, 'registration_5_years') === 0 ||
                    strpos($r->general_remarks, 'registration_10_years') === 0) {
                    $reg_mid = $r;
                }

                if (strpos($r->general_remarks, 'registration_') === 0) {
                    $reg_other = $r;
                }
            }

            if ($reg_basic) {
                $p = $reg_basic;
            } elseif ($reg_mid) {
                $p = $reg_mid;
            } elseif ($reg_other) {
                $p = $reg_other;
            }
        }



        if (!$p) continue;

        // Apply pricing rules
        $fees = tm_resolve_prices($p, $selected_type);

        // Totals
        $tot_one = $fees['s1_one'] + $fees['s2_one'] + $fees['s3_one'];
        $tot_add = $fees['s1_add'] + $fees['s2_add'] + $fees['s3_add'];

        $country_url = $single_page
            ? esc_url(add_query_arg(['country' => $c->iso_code], $single_page))
            : '#';
        ?>

        <tr>

            <!-- COUNTRY CELL -->
            <td class="tm-country-cell tm-country-flag-wraper">
                <span class="flag-shadowed flag-shadowed-<?php echo esc_attr($c->iso_code); ?>"></span>
                <a href="<?php echo $country_url; ?>" class="tm-country-link">
                    <strong style="text-transform: capitalize" class="tm-country-name"><?php echo esc_html($c->country_name); ?></strong>
                </a>
            </td>

            <!-- STEP 1 -->
            <td><?php echo number_format($fees['s1_one'], 2); ?></td>
            <td><?php echo number_format($fees['s1_add'], 2); ?></td>

            <!-- STEP 2 -->
            <td><?php echo number_format($fees['s2_one'], 2); ?></td>
            <td><?php echo number_format($fees['s2_add'], 2); ?></td>

            <!-- STEP 3 -->
            <td><?php echo number_format($fees['s3_one'], 2); ?></td>
            <td><?php echo number_format($fees['s3_add'], 2); ?></td>

            <!-- TOTAL -->
            <td><strong><?php echo number_format($tot_one, 2); ?></strong></td>
            <td><strong><?php echo number_format($tot_add, 2); ?></strong></td>

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
