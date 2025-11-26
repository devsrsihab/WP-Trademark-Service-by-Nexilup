<?php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$items = TM_Trademarks::get_user_trademarks($user_id);
$nonce = wp_create_nonce('tm_user_trademark_nonce');
?>

<div class="tm-dashboard">

    <h2>My Trademarks</h2>

    <?php if (empty($items)): ?>
        <p>You have no trademarks yet.</p>
    <?php else: ?>

        <table class="widefat striped tm-user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Country</th>
                    <th>Type</th>
                    <th>Classes</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Details</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($items as $t): ?>
                <tr>
                    <td>#<?php echo $t->id; ?></td>
                    <td><?php echo esc_html($t->country_name); ?></td>
                    <td><?php echo ucfirst($t->trademark_type); ?></td>
                    <td><?php echo intval($t->class_count); ?></td>

                    <td>
                        <span class="tm-badge tm-status-<?php echo $t->status; ?>">
                            <?php echo ucfirst(str_replace('_',' ', $t->status)); ?>
                        </span>
                    </td>

                    <td><?php echo date('M d, Y', strtotime($t->created_at)); ?></td>

                    <td>
                        <button class="button tm-user-view"
                                data-id="<?php echo $t->id; ?>"
                                data-nonce="<?php echo $nonce; ?>">
                            View
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>


<!-- MODAL -->
<div id="tm-user-modal" class="tm-modal">
    <div class="tm-modal-content">
        <span class="tm-close">&times;</span>
        <div id="tm-user-modal-body">Loading…</div>
    </div>
</div>

<script>
const TM_USER_TRADEMARK_AJAX = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>
