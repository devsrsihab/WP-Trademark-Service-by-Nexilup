<?php
if (!defined('ABSPATH')) exit;

$trademarks = TM_Trademarks::admin_get_all();
$nonce = wp_create_nonce('tm_admin_trademark_nonce');
?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin-trademarks.css'; ?>">

<div class="wrap tm-wrap">
    <h1 class="tm-page-title">Trademark Orders</h1>

    <table class="wp-list-table widefat fixed striped mt-20" id="tm-admin-trademark-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Country</th>
                <th>Mark</th>
                <th>Classes</th>
                <th>Status</th>
                <th>User</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($trademarks as $t): ?>
            <tr data-id="<?php echo $t->id; ?>">
                <td><?php echo $t->id; ?></td>
                <td><?php echo esc_html($t->country_name); ?></td>
                <td><?php echo esc_html($t->mark_text); ?></td>
                <td><?php echo $t->class_count; ?></td>

                <td>
                    <span class="tm-status-badge tm-status-<?php echo $t->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $t->status)); ?>
                    </span>
                </td>

                <td><?php echo esc_html(get_user_by('id', $t->user_id)->user_email); ?></td>

                <td><?php echo date('M d, Y', strtotime($t->created_at)); ?></td>

         

                <td>
                    <button class="button tm-admin-view-btn" data-id="<?php echo $t->id; ?>">View</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    const TM_ADMIN_TRADEMARK_AJAX = "<?php echo admin_url('admin-ajax.php'); ?>";
    const TM_ADMIN_TRADEMARK_NONCE = "<?php echo $nonce; ?>";
</script>

<script src="<?php echo WP_TMS_NEXILUP_URL . 'assets/js/admin-trademarks.js'; ?>"></script>
<?php
include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/trademark-detail-modal.php';
?>