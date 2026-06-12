<?php

defined('BASEPATH') or exit('No direct script access allowed.');

/*
Module Name: Ngo Trust
Description: A module for Ngo Trust
Version: 1.0.9
Author: CorpIndex
Requires at least: 2.3.*
*/

define('NGO_TRUST_MODULE_NAME', 'ngo_trust');

$autoload = module_dir_path(NGO_TRUST_MODULE_NAME, 'vendor/autoload.php');

if(file_exists($autoload)) {
    require_once $autoload;
}

$CI = & get_instance();
$CI->load->helper(NGO_TRUST_MODULE_NAME . '/ngo_trust');

hooks()->add_action('admin_init', 'ngo_trust_module_init_menu_items');

function ngo_trust_module_init_menu_items() {
    $CI = & get_instance();

    $CI->app_menu->add_sidebar_menu_item('accounting', [
        'name' => _l('accounting'),
        'icon' => 'fa fa-calculator menu-icon',
        'position' => 35,
    ]);

    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'accounts',
        'name' => _l('account'),
        'href' => admin_url('ngo_trust/bank_account'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'categories',
        'name' => _l('category'),
        'href' => admin_url('ngo_trust/category'),
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'donors_vendors',
        'name' => _l('donors_vendors'),
        'href' => admin_url('ngo_trust/parties'),
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'asstes',
        'name' => _l('asstes'),
        'href' => admin_url('ngo_trust/asset_depreciation'),
        'position' => 4,
    ]);

    
    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'receipts',
        'name' => _l('receipt'),
        'href' => admin_url('ngo_trust/receipt'),
        'position' => 5,
    ]);

    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'payments',
        'name' => _l('payment'),
        'href' => admin_url('ngo_trust/payment'),
        'position' => 6,
    ]);

    $CI->app_menu->add_sidebar_children_item('accounting', [
        'slug' => 'ngo_trust-report',
        'name' => _l('reports'),
        'href' => admin_url('ngo_trust/reports'),
        'icon' => 'fa fa-line-chart',
        'position' => 7,
    ]);

   
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(NGO_TRUST_MODULE_NAME, [NGO_TRUST_MODULE_NAME]);

/**
* Register activation module hook
*/
register_activation_hook(NGO_TRUST_MODULE_NAME, 'ngo_trust_module_activation_hook');

function ngo_trust_module_activation_hook() {
    $CI = & get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register uninstall module hook
 */

register_deactivation_hook(NGO_TRUST_MODULE_NAME, 'ngo_trust_module_deactivation_hook');

function ngo_trust_module_deactivation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}