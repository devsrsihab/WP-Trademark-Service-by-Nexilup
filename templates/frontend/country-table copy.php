<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/** @var array $countries */
/** @var int   $paged */
/** @var int   $max_pages */
/** @var string $search */
/** @var string $single_page */

global $wpdb;
$prices_table = TM_Database::table_name('country_prices');

// Helper to build pagination links keeping search param
function tm_country_page_link($page) {
    $args = $_GET;
    $args['tm_page'] = $page;
    return esc_url( add_query_arg( $args ) );
}
?>

<div class="tm-country-table-wrap">

    <h2>Trademark Registration by Country</h2>

    <form method="get" class="tm-country-filter">
        <input type="text"
               name="tm_search"
               value="<?php echo esc_attr($search); ?>"
               placeholder="Search country…">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (empty($countries)) : ?>

        <p>No countries found.</p>

    <?php else : ?>

        <table class="widefat fixed striped tm-country-table">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Step 1 (Study)</th>
                    <th>Step 2 (Application)</th>
                    <th>Step 3 (Registration)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($countries as $c) : ?>
                <?php
                // Get Word Mark prices for each step for this country
                $step_prices = [];
                for ($s = 1; $s <= 3; $s++) {
                    $row = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT price_one_class, price_add_class, currency
                             FROM $prices_table
                             WHERE country_id = %d
                               AND trademark_type = 'word'
                               AND step_number = %d
                             LIMIT 1",
                            $c->id,
                            $s
                        )
                    );
                    $step_prices[$s] = $row;
                }

                $country_url = $single_page
                    ? add_query_arg(['country' => $c->iso_code], $single_page)
                    : '#';
                ?>
                <tr>
                    <td>
                        <div class="tm-flag flag-shadowed-<?php echo esc_attr($c->iso_code); ?>"></div>
                        <?php echo esc_html($c->country_name); ?>
                    </td>

                    <?php for ($s = 1; $s <= 3; $s++): ?>
                        <td>
                            <?php if (!empty($step_prices[$s])) : ?>
                                <?php
                                $p = $step_prices[$s];
                                echo esc_html( number_format($p->price_one_class, 2) . ' ' . $p->currency );
                                ?>
                                <br><small>Each additional class:
                                    <?php echo esc_html( number_format($p->price_add_class, 2) . ' ' . $p->currency ); ?>
                                </small>
                            <?php else : ?>
                                <em>N/A</em>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>

                    <td>
                        <?php if ($single_page): ?>
                            <a class="button button-primary" href="<?php echo esc_url($country_url); ?>">
                                View Details
                            </a>
           

                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($max_pages > 1): ?>
            <div class="tm-pagination">
                <?php for ($i = 1; $i <= $max_pages; $i++): ?>
                    <a class="tm-page-link <?php echo $i == $paged ? 'active' : ''; ?>"
                       href="<?php echo tm_country_page_link($i); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>
