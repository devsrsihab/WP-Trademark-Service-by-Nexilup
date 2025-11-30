<?php if (!defined('ABSPATH')) exit;

$data = TM_Database::paginate(
    TM_Database::table_name('countries'),
    "WHERE 1=1",
    "country_name ASC",
    10
);

$countries = $data['items'];
$nonce = wp_create_nonce('tm_countries_nonce');
?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin-countries.css'; ?>">


<!-- ===========================
     COUNTRY LIST TABLE
=============================== -->
<div class="tm-country-container">

    <div class="tm-header">
        <h1>Manage Countries</h1>

        <div class="tm-actions">
            <button class="button button-primary" id="tm-add-country-btn">+ Add Country</button>
            <button class="button button-secondary" id="tm-bulk-add-btn">Bulk Import</button>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped tm-country-table">
        <thead>
            <tr>
                <th>Country Name</th>
                <th>ISO</th>
                <th>Madrid</th>
                <th>Opposition</th>
                <th>POA</th>
                <th>Multi-Class</th>
                <th>Evidence</th>
                <th>Protection</th>
                <th>Belt & Road</th>
                <th>Status</th>
                <th width="140">Actions</th>
            </tr>
        </thead>

        <tbody id="tm-country-list">
            <?php if ($countries): ?>
                <?php foreach ($countries as $c): ?>
                    <tr data-id="<?php echo $c->id; ?>"

                        data-name="<?php echo esc_attr($c->country_name); ?>"
                        data-iso="<?php echo esc_attr($c->iso_code); ?>"

                        data-madrid="<?php echo esc_attr($c->is_madrid_member); ?>"
                        data-registration="<?php echo esc_attr($c->registration_time); ?>"
                        data-opposition="<?php echo esc_attr($c->opposition_period); ?>"
                        data-poa="<?php echo esc_attr($c->poa_required); ?>"
                        data-multi="<?php echo esc_attr($c->multi_class_allowed); ?>"
                        data-evidence="<?php echo esc_attr($c->evidence_required); ?>"
                        data-protection="<?php echo esc_attr($c->protection_term); ?>"
                        data-general="<?php echo esc_attr($c->general_remarks); ?>"
                        data-other="<?php echo esc_attr($c->other_remarks); ?>"
                        data-beltroad="<?php echo esc_attr($c->belt_and_road); ?>"
                        data-status="<?php echo esc_attr($c->status); ?>">

                        <td><?php echo esc_html($c->country_name); ?></td>
                        <td><?php echo esc_html($c->iso_code); ?></td>
                        <td><?php echo $c->is_madrid_member ? "Yes" : "No"; ?></td>
                        <td><?php echo esc_html($c->opposition_period ?: '—'); ?></td>
                        <td><?php echo esc_html($c->poa_required ?: '—'); ?></td>
                        <td><?php echo esc_html($c->multi_class_allowed ?: '—'); ?></td>
                        <td><?php echo esc_html($c->evidence_required ?: '—'); ?></td>
                        <td><?php echo esc_html($c->protection_term ?: '—'); ?></td>
                        <td><?php echo $c->belt_and_road ? "Yes" : "No"; ?></td>

                        <td>
                            <?php if ($c->status == 1): ?>
                                <span class="tm-status-active">Active</span>
                            <?php else: ?>
                                <span class="tm-status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <button class="button tm-edit">Edit</button>
                            <button class="button tm-delete" data-id="<?php echo $c->id; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="11">No countries found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($data['max_pages'] > 1): ?>
        <div class="tm-pagination">
            <?php for ($i = 1; $i <= $data['max_pages']; $i++): ?>
                <a class="tm-page-link <?php echo $i == $data['current'] ? 'active' : ''; ?>"
                    href="?page=tm-countries&paged=<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>


<!-- ===========================
     ADD COUNTRY MODAL
=============================== -->
<div id="tm-add-modal" class="tm-modal">
    <div class="tm-modal-content">

        <h2>Add Country</h2>

        <!-- Country Name -->
        <div class="tm-field">
            <label>Country Name</label>
            <input type="text" id="tm-country-input" placeholder="Example: Japan">
        </div>

        <!-- ISO Code -->
        <div class="tm-field">
            <label>ISO Code</label>
            <input type="text" id="tm-iso-input" placeholder="JP">
        </div>

        <!-- Madrid Union Member -->
        <div class="tm-field">
            <label>Madrid Union Member?</label>
            <select id="tm-is-madrid">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <!-- POA Requirement -->
        <div class="tm-field">
            <label>POA Requirement</label>
            <select id="tm-poa-required">
                <option value="">Select requirement</option>
                <option value="Not Required">Not Required</option>
                <option value="Required">Required</option>
                <option value="Optional">Optional</option>
                <option value="Required if Claiming Priority">Required if Claiming Priority</option>
            </select>
        </div>

        <!-- Multi-class Allowed -->
        <div class="tm-field">
            <label>Multi-class Filing</label>
            <select id="tm-multiclass">
                <option value="">Select</option>
                <option value="Accepted">Accepted</option>
                <option value="Not Accepted">Not Accepted</option>
                <option value="Partially Accepted">Partially Accepted</option>
            </select>
        </div>

        <!-- Evidence Requirement -->
        <div class="tm-field">
            <label>Evidence of Use Required?</label>
            <select id="tm-evidence">
                <option value="">Select</option>
                <option value="None">None</option>
                <option value="Required">Required</option>
                <option value="Sometimes Required">Sometimes Required</option>
            </select>
        </div>

        <!-- Estimated Registration Time -->
        <div class="tm-field">
            <label>Estimated Registration Time</label>
            <input type="text" id="tm-registration-time" placeholder="Example: 12–14 months">
        </div>

        <!-- Opposition Period -->
        <div class="tm-field">
            <label>Opposition Period</label>
            <input type="text" id="tm-opposition" placeholder="Example: 2 months">
        </div>

        <!-- Protection Term -->
        <div class="tm-field">
            <label>Protection Term</label>
            <input type="text" id="tm-protection-term" placeholder="Example: 10 years">
        </div>



        <!-- Belt and Road -->
        <div class="tm-field">
            <label>Belt & Road Member?</label>
            <select id="tm-belt-road">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <!-- Other Remarks -->
        <div class="tm-field">
            <label>Other Remarks</label>
            <input id="tm-other-remarks"
                placeholder="Optional: Additional notes such as special rules or exceptions"></input>
        </div>

        <!-- Buttons -->
        <div class="tm-buttons">
            <button class="button button-primary" id="tm-save-country">Save</button>
            <button class="button" id="tm-close-add">Cancel</button>
        </div>

    </div>
