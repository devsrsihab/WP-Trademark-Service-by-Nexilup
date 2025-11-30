<?php
$countries = TM_Database::get_countries(['active_only' => false]);
$nonce = wp_create_nonce('tm_country_prices_nonce');
$old   = TM_Country_Prices::$old_input;
?>

<div class="wrap">
    <h1>Add Country Price</h1>

    <?php if (TM_Country_Prices::$error_message): ?>
        <div class="notice notice-error">
            <p><?php echo TM_Country_Prices::$error_message; ?></p>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="tms_action" value="add_price">
        <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">
        <input type="hidden" name="mode" value="0">

        <table class="form-table">

            <!-- COUNTRY -->
            <tr>
                <th>Country</th>
                <td>
                    <select name="country">
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo $c->id; ?>"
                                <?php echo (!empty($old['country']) && $old['country'] == $c->id) ? 'selected' : ''; ?>>
                                <?php echo $c->country_name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- PRICING REMARK -->
            <tr>
                <th>Pricing Remark</th>
                <td>
                    <select name="general_remarks">
                        <option value="">Select</option>

                       <option value="filing_basic"
                            <?php echo (isset($old['general_remarks']) && $old['general_remarks'] === 'filing_basic') ? 'selected' : ''; ?>>
                            Filing Fee — basic per class
                        </option>

                        <option value="filing_20_goods"
                            <?php echo (isset($old['general_remarks']) && $old['general_remarks'] === 'filing_20_goods') ? 'selected' : ''; ?>>
                            Filing Fee — includes up to 20 goods
                        </option>

                

                        <option value="registration_basic"
                            <?php echo (isset($old['general_remarks']) && $old['general_remarks'] === 'registration_basic') ? 'selected' : ''; ?>>
                            Registration Fee — basic per class
                        </option>
                        
                        <option value="registration_5_years"
                            <?php echo (isset($old['general_remarks']) && $old['general_remarks'] === 'registration_5_years') ? 'selected' : ''; ?>>
                            Registration Fee — valid for 5 years
                        </option>

                        <option value="registration_10_years"
                            <?php echo (isset($old['general_remarks']) && $old['general_remarks'] === 'registration_10_years') ? 'selected' : ''; ?>>
                            Registration Fee — valid for 10 years
                        </option>
                    </select>
                </td>
            </tr>

            <!-- FEES -->
            <tr>
                <th>First Class Fee</th>
                <td><input type="number" step="0.01" name="first_class_fee"
                           value="<?php echo isset($old['first_class_fee']) ? esc_attr($old['first_class_fee']) : ''; ?>"></td>
            </tr>

            <tr>
                <th>Additional Class Fee</th>
                <td><input type="number" step="0.01" name="additional_class_fee"
                           value="<?php echo isset($old['additional_class_fee']) ? esc_attr($old['additional_class_fee']) : ''; ?>"></td>
            </tr>

            <tr>
                <th>Priority Claim Fee</th>
                <td><input type="number" step="0.01" name="priority_claim_fee"
                           value="<?php echo isset($old['priority_claim_fee']) ? esc_attr($old['priority_claim_fee']) : ''; ?>"></td>
            </tr>

            <tr>
                <th>POA Late Fee</th>
                <td><input type="number" step="0.01" name="poa_late_fee"
                           value="<?php echo isset($old['poa_late_fee']) ? esc_attr($old['poa_late_fee']) : ''; ?>"></td>
            </tr>

        </table>

        <p>
            <button class="button button-primary">Save</button>
            <a href="?page=tm-country-prices" class="button">Cancel</a>
        </p>
    </form>
</div>
