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

<div class="tm-country-container">

    <div class="tm-header">
        <h1>Manage Countries</h1>

        <div class="tm-actions">
            <button class="button button-primary" id="tm-add-country-btn">
                + Add Country
            </button>

            <button class="button button-secondary" id="tm-bulk-add-btn">
                Bulk Import
            </button>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped tm-country-table">
        <thead>
            <tr>
                <!-- <th>Flag</th> -->
                <th>Country Name</th>
                <th>ISO Code</th>
                <th>Status</th>
                <th width="150">Actions</th>
            </tr>
        </thead>

        <tbody id="tm-country-list">
            <?php if ($countries) : ?>
                <?php foreach ($countries as $c) : ?>
                    <tr data-id="<?php echo $c->id; ?>">

                        <!-- <td>
                            <div class="tm-flag flag-shadowed flag-shadowed-<?php //echo esc_attr($c->iso_code); ?>"></div>
                        </td> -->

                        <td><?php echo esc_html($c->country_name); ?></td>

                        <td><?php echo esc_html($c->iso_code); ?></td>

                        <td>
                            <?php if ($c->status == 1): ?>
                                <span class="tm-status-active">Active</span>
                            <?php else: ?>
                                <span class="tm-status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <button class="button tm-edit" 
                                    data-id="<?php echo $c->id; ?>"
                                    data-name="<?php echo esc_attr($c->country_name); ?>"
                                    data-iso="<?php echo esc_attr($c->iso_code); ?>"
                                    data-status="<?php echo esc_attr($c->status); ?>">
                                Edit
                            </button>

                            <button class="button tm-delete" data-id="<?php echo $c->id; ?>">
                                Delete
                            </button>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="5">No countries found.</td></tr>
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

        <div class="tm-field">
            <label>Country</label>
            <select id="tm-country-select">
                <option value="">Select Country</option>
                <?php include WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/country-list.php'; ?>
            </select>
        </div>

        <div class="tm-field">
            <label>ISO Code</label>
            <input type="text" id="tm-iso-input" readonly>
        </div>

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
            <label>Country</label>
            <input type="text" id="tm-edit-name">
        </div>

        <div class="tm-field">
            <label>ISO Code</label>
            <input type="text" id="tm-edit-iso">
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

        <p class="tm-small-note">Format:<br>
            <code>{"name":"Albania","iso":"AL"}, {"name":"Brazil","iso":"BR"}</code>
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