</div>



<!-- ===========================
     EDIT COUNTRY MODAL
=============================== -->
<div id="tm-edit-modal" class="tm-modal">
    <div class="tm-modal-content">

        <h2>Edit Country</h2>
        <input type="hidden" id="tm-edit-id">

        <div class="tm-field">
            <label>Country Name</label>
            <input type="text" id="tm-edit-name" placeholder="Example: Japan">
        </div>

        <div class="tm-field">
            <label>ISO Code</label>
            <input type="text" id="tm-edit-iso" placeholder="JP">
        </div>

        <div class="tm-field">
            <label>Madrid Union Member?</label>
            <select id="tm-edit-is-madrid">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <div class="tm-field">
            <label>PoA Requirement</label>
            <select id="tm-edit-poa-required">
                <option value="">Select Requirement</option>
                <option value="Not Required">Not Required</option>
                <option value="Required">Required</option>
                <option value="Optional">Optional</option>
                <option value="Required if Claiming Priority">Required if Claiming Priority</option>
            </select>
        </div>

        <div class="tm-field">
            <label>Multi-class Filing</label>
            <select id="tm-edit-multiclass">
                <option value="">Select</option>
                <option value="Accepted">Accepted</option>
                <option value="Not Accepted">Not Accepted</option>
                <option value="Partially Accepted">Partially Accepted</option>
            </select>
        </div>

        <div class="tm-field">
            <label>Evidence of Use Required?</label>
            <select id="tm-edit-evidence">
                <option value="">Select</option>
                <option value="None">None</option>
                <option value="Required">Required</option>
                <option value="Sometimes Required">Sometimes Required</option>
            </select>
        </div>

        <div class="tm-field">
            <label>Estimated Registration Time</label>
            <input type="text" id="tm-edit-registration-time" placeholder="Example: 8–12 months">
        </div>

        <div class="tm-field">
            <label>Opposition Period</label>
            <input type="text" id="tm-edit-opposition" placeholder="Example: 30 Days">
        </div>

        <div class="tm-field">
            <label>Protection Term</label>
            <input type="text" id="tm-edit-protection-term" placeholder="Example: 10 years">
        </div>

        <div class="tm-field">
            <label>Pricing Remark (Fee Rule)</label>
            <select id="tm-edit-remark-type">
                <option value="">Select a pricing rule</option>
                <option value="filing_20_goods">Filing Fee — includes up to 20 goods</option>
                <option value="filing_basic">Filing Fee — basic per class</option>
                <option value="registration_5_years">Registration Fee — valid for 5 years</option>
                <option value="registration_10_years">Registration Fee — valid for 10 years</option>
                <option value="registration_after_acceptance">
                    Registration Fee — payable only after acceptance
                </option>
                <option value="filing_only">Only Filing Fee (No Registration Fee)</option>
            </select>
        </div>

        <div class="tm-field">
            <label>Belt & Road Member?</label>
            <select id="tm-edit-belt-road">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <div class="tm-field">
            <label>Other Remarks</label>
            <textarea id="tm-edit-other-remarks" placeholder="Extra remarks if available"></textarea>
        </div>

        <div class="tm-field">
            <label>Status</label>
            <select id="tm-edit-status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="tm-buttons">
            <button class="button button-primary" id="tm-update-country">Update</button>
            <button class="button" id="tm-close-edit">Cancel</button>
        </div>

    </div>
</div>



<!-- ===========================
     BULK IMPORT MODAL
=============================== -->
<div id="tm-bulk-modal" class="tm-modal">
    <div class="tm-modal-content">

        <h2>Bulk Import Countries</h2>

        <p class="tm-small-note">
            JSON format: <code>{"name":"Japan","iso":"JP"}</code><br>
            * Additional fields will be auto-set to NULL *
        </p>

        <textarea id="tm-bulk-input" placeholder='{"name":"India","iso":"IN"}, {"name":"Italy","iso":"IT"}'></textarea>

        <div class="tm-buttons">
            <button class="button button-primary" id="tm-bulk-save">Import</button>
            <button class="button" id="tm-close-bulk">Cancel</button>
        </div>

    </div>
</div>

<script>
    const tmCountriesAjax = "<?php echo admin_url('admin-ajax.php'); ?>";
    const tmCountriesNonce = "<?php echo $nonce; ?>";
</script>

<script src="<?php echo WP_TMS_NEXILUP_PLUGIN_URL . 'assets/js/admin-countries.js'; ?>"></script>
