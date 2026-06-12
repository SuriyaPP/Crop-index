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
      `payment_method` INT(11) NOT NULL,

      `reference` VARCHAR(255) DEFAULT NULL,
      `description` LONGTEXT DEFAULT NULL,
      `add_receipt` VARCHAR(255) DEFAULT NULL,

      `is_fully_received` TINYINT(1) NOT NULL DEFAULT 0,
      `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'receipts`
        ADD PRIMARY KEY (`id`),
        ADD KEY `account_id` (`account_id`),
        ADD KEY `customer_id` (`userid`),
        ADD KEY `category_id` (`category_id`),
        ADD KEY `payment_method` (`payment_method`),
        ADD KEY `created_by` (`created_by`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'receipts`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'receipts`
        ADD CONSTRAINT `fk_receipts_account`
        FOREIGN KEY (`account_id`) REFERENCES `' . db_prefix() . 'accounts`(`id`)
        ON DELETE SET NULL,

        ADD CONSTRAINT `fk_receipts_customers`
        FOREIGN KEY (`customer_id`) REFERENCES `' . db_prefix() . 'clients`(`userid`)
        ON DELETE SET NULL,

        ADD CONSTRAINT `fk_receipts_user`
        FOREIGN KEY (`user_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`)
        ON DELETE SET NULL,

        ADD CONSTRAINT `fk_receipts_category`
        FOREIGN KEY (`category_id`) REFERENCES `' . db_prefix() . 'categories`(`id`)
        ON DELETE CASCADE,

        ADD CONSTRAINT `fk_receipts_payment_method`
        FOREIGN KEY (`payment_method`) REFERENCES `' . db_prefix() . 'payment_modes`(`id`)
        ON DELETE RESTRICT;

        ADD CONSTRAINT `fk_receipts_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `' . db_prefix() . 'staff`(`staffid`)
        ON DELETE RESTRICT;
    ');
}

if(!$CI->db->table_exists(db_prefix() . 'categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "categories` (
      `id` BIGINT(20) UNSIGNED NOT NULL,
      `name` VARCHAR(255) NOT NULL,
      `type` VARCHAR(255) NOT NULL,
      `created_by` INT(11) NOT NULL DEFAULT '0',
      `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'categories`
        ADD PRIMARY KEY (`id`),
        ADD KEY `created_by` (`created_by`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'categories`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}

if(!$CI->db->table_exists(db_prefix() . 'payments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "payments` (
      `id` BIGINT(20) UNSIGNED NOT NULL,
      `date` DATE NOT NULL,
      `amount` FLOAT NOT NULL DEFAULT 0,
      `received_amount` FLOAT NOT NULL DEFAULT 0,
      `balance` FLOAT NOT NULL DEFAULT 0,

      `account_id` INT(11) DEFAULT NULL,
      `vendor_id` INT(11) DEFAULT NULL,
      `description` LONGTEXT DEFAULT NULL,

      `category_id` INT(11) DEFAULT NULL,
      `receipt_category_id` INT(11) DEFAULT NULL,
      `asset_category_id` INT(11) DEFAULT NULL,

      `receipt_id` INT(11) NOT NULL DEFAULT '0',
      `payment_method` INT(11) NOT NULL,
      `reference` VARCHAR(255) DEFAULT NULL,
      `add_receipt` VARCHAR(255) DEFAULT NULL,

      `is_fully_paid` TINYINT(1) NOT NULL DEFAULT 0,
      `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'payments`
        ADD PRIMARY KEY (`id`),
        ADD KEY `account_id` (`account_id`),
        ADD KEY `vendor_id` (`vendor_id`),
        ADD KEY `category_id` (`category_id`),
        ADD KEY `receipt_category_id` (`receipt_category_id`),
        ADD KEY `asset_category_id` (`category_asset_id`),
        ADD KEY `receipt_id` (`receipt_id`),
        ADD KEY `payment_method` (`payment_method`),
        ADD KEY `created_by` (`created_by`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'payments`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'payments`
        ADD CONSTRAINT `fk_payments_account`
        FOREIGN KEY (`account_id`) REFERENCES `' . db_prefix() . 'accounts`(`id`)
        ON DELETE SET NULL,

        ADD CONSTRAINT `fk_payments_customers`
        FOREIGN KEY (`vendor_id`) REFERENCES `' . db_prefix() . 'vendor`(`userid`)
        ON DELETE SET NULL,

        ADD CONSTRAINT `fk_payments_user`
        FOREIGN KEY (`user_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`)
        ON DELETE SET NULL,

        ADD CONSTRAINT `fk_payments_category`
        FOREIGN KEY (`category_id`) REFERENCES `' . db_prefix() . 'categories`(`id`)
        ON DELETE CASCADE,

        ADD CONSTRAINT `fk_payments_receipts_category`
        FOREIGN KEY (`receipt_category_id`) REFERENCES `' . db_prefix() . 'categories`(`id`)
        ON DELETE CASCADE,

        ADD CONSTRAINT `fk_payments_asset_category`
        FOREIGN KEY (`asset_category_id`) REFERENCES `' . db_prefix() . 'categories`(`id`)
        ON DELETE CASCADE,

        ADD CONSTRAINT `fk_payments_payment_method`
        FOREIGN KEY (`payment_method`) REFERENCES `' . db_prefix() . 'payment_modes`(`id`)
        ON DELETE RESTRICT;

        ADD CONSTRAINT `fk_payments_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `' . db_prefix() . 'staff`(`staffid`)
        ON DELETE RESTRICT;
    ');
}

if(!$CI->db->table_exists(db_prefix() . 'asset_depreciation')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "asset_depreciation` (
      `id` BIGINT(20) UNSIGNED NOT NULL,
      `category` VARCHAR(255) NOT NULL,
      `month_year` VARCHAR(7) NOT NULL,
      `amount` FLOAT NOT NULL DEFAULT 0,
      `rate` INT(11) DEFAULT 0,
      `created_by` INT(11) NOT NULL DEFAULT '0',
      `datecreated` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'asset_depreciation`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `asset_depreciation_category_month_year_unique` (`category`, `month_year`),
        ADD KEY `created_by` (`created_by`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'asset_depreciation`
        MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
    ');
}