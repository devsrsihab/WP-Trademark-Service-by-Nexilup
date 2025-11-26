<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$paged   = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$data    = TM_Service_Conditions::get_paginated( $paged, 20 );
$rows    = $data['items'];
$steps   = TM_Service_Conditions::get_step_labels();
$countries = TM_Database::get_countries( [ 'active_only' => false ] );
$nonce   = wp_create_nonce( 'tm_service_conditions_nonce' );
?>

<link rel="stylesheet" href="<?php echo esc_url( WP_TMS_NEXILUP_URL . 'assets/css/admin.css' ); ?>">

<div class="wrap tm-wrap">
    <h1 class="tm-page-title">Service Conditions</h1>

    <button class="button button-primary" id="tm-add-condition-btn">+ Add Condition</button>

    <table class="wp-list-table widefat fixed striped mt-20">
        <thead>
        <tr>
            <th>Country</th>
            <th>Step</th>
            <th>Preview</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ( ! empty( $rows ) ) : ?>
            <?php foreach ( $rows as $row ) : ?>
                <tr data-id="<?php echo intval( $row->id ); ?>">
                    <td><?php echo esc_html( $row->country_name ); ?></td>
                    <td>
                        <?php
                        $step_label = isset( $steps[ $row->step_number ] )
                            ? $steps[ $row->step_number ]
                            : 'Step ' . intval( $row->step_number );
                        echo esc_html( $step_label );
                        ?>
                    </td>
                    <td>
                        <?php
                        $preview = wp_strip_all_tags( $row->content );
                        if ( strlen( $preview ) > 80 ) {
                            $preview = substr( $preview, 0, 77 ) . '...';
                        }
                        echo esc_html( $preview );
                        ?>
                    </td>
                    <td>
                        <button class="button tm-edit-condition"
                                data-id="<?php echo intval( $row->id ); ?>">
                            Edit
                        </button>
                        <button class="button tm-delete-condition"
                                data-id="<?php echo intval( $row->id ); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="4">No conditions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ( $data['max_pages'] > 1 ) : ?>
        <div class="tm-pagination">
            <?php for ( $i = 1; $i <= $data['max_pages']; $i++ ) : ?>
                <a class="tm-page-link <?php echo $i == $data['current'] ? 'active' : ''; ?>"
                   href="?page=tm-service-conditions&paged=<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
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
                <?php foreach ( $countries as $c ) : ?>
                    <option value="<?php echo intval( $c->id ); ?>">
                        <?php echo esc_html( $c->country_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label><strong>Step</strong></label><br>
            <select id="tm-condition-step" class="tm-input">
                <?php foreach ( $steps as $num => $label ) : ?>
                    <option value="<?php echo intval( $num ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label><strong>Content</strong></label><br>
            <textarea id="tm-condition-content" class="tm-input" rows="6"
                      placeholder="Enter HTML/text that will appear on the frontend step."></textarea>
        </p>

        <p>
            <button class="button button-primary" id="tm-save-condition">Save </button>
            <button class="button tm-modal-close" type="button">Cancel</button>
        </p>
    </div>
</div>

<script>
    const TM_COND_AJAX  = "<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>";
    const TM_COND_NONCE = "<?php echo esc_js( $nonce ); ?>";
</script>

<script src="<?php echo esc_url( WP_TMS_NEXILUP_URL . 'assets/js/admin-conditions.js' ); ?>"></script>
