<?php if (!defined('ABSPATH')) exit; ?>

<link rel="stylesheet" href="<?php echo WP_TMS_NEXILUP_URL . 'assets/css/admin-dashboard.css'; ?>">

<div class="tm-dashboard-container">
    
    <div class="tm-dashboard-header">
        <h1>Trademark Service Dashboard</h1>
        <p class="tm-dashboard-subtitle">Manage all Trademark Service configurations and data.</p>
    </div>

    <div class="tm-cards">

        <div class="tm-card">
            <div class="tm-card-icon">🌍</div>
            <h3>Countries</h3>
            <p>Manage supported countries and ISO codes used in trademark filings.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-countries'); ?>" class="tm-btn">Manage Countries</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">💰</div>
            <h3>Country Pricing</h3>
            <p>Configure fees, government charges & class-based pricing per country.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-country-prices'); ?>" class="tm-btn">Manage Pricing</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">📄</div>
            <h3>Service Conditions</h3>
            <p>Edit texts & instructions used throughout the trademark filing workflow.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-service-conditions'); ?>" class="tm-btn">Edit Conditions</a>
        </div>

        <div class="tm-card">
            <div class="tm-card-icon">📦</div>
            <h3>Trademark Orders</h3>
            <p>View and manage trademark applications submitted by users.</p>
            <a href="<?php echo admin_url('admin.php?page=tm-trademarks'); ?>" class="tm-btn">View Orders</a>
        </div>

    </div>


    <!-- Shortcodes Panel -->
<div class="tm-shortcodes-box">
    <h2>Available Shortcodes</h2>

    <div class="tm-shortcode-item">
        <code>[tm_country_table single_page="/trademark-country/"]</code>
        <p>Displays the full list of trademark countries with pricing table, [tm_country_table single_page="/country-single"] filters, and AJAX pagination.</p>
        <strong>Use on:</strong> Pricing List Page
    </div>

    <div class="tm-shortcode-item">
        <code>[tm_country_single]</code>
        <p>Displays pricing details for one specific country. Automatically loads based on <code>?country=US</code>.</p>
        <strong>Use on:</strong> Country Details Page
    </div>

    <div class="tm-shortcode-item">
        <code>[tm_service_form]</code>
        <p>Loads the multi-step trademark filing form (study → applicant info → checkout → confirmation).</p>
        <strong>Use on:</strong> Trademark Filing Page
    </div>

    <div class="tm-shortcode-item">
        <code>[tm_my_trademarks]</code>
        <p>Shows a logged-in user's trademark orders, status, and documents.</p>
        <strong>Use on:</strong> User Dashboard (login required)
    </div>

    <p class="tm-shortcodes-note">
        Paste these shortcodes inside any WordPress page to enable Trademark features.
    </p>
</div>


</div>
