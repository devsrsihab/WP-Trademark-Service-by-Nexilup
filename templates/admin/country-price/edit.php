<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = TM_Country_Prices::table();

$id = intval($_GET['id']);
$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id));

$countries = TM_Database::get_countries(['active_only' => false]);
$nonce = wp_create_nonce('tm_country_prices_nonce');

/* Restore old input if validation failed */
$old = TM_Country_Prices::$old_input;
$err = TM_Country_Prices::$error_message;

// Pre-fill values (old input OR DB row)
$general_remarks    = $old ? $old['general_remarks']    : $row->general_remarks;
$first_class_fee    = $old ? $old['first_class_fee']    : $row->first_class_fee;
$additional_class_fee = $old ? $old['additional_class_fee'] : $row->additional_class_fee;
$priority_claim_fee = $old ? $old['priority_claim_fee'] : $row->priority_claim_fee;
$poa_late_fee       = $old ? $old['poa_late_fee']       : $row->poa_late_fee;
?>

<div class="wrap">
    <h1>Edit Country Price</h1>

    <?php if ($err): ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php echo esc_html($err); ?></strong></p>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="tms_action" value="update_price">
        <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="country" value="<?php echo $row->country_id; ?>">

        <table class="form-table">

            <!-- Country (locked) -->
            <tr>
                <th>Country</th>
                <td>
                    <select disabled>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo $c->id; ?>" <?php selected($c->id, $row->country_id); ?>>
                                <?php echo esc_html($c->country_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Pricing Remark -->
            <tr>
                <th>Pricing Remark</th>
                <td>
                    <select name="general_remarks">
                        <option value="filing_20_goods" <?php selected("filing_20_goods", $general_remarks); ?>>Filing Fee — includes up to 20 goods</option>
                        <option value="filing_basic" <?php selected("filing_basic", $general_remarks); ?>>Filing Fee — basic per class</option>
                        <option value="registration_5_years" <?php selected("registration_5_years", $general_remarks); ?>>Registration Fee — valid for 5 years</option>
                        <option value="registration_10_years" <?php selected("registration_10_years", $general_remarks); ?>>Registration Fee — valid for 10 years</option>
                    </select>
                </td>
            </tr>

            <!-- Fees -->
            <tr>
                <th>First Class Fee</th>
                <td>
                    <input type="number" step="0.01" name="first_class_fee" value="<?php echo esc_attr($first_class_fee); ?>">
                </td>
            </tr>

            <tr>
                <th>Additional Class Fee</th>
                <td>
                    <input type="number" step="0.01" name="additional_class_fee" value="<?php echo esc_attr($additional_class_fee); ?>">
                </td>
            </tr>

            <tr>
                <th>Priority Claim Fee</th>
                <td>
                    <input type="number" step="0.01" name="priority_claim_fee" value="<?php echo esc_attr($priority_claim_fee); ?>">
                </td>
            </tr>

            <tr>
                <th>POA Late Fee</th>
                <td>
                    <input type="number" step="0.01" name="poa_late_fee" value="<?php echo esc_attr($poa_late_fee); ?>">
                </td>
            </tr>

        </table>

        <p>
            <button class="button button-primary">Update</button>
            <a href="?page=tm-country-prices" class="button">Cancel</a>
        </p>
    </form>
</div>
