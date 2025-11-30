<?php
if (!defined('ABSPATH')) exit;

/**
 * ROUTER: Decide which template to load
 */
$action = $_GET['action'] ?? '';
$id     = intval($_GET['id'] ?? 0);

$tpl_base = WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/country-price/';

// DELETE PRICE
if ($action  === 'delete' && !empty($id)) {
    TM_Country_Prices::delete_price();
    exit;
}

/* ===========================
   LOAD ADD PAGE
=========================== */
if ($action === 'add') {
    include $tpl_base . 'add.php';
    return;
}

/* ===========================
   LOAD EDIT PAGE
=========================== */
if ($action === 'edit' && $id > 0) {
    include $tpl_base . 'edit.php';
    return;
}

/* ===========================
   DEFAULT → SHOW TABLE
=========================== */
include $tpl_base . 'table.php';
return;
