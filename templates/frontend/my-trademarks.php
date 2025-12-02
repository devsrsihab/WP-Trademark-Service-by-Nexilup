<?php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$trademarks = isset($trademarks) ? $trademarks : [];

global $wpdb;
function tm_get_country_name($country_id) {
    global $wpdb;
    return $wpdb->get_var(
        $wpdb->prepare("SELECT country_name FROM {$wpdb->prefix}tm_countries WHERE id=%d", $country_id)
    );
}
?>

<div class="tm-container">
    <div class="tm-dashboard">
        <h1 class="tm-title">® Active Trademarks</h1>
        <p class="tm-subtitle">Trademarks that are undergoing the registration process or already registered.</p>

        <!-- Search -->
        <div class="tm-search-box">
            <input type="text" id="tm-search" class="tm-search-input" placeholder="Search trademarks...">
            <button class="tm-search-btn">🔍 Search</button>
        </div>

        <!-- Table -->
        <div class="tm-table-wrapper">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>TRADEMARK</th>
                        <th>COUNTRY</th>
                        <th>STATUS</th>
                        <th>EXPIRATION</th>
                    </tr>
                </thead>

                <tbody id="tm-table-body">
                <?php if (empty($trademarks)): ?>
                    <tr><td colspan="4" class="tm-no-results">No results found</td></tr>
                <?php else: ?>
                    <?php foreach ($trademarks as $tm): ?>
                        <tr class="tm-row">
                            <td><?php echo esc_html($tm->mark_text ?: "No Name"); ?></td>
                            <td><?php echo esc_html(tm_get_country_name($tm->country_id) ?: "Unknown"); ?></td>
                            <td>
                                <span class="tm-status tm-status-<?php echo esc_attr($tm->status); ?>">
                                    <?php echo ucfirst($tm->status); ?>
                                </span>
                            </td>
                            <td>—</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* MAIN WRAPPER — FIXED WIDTH */
.tm-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 15px;
}

/* DASHBOARD WRAPPER */
.tm-dashboard {
    background: #f5f8ff;
    padding: 30px;
    border-radius: 6px;
}

/* TITLES */
.tm-title {
    font-size: 28px;
    margin-bottom: 5px;
}
.tm-subtitle {
    color: #666;
    margin-bottom: 25px;
}

/* SEARCH */
.tm-search-box {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
}
.tm-search-input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ccd4e0;
    border-radius: 4px;
}
.tm-search-btn {
    background: #0052cc;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

/* TABLE BOX */
.tm-table-wrapper {
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
}

.tm-table {
    width: 100%;
    border-collapse: collapse;
}

.tm-table thead {
    background: #e9eef8;
}

.tm-table th,
.tm-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #eef2f7;
}

.tm-table th {
    font-weight: 600;
    color: #334;
}

/* STATUS BADGES */
.tm-status {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
}
.tm-status-paid {
    background: #d4f8d4;
    color: #2b7a2b;
}
.tm-status-pending {
    background: #fff0c2;
    color: #8a6d00;
}
.tm-status-failed {
    background: #ffd4d4;
    color: #a00;
}

/* NO RESULT MSG */
.tm-no-results {
    text-align: center;
    padding: 30px;
    color: #777;
}
</style>

<script>
document.getElementById("tm-search").addEventListener("keyup", function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll("#tm-table-body .tm-row").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? "" : "none";
    });
});
</script>
