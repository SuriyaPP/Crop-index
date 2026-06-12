<?php
defined('BASEPATH') or exit('No direct script access allowed');

function category_type($type) {
	return [
		1 => 'Receipt',
		2 => 'Payment',
		3 => 'Asset',
	][$type] ?? '-';
}

if(!function_exists('getChildIds')) {
	function getChildIds($parent_id, $tree) {
		static $cache = [];

		if(isset($cache[$parent_id])) {
			return $cache[$parent_id];
		}

		$ids = [$parent_id];

		if(isset($tree[$parent_id])) {
			foreach($tree[$parent_id] as $child) {
				$ids = array_merge($ids, getChildIds($child, $tree));
			}
		}

		return $cache[$parent_id] = $ids;
	}
}

if(!function_exists('getReceiptAmount')) {
	function getReceiptAmount($CI, $ids, $start, $end) {
		static $cache = [];

		$key = md5(json_encode($ids) . $start . $end);

		if(isset($cache[$key])) {
			return $cache[$key];
		}

		$CI->db->select_sum('received_amount');
		$CI->db->group_start()
			->where_in('category_id', $ids)
			->or_where_in('sub_category_id', $ids)
			->or_where_in('sub_sub_category_id', $ids)
			->or_where_in('sub_sub_sub_category_id', $ids)
		->group_end();
		$CI->db->where('date >=', $start);
		$CI->db->where('date <=', $end);

		$result = (float) $CI->db->get(db_prefix() . 'receipts')->row()->received_amount ?? 0;

		return $cache[$key] = $result;
	}
}

if(!function_exists('getPaymentAmount')) {
	function getPaymentAmount($CI, $ids, $start, $end) {
		static $cache = [];

		$key = md5(json_encode($ids) . $start . $end);

		if(isset($cache[$key])) {
			return $cache[$key];
		}

		$CI->db->select_sum('received_amount');
		$CI->db->group_start()
			->where_in('category_id', $ids)
			->or_where_in('sub_category_id', $ids)
			->or_where_in('sub_sub_category_id', $ids)
			->or_where_in('sub_sub_sub_category_id', $ids)
		->group_end();
		$CI->db->where('date >=', $start);
		$CI->db->where('date <=', $end);

		$result = (float) $CI->db->get(db_prefix() . 'payments')->row()->received_amount ?? 0;

		return $cache[$key] = $result;
	}
}

if(!function_exists('formatAmount')) {
	function formatAmount($val) {
		return ($val == 0 || $val == null) ? ' - ' : $val;
	}
}