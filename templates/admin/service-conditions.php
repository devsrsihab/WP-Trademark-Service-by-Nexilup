<?php
if (!defined('ABSPATH')) exit;

$paged     = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$data      = TM_Service_Conditions::get_paginated($paged, 20);
$rows      = $data['items'];
$countries = TM_Database::get_countries(['active_only' => false]);
$nonce     = wp_create_nonce('tm_service_conditions_nonce');
?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin.css'; ?>">

<div class="wrap tm-wrap">

    <h1 class="tm-page-title">Service Conditions</h1>

    <button class="button button-primary" id="tm-add-condition-btn">+ Add Condition</button>

    <table class="wp-list-table widefat fixed striped mt-20">
        <thead>
        <tr>
            <th>Country</th>
            <th>Preview</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php if ($rows): foreach ($rows as $row): ?>
            <tr data-id="<?php echo $row->id; ?>">

                <td><?php echo esc_html($row->country_name); ?></td>

                <td>
                    <?php
                    $preview = wp_strip_all_tags($row->content);
                    echo esc_html(strlen($preview) > 80 ? substr($preview, 0, 77) . '...' : $preview);
                    ?>
                </td>

                <td>
                    <button class="button tm-edit-condition" data-id="<?php echo $row->id; ?>">Edit</button>
                    <button class="button tm-delete-condition" data-id="<?php echo $row->id; ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="3">No service conditions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- MODAL -->
<div id="tm-condition-modal" class="tm-modal" style="display:none;">
    <div class="tm-modal-inner">

        <h2 id="tm-condition-modal-title">Add Service Condition</h2>

        <input type="hidden" id="tm-condition-id" value="0">

        <p>
            <label><strong>Country</strong></label><br>
            <select id="tm-condition-country" class="tm-input">
                <option value="">Select Country</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo $c->id; ?>">
                        <?php echo esc_html($c->country_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label><strong>Content</strong></label><br>

            <?php
            wp_editor(
                '',
                'tm_condition_editor',
                [
                    'textarea_name' => 'tm_condition_editor',
                    'textarea_rows' => 8,
                    'media_buttons' => true,
                    'teeny'         => false
                ]
            );
            ?>
        </p>

        <p>
            <button class="button button-primary" id="tm-save-condition">Save</button>
            <button class="button tm-modal-close">Cancel</button>
        </p>

    </div>
</div>

<script>
const TM_COND_AJAX  = "<?php echo admin_url('admin-ajax.php'); ?>";
const TM_COND_NONCE = "<?php echo $nonce; ?>";
</script>

<script src="<?php echo WP_TMS_NEXILUP_URL . 'assets/js/admin-conditions.js'; ?>"></script>
