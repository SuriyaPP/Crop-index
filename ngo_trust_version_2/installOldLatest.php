<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = & get_instance();

if (!$CI->db->table_exists(db_prefix() . 'receipts')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "receipts` (
        `id` BIGINT(20) UNSIGNED NOT NULL,
        `date` DATE NOT NULL,
        `amount` FLOAT NOT NULL DEFAULT 0,
        `received_amount` FLOAT NOT NULL DEFAULT 0,
        `balance` FLOAT NOT NULL DEFAULT 0,

        `account_id` INT(11) DEFAULT NULL,
        `donor_id` INT(11) DEFAULT NULL,
        `category_id` INT(11) NOT NULL,

        `reference` VARCHAR(255) DEFAULT NULL,
        `description` LONGTEXT DEFAULT NULL,
        `attachment` VARCHAR(255) DEFAULT NULL,

        `is_fully_received` TINYINT(1) NOT NULL DEFAULT 0,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'receipts`
        ADD PRIMARY KEY (`id`),
        ADD KEY `account_id` (`account_id`),
        ADD KEY `donor_id` (`donor_id`),
        ADD KEY `category_id` (`category_id`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'receipts`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

if (!$CI->db->table_exists(db_prefix() . 'categories')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "categories` (
        `id` BIGINT(20) UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `type` VARCHAR(255) NOT NULL,
        `parent_id` INT(11) NOT NULL DEFAULT 0,
        `is_default` TINYINT(1) NOT NULL DEFAULT 0,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'categories`
        ADD PRIMARY KEY (`id`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'categories`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

$categories = [
    [
        'name' => 'Member Contribution',
        'type' => 1,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Donation',
        'type' => 1,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Grant',
        'type' => 1,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Loan',
        'type' => 1,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Other Income',
        'type' => 1,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Program 1',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Program 2',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Program 3',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Salary & Wages',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Rent',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Electricity',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Travel Expenses',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Prining & Stationary',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Registration Expenses',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Other Expenses',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Loan Repayament',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Asset',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Advance',
        'type' => 2,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Building',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Furniture & Fitting',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Computer & Software',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Plant & Machinery',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Motor Vechicle',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Ships',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Aircraft',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Tangible Assets',
        'type' => 3,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Corpus',
        'type' => 1,
        'is_default' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ],
];

$CI->db->insert_batch(db_prefix() . 'categories', $categories);

if (!$CI->db->table_exists(db_prefix() . 'bank_accounts')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "bank_accounts` (
        `id` BIGINT(20) UNSIGNED NOT NULL,
        `holder_name` VARCHAR(255) NOT NULL,
        `bank_name` VARCHAR(255) NOT NULL,
        `bank_type` VARCHAR(255) NOT NULL,
        `account_number` VARCHAR(255) NOT NULL,
        `opening_balance` FLOAT NOT NULL DEFAULT 0,
        `bank_address` VARCHAR(255) NOT NULL,
        `contact_number` VARCHAR(255) NOT NULL,
        `bank_branch` VARCHAR(255) NOT NULL,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'bank_accounts`
        ADD PRIMARY KEY (`id`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'bank_accounts`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

if (!$CI->db->table_exists(db_prefix() . 'payments')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "payments` (
        `id` BIGINT(20) UNSIGNED NOT NULL,
        `date` DATE NOT NULL,
        `amount` FLOAT NOT NULL DEFAULT 0,
        `received_amount` FLOAT NOT NULL DEFAULT 0,
        `balance` FLOAT NOT NULL DEFAULT 0,

        `account_id` INT(11) DEFAULT NULL,
        `vendorid` INT(11) DEFAULT NULL,
        `description` LONGTEXT DEFAULT NULL,

        `category_id` INT(11) DEFAULT NULL,
        `receipt_category_id` INT(11) DEFAULT NULL,
        `asset_category_id` INT(11) DEFAULT NULL,

        `reference` VARCHAR(255) DEFAULT NULL,
        `attachment` VARCHAR(255) DEFAULT NULL,

        `is_fully_paid` TINYINT(1) NOT NULL DEFAULT 0,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'payments`
        ADD PRIMARY KEY (`id`),
        ADD KEY `account_id` (`account_id`),
        ADD KEY `vendorid` (`vendorid`),
        ADD KEY `category_id` (`category_id`),
        ADD KEY `receipt_category_id` (`receipt_category_id`),
        ADD KEY `asset_category_id` (`asset_category_id`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'payments`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

if (!$CI->db->table_exists(db_prefix() . 'transaction')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "transaction` (
        `id` BIGINT(20) UNSIGNED NOT NULL,
        `account_id` INT(11) DEFAULT NULL,
        `type` INT(11) DEFAULT NULL,

        `amount` FLOAT NOT NULL DEFAULT 0,
        `received_amount` FLOAT NOT NULL DEFAULT 0,
        `total_balance` FLOAT NOT NULL DEFAULT 0,

        `description` LONGTEXT DEFAULT NULL,
        `date` DATE NOT NULL,
        `track_id` INT(11) DEFAULT NULL,

        `category` VARCHAR(255) DEFAULT NULL,
        `receipt_category` VARCHAR(255) DEFAULT NULL,
        `asset_category` VARCHAR(255) DEFAULT NULL,

        `previous_balance` FLOAT NOT NULL DEFAULT 0,
        `balance` FLOAT NOT NULL DEFAULT 0,

        `is_fully_paid_or_received` TINYINT(1) NOT NULL DEFAULT 0,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'transaction`
        ADD PRIMARY KEY (`id`),
        ADD KEY `account_id` (`account_id`),
        ADD KEY `track_id` (`track_id`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'transaction`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

if (!$CI->db->table_exists(db_prefix() . 'asset_depreciation')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "asset_depreciation` (
        `id` BIGINT(20) UNSIGNED NOT NULL,
        `category` VARCHAR(255) NOT NULL,
        `month_year` VARCHAR(7) NOT NULL,
        `amount` FLOAT NOT NULL DEFAULT 0,
        `rate` INT(11) DEFAULT 0,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'asset_depreciation`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `asset_depreciation_category_month_year_unique` (`category`,`month_year`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'asset_depreciation`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

if (!$CI->db->table_exists(db_prefix() . 'vendors')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "vendors` (
        `vendorid` BIGINT(20) UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phonenumber` VARCHAR(255) NOT NULL,
        `balance` FLOAT NOT NULL DEFAULT 0,
        `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vendors`
        ADD PRIMARY KEY (`vendorid`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vendors`
        MODIFY `vendorid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}