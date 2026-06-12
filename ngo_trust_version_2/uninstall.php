<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = & get_instance();

$tables = [
	'asset_depreciation',
	'categories',
	'bank_accounts',
	'receipts',
	'payments',
	'transaction',
	'donors',
	'vendors',
];

foreach($tables as $table) {
	$fullTableName = db_prefix() . $table;

	if($CI->db->table_exists($fullTableName)) {
		$CI->db->query('DROP TABLE `' . $fullTableName . '`;');
	}
}