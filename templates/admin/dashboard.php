<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="tm-dashboard-container">
    
    <div class="tm-dashboard-header">
        <h1>Trademark Service Dashboard</h1>
    </div>

    <div class="tm-cards">

        <div class="tm-card">
            <h3>Countries</h3>
            <p>Manage supported countries and their ISO codes.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-countries'); ?>">Manage Countries</a>
        </div>

        <div class="tm-card">
            <h3>Country Pricing</h3>
            <p>Set study filing fees, registration fees, and class-based pricing.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-country-prices'); ?>">Manage Pricing</a>
        </div>

        <div class="tm-card">
            <h3>Service Conditions</h3>
            <p>Configure per-step text (study, filing, owner info, confirm).</p>
            <a href="<?php echo admin_url('admin.php?page=tm-service-conditions'); ?>">Edit Conditions</a>
        </div>

        <div class="tm-card">
            <h3>Plugin Settings</h3>
            <p>Master product, default currency, WooCommerce options.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-settings'); ?>">Settings</a>
        </div>

    </div>

</div>
