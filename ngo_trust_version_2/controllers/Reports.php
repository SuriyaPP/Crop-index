<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends AdminController
{
	protected $base_currency;

	public function __construct() {
		parent::__construct();
		$this->load->model('BankAccount_model');
		$this->load->model('categories_model');
		$this->load->model('currencies_model');
		$this->load->helper('ngo_trust');
		$this->base_currency = $this->currencies_model->get_base_currency();
	}

	public function index() {
		$data['title'] = _l('reports');
		$data['accounts'] = $this->BankAccount_model->get();
		$data['categories'] = $this->categories_model->get_by_type(1);
		$this->load->view('reports/manage_reports', $data);
	}

	public function liability_report() {
		if($this->input->is_ajax_request()) {
            $categories = [];
			$category_rows = $this->db->select('name')->where_in('id', array(4, 16))->get(db_prefix() . 'categories')->result_array();

			foreach($category_rows as $row) {
				$categories[] = $row['name'];
			}

			$select = [
				'date',
				db_prefix() . 'bank_accounts.bank_name as account_name',
				'account_id',
				'category',
				'type',
				'amount',
				'received_amount',
				'total_balance',
			];

			$where = [];

			if(!empty($categories)) {
				$where[] = 'AND category IN ("' . implode('","', $categories) . '")';
			}

			$where[] = 'AND type IN ("1", "2")';

            $custom_date_select = $this->get_where_report_period(db_prefix() . 'transaction.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $where[] = 'AND received_amount IS NOT NULL';
            $where[] = 'AND received_amount != 0';

            $join = [
            	'LEFT JOIN ' . db_prefix() . 'bank_accounts ON ' . db_prefix() . 'bank_accounts.id = ' . db_prefix() . 'transaction.account_id',
            ];

            $result = data_tables_init($select, 'id', db_prefix() . 'transaction', $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];

            $running_balance = 0;

            $footer_data = [
            	'amount' => 0,
            	'credit' => 0,
            	'debit' => 0,
            	'balance' => 0,
            ];

            foreach($rResult as $aRow) {
            	$credit = $debit = 0;

            	if($aRow['type'] == 1) {
            		$credit = $aRow['received_amount'];
            		$running_balance += $credit;
            	}

            	if($aRow['type'] == 2) {
            		$debit = $aRow['received_amount'];
            		$running_balance -= $debit;
            	}

            	$row = [];
            	$row[] = _d($aRow['date']);
            	$row[] = $aRow['account_name'];
            	$row[] = $aRow['category'];
            	$row[] = ($aRow['type']) == 1 ? 'Receipt' : 'Payment';

            	$row[] = app_format_money($aRow['received_amount'], $this->base_currency->name);
            	$row[] = app_format_money($credit, $this->base_currency->name);
            	$row[] = app_format_money($debit, $this->base_currency->name);
            	$row[] = app_format_money($running_balance, $this->base_currency->name);
            	$row[] = app_format_money($aRow['total_balance'], $this->base_currency->name);

            	$footer_data['amount'] += $aRow['received_amount'];
            	$footer_data['credit'] += $credit;
            	$footer_data['debit'] += $debit;

            	$output['aaData'][] = $row;
            }

            $footer_data['balance'] = $footer_data['credit'] - $footer_data['debit'];

            foreach($footer_data as $key => $data) {
            	$footer_data[$key] = app_format_money($data, $this->base_currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die;
		}
	}

	public function asset_report() {
		if($this->input->is_ajax_request()) {
			$categories = [];
			$category_rows = $this->db->select('name')->where_in('id', array(17))->get(db_prefix() . 'categories')->result_array();

			foreach($category_rows as $row) {
				$categories[] = $row['name'];
			}

			$select = [
				'date',
				db_prefix() . 'bank_accounts.bank_name as account_name',
				'account_id',
				'category',
				'type',
				'amount',
				'received_amount',
				'total_balance',
			];

			$where = [];

			if(!empty($categories)) {
				$where[] = 'AND category IN ("' . implode('","', $categories) . '")';
			}

			$where[] = 'AND type = 2';

            $custom_date_select = $this->get_where_report_period(db_prefix() . 'transaction.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $where[] = 'AND received_amount IS NOT NULL';
            $where[] = 'AND received_amount != 0';

            $join = [
            	'LEFT JOIN ' . db_prefix() . 'bank_accounts ON ' . db_prefix() . 'bank_accounts.id = ' . db_prefix() . 'transaction.account_id',
            ];

            $result = data_tables_init($select, 'id', db_prefix() . 'transaction', $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];

            $running_balance = 0;

            $footer_data = [
            	'amount' => 0,
            	'credit' => 0,
            	'debit' => 0,
            	'balance' => 0,
            ];

            foreach($rResult as $aRow) {
        		$debit = $aRow['received_amount'];
        		$running_balance += $debit;

            	$row = [];
            	$row[] = _d($aRow['date']);
            	$row[] = $aRow['account_name'];
            	$row[] = $aRow['category'];
            	$row[] = 'Payment';

            	$row[] = app_format_money($aRow['received_amount'], $this->base_currency->name);
            	$row[] = app_format_money(0, $this->base_currency->name);
            	$row[] = app_format_money($debit, $this->base_currency->name);
            	$row[] = app_format_money($running_balance, $this->base_currency->name);
            	$row[] = app_format_money($aRow['total_balance'], $this->base_currency->name);

            	$footer_data['amount'] += $aRow['received_amount'];
            	$footer_data['debit'] += $debit;
            	$footer_data['balance'] += $debit;

            	$output['aaData'][] = $row;
            }

            foreach($footer_data as $key => $data) {
            	$footer_data[$key] = app_format_money($data, $this->base_currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die;
		}
	}

	public function payment_receipt_report() {
		if($this->input->is_ajax_request()) {
			$select = [
				'date',
				db_prefix() . 'bank_accounts.bank_name as account_name',
				'account_id',
				'type',
				'description',
				'amount',
				'received_amount',
				'total_balance',
			];

			$where = [];
			if ($this->input->post('account')) {
                $accounts  = $this->input->post('account');
                $_accounts = [];
                if (is_array($accounts)) {
                    foreach ($accounts as $acc) {
                        if ($acc != '') {
                            array_push($_accounts, $acc);
                        }
                    }
                }
                if (count($_accounts) > 0) {
                    array_push($where, 'AND tbltransaction.account_id IN (' . implode(', ', $_accounts) . ')');
                }
            }

            if($this->input->post('category')) {
            	$category_ids = array_filter((array)$this->input->post('category'));

            	if(!empty($category_ids)) {
            		$this->db->select('name');
            		$this->db->from(db_prefix() . 'categories');
            		$this->db->where_in('id', $category_ids);
            		$query = $this->db->get();

            		$category_names = array_column($query->result_array(), 'name');

            		if(!empty($category_names)) {
            			$escaped = array_map([$this->db, 'escape'], $category_names);
            			// $where[] = 'AND tbltransaction.category IN (' . implode(',', $escaped) . ')';
            			$where[] = 'AND (tbltransaction.category IN (' . implode(',', $escaped) . ') OR tbltransaction.receipt_category IN (' . implode(',', $escaped) . '))';
            		}
            	}
            }

            $where[] = 'AND type IN (1,2)';

            $custom_date_select = $this->get_where_report_period(db_prefix() . 'transaction.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $where[] = 'AND received_amount IS NOT NULL';
        	$where[] = 'AND received_amount != 0';

        	$join = [
            	'LEFT JOIN ' . db_prefix() . 'bank_accounts ON ' . db_prefix() . 'bank_accounts.id = ' . db_prefix() . 'transaction.account_id',
            ];

            $result = data_tables_init($select, 'id', db_prefix() . 'transaction', $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];

            $running_balance = 0;

            $footer_data = [
	            'amount'  => 0,
	            'credit'  => 0,
	            'debit'   => 0,
	            'balance' => 0,
	        ];

	        foreach($rResult as $aRow) {
	        	$credit = $debit = 0;

	        	if($aRow['type'] == 1) {
	        		$credit = $aRow['received_amount'];
	        		$running_balance += $credit;
	        	}

	        	if($aRow['type'] == 2) {
	        		$debit = $aRow['received_amount'];
	        		$running_balance -= $debit;
	        	}

	        	$row = [];
	        	$row[] = _d($aRow['date']);
	        	$row[] = $aRow['account_name'];
	        	$row[] = ($aRow['type']) == 1 ? 'Receipt' : 'Payment';
	        	$row[] = $aRow['description'];

	        	$row[] = app_format_money($aRow['received_amount'], $this->base_currency->name);
            	$row[] = app_format_money($credit, $this->base_currency->name);
            	$row[] = app_format_money($debit, $this->base_currency->name);
            	$row[] = app_format_money($running_balance, $this->base_currency->name);

            	$footer_data['amount'] += $aRow['received_amount'];
            	$footer_data['credit'] += $credit;
            	$footer_data['debit'] += $debit;

            	$output['aaData'][] = $row;
	        }

	        $footer_data['balance'] = $footer_data['credit'] - $footer_data['debit'];

            foreach($footer_data as $key => $data) {
            	$footer_data[$key] = app_format_money($data, $this->base_currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die;
		}
	}

	public function receivables_report() {
		if($this->input->is_ajax_request()) {
			$select = [
				'date',
				db_prefix() . 'bank_accounts.bank_name as account_name',
				db_prefix() . 'categories.name as category_name',
				'amount',
				'received_amount',
				'balance',
				'is_fully_received',
			];

			$where = [];

			$where[] = 'AND balance IS NOT NULL';
			$where[] = 'AND balance != 0';
			$where[] = 'AND is_fully_received = 0';

			$custom_date_select = $this->get_where_report_period(db_prefix() . 'receipts.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $join = [
            	'LEFT JOIN ' . db_prefix() . 'bank_accounts ON ' . db_prefix() . 'bank_accounts.id = ' . db_prefix() . 'receipts.account_id',
            	'LEFT JOIN ' . db_prefix() . 'categories ON ' . db_prefix() . 'categories.id = ' . db_prefix() . 'receipts.category_id',
            ];

            $result = data_tables_init($select, 'id', db_prefix() . 'receipts', $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
            	'amount' => 0,
            	'received' => 0,
            	'balance' => 0,
            ];

            foreach($rResult as $aRow) {
            	$row = [];
            	$row[] = _d($aRow['date']);
            	$row[] = $aRow['account_name'];
            	$row[] = $aRow['category_name'];

            	$row[] = app_format_money($aRow['amount'], $this->base_currency->name);
            	$row[] = app_format_money($aRow['received_amount'], $this->base_currency->name);
            	$row[] = app_format_money($aRow['balance'], $this->base_currency->name);

            	$footer_data['amount'] += $aRow['amount'];
            	$footer_data['received'] += $aRow['received_amount'];
            	$footer_data['balance'] += $aRow['balance'];

            	$output['aaData'][] = $row;
            }

            foreach($footer_data as $key => $data) {
            	$footer_data[$key] = app_format_money($data, $this->base_currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die;
		}
	}

	public function payables_report() {
		if($this->input->is_ajax_request()) {
			$select = [
				'date',
				db_prefix() . 'bank_accounts.bank_name as account_name',
				db_prefix() . 'categories.name as category_name',
				'asset_categories.name as asset_category_name',
				'amount',
				'received_amount',
				'balance',
				'is_fully_paid',
			];

			$where = [];

			$where[] = 'AND category_id != 16';
			$where[] = 'AND balance IS NOT NULL';
			$where[] = 'AND balance != 0';
			$where[] = 'AND is_fully_paid = 0';

			$custom_date_select = $this->get_where_report_period(db_prefix() . 'payments.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $join = [
            	'LEFT JOIN ' . db_prefix() . 'bank_accounts ON ' . db_prefix() . 'bank_accounts.id = ' . db_prefix() . 'payments.account_id',
            	'LEFT JOIN ' . db_prefix() . 'categories ON ' . db_prefix() . 'categories.id = ' . db_prefix() . 'payments.category_id',
            	'LEFT JOIN ' . db_prefix() . 'categories AS asset_categories ON asset_categories.id = ' . db_prefix() . 'payments.asset_category_id',
            ];

            $result = data_tables_init($select, 'id', db_prefix() . 'payments', $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
            	'amount' => 0,
            	'paid' => 0,
            	'balance' => 0,
            ];

            foreach($rResult as $aRow) {
            	$row = [];
            	$row[] = _d($aRow['date']);
            	$row[] = $aRow['account_name'];
            	$row[] = $aRow['category_name'];
            	$row[] = !empty($aRow['asset_category_name']) ? $aRow['asset_category_name'] : ' - ';

            	$row[] = app_format_money($aRow['amount'], $this->base_currency->name);
            	$row[] = app_format_money($aRow['received_amount'], $this->base_currency->name);
            	$row[] = app_format_money($aRow['balance'], $this->base_currency->name);

            	$footer_data['amount'] += $aRow['amount'];
            	$footer_data['paid'] += $aRow['received_amount'];
            	$footer_data['balance'] += $aRow['balance'];

            	$output['aaData'][] = $row;
            }

            foreach($footer_data as $key => $data) {
            	$footer_data[$key] = app_format_money($data, $this->base_currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die;
		}
	}

	public function sources_application_report() {
	    if($this->input->is_ajax_request()) {
	        list($start_date, $end_date) = $this->get_report_start_end_dates();

	        $opening_balance = $closing_balance = [];
			$total_opening = $total_closing = 0;

			if ($start_date && $end_date) {

			    // Previous day before selected period
			    $opening_date = date('Y-m-d', strtotime($start_date . ' -1 day'));

			    $accounts = $this->db
			        ->select('id, bank_name')
			        ->get(db_prefix() . 'bank_accounts')
			        ->result_array();

			    foreach ($accounts as $account) {

			        // =====================================================
			        // OPENING BALANCE
			        // Previous year's closing balance
			        // =====================================================

			        $opening_txn = $this->db
			            ->select('total_balance')
			            ->from(db_prefix() . 'transaction')
			            ->where('account_id', $account['id'])
			            ->where('date <=', $opening_date)
			            ->order_by('date', 'DESC')
			            ->order_by('id', 'DESC')
			            ->limit(1)
			            ->get()
			            ->row();

			        $opening_amount = $opening_txn
			            ? (float) $opening_txn->total_balance
			            : 0;

			        $opening_balance[] = [
			            'account' => 'Cash in ' . $account['bank_name'],
			            'amount'  => app_format_money(
			                $opening_amount,
			                $this->base_currency->name
			            ),
			        ];

			        $total_opening += $opening_amount;

			        // =====================================================
			        // CLOSING BALANCE
			        // Balance as on report end date
			        // =====================================================

			        $closing_txn = $this->db
			            ->select('total_balance')
			            ->from(db_prefix() . 'transaction')
			            ->where('account_id', $account['id'])
			            ->where('date <=', $end_date)
			            ->order_by('date', 'DESC')
			            ->order_by('id', 'DESC')
			            ->limit(1)
			            ->get()
			            ->row();

			        $closing_amount = $closing_txn
			            ? (float) $closing_txn->total_balance
			            : 0;

			        $closing_balance[] = [
			            'account' => 'Cash in ' . $account['bank_name'],
			            'amount'  => app_format_money(
			                $closing_amount,
			                $this->base_currency->name
			            ),
			        ];

			        $total_closing += $closing_amount;
			    }
			}

	        $select = [
	            't.id',
	            't.date',
	            't.category',
	            't.type',
	            'summary.total_amount',
	            'summary.all_transaction_ids',
	        ];

	        $where = [];
	        $where[] = 'AND t.received_amount IS NOT NULL';
	        $where[] = 'AND t.received_amount != 0';

	        $custom_date_select = $this->get_where_report_period('t.date');
	        if ($custom_date_select != '') {
	            array_push($where, $custom_date_select);
	        }

	        // ✅ FIX: Add date range filter inside the subquery too
	        $date_filter_in_subquery = '';
	        if ($start_date && $end_date) {
	            $date_filter_in_subquery = "AND date >= '{$start_date}' AND date <= '{$end_date}'";
	        }

	        $join = [
	            'INNER JOIN (
	                SELECT category, MAX(id) AS latest_transaction, SUM(received_amount) AS total_amount, GROUP_CONCAT(id ORDER BY id DESC) AS all_transaction_ids
	                FROM ' . db_prefix() . 'transaction
	                WHERE received_amount IS NOT NULL
	                    AND received_amount != 0
	                    ' . $date_filter_in_subquery . '  
	                GROUP BY category
	            ) AS summary
	            ON summary.category = t.category
	            AND summary.latest_transaction = t.id'
	        ];

	        $result = data_tables_init($select, 't.id', db_prefix() . 'transaction AS t', $join, $where, ['t.type']);
	        $output = $result['output'];
	        $rResult = $result['rResult'];

            $output['aaData'][] = ['', 'Opening Balance', '', '', 'DT_RowAttr' => ['style' => 'text-align: center; font-weight: bolder; font-size: 18px; background: #dff0d8;']];
            foreach($opening_balance as $aRow) {
            	$output['aaData'][] = ['', $aRow['account'], $aRow['amount'], ''];
            }

            $output['aaData'][] = ['', '', '', ''];

            $total_receipt = $total_payment = 0;

            foreach($rResult as $key => $aRow) {
            	if($aRow['type'] != 1) {
            		continue;
            	}

            	$txnIds = explode(',', $aRow['all_transaction_ids']);
            	$hasMultiple = count($txnIds) > 1;

            	$total_receipt += $aRow['total_amount'];

            	if($hasMultiple) {
            		$output['aaData'][] = [
            			'To',
            			'<span id="toggle-icon-Receipt-' . $key . '">➕ </span><strong>' . html_escape($aRow['category']) . '</strong>',
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            			'',
            			'DT_RowAttr' => [
            				'data-key' => $key,
            				'data-type' => 'Receipt',
            				'data-ids' => $aRow['all_transaction_ids'],
            				'data-report' => 'sources_application',
            				'onclick' => "toggleDetails(this)",
            				'style' => 'cursor: pointer; background: #e3eeef; font-weight: bolder;',
            			],
            		];

            		// $output['aaData'][] = [
            		// 	'',
            		// 	'<div id="Receipt-details-' . $key . '" style="display: none;">
            		// 		<table class="table table-borderless">
			        //             <thead>
			        //                 <tr>
			        //                     <th></th>
			        //                     <th>Particulars</th>
			        //                     <th>Date</th>
			        //                     <th>Amount</th>
			        //                 </tr>
			        //             </thead>
			        //             <tbody id="details-body-Receipt-' . $key . '">
			        //                 <tr><td colspan="4">Loading...</td></tr>
			        //             </tbody>
			        //         </table>
            		// 	</div>',
            		// 	'',
            		// 	'',
            		// ];
            	} else {
            		$output['aaData'][] = [
            			'To',
            			html_escape($aRow['category']),
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            			'',
            		];
            	}
            }

            $output['aaData'][] = ['', '', '', ''];

            foreach($rResult as $key => $aRow) {
            	if($aRow['type'] != 2) {
            		continue;
            	}

            	$txnIds = explode(',', $aRow['all_transaction_ids']);
            	$hasMultiple = count($txnIds) > 1;

            	$total_payment += $aRow['total_amount'];

            	if($hasMultiple) {
            		$output['aaData'][] = [
            			'By',
            			'<span id="toggle-icon-Payment-' . $key . '">➕ </span><strong>' . html_escape($aRow['category']) . '</strong>',
            			'',
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            			'DT_RowAttr' => [
            				'data-key' => $key,
            				'data-type' => 'Payment',
            				'data-ids' => $aRow['all_transaction_ids'],
            				'data-report' => 'sources_application',
            				'onclick' => "toggleDetails(this)",
            				'style' => 'cursor: pointer; background: #e3eeef; font-weight: bolder;'
            			],
            		];

            		// $output['aaData'][] = [
            		// 	'',
            		// 	'<div id="Payment-details-' . $key . '" style="display: none;">
            		// 		<table class="table table-borderless">
			        //             <thead>
			        //                 <tr>
			        //                     <th></th>
			        //                     <th>Particulars</th>
			        //                     <th>Date</th>
			        //                     <th>Amount</th>
			        //                 </tr>
			        //             </thead>
			        //             <tbody id="details-body-Payment-' . $key . '">
			        //                 <tr><td colspan="4">Loading...</td></tr>
			        //             </tbody>
			        //         </table>
            		// 	</div>',
            		// 	'',
            		// 	'',
            		// ];
            	} else {
            		$output['aaData'][] = [
            			'By',
            			html_escape($aRow['category']),
            			'',
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            		];
            	}
            }

            $output['aaData'][] = ['', '', '', ''];

            $output['aaData'][] = ['', 'Closing Balance', '', '', 'DT_RowAttr' => ['style' => 'text-align: center; font-weight: bolder; font-size: 18px; background: #dff0d8;']];
            foreach($closing_balance as $aRow) {
            	$output['aaData'][] = ['', $aRow['account'], '', $aRow['amount']];
            }

            $output['aaData'][] = ['', '<div class="report-divider"></div>', '', ''];

            $final_receipt = $total_opening + $total_receipt;
            $final_payment = $total_payment + $total_closing;

            $output['aaData'][] = [
            	'',
            	'<strong>Total</strong>',
            	'<strong>' . app_format_money($final_receipt, $this->base_currency->name) . '</strong>',
            	'<strong>' . app_format_money($final_payment, $this->base_currency->name) . '</strong>',
            ];

            echo json_encode($output);
            die;
		}
	}

	public function surplus_deficit_report() {
		if($this->input->is_ajax_request()) {
			list($start_date, $end_date) = $this->get_report_start_end_dates();

			$receiptCats = $this->db->select('name')->where('type', 1)->where_not_in('id', [4,27])->get(db_prefix() . 'categories')->result_array();
			$receiptNames = array_column($receiptCats, 'name');
			$receivable = $this->db->select_sum('balance')->where('is_fully_received = ', 0)->where('date >=', $start_date)->where('date <=', $end_date)->get(db_prefix() . 'receipts')->row()->balance ?? 0;

			$paymentCats = $this->db->select('name')->where('type', 2)->where_not_in('id', [16, 18, 197, 198])->get(db_prefix() . 'categories')->result_array();
			$paymentNames = array_column($paymentCats, 'name');
			$payable = $this->db->select_sum('balance')->where('is_fully_paid = ', 0)->where('date >=', $start_date)->where('date <=', $end_date)->get(db_prefix() . 'payments')->row()->balance ?? 0;

			$select = [
				't.id',
				't.date',
				't.category',
				't.type',
				'summary.total_amount',
				'summary.all_transaction_ids',
			];

			$where = [
				'AND t.received_amount IS NOT NULL',
				'AND t.received_amount != 0',
			];

			$custom_date_select = $this->get_where_report_period('t.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $date_filter_in_subquery = '';
            if($start_date && $end_date) {
            	$date_filter_in_subquery = "AND date >= '{$start_date}' AND date <= '{$end_date}'";
            }

            $join = [
            	'INNER JOIN (
            		SELECT category, MAX(id) AS latest_transaction, SUM(received_amount) AS total_amount, GROUP_CONCAT(id ORDER BY id DESC) AS all_transaction_ids
            		FROM ' . db_prefix() . 'transaction
            		WHERE received_amount IS NOT NULL
            			AND received_amount != 0
            			' . $date_filter_in_subquery . '
            		GROUP BY category
            	) AS summary
            	ON summary.category = t.category
            	AND summary.latest_transaction = t.id'
            ];

            $result = data_tables_init($select, 't.id', db_prefix() . 'transaction AS t', $join, $where, ['t.type']);
            $output = $result['output'];
            $rResult = $result['rResult'];

            $total_income = $total_expense = 0;

            foreach($rResult as $key => $aRow) {
            	if($aRow['type'] != 1 || !in_array($aRow['category'], $receiptNames)) {
            		continue;
            	}

            	$txnIds = explode(',', $aRow['all_transaction_ids']);
            	$hasMultiple = count($txnIds) > 1;

            	$total_income += $aRow['total_amount'];

            	if($hasMultiple) {
            		$output['aaData'][] = [
            			'To',
            			'<span id="toggle-icon-Receipt-' . $key . '">➕ </span><strong>' . html_escape($aRow['category']) . '</strong>',
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            			'',
            			'DT_RowAttr' => [
            				'data-key' => $key,
            				'data-type' => 'Receipt',
            				'data-ids' => $aRow['all_transaction_ids'],
            				'data-report' => 'surplus_deficit',
            				'onclick' => "toggleDetails(this)",
            				'style' => 'cursor: pointer; background: #e3eeef; font-weight: bolder;',
            			]
            		];

            		// $output['aaData'][] = [
            		// 	'',
            		// 	'<div id="Receipt-details-' . $key . '" style="display: none;">
            		// 		<table class="table table-borderless">
			        //             <thead>
			        //                 <tr>
			        //                     <th></th>
			        //                     <th>Particulars</th>
			        //                     <th>Date</th>
			        //                     <th>Amount</th>
			        //                 </tr>
			        //             </thead>
			        //             <tbody id="details-body-Receipt-' . $key . '">
			        //                 <tr><td colspan="4">Loading...</td></tr>
			        //             </tbody>
			        //         </table>
            		// 	</div>',
            		// 	'',
            		// 	'',
            		// ];
            	} else {
            		$output['aaData'][] = [
            			'To',
            			html_escape($aRow['category']),
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            			'',
            		];
            	}
            }

            if($receivable > 0) {
            	$total_income += $receivable;

            	$output['aaData'][] = [
            		'To',
            		'Receivables',
            		app_format_money($receivable, $this->base_currency->name),
            		'',
            		'DT_RowAttr' => [
        				'style' => 'font-weight: bolder;',
        			]
            	];
            }

            $output['aaData'][] = ['', '', '', ''];
            $output['aaData'][] = ['', '', '', ''];

            foreach($rResult as $key => $aRow) {
            	if($aRow['type'] != 2 || !in_array($aRow['category'], $paymentNames)) {
            		continue;
            	}

            	$txnIds = explode(',', $aRow['all_transaction_ids']);
            	$hasMultiple = count($txnIds) > 1;

            	$total_expense += $aRow['total_amount'];

            	if($hasMultiple) {
            		$output['aaData'][] = [
            			'By',
            			'<span id="toggle-icon-Payment-' . $key . '">➕ </span><strong>' . html_escape($aRow['category']) . '</strong>',
            			'',
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            			'DT_RowAttr' => [
            				'data-key' => $key,
            				'data-type' => 'Payment',
            				'data-ids' => $aRow['all_transaction_ids'],
            				'data-report' => 'surplus_deficit',
            				'onclick' => "toggleDetails(this)",
            				'style' => 'cursor: pointer; background: #e3eeef; font-weight: bolder;',
            			]
            		];

            		// $output['aaData'][] = [
            		// 	'',
            		// 	'<div id="Payment-details-' . $key . '" style="display: none;">
            		// 		<table class="table table-borderless">
			        //             <thead>
			        //                 <tr>
			        //                     <th></th>
			        //                     <th>Particulars</th>
			        //                     <th>Date</th>
			        //                     <th>Amount</th>
			        //                 </tr>
			        //             </thead>
			        //             <tbody id="details-body-Payment-' . $key . '">
			        //                 <tr><td colspan="4">Loading...</td></tr>
			        //             </tbody>
			        //         </table>
            		// 	</div>',
            		// 	'',
            		// 	'',
            		// ];
            	} else {
            		$output['aaData'][] = [
            			'By',
            			html_escape($aRow['category']),
            			'',
            			app_format_money($aRow['total_amount'], $this->base_currency->name),
            		];
            	}
            }

            if($payable > 0) {
            	$total_expense += $payable;

            	$output['aaData'][] = [
            		'By',
            		'Payables',
            		'',
            		app_format_money($payable, $this->base_currency->name),
            		'DT_RowAttr' => [
        				'style' => 'font-weight: bolder;',
        			]
            	];
            }

            $surplusBefore = $deficitBefore = 0;

            if($total_income > $total_expense) {
            	$surplusBefore = $total_income - $total_expense;
            } elseif($total_income < $total_expense) {
            	$deficitBefore = $total_expense - $total_income;
            }

            $startMonth = date('Y-m', strtotime($start_date));

			$latestAmountSql = "
				SELECT ad.category, ad.amount
				FROM " . db_prefix() . "asset_depreciation ad
				INNER JOIN (
					SELECT category, MAX(month_year) AS max_month
					FROM " . db_prefix() . "asset_depreciation
					WHERE month_year < " . $this->db->escape($startMonth) . "
					GROUP BY category
				) latest
					ON latest.category = ad.category
					AND latest.max_month = ad.month_year
			";

			$latestRateSql = "
				SELECT ad.category, ad.rate
				FROM " . db_prefix() . "asset_depreciation ad
				INNER JOIN (
					SELECT category, MAX(month_year) AS max_month
					FROM " . db_prefix() . "asset_depreciation
					GROUP BY category
				) latest
					ON latest.category = ad.category
					AND latest.max_month = ad.month_year
			";

			$depreciationRows = $this->db->query("
				SELECT
					t.asset_category,
					SUM(CASE WHEN MONTH(t.date) BETWEEN 4 AND 9 THEN t.received_amount ELSE 0 END) AS first_half,
					SUM(CASE WHEN MONTH(t.date) NOT BETWEEN 4 AND 9 THEN t.received_amount ELSE 0 END) AS second_half,
					COALESCE(ad_amount.amount, 0) AS wd_value,
					COALESCE(ad_rate.rate, 0) AS rate
				FROM " . db_prefix() . "transaction t
				LEFT JOIN ($latestAmountSql) ad_amount ON ad_amount.category = t.asset_category
				LEFT JOIN ($latestRateSql) ad_rate ON ad_rate.category = t.asset_category
				WHERE t.type = 2
					AND t.received_amount IS NOT NULL
					AND t.received_amount != 0
					AND t.asset_category IS NOT NULL
					AND t.asset_category != ''
					" . $this->get_where_report_period('t.date') . "
				GROUP BY t.asset_category, ad_amount.amount, ad_rate.rate
			")->result();

			$totalDepreciation = 0;
			foreach($depreciationRows as $row) {
				$depreciationMore = round(($row->wd_value + $row->first_half) * ($row->rate / 100), 2);
				$depreciationLess = round(($row->second_half) * ($row->rate / 2 / 100), 2);
				$totalDepreciation += ($depreciationMore + $depreciationLess);
			}

			$netBalance = 0;
			if($surplusBefore > 0) {
				$netBalance = $surplusBefore - $totalDepreciation;
			} elseif($deficitBefore > 0) {
				$netBalance = -1 * ($deficitBefore + $totalDepreciation);
			}

			$output['aaData'][] = ['', '', '', ''];
            $output['aaData'][] = ['', '<div class="report-divider"></div>', '', ''];

            if($surplusBefore > 0) {
            	$output['aaData'][] = [
            		'',
            		'Surplus - Before Depreciation',
            		app_format_money($surplusBefore, $this->base_currency->name),
            		'',
            		'DT_RowAttr' => [
            			'style' => 'font-weight: bolder;',
            			'class' => 'surplus-row'
            		]
            	];
            } elseif($deficitBefore > 0) {
            	$output['aaData'][] = [
            		'',
            		'Deficit - Before Depreciation',
            		'',
            		app_format_money($deficitBefore, $this->base_currency->name),
            		'DT_RowAttr' => [
            			'style' => 'font-weight: bolder;',
            			'class' => 'deficit-row'
            		]
            	];
            }

            $output['aaData'][] = ['', '<div class="report-divider"></div>', '', ''];

            if($totalDepreciation > 0) {
            	$output['aaData'][] = [
            		'By',
            		'Depreciation',
            		'',
            		app_format_money($totalDepreciation, $this->base_currency->name),
            		'DT_RowAttr' => [
            			'style' => 'font-weight: bolder;'
            		]
            	];
            }

            $output['aaData'][] = ['', '', '', ''];
            $output['aaData'][] = ['', '<div class="report-divider"></div>', '', ''];

            if($netBalance > 0) {
            	$output['aaData'][] = [
            		'',
            		'Surplus - After Depreciation',
            		app_format_money($netBalance, $this->base_currency->name),
            		'',
            		'DT_RowAttr' => [
            			'style' => 'font-weight: bolder;',
            			'class' => 'surplus-row'
            		]
            	];
            } elseif($netBalance < 0) {
            	$output['aaData'][] = [
            		'',
            		'Deficit - After Depreciation',
            		'',
            		app_format_money(abs($netBalance), $this->base_currency->name),
            		'DT_RowAttr' => [
            			'style' => 'font-weight: bolder;',
            			'class' => 'deficit-row'
            		]
            	];
            }

            echo json_encode($output);
            die;
		}
	}

	public function asset_depreciation_report() {
		if($this->input->is_ajax_request()) {
			list($start_date, $end_date) = $this->get_report_start_end_dates();
			$startMonth = date('Y-m', strtotime($start_date));

			$latestAmountSql = "
				SELECT ad.category, ad.amount
				FROM " . db_prefix() . "asset_depreciation ad
				INNER JOIN (
					SELECT category, MAX(month_year) AS max_month
					FROM " . db_prefix() . "asset_depreciation
					WHERE month_year < " . $this->db->escape($startMonth) . "
					GROUP BY category
				) latest
					ON latest.category = ad.category
					AND latest.max_month = ad.month_year
			";

			$latestRateSql = "
				SELECT ad.category, ad.rate
				FROM " . db_prefix() . "asset_depreciation ad
				INNER JOIN (
					SELECT category, MAX(month_year) AS max_month
					FROM " . db_prefix() . "asset_depreciation
					GROUP BY category
				) latest
					ON latest.category = ad.category
					AND latest.max_month = ad.month_year
			";

			$select = [
				't.asset_category',
				'SUM(CASE WHEN MONTH(t.date) BETWEEN 4 AND 9 THEN t.received_amount ELSE 0 END) AS first_half',
				'SUM(CASE WHEN MONTH(t.date) NOT BETWEEN 4 AND 9 THEN t.received_amount ELSE 0 END) AS second_half',
				'COALESCE(ad_amount.amount, 0) AS wd_value',
				'ad_rate.rate'
			];

			$join = [
				"LEFT JOIN ($latestAmountSql) ad_amount ON ad_amount.category = t.asset_category",
				"LEFT JOIN ($latestRateSql) ad_rate ON ad_rate.category = t.asset_category",
			];

			$where = [
				'AND t.type = 2',
				'AND t.received_amount IS NOT NULL',
				'AND t.received_amount != 0',
				'AND t.asset_category IS NOT NULL',
				"AND t.asset_category != ''"
			];

			$custom_date_select = $this->get_where_report_period('t.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $groupBy = 'GROUP BY t.asset_category, ad_amount.amount, ad_rate.rate';

            $result = data_tables_init($select, 't.asset_category', db_prefix() . 'transaction t', $join, $where, ['ad_amount.amount', 'ad_rate.rate'], $groupBy);
            $output = $result['output'];
            $rResult = $result['rResult'];

			$totals = [
				'wdv' => 0,
				'first' => 0,
				'second' => 0,
				'before' => 0,
				'dep_more' => 0,
				'dep_less' => 0,
				'dep_total' => 0,
				'closing' => 0,
			];

			foreach($rResult as $aRow) {
				$wdv = (float) $aRow['wd_value'];
				$first = (float) $aRow['first_half'];
				$second = (float) $aRow['second_half'];
				$rate = (float) $aRow['rate'];

				$total_before = $wdv + $first + $second;

				$dep_more = round(($wdv + $first) * ($rate / 100), 2);
				$dep_less = round($second * ($rate / 2 / 100), 2);

				$total_dep = $dep_more + $dep_less;
				$closing = $total_before - $total_dep;

				$totals['wdv'] += $wdv;
				$totals['first'] += $first;
				$totals['second'] += $second;
				$totals['before'] += $total_before;
				$totals['dep_more'] += $dep_more;
				$totals['dep_less'] += $dep_less;
				$totals['dep_total'] += $total_dep;
				$totals['closing'] += $closing;

				$output['aaData'][] = [
					html_escape($aRow['asset_category']),
					app_format_money($wdv, $this->base_currency->name),
					app_format_money($first, $this->base_currency->name),
					app_format_money($second, $this->base_currency->name),
					app_format_money($total_before, $this->base_currency->name),
					$rate . '%',
					app_format_money($dep_more, $this->base_currency->name),
					app_format_money($dep_less, $this->base_currency->name),
					app_format_money($total_dep, $this->base_currency->name),
					app_format_money($closing, $this->base_currency->name),
				];
			}

			$output['aaData'][] = [
				'Total',
				app_format_money($totals['wdv'], $this->base_currency->name),
				app_format_money($totals['first'], $this->base_currency->name),
				app_format_money($totals['second'], $this->base_currency->name),
				app_format_money($totals['before'], $this->base_currency->name),
				'',
				app_format_money($totals['dep_more'], $this->base_currency->name),
				app_format_money($totals['dep_less'], $this->base_currency->name),
				app_format_money($totals['dep_total'], $this->base_currency->name),
				app_format_money($totals['closing'], $this->base_currency->name),
				'DT_RowAttr' => [
					'style' => 'font-weight: bolder;'
				]
			];

			echo json_encode($output);
			die;
		}
	}

	public function balance_sheet_report()
	{
	    if ( ! $this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	 
	    $catTable     = db_prefix() . 'categories';
	    $txnTable     = db_prefix() . 'transaction';
	    $receiptTable = db_prefix() . 'receipts';
	    $paymentTable = db_prefix() . 'payments';
	    $bankTable    = db_prefix() . 'bank_accounts';
	    $deprTable    = db_prefix() . 'asset_depreciation';
	 
	    // =========================================================
	    // HELPER: Recursively collect all child category IDs
	    // =========================================================
	    $getChildCategoryIds = function ($parentIds) use (&$getChildCategoryIds, $catTable) {
	        if ( ! is_array($parentIds)) {
	            $parentIds = [$parentIds];
	        }
	        if (empty($parentIds)) {
	            return [];
	        }
	        $children = $this->db->select('id')
	            ->where_in('parent_id', $parentIds)
	            ->get($catTable)
	            ->result_array();
	 
	        $childIds = array_column($children, 'id');
	        if ( ! empty($childIds)) {
	            $childIds = array_unique(
	                array_merge($childIds, $getChildCategoryIds($childIds))
	            );
	        }
	        return $childIds;
	    };
	 
	    // =========================================================
	    // CATEGORY ID GROUPS
	    // =========================================================
	 
	    // Corpus = category 27 (Unrestricted funds) + children [28=Corpus, 29=Designated funds]
	    $corpusParentIds = [27];
	    $corpusChildIds  = $getChildCategoryIds($corpusParentIds);
	    $corpusAllIds    = array_unique(array_merge($corpusParentIds, $corpusChildIds));
	 
	    // Loan receipt categories: id=4 (Non-current liabilities) + all its children
	    $loanReceiptParentIds = [4];
	    $loanReceiptChildIds  = $getChildCategoryIds($loanReceiptParentIds);
	    $loanReceiptAllIds    = array_unique(array_merge($loanReceiptParentIds, $loanReceiptChildIds));
	 
	    // Loan payment category: id=16 (Loan Repayment) — direct payments table category
	    $loanPaymentIds = [16];
	 
	    // Asset categories: 197 (Non-current assets) + 198 (Current assets) + all children
	    $assetParentIds = [197, 198];
	    $assetChildIds  = $getChildCategoryIds($assetParentIds);
	    $assetAllIds    = array_unique(array_merge($assetParentIds, $assetChildIds));
	 
	    // Income categories for I&E (Surplus/Deficit): type=1, exclude corpus + loan receipt groups
	    $excludeFromIncome = array_unique(array_merge($corpusAllIds, $loanReceiptAllIds));
	    $incomeRows = $this->db->select('id')
	        ->where('type', 1)
	        ->where_not_in('id', $excludeFromIncome)
	        ->get($catTable)
	        ->result_array();
	    $incomeIds = array_column($incomeRows, 'id');
	 
	    // Expenditure categories for I&E: type=2, exclude loan repayment + asset categories
	    $excludeFromExpenditure = array_unique(array_merge($loanPaymentIds, $assetAllIds));
	    $expenditureRows = $this->db->select('id')
	        ->where('type', 2)
	        ->where_not_in('id', $excludeFromExpenditure)
	        ->get($catTable)
	        ->result_array();
	    $expenditureIds = array_column($expenditureRows, 'id');
	 
	    // =========================================================
	    // HELPER: SUM received_amount from tblreceipts
	    // =========================================================
	    $sumReceipts = function ($categoryIds, $dateFrom = null, $dateTo = null)
	        use ($receiptTable, $start_date, $end_date) {
	        if (empty($categoryIds)) {
	            return 0.0;
	        }
	        $from = $dateFrom ?? $start_date;
	        $to   = $dateTo   ?? $end_date;
	 
	        $row = $this->db->select_sum('received_amount')
	            ->where_in('category_id', $categoryIds)
	            ->where('date >=', $from)
	            ->where('date <=', $to)
	            ->get($receiptTable)
	            ->row();
	 
	        return (float) ($row->received_amount ?? 0);
	    };
	 
	    // =========================================================
	    // HELPER: SUM received_amount from tblpayments
	    // =========================================================
	    $sumPayments = function ($categoryIds, $dateFrom = null, $dateTo = null)
	        use ($paymentTable, $start_date, $end_date) {
	        if (empty($categoryIds)) {
	            return 0.0;
	        }
	        $from = $dateFrom ?? $start_date;
	        $to   = $dateTo   ?? $end_date;
	 
	        $row = $this->db->select_sum('received_amount')
	            ->where_in('category_id', $categoryIds)
	            ->where('date >=', $from)
	            ->where('date <=', $to)
	            ->get($paymentTable)
	            ->row();
	 
	        return (float) ($row->received_amount ?? 0);
	    };
	 
	    // =========================================================
	    // 1. CORPUS  (cumulative up to end_date — it's a permanent fund)
	    // =========================================================
	    $corpusAmount = $sumReceipts($corpusAllIds, '1900-01-01', $end_date);
	 
	    // =========================================================
	    // 2. LOANS  (net liability: all-time loan receipts minus all-time repayments)
	    // =========================================================
	    $loanReceiptTotal  = $sumReceipts($loanReceiptAllIds, '1900-01-01', $end_date);
	    $loanPaymentTotal  = $sumPayments($loanPaymentIds,    '1900-01-01', $end_date);
	    $loanAmount        = max(0, $loanReceiptTotal - $loanPaymentTotal);
	 
	    // =========================================================
	    // 3. INCOME & EXPENDITURE for current period (Surplus / Deficit)
	    // =========================================================
	    $periodIncome      = $sumReceipts($incomeIds);       // date between start and end
	    $periodExpenditure = $sumPayments($expenditureIds);  // date between start and end
	 
	    // =========================================================
	    // 4. RECEIVABLES  (outstanding receipt balances within period)
	    // =========================================================
	    $receivableAmount = (float) ($this->db
	        ->select_sum('balance')
	        ->where('is_fully_received', 0)
	        ->where('balance !=', 0)
	        ->where('date >=', $start_date)
	        ->where('date <=', $end_date)
	        ->get($receiptTable)
	        ->row()->balance ?? 0);
	 
	    // =========================================================
	    // 5. PAYABLES  (outstanding payment balances within period)
	    // =========================================================
	    $payableAmount = (float) ($this->db
	        ->select_sum('balance')
	        ->where('is_fully_paid', 0)
	        ->where('balance !=', 0)
	        ->where('date >=', $start_date)
	        ->where('date <=', $end_date)
	        ->get($paymentTable)
	        ->row()->balance ?? 0);
	 
	    // =========================================================
	    // 6. FIXED ASSETS
	    //    a) Carry-forward: all asset payments BEFORE this period
	    //    b) Current period: asset payments within this period
	    //    Source: tblpayments WHERE category_id IN (assetAllIds)
	    // =========================================================
	    $assetCarryForward   = $sumPayments($assetAllIds, '1900-01-01',
	                                        date('Y-m-d', strtotime($start_date . ' -1 day')));
	    $assetCurrentPeriod  = $sumPayments($assetAllIds);
	    $assetGross          = $assetCarryForward + $assetCurrentPeriod;
	 
	    // =========================================================
	    // 7. DEPRECIATION  (WDV method, half-year convention)
	    // =========================================================
	    $startMonth = date('Y-m', strtotime($start_date));
	 
	    $latestAmountSql = "
	        SELECT ad.category, ad.amount
	        FROM {$deprTable} ad
	        INNER JOIN (
	            SELECT category, MAX(month_year) AS max_month
	            FROM {$deprTable}
	            WHERE month_year < " . $this->db->escape($startMonth) . "
	            GROUP BY category
	        ) latest
	            ON latest.category   = ad.category
	            AND latest.max_month = ad.month_year
	    ";
	 
	    $latestRateSql = "
	        SELECT ad.category, ad.rate
	        FROM {$deprTable} ad
	        INNER JOIN (
	            SELECT category, MAX(month_year) AS max_month
	            FROM {$deprTable}
	            GROUP BY category
	        ) latest
	            ON latest.category   = ad.category
	            AND latest.max_month = ad.month_year
	    ";
	 
	    $deprRows = $this->db->query("
	        SELECT
	            p.category_id,
	            SUM(
	                CASE WHEN MONTH(p.date) BETWEEN 4 AND 9
	                THEN p.received_amount ELSE 0 END
	            ) AS first_half,
	            SUM(
	                CASE WHEN MONTH(p.date) BETWEEN 10 AND 3
	                THEN p.received_amount ELSE 0 END
	            ) AS second_half,
	            COALESCE(ad_amount.amount, 0) AS wd_value,
	            COALESCE(ad_rate.rate,    0) AS rate
	        FROM {$paymentTable} p
	        LEFT JOIN ({$latestAmountSql}) ad_amount
	            ON ad_amount.category = p.category_id
	        LEFT JOIN ({$latestRateSql}) ad_rate
	            ON ad_rate.category = p.category_id
	        WHERE p.category_id IN (" . implode(',', array_map('intval', $assetAllIds)) . ")
	            AND p.received_amount IS NOT NULL
	            AND p.received_amount != 0
	            AND p.date >= " . $this->db->escape($start_date) . "
	            AND p.date <= " . $this->db->escape($end_date) . "
	        GROUP BY p.category_id
	    ")->result();
	 
	    $depreciation = 0.0;
	    foreach ($deprRows as $row) {
	        $wdvDep        = (($row->wd_value + $row->first_half) * $row->rate) / 100;
	        $halfDep       = ($row->second_half * ($row->rate / 2)) / 100;
	        $depreciation += round($wdvDep + $halfDep, 2);
	    }
	 
	    // Net fixed assets after depreciation (only current-period depreciation deducted)
	    $assetAmount = $assetGross - $depreciation;
	 
	    // =========================================================
	    // 8. SURPLUS / DEFICIT for current period
	    // =========================================================
	    $totalIncome      = $periodIncome + $receivableAmount;
	    $totalExpenditure = $periodExpenditure + $payableAmount;
	 
	    $net = ['surplus' => 0.0, 'deficit' => 0.0];
	    if ($totalIncome >= $totalExpenditure) {
	        $net['surplus'] = ($totalIncome - $totalExpenditure) - $depreciation;
	    } else {
	        $net['deficit'] = ($totalExpenditure - $totalIncome) + $depreciation;
	    }
	 
	    // =========================================================
	    // 9. GENERAL FUND  (cumulative surplus/deficit before this period)
	    // =========================================================
	    $general = $this->get_general_fund_amount($start_date);
	 
	    // =========================================================
	    // 10. CASH & BANK BALANCES
	    //     For each bank account: get the LATEST total_balance
	    //     from tbltransaction where date <= end_date.
	    //     Falls back to opening_balance if no transaction exists yet.
	    // =========================================================
	    $bankAccounts = $this->db
	        ->select('id, bank_name, opening_balance')
	        ->get($bankTable)
	        ->result();
	 
	    $bankBalances = [];
	    foreach ($bankAccounts as $account) {
	        // Latest total_balance for this account up to end_date
	        $latestTxn = $this->db->query("
	            SELECT total_balance
	            FROM {$txnTable}
	            WHERE account_id = " . (int) $account->id . "
	              AND date <= " . $this->db->escape($end_date) . "
	            ORDER BY date DESC, id DESC
	            LIMIT 1
	        ")->row();
	 
	        $balance = $latestTxn
	            ? (float) $latestTxn->total_balance
	            : (float) $account->opening_balance;
	 
	        if ($balance != 0) {
	            $bankBalances[] = [
	                'name'    => $account->bank_name,
	                'balance' => $balance,
	            ];
	        }
	    }
	 
	    // =========================================================
	    // BUILD OUTPUT
	    // =========================================================
	    $totalLiability = 0.0;
	    $totalAsset     = 0.0;
	    $rowCount       = 0;
	    $output         = ['aaData' => []];
	 
	    // ---------------------------------------------------------
	    // LIABILITIES HEADER
	    // ---------------------------------------------------------
	    $output['aaData'][] = [
	        '',
	        'Liabilities',
	        '',
	        'DT_RowAttr' => ['style' => 'text-align:center;font-weight:bolder;font-size:18px;'],
	    ];
	 
	    // ---------------------------------------------------------
	    // CORPUS
	    // ---------------------------------------------------------
	    if ($corpusAmount > 0) {
	        $totalLiability    += $corpusAmount;
	        $output['aaData'][] = [
	            'Corpus',
	            app_format_money($corpusAmount, $this->base_currency->name),
	            '',
	        ];
	        $rowCount++;
	    }
	 
	    // ---------------------------------------------------------
	    // GENERAL FUND  (carried-forward accumulated surplus/deficit)
	    // ---------------------------------------------------------
	    $generalSurplus = (float) ($general['surplus'] ?? 0);
	    $generalDeficit = (float) ($general['deficit'] ?? 0);
	 
	    if ($generalSurplus > 0 || $generalDeficit > 0) {
	        // Surplus adds to fund; deficit reduces it
	        $generalFund     = $generalSurplus > 0 ? $generalSurplus : -$generalDeficit;
	        $totalLiability += $generalFund;
	        $output['aaData'][] = [
	            'General Fund',
	            app_format_money($generalFund, $this->base_currency->name),
	            '',
	        ];
	        $rowCount++;
	    }
	 
	    // ---------------------------------------------------------
	    // SURPLUS & DEFICIT  (current period I&E result)
	    // ---------------------------------------------------------
	    // if ($net['surplus'] > 0 || $net['deficit'] > 0) {
	    //     $netBalance      = $net['surplus'] > 0 ? $net['surplus'] : $net['deficit'];
	    //     $totalLiability += $netBalance;
	    //     $label           = $net['surplus'] > 0 ? 'Surplus' : 'Deficit';
	    //     $output['aaData'][] = [
	    //         'Surplus & Deficit (' . $label . ')',
	    //         app_format_money($netBalance, $this->base_currency->name),
	    //         '',
	    //     ];
	    //     $rowCount++;
	    // }
	    if($net['surplus'] > 0 || $net['deficit'] > 0) {
	    	if($net['surplus'] > 0) {
	    		$netBalance = $net['surplus'];
	    		$totalLiability += $netBalance;
	    		$output['aaData'][] = [
	    			'Surplus & Deficit (Surplus)',
	    			app_format_money($netBalance, $this->base_currency->name),
	    			'',
	    		];
	    	} else {
	    		$netBalance = $net['deficit'];
	    		$totalLiability -= $netBalance;
	    		$output['aaData'][] = [
	    			'Surplus & Deficit (Deficit)',
	    			app_format_money($netBalance, $this->base_currency->name),
	    			'',
	    		];
	    	}
	    }
	 
	    // ---------------------------------------------------------
	    // LOANS
	    // ---------------------------------------------------------
	    if ($loanAmount > 0) {
	        $totalLiability    += $loanAmount;
	        $output['aaData'][] = [
	            'Loans',
	            app_format_money($loanAmount, $this->base_currency->name),
	            '',
	        ];
	        $rowCount++;
	    }
	 
	    // ---------------------------------------------------------
	    // PAYABLES
	    // ---------------------------------------------------------
	    if ($payableAmount > 0) {
	        $totalLiability    += $payableAmount;
	        $output['aaData'][] = [
	            'Payables',
	            app_format_money($payableAmount, $this->base_currency->name),
	            '',
	        ];
	        $rowCount++;
	    }
	 
	    // Spacer rows between Liabilities and Assets
	    $output['aaData'][] = ['', '', ''];
	    $output['aaData'][] = ['', '', ''];
	 
	    // ---------------------------------------------------------
	    // ASSETS HEADER
	    // ---------------------------------------------------------
	    $output['aaData'][] = [
	        '',
	        'Assets',
	        '',
	        'DT_RowAttr' => ['style' => 'text-align:center;font-weight:bolder;font-size:18px;'],
	    ];
	 
	    // ---------------------------------------------------------
	    // FIXED ASSETS  (prior carry-forward + current, net of depreciation)
	    // ---------------------------------------------------------
	    if ($assetAmount > 0) {
	        $totalAsset        += $assetAmount;
	        $output['aaData'][] = [
	            'Fixed Assets (Net of Depreciation)',
	            '',
	            app_format_money($assetAmount, $this->base_currency->name),
	        ];
	        $rowCount++;
	    }
	 
	    // ---------------------------------------------------------
	    // RECEIVABLES
	    // ---------------------------------------------------------
	    if ($receivableAmount > 0) {
	        $totalAsset        += $receivableAmount;
	        $output['aaData'][] = [
	            'Receivables',
	            '',
	            app_format_money($receivableAmount, $this->base_currency->name),
	        ];
	        $rowCount++;
	    }
	 
	    // ---------------------------------------------------------
	    // CASH & BANK BALANCES  (latest total_balance per account)
	    // ---------------------------------------------------------
	    foreach ($bankBalances as $bank) {
	        $totalAsset        += $bank['balance'];
	        $output['aaData'][] = [
	            $bank['name'],
	            '',
	            app_format_money($bank['balance'], $this->base_currency->name),
	        ];
	        $rowCount++;
	    }
	 
	    // ---------------------------------------------------------
	    // TOTAL ROW
	    // ---------------------------------------------------------
	    $output['aaData'][] = ['<div class="report-divider"></div>', '<div class="report-divider"></div>', ''];
	 
	    $output['aaData'][] = [
	        'Total',
	        app_format_money($totalLiability, $this->base_currency->name),
	        app_format_money($totalAsset,     $this->base_currency->name),
	        'DT_RowAttr' => ['style' => 'font-weight:bolder;'],
	    ];
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => $rowCount,
	        'recordsFiltered' => $rowCount,
	        'data'            => $output['aaData'],
	    ]);
	 
	    die;
	}

	private function build_balance_sheet_report_new_data($start_date, $end_date, $prev_start, $prev_end) {
		$catTable     = db_prefix() . 'categories';
	    $txnTable     = db_prefix() . 'transaction';
	    $receiptTable = db_prefix() . 'receipts';
	    $paymentTable = db_prefix() . 'payments';
	    $bankTable    = db_prefix() . 'bank_accounts';

	    // =========================================================
	    // LOAD CATEGORY TREE
	    // =========================================================

	    $categories = $this->db->get($catTable)->result_array();

	    $catMap = [];
	    $tree   = [];

	    foreach ($categories as $cat) {

	        $catMap[$cat['id']] = $cat;

	        $tree[$cat['parent_id']][] = $cat['id'];
	    }

	    $fmt = 'formatAmount';

	    // =========================================================
	    // HELPER : GET ALL CHILD IDS
	    // =========================================================

	    $allIds = function ($parentId)
	    use (&$allIds, $tree) {

	        $ids = [$parentId];

	        if (!empty($tree[$parentId])) {

	            foreach ($tree[$parentId] as $childId) {

	                $ids = array_merge(
	                    $ids,
	                    $allIds($childId)
	                );
	            }
	        }

	        return array_unique($ids);
	    };

	    // =========================================================
	    // RECEIPT TOTAL
	    // =========================================================

	    $sumReceipts = function (
		    $categoryIds,
		    $toDate
		) use ($receiptTable) {

		    if (empty($categoryIds)) {
		        return 0;
		    }

		    $this->db->select_sum('received_amount');

		    $this->db->group_start();

		    $this->db->or_where_in(
		        'category_id',
		        $categoryIds
		    );

		    if ($this->db->field_exists('sub_category_id', $receiptTable)) {

		        $this->db->or_where_in(
		            'sub_category_id',
		            $categoryIds
		        );
		    }

		    if ($this->db->field_exists('sub_sub_category_id', $receiptTable)) {

		        $this->db->or_where_in(
		            'sub_sub_category_id',
		            $categoryIds
		        );
		    }

		    if ($this->db->field_exists('sub_sub_sub_category_id', $receiptTable)) {

		        $this->db->or_where_in(
		            'sub_sub_sub_category_id',
		            $categoryIds
		        );
		    }

		    $this->db->group_end();

		    $this->db->where('date <=', $toDate);

		    $row = $this->db
		        ->get($receiptTable)
		        ->row();

		    return (float) ($row->received_amount ?? 0);
		};

	    // =========================================================
	    // PAYMENT TOTAL
	    // =========================================================

	    $sumPayments = function (
	        $categoryIds,
	        $toDate
	    ) use ($paymentTable) {

	        if (empty($categoryIds)) {
	            return 0;
	        }

	        $this->db->select_sum('received_amount');

	        $this->db->group_start();

	        $this->db->or_where_in(
	            'category_id',
	            $categoryIds
	        );

	        $this->db->or_where_in(
	            'sub_category_id',
	            $categoryIds
	        );

	        $this->db->or_where_in(
	            'sub_sub_category_id',
	            $categoryIds
	        );

	        $this->db->or_where_in(
	            'sub_sub_sub_category_id',
	            $categoryIds
	        );

	        $this->db->group_end();

	        $this->db->where('date <=', $toDate);

	        $row = $this->db
	            ->get($paymentTable)
	            ->row();

	        return (float) ($row->received_amount ?? 0);
	    };

	    // =========================================================
	    // PAYABLES
	    // =========================================================

	    $payableBalance = function ($toDate)
	    use ($paymentTable) {

	        $row = $this->db
	            ->select_sum('balance')
	            ->where('is_fully_paid', 0)
	            ->where('balance !=', 0)
	            ->where('date <=', $toDate)
	            ->get($paymentTable)
	            ->row();

	        return (float) ($row->balance ?? 0);
	    };

	    // =========================================================
	    // RECEIVABLES
	    // =========================================================

	    $receivableBalance = function ($toDate)
	    use ($receiptTable) {

	        $row = $this->db
	            ->select_sum('balance')
	            ->where('is_fully_received', 0)
	            ->where('balance !=', 0)
	            ->where('date <=', $toDate)
	            ->get($receiptTable)
	            ->row();

	        return (float) ($row->balance ?? 0);
	    };

	    // =========================================================
	    // CASH & BANK BALANCE
	    // =========================================================

	    $cashBankBalance = function ($upToDate)
	    use ($txnTable, $bankTable) {

	        $accounts = $this->db
	            ->select('id, bank_name, opening_balance')
	            ->get($bankTable)
	            ->result();

	        $total = 0;

	        foreach ($accounts as $acc) {

	            $row = $this->db->query("
	                SELECT total_balance
	                FROM {$txnTable}
	                WHERE account_id = {$acc->id}
	                AND date <= " . $this->db->escape($upToDate) . "
	                ORDER BY date DESC, id DESC
	                LIMIT 1
	            ")->row();

	            if ($row) {

	                $total += (float) $row->total_balance;

	            } else {

	                $total += (float) $acc->opening_balance;
	            }
	        }

	        return $total;
	    };

	    // =========================================================
		// NPO FUNDS
		// =========================================================

		// UNRESTRICTED

		$unrestricted_ids = $allIds(27);

		// RESTRICTED

		$restricted_ids = $allIds(30);

	    // =========================================================
		// LONG TERM BORROWINGS
		// =========================================================

		$long_term_ids = array_diff(

		    $allIds(4),

		    // REMOVE RESTRICTED FUND TREE
		    $allIds(30),

		    // REMOVE DESIGNATED FUNDS
		    $allIds(29)
		);

	    // LOAN REPAYMENT CATEGORY

	    $loan_payment_ids = [16];

	    // OTHER LIABILITIES

	    $other_long_ids = $allIds(61);
	    $provision_ids  = $allIds(62);

	    // CURRENT LIABILITIES

	    $short_term_ids = array_diff(

		    $allIds(64),

		    $allIds(30),

		    $allIds(29)
		);

	    $other_short_ids = $allIds(65);
	    $short_prov_ids  = $allIds(66);

	    // ASSETS

	    $ppe_ids             = $allIds(17);
	    $non_current_inv_ids = $allIds(116);
	    $lt_loan_ids         = $allIds(120);
	    $other_nca_ids       = $allIds(147);

	    $current_inv_ids = $allIds(150);
	    $st_loan_ids     = $allIds(154);
	    $other_ca_ids    = $allIds(181);

	    // =========================================================
		// NPO FUNDS (MATCH NOTES - 3)
		// =========================================================

		// ---------------------------------------------------------
		// RECEIPTS BETWEEN DATES
		// ---------------------------------------------------------

		$sumReceiptsBetween = function (
		    $catIds,
		    $from,
		    $to
		) use ($receiptTable) {

		    if (empty($catIds)) {
		        return 0;
		    }

		    $this->db->select_sum('received_amount');

		    $this->db->group_start();

		    $this->db->or_where_in(
		        'category_id',
		        $catIds
		    );

		    if ($this->db->field_exists('sub_category_id', $receiptTable)) {

		        $this->db->or_where_in(
		            'sub_category_id',
		            $catIds
		        );
		    }

		    if ($this->db->field_exists('sub_sub_category_id', $receiptTable)) {

		        $this->db->or_where_in(
		            'sub_sub_category_id',
		            $catIds
		        );
		    }

		    if ($this->db->field_exists('sub_sub_sub_category_id', $receiptTable)) {

		        $this->db->or_where_in(
		            'sub_sub_sub_category_id',
		            $catIds
		        );
		    }

		    $this->db->group_end();

		    $this->db->where('date >=', $from);

		    $this->db->where('date <=', $to);

		    $row = $this->db
		        ->get($receiptTable)
		        ->row();

		    return (float) ($row->received_amount ?? 0);
		};

		// ---------------------------------------------------------
		// UTILISED BETWEEN DATES
		// ---------------------------------------------------------

		$sumUtilisedBetween = function (
		    $catIds,
		    $from,
		    $to
		) use ($paymentTable) {

		    if (empty($catIds)) {
		        return 0;
		    }

		    $this->db->select_sum('received_amount');

		    $this->db->group_start();

		    $this->db->or_where_in(
		        'category_id',
		        $catIds
		    );

		    $this->db->or_where_in(
		        'sub_category_id',
		        $catIds
		    );

		    $this->db->or_where_in(
		        'sub_sub_category_id',
		        $catIds
		    );

		    $this->db->or_where_in(
		        'sub_sub_sub_category_id',
		        $catIds
		    );

		    // if ($this->db->field_exists('receipt_category_id', $paymentTable)) {

		    //     $this->db->or_where_in(
		    //         'receipt_category_id',
		    //         $catIds
		    //     );
		    // }

		    $this->db->group_end();

		    $this->db->where('date >=', $from);

		    $this->db->where('date <=', $to);

		    $row = $this->db
		        ->get($paymentTable)
		        ->row();

		    return (float) ($row->received_amount ?? 0);
		};

		// ---------------------------------------------------------
		// OPENING BALANCE
		// ---------------------------------------------------------

		$getOpeningBalance = function (
		    $catIds,
		    $startDate
		) use (
		    $sumReceiptsBetween,
		    $sumUtilisedBetween
		) {

		    $dayBefore = date(
		        'Y-m-d',
		        strtotime($startDate . ' -1 day')
		    );

		    $received = $sumReceiptsBetween(
		        $catIds,
		        '1900-01-01',
		        $dayBefore
		    );

		    $utilised = $sumUtilisedBetween(
		        $catIds,
		        '1900-01-01',
		        $dayBefore
		    );

		    return $received - $utilised;
		};

		// ---------------------------------------------------------
		// GENERAL FUND VALUE
		// ---------------------------------------------------------

		$generalFundValue = function ($gf) {

		    return ($gf['surplus'] > 0)
		        ? (float) $gf['surplus']
		        : -(float) $gf['deficit'];
		};

		// =========================================================
		// CURRENT YEAR GENERAL FUND
		// =========================================================

		$gf_cy_opening_raw = $this->get_general_fund_amount($start_date);

		$gf_cy_full_raw = $this->get_general_fund_amount(
		    date('Y-m-d', strtotime($end_date . ' +1 day'))
		);

		$gf_cy_opening = $generalFundValue($gf_cy_opening_raw);

		$gf_cy_full = $generalFundValue($gf_cy_full_raw);

		$gf_cy_received = $gf_cy_full - $gf_cy_opening;

		$gf_cy_closing =
		    $gf_cy_opening +
		    $gf_cy_received;

		// =========================================================
		// PREVIOUS YEAR GENERAL FUND
		// =========================================================

		$gf_py_opening_raw = $this->get_general_fund_amount($prev_start);

		$gf_py_full_raw = $this->get_general_fund_amount(
		    date('Y-m-d', strtotime($prev_end . ' +1 day'))
		);

		$gf_py_opening = $generalFundValue($gf_py_opening_raw);

		$gf_py_full = $generalFundValue($gf_py_full_raw);

		$gf_py_received = $gf_py_full - $gf_py_opening;

		$gf_py_closing =
		    $gf_py_opening +
		    $gf_py_received;

		// =========================================================
		// UNRESTRICTED FUNDS
		// =========================================================

		$unrestricted_opening_current =
		    $getOpeningBalance(
		        $unrestricted_ids,
		        $start_date
		    );

		$unrestricted_received_current =
		    $sumReceiptsBetween(
		        $unrestricted_ids,
		        $start_date,
		        $end_date
		    );

		$unrestricted_utilised_current =
		    $sumUtilisedBetween(
		        $unrestricted_ids,
		        $start_date,
		        $end_date
		    );

		$unrestricted_current =
		    $unrestricted_opening_current +
		    $unrestricted_received_current -
		    $unrestricted_utilised_current;

		// =========================================================
		// RESTRICTED FUNDS
		// =========================================================

		$restricted_opening_current =
		    $getOpeningBalance(
		        $restricted_ids,
		        $start_date
		    );

		$restricted_received_current =
		    $sumReceiptsBetween(
		        $restricted_ids,
		        $start_date,
		        $end_date
		    );

		$restricted_utilised_current =
		    $sumUtilisedBetween(
		        $restricted_ids,
		        $start_date,
		        $end_date
		    );

		$restricted_current =
		    $restricted_opening_current +
		    $restricted_received_current -
		    $restricted_utilised_current;

		// =========================================================
		// PREVIOUS YEAR VALUES
		// =========================================================

		$unrestricted_opening_previous =
		    $getOpeningBalance(
		        $unrestricted_ids,
		        $prev_start
		    );

		$unrestricted_received_previous =
		    $sumReceiptsBetween(
		        $unrestricted_ids,
		        $prev_start,
		        $prev_end
		    );

		$unrestricted_utilised_previous =
		    $sumUtilisedBetween(
		        $unrestricted_ids,
		        $prev_start,
		        $prev_end
		    );

		$unrestricted_previous =
		    $unrestricted_opening_previous +
		    $unrestricted_received_previous -
		    $unrestricted_utilised_previous;

		// ---------------------------------------------------------

		$restricted_opening_previous =
		    $getOpeningBalance(
		        $restricted_ids,
		        $prev_start
		    );

		$restricted_received_previous =
		    $sumReceiptsBetween(
		        $restricted_ids,
		        $prev_start,
		        $prev_end
		    );

		$restricted_utilised_previous =
		    $sumUtilisedBetween(
		        $restricted_ids,
		        $prev_start,
		        $prev_end
		    );

		$restricted_previous =
		    $restricted_opening_previous +
		    $restricted_received_previous -
		    $restricted_utilised_previous;

	    // =========================================================
	    // LONG TERM BORROWINGS
	    // =========================================================

	    $loan_received_current =
	        $sumReceipts(
	            $long_term_ids,
	            $end_date
	        );

	    $loan_repaid_current =
	        $sumPayments(
	            $loan_payment_ids,
	            $end_date
	        );

	    $long_term_current =
	        $loan_received_current -
	        $loan_repaid_current;

	    if ($long_term_current < 0) {
	        $long_term_current = 0;
	    }

	    // PREVIOUS

	    $loan_received_previous =
	        $sumReceipts(
	            $long_term_ids,
	            $prev_end
	        );

	    $loan_repaid_previous =
	        $sumPayments(
	            $loan_payment_ids,
	            $prev_end
	        );

	    $long_term_previous =
	        $loan_received_previous -
	        $loan_repaid_previous;

	    if ($long_term_previous < 0) {
	        $long_term_previous = 0;
	    }

	    // =========================================================
	    // OTHER LONG TERM LIABILITIES
	    // =========================================================

	    $other_long_current =
	        $sumReceipts(
	            $other_long_ids,
	            $end_date
	        );

	    $other_long_previous =
	        $sumReceipts(
	            $other_long_ids,
	            $prev_end
	        );

	    // =========================================================
	    // LONG TERM PROVISIONS
	    // =========================================================

	    $provision_current =
	        $sumReceipts(
	            $provision_ids,
	            $end_date
	        );

	    $provision_previous =
	        $sumReceipts(
	            $provision_ids,
	            $prev_end
	        );

	    // =========================================================
	    // SHORT TERM BORROWINGS
	    // =========================================================

	    $short_term_received_current =
	        $sumReceipts(
	            $short_term_ids,
	            $end_date
	        );

	    $short_term_paid_current =
	        $sumPayments(
	            $short_term_ids,
	            $end_date
	        );

	    $short_term_current =
	        $short_term_received_current -
	        $short_term_paid_current;

	    if ($short_term_current < 0) {
	        $short_term_current = 0;
	    }

	    // PREVIOUS

	    $short_term_received_previous =
	        $sumReceipts(
	            $short_term_ids,
	            $prev_end
	        );

	    $short_term_paid_previous =
	        $sumPayments(
	            $short_term_ids,
	            $prev_end
	        );

	    $short_term_previous =
	        $short_term_received_previous -
	        $short_term_paid_previous;

	    if ($short_term_previous < 0) {
	        $short_term_previous = 0;
	    }

	    // =========================================================
	    // OTHER CURRENT LIABILITIES
	    // =========================================================

	    $other_short_current =
	        $sumReceipts(
	            $other_short_ids,
	            $end_date
	        );

	    $other_short_previous =
	        $sumReceipts(
	            $other_short_ids,
	            $prev_end
	        );

	    // =========================================================
	    // SHORT TERM PROVISIONS
	    // =========================================================

	    $short_prov_current =
	        $sumReceipts(
	            $short_prov_ids,
	            $end_date
	        );

	    $short_prov_previous =
	        $sumReceipts(
	            $short_prov_ids,
	            $prev_end
	        );

	    // =========================================================
	    // PAYABLES
	    // =========================================================

	    $payable_current =
	        $payableBalance($end_date);

	    $payable_previous =
	        $payableBalance($prev_end);

	    // =========================================================
	    // TOTAL LIABILITIES
	    // =========================================================

	    $restricted_current  = $gf_cy_closing;
		$restricted_previous = $gf_py_closing;

		$npo_total_current =
		    $unrestricted_current +
		    $restricted_current;

		$npo_total_previous =
		    $unrestricted_previous +
		    $restricted_previous;

	    $ncl_current =
	        $long_term_current +
	        $other_long_current +
	        $provision_current;

	    $ncl_previous =
	        $long_term_previous +
	        $other_long_previous +
	        $provision_previous;

	    $cl_current =
	        $short_term_current +
	        $other_short_current +
	        $short_prov_current +
	        $payable_current;

	    $cl_previous =
	        $short_term_previous +
	        $other_short_previous +
	        $short_prov_previous +
	        $payable_previous;

	    // =========================================================
	    // ASSETS
	    // =========================================================

	    $asset_current =
	        $sumPayments(
	            $ppe_ids,
	            $end_date
	        );

	    $asset_previous =
	        $sumPayments(
	            $ppe_ids,
	            $prev_end
	        );

	    $non_investment_current =
	        $sumPayments(
	            $non_current_inv_ids,
	            $end_date
	        );

	    $non_investment_previous =
	        $sumPayments(
	            $non_current_inv_ids,
	            $prev_end
	        );

	    $lt_loan_current =
	        $sumPayments(
	            $lt_loan_ids,
	            $end_date
	        );

	    $lt_loan_previous =
	        $sumPayments(
	            $lt_loan_ids,
	            $prev_end
	        );

	    $other_nca_current =
	        $sumPayments(
	            $other_nca_ids,
	            $end_date
	        );

	    $other_nca_previous =
	        $sumPayments(
	            $other_nca_ids,
	            $prev_end
	        );

	    // CURRENT ASSETS

	    $cur_invest_current =
	        $sumPayments(
	            $current_inv_ids,
	            $end_date
	        );

	    $cur_invest_previous =
	        $sumPayments(
	            $current_inv_ids,
	            $prev_end
	        );

	    $st_loan_current =
	        $sumPayments(
	            $st_loan_ids,
	            $end_date
	        );

	    $st_loan_previous =
	        $sumPayments(
	            $st_loan_ids,
	            $prev_end
	        );

	    $other_ca_current =
	        $sumPayments(
	            $other_ca_ids,
	            $end_date
	        );

	    $other_ca_previous =
	        $sumPayments(
	            $other_ca_ids,
	            $prev_end
	        );

	    // RECEIVABLES

	    $receivable_current =
	        $receivableBalance($end_date);

	    $receivable_previous =
	        $receivableBalance($prev_end);

	    // BANK BALANCE

	    $bank_current =
	        $cashBankBalance($end_date);

	    $bank_previous =
	        $cashBankBalance($prev_end);

	    // =========================================================
	    // TOTAL ASSETS
	    // =========================================================

	    $nca_current =
	        $asset_current +
	        $non_investment_current +
	        $lt_loan_current +
	        $other_nca_current;

	    $nca_previous =
	        $asset_previous +
	        $non_investment_previous +
	        $lt_loan_previous +
	        $other_nca_previous;

	    $ca_current =
	        $cur_invest_current +
	        $st_loan_current +
	        $other_ca_current +
	        $receivable_current +
	        $bank_current;

	    $ca_previous =
	        $cur_invest_previous +
	        $st_loan_previous +
	        $other_ca_previous +
	        $receivable_previous +
	        $bank_previous;

	    // =========================================================
	    // GRAND TOTALS
	    // =========================================================

	    $sources_total_current =
	        $npo_total_current +
	        $ncl_current +
	        $cl_current;

	    $sources_total_previous =
	        $npo_total_previous +
	        $ncl_previous +
	        $cl_previous;

	    $application_total_current =
	        $nca_current +
	        $ca_current;

	    $application_total_previous =
	        $nca_previous +
	        $ca_previous;

	    // =========================================================
	    // OUTPUT
	    // =========================================================

	    $output = ['aaData' => []];

	    // =========================================================
	    // SOURCES OF FUNDS
	    // =========================================================

	    $output['aaData'][] = [
	        'I',
	        'Sources of Funds',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:18px;'
	        ]
	    ];

	    // NPO FUNDS

	    $output['aaData'][] = [
	        '1',
	        'NPO Funds',
	        '3',
	        $fmt($npo_total_current),
	        $fmt($npo_total_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    $output['aaData'][] = [
	        '(a)',
	        'Unrestricted Funds',
	        '',
	        $fmt($unrestricted_current),
	        $fmt($unrestricted_previous),
	    ];

	    $output['aaData'][] = [
	        '(b)',
	        'Restricted Funds',
	        '',
	        $fmt($restricted_current),
	        $fmt($restricted_previous),
	    ];

	    $output['aaData'][] = [
	        '',
	        '',
	        '',
	        $fmt($npo_total_current),
	        $fmt($npo_total_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;'
	        ]
	    ];

	    $output['aaData'][] = ['', '', '', '', ''];

	    // =========================================================
	    // NON CURRENT LIABILITIES
	    // =========================================================

	    $output['aaData'][] = [
	        '2',
	        'Non-current liabilities',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    $output['aaData'][] = [
	        '(a)',
	        'Long-term borrowings',
	        '4',
	        $fmt($long_term_current),
	        $fmt($long_term_previous),
	    ];

	    $output['aaData'][] = [
	        '(b)',
	        'Other long-term liabilities',
	        '5',
	        $fmt($other_long_current),
	        $fmt($other_long_previous),
	    ];

	    $output['aaData'][] = [
	        '(c)',
	        'Long-term provisions',
	        '6',
	        $fmt($provision_current),
	        $fmt($provision_previous),
	    ];

	    $output['aaData'][] = [
	        '',
	        '',
	        '',
	        $fmt($ncl_current),
	        $fmt($ncl_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;'
	        ]
	    ];

	    $output['aaData'][] = ['', '', '', '', ''];

	    // =========================================================
	    // CURRENT LIABILITIES
	    // =========================================================

	    $output['aaData'][] = [
	        '3',
	        'Current liabilities',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    $output['aaData'][] = [
	        '(a)',
	        'Short-term borrowings',
	        '4',
	        $fmt($short_term_current),
	        $fmt($short_term_previous),
	    ];

	    $output['aaData'][] = [
	        '(b)',
	        'Payables',
	        '7',
	        $fmt($payable_current),
	        $fmt($payable_previous),
	    ];

	    $output['aaData'][] = [
	        '(c)',
	        'Other current liabilities',
	        '8',
	        $fmt($other_short_current),
	        $fmt($other_short_previous),
	    ];

	    $output['aaData'][] = [
	        '(d)',
	        'Short-term provisions',
	        '6',
	        $fmt($short_prov_current),
	        $fmt($short_prov_previous),
	    ];

	    $output['aaData'][] = [
	        '',
	        '',
	        '',
	        $fmt($cl_current),
	        $fmt($cl_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;'
	        ]
	    ];

	    // TOTAL SOURCES

	    $output['aaData'][] = [
	        '',
	        'Total',
	        '',
	        $fmt($sources_total_current),
	        $fmt($sources_total_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    $output['aaData'][] = ['', '', '', '', ''];

	    // =========================================================
	    // APPLICATION OF FUNDS
	    // =========================================================

	    $output['aaData'][] = [
	        'II',
	        'Application of Funds',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:18px;'
	        ]
	    ];

	    // NON CURRENT ASSETS

	    $output['aaData'][] = [
	        '1',
	        'Non-current assets',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    $output['aaData'][] = [
	        '(a)',
	        'Property, Plant and Equipment and Intangible assets',
	        '9',
	        $fmt($asset_current),
	        $fmt($asset_previous),
	    ];

	    $output['aaData'][] = [
	        '(b)',
	        'Non-current investments',
	        '10',
	        $fmt($non_investment_current),
	        $fmt($non_investment_previous),
	    ];

	    $output['aaData'][] = [
	        '(c)',
	        'Long Term Loans and Advances',
	        '11',
	        $fmt($lt_loan_current),
	        $fmt($lt_loan_previous),
	    ];

	    $output['aaData'][] = [
	        '(d)',
	        'Other non-current assets',
	        '12',
	        $fmt($other_nca_current),
	        $fmt($other_nca_previous),
	    ];

	    $output['aaData'][] = [
	        '',
	        '',
	        '',
	        $fmt($nca_current),
	        $fmt($nca_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;'
	        ]
	    ];

	    $output['aaData'][] = ['', '', '', '', ''];

	    // CURRENT ASSETS

	    $output['aaData'][] = [
	        '2',
	        'Current assets',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    $output['aaData'][] = [
	        '(a)',
	        'Current investments',
	        '9',
	        $fmt($cur_invest_current),
	        $fmt($cur_invest_previous),
	    ];

	    $output['aaData'][] = [
	        '(b)',
	        'Inventories',
	        '',
	        '-',
	        '-',
	    ];

	    $output['aaData'][] = [
	        '(c)',
	        'Receivables',
	        '13',
	        $fmt($receivable_current),
	        $fmt($receivable_previous),
	    ];

	    $output['aaData'][] = [
	        '(d)',
	        'Cash and bank balances',
	        '14',
	        $fmt($bank_current),
	        $fmt($bank_previous),
	    ];

	    $output['aaData'][] = [
	        '(e)',
	        'Short Term Loans and Advances',
	        '11',
	        $fmt($st_loan_current),
	        $fmt($st_loan_previous),
	    ];

	    $output['aaData'][] = [
	        '(f)',
	        'Other current assets',
	        '15',
	        $fmt($other_ca_current),
	        $fmt($other_ca_previous),
	    ];

	    $output['aaData'][] = [
	        '',
	        '',
	        '',
	        $fmt($ca_current),
	        $fmt($ca_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;'
	        ]
	    ];

	    // TOTAL APPLICATION

	    $output['aaData'][] = [
	        '',
	        'Total',
	        '',
	        $fmt($application_total_current),
	        $fmt($application_total_previous),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;'
	        ]
	    ];

	    return $output;
	}

	public function balance_sheet_report_new()
	{
	    if (!$this->input->is_ajax_request()) {
	        return;
	    }

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $rows = $this->build_balance_sheet_report_new_data($start_date, $end_date, $prev_start, $prev_end);

	    // =========================================================
	    // RESPONSE
	    // =========================================================

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows['aaData']),
	        'recordsFiltered' => count($rows['aaData']),
	        'aaData'          => $rows['aaData'],
	    ]);

	    die;
	}

	private function build_npo_funds_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$rec_table = db_prefix() . 'receipts';
	    $pay_table = db_prefix() . 'payments';
	    $cat_table = db_prefix() . 'categories';

	    // ---------------------------------------------------------
	    // CATEGORY TREE
	    // ---------------------------------------------------------
	    $categories = $this->db->get($cat_table)->result_array();

	    $catMap = [];
	    $tree   = [];

	    foreach ($categories as $cat) {
	        $catMap[$cat['id']] = $cat;
	        $tree[$cat['parent_id']][] = $cat['id'];
	    }

	    $fmt = 'formatAmount';

	    $fmtCell = function ($value) use ($fmt) {
	        return ($value == 0) ? ' - ' : $fmt($value);
	    };

	    // ---------------------------------------------------------
	    // GET ALL CHILD IDS
	    // ---------------------------------------------------------
	    $allIds = function ($parentId) use (&$allIds, $tree) {

	        $ids = [$parentId];

	        if (isset($tree[$parentId])) {
	            foreach ($tree[$parentId] as $childId) {
	                $ids = array_merge($ids, $allIds($childId));
	            }
	        }

	        return array_unique($ids);
	    };

	    // ---------------------------------------------------------
	    // SUM RECEIPTS
	    // ---------------------------------------------------------
	    $sumReceipts = function ($catIds, $from, $to) use ($rec_table) {

	        if (empty($catIds)) {
	            return 0;
	        }

	        $row = $this->db
	            ->select_sum('received_amount')
	            ->where_in('category_id', $catIds)
	            ->where('date >=', $from)
	            ->where('date <=', $to)
	            ->get($rec_table)
	            ->row();

	        return (float) ($row->received_amount ?? 0);
	    };

	    // ---------------------------------------------------------
	    // SUM UTILISED
	    // ---------------------------------------------------------
	    $sumUtilised = function ($catIds, $from, $to) use ($pay_table) {

	        if (empty($catIds)) {
	            return 0;
	        }

	        $row = $this->db
	            ->select_sum('received_amount')
	            ->where_in('receipt_category_id', $catIds)
	            ->where('date >=', $from)
	            ->where('date <=', $to)
	            ->get($pay_table)
	            ->row();

	        return (float) ($row->received_amount ?? 0);
	    };

	    // ---------------------------------------------------------
	    // OPENING BALANCE
	    // ---------------------------------------------------------
	    $openingBalance = function ($catIds, $startDate)
	        use ($sumReceipts, $sumUtilised) {

	        $dayBefore = date('Y-m-d', strtotime($startDate . ' -1 day'));

	        $received = $sumReceipts(
	            $catIds,
	            '1900-01-01',
	            $dayBefore
	        );

	        $utilised = $sumUtilised(
	            $catIds,
	            '1900-01-01',
	            $dayBefore
	        );

	        return $received - $utilised;
	    };

	    // ---------------------------------------------------------
	    // FUND ROW
	    // ---------------------------------------------------------
	    $fundRow = function ($catIds, $from, $to)
	        use (
	            $sumReceipts,
	            $sumUtilised,
	            $openingBalance
	        ) {

	        $opening = $openingBalance($catIds, $from);

	        $received = $sumReceipts(
	            $catIds,
	            $from,
	            $to
	        );

	        $utilised = $sumUtilised(
	            $catIds,
	            $from,
	            $to
	        );

	        $closing = $opening + $received - $utilised;

	        return [
	            $opening,
	            $received,
	            $utilised,
	            $closing
	        ];
	    };

	    // ---------------------------------------------------------
	    // GENERAL FUND LOGIC
	    // ---------------------------------------------------------
	    $generalFundValue = function ($gf) {

	        return ($gf['surplus'] > 0)
	            ? (float) $gf['surplus']
	            : -(float) $gf['deficit'];
	    };

	    // CURRENT YEAR
	    $gf_cy_opening_raw = $this->get_general_fund_amount($start_date);

	    $gf_cy_full_raw = $this->get_general_fund_amount(
	        date('Y-m-d', strtotime($end_date . ' +1 day'))
	    );

	    $gf_cy_opening = $generalFundValue($gf_cy_opening_raw);

	    $gf_cy_full = $generalFundValue($gf_cy_full_raw);

	    $gf_cy_received = $gf_cy_full - $gf_cy_opening;

	    $gf_cy_utilised = 0;

	    $gf_cy_closing = $gf_cy_opening
	        + $gf_cy_received
	        - $gf_cy_utilised;

	    // PREVIOUS YEAR
	    $gf_py_opening_raw = $this->get_general_fund_amount($prev_start);

	    $gf_py_full_raw = $this->get_general_fund_amount(
	        date('Y-m-d', strtotime($prev_end . ' +1 day'))
	    );

	    $gf_py_opening = $generalFundValue($gf_py_opening_raw);

	    $gf_py_full = $generalFundValue($gf_py_full_raw);

	    $gf_py_received = $gf_py_full - $gf_py_opening;

	    $gf_py_utilised = 0;

	    $gf_py_closing = $gf_py_opening
	        + $gf_py_received
	        - $gf_py_utilised;

	    // ---------------------------------------------------------
	    // OUTPUT
	    // ---------------------------------------------------------
	    $output = ['aaData' => []];

	    $cy = [
	        'opening'  => 0,
	        'received' => 0,
	        'utilised' => 0,
	        'closing'  => 0,
	    ];

	    $py = [
	        'opening'  => 0,
	        'received' => 0,
	        'utilised' => 0,
	        'closing'  => 0,
	    ];

	    // ---------------------------------------------------------
	    // HEADER
	    // ---------------------------------------------------------
	    $output['aaData'][] = [
	        '3',
	        'NPO Funds',
	        '',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;font-size:16px;'
	        ],
	    ];

	    // =========================================================
	    // UNRESTRICTED FUNDS
	    // =========================================================
	    $output['aaData'][] = [
	        '(A)',
	        'Unrestricted Funds',
	        '',
	        '',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;font-size:14px;background:#fafafa;'
	        ],
	    ];

	    $i = 1;

	    foreach ($tree[27] ?? [] as $childId) {

	        // Insert General Fund as second row
	        if ($i == 2) {

	            $cy['opening'] += $gf_cy_opening;
	            $cy['received'] += $gf_cy_received;
	            $cy['utilised'] += $gf_cy_utilised;
	            $cy['closing'] += $gf_cy_closing;

	            $py['opening'] += $gf_py_opening;
	            $py['received'] += $gf_py_received;
	            $py['utilised'] += $gf_py_utilised;
	            $py['closing'] += $gf_py_closing;

	            $output['aaData'][] = [
	                $i++,
	                'General Fund',
	                $fmtCell($gf_cy_opening),
	                $fmtCell($gf_cy_received),
	                $fmtCell($gf_cy_utilised),
	                $fmtCell($gf_cy_closing),
	            ];
	        }

	        $ids = $allIds($childId);

	        list(
	            $cy_open,
	            $cy_rec,
	            $cy_util,
	            $cy_close
	        ) = $fundRow($ids, $start_date, $end_date);

	        list(
	            $py_open,
	            $py_rec,
	            $py_util,
	            $py_close
	        ) = $fundRow($ids, $prev_start, $prev_end);

	        $cy['opening'] += $cy_open;
	        $cy['received'] += $cy_rec;
	        $cy['utilised'] += $cy_util;
	        $cy['closing'] += $cy_close;

	        $py['opening'] += $py_open;
	        $py['received'] += $py_rec;
	        $py['utilised'] += $py_util;
	        $py['closing'] += $py_close;

	        $output['aaData'][] = [
	            $i++,
	            $catMap[$childId]['name'] ?? '-',
	            $fmtCell($cy_open),
	            $fmtCell($cy_rec),
	            $fmtCell($cy_util),
	            $fmtCell($cy_close),
	        ];
	    }

	    // =========================================================
		// RESTRICTED FUNDS
		// =========================================================

		$output['aaData'][] = [
		    '(B)',
		    'Restricted Funds',
		    '',
		    '',
		    '',
		    '',
		    'DT_RowAttr' => [
		        'style' => 'font-weight:bold;font-size:14px;background:#fafafa;'
		    ],
		];

		// ---------------------------------------------------------
		// GET ALL RESTRICTED FUND CHILD CATEGORIES
		// ---------------------------------------------------------

		$restrictedCategories = $this->db
		    ->where('parent_id', 30)
		    ->order_by('id', 'ASC')
		    ->get($cat_table)
		    ->result_array();

		$i = 1;

		// ---------------------------------------------------------
		// ALWAYS DISPLAY CATEGORY ROWS
		// ---------------------------------------------------------

		foreach ($restrictedCategories as $restrictedCat) {

		    $childId = $restrictedCat['id'];

		    $ids = $allIds($childId);

		    list(
		        $cy_open,
		        $cy_rec,
		        $cy_util,
		        $cy_close
		    ) = $fundRow($ids, $start_date, $end_date);

		    list(
		        $py_open,
		        $py_rec,
		        $py_util,
		        $py_close
		    ) = $fundRow($ids, $prev_start, $prev_end);

		    // -----------------------------------------------------
		    // ADD TO TOTALS
		    // -----------------------------------------------------

		    $cy['opening'] += $cy_open;
		    $cy['received'] += $cy_rec;
		    $cy['utilised'] += $cy_util;
		    $cy['closing'] += $cy_close;

		    $py['opening'] += $py_open;
		    $py['received'] += $py_rec;
		    $py['utilised'] += $py_util;
		    $py['closing'] += $py_close;

		    // -----------------------------------------------------
		    // DISPLAY ROW EVEN IF VALUES ARE ZERO
		    // -----------------------------------------------------

		    $output['aaData'][] = [
		        $i++,
		        $restrictedCat['name'],
		        $fmtCell($cy_open),
		        $fmtCell($cy_rec),
		        $fmtCell($cy_util),
		        $fmtCell($cy_close),
		    ];
		}

	    // =========================================================
	    // FINAL TOTALS
	    // =========================================================

	    $cy_total_closing = $cy['opening']
	        + $cy['received']
	        - $cy['utilised'];

	    $py_total_closing = $py['opening']
	        + $py['received']
	        - $py['utilised'];

	    $output['aaData'][] = [
	        '',
	        'Current Year',
	        $fmtCell($cy['opening']),
	        $fmtCell($cy['received']),
	        $fmtCell($cy['utilised']),
	        $fmtCell($cy_total_closing),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;border-top:1px solid #aaa;'
	        ],
	    ];

	    $output['aaData'][] = [
	        '',
	        'Previous Year',
	        $fmtCell($py['opening']),
	        $fmtCell($py['received']),
	        $fmtCell($py['utilised']),
	        $fmtCell($py_total_closing),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;border-top:1px solid #aaa;'
	        ],
	    ];

	    return $output['aaData'];
	}

	private function build_borrowings_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    $getOutstanding = function ($catId, $asOnDate) use ($CI) {

		    if (!$catId) {
		        return 0;
		    }

		    $CI->db->select_sum('received_amount');
		    $CI->db->group_start();
		    $CI->db->or_where('category_id', $catId);
		    $CI->db->or_where('sub_category_id', $catId);
		    $CI->db->or_where('sub_sub_category_id', $catId);
		    $CI->db->or_where('sub_sub_sub_category_id', $catId);

		    $CI->db->group_end();

		    $CI->db->where('date <=', $asOnDate);

		    $receiptTotal =
		        (float)(
		            $CI->db
		                ->get(db_prefix().'receipts')
		                ->row()
		                ->received_amount ?? 0
		        );

		    $receiptRows = $CI->db->query(
		        "SELECT id
		         FROM ".db_prefix()."receipts
		         WHERE date <= ?
		         AND (
		                category_id = ?
		             OR sub_category_id = ?
		             OR sub_sub_category_id = ?
		             OR sub_sub_sub_category_id = ?
		         )",
		        [
		            $asOnDate,
		            $catId,
		            $catId,
		            $catId,
		            $catId
		        ]
		    )->result_array();

		    if (empty($receiptRows)) {
		        return 0;
		    }

		    $receiptIds = array_column(
		        $receiptRows,
		        'id'
		    );

		    $placeholders = implode(
		        ',',
		        array_fill(
		            0,
		            count($receiptIds),
		            '?'
		        )
		    );

		    $repaid = $CI->db->query(
		        "SELECT
		            COALESCE(
		                SUM(received_amount),
		                0
		            ) total
		         FROM ".db_prefix()."payments
		         WHERE category_id = 16
		         AND receipt_id IN ($placeholders)
		         AND date <= ?",
		        array_merge(
		            $receiptIds,
		            [$asOnDate]
		        )
		    )->row()->total;

		    return max(
		        0,
		        $receiptTotal - $repaid
		    );
		};
	 
	    // Always show value or dash — never NA
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Fixed structure matching Excel Note 4 exactly
	    // [serial, label, ltId, stId, isGroup]
	    // All cells query DB — no NA anywhere
	    // ----------------------------------------------------------------
	    $sections = [
	        'Secured' => [
	            ['(a)',  'Term loans',                                               null, null, true ],
	            ['(i)',  'from banks',                                               69,   88,   false],
	            ['(ii)', 'from other parties',                                      70,   89,   false],
	            ['(b)',  'Loans repayable on demand',                                null, null, true ],
	            ['(i)',  'from banks',                                               71,   90,   false],
	            ['(ii)', 'from other parties',                                       72,   91,   false],
	            ['(c)',  'Deferred payment liabilities',                             73,   92,   false],
	            ['(d)',  'Loans and advances from related parties',                  74,   93,   false],
	            ['(e)',  'Long term/current maturities of finance lease obligation', 75,   94,   false],
	            ['(f)',  'Other loans advances',                                     76,   95,   false],
	        ],
	        'Unsecured' => [
	            ['(a)',  'Term loans',                                               null, null, true ],
	            ['(i)',  'from banks',                                               77,   96,   false],
	            ['(ii)', 'from other parties',                                       78,   97,   false],
	            ['(b)',  'Loans repayable on demand',                                null, null, true ],
	            ['(i)',  'from banks',                                               79,   98,  false],
	            ['(ii)', 'from other parties',                                       80,   99,  false],
	            ['(c)',  'Deferred payment liabilities',                             81,   100,  false],
	            ['(d)',  'Loans and advances from related parties',                  82,   101,  false],
	            ['(e)',  'Long term/current maturities of finance lease obligation', 83,   102,  false],
	            ['(f)',  'Other loans advances',                                     84,   103,  false],
	        ],
	    ];
	 
	    $output       = [];
	    $grandLtCY    = 0;
	    $grandLtPY    = 0;
	    $grandStCY    = 0;
	    $grandStPY    = 0;
	    $totalLabels  = ['A', 'B'];
	    $sectionIndex = 0;

	    $output[] = [
	    	'4',
	    	'Borrowings',
	    	'',
	    	'',
	    	'',
	    	'',
	    	'DT_RowAttr' => [
	    		'style' => 'font-weight: bolder; font-size: 16px;'
	    	]
	    ];
	 
	    foreach ($sections as $sectionName => $rows) {
	 
	        // Section header — Secured / Unsecured
	        $output[] = [
	            '', $sectionName, '', '', '', '',
	            'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:14px; text-decoration: underline;']
	        ];
	 
	        $secLtCY = 0;
	        $secLtPY = 0;
	        $secStCY = 0;
	        $secStPY = 0;
	 
	        foreach ($rows as $row) {
	            list($serial, $label, $ltId, $stId, $isGroup) = $row;
	 
	            if ($isGroup) {
	                // Sub-group header e.g. "(a) Term loans" — no amounts
	                $output[] = [
	                    $serial, $label, '', '', '', '',
	                    'DT_RowAttr' => ['style' => 'font-weight:bold;']
	                ];
	                continue;
	            }
	 
	            // Query DB for all 4 cells
	            $ltCY = $getOutstanding(
				    $ltId,
				    $end_date
				);

				$ltPY = $getOutstanding(
				    $ltId,
				    $prev_end
				);

				$stCY = $getOutstanding(
				    $stId,
				    $end_date
				);

				$stPY = $getOutstanding(
				    $stId,
				    $prev_end
				);
	 
	            $secLtCY += $ltCY;
	            $secLtPY += $ltPY;
	            $secStCY += $stCY;
	            $secStPY += $stPY;
	 
	            $output[] = [
	                $serial,
	                $label,
	                $fmtCell($ltCY),
	                $fmtCell($ltPY),
	                $fmtCell($stCY),
	                $fmtCell($stPY),
	            ];
	        }
	 
	        // Total (A) or Total (B)
	        $output[] = [
	            '',
	            'Total (' . $totalLabels[$sectionIndex] . ')',
	            $fmtCell($secLtCY),
	            $fmtCell($secLtPY),
	            $fmtCell($secStCY),
	            $fmtCell($secStPY),
	            'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	        ];
	 
	        // Spacer
	        $output[] = ['', '', '', '', '', ''];
	 
	        $grandLtCY += $secLtCY;
	        $grandLtPY += $secLtPY;
	        $grandStCY += $secStCY;
	        $grandStPY += $secStPY;
	        $sectionIndex++;
	    }
	 
	    // Grand Total (A) + (B)
	    $output[] = [
	        '',
	        'Total (A) + (B)',
	        $fmtCell($grandLtCY),
	        $fmtCell($grandLtPY),
	        $fmtCell($grandStCY),
	        $fmtCell($grandStPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:14px; border-top:2px solid #333;']
	    ];
	 
	    return $output;
	}

	private function build_other_long_term_liabilities_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    $getOutstanding = function ($catId, $from, $to) use ($CI) {
	        $receipts = $CI->db->query(
	            "SELECT id, received_amount FROM " . db_prefix() . "receipts
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->result_array();
	 
	        if (empty($receipts)) return 0;
	 
	        $receiptIds    = array_column($receipts, 'id');
	        $totalReceived = array_sum(array_column($receipts, 'received_amount'));
	 
	        $rpPlaceholders = implode(',', array_fill(0, count($receiptIds), '?'));
	        $repaid = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE category_id = 16
	             AND receipt_id IN ($rpPlaceholders)
	             AND date BETWEEN ? AND ?",
	            array_merge($receiptIds, [$from, $to])
	        )->row()->total;
	 
	        return max(0, $totalReceived - $repaid);
	    };
	 
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Note 5 structure: # | Particulars | CY | PY
	    // cat 61 = Other long-term liabilities
	    //   cat 87 = Advance from customers
	    //   (b) Others = any direct entries on cat 61 itself,
	    //                OR children of 61 that are not cat 87
	    // ----------------------------------------------------------------
	 
	    // (a) Advance from customers — cat 87
	    $aCY = $getOutstanding(85, $start_date, $end_date);
	    $aPY = $getOutstanding(85, $prev_start, $prev_end);
	 
	    // (b) Others — cat 61 directly (entries that stopped at parent level)
	    // plus any future sub-cats added under 61 that aren't cat 87
	    // Strategy: get all children of cat 61 except 87, sum them up
	    // $categories = $CI->db->query(
	    //     "SELECT id FROM " . db_prefix() . "categories
	    //      WHERE parent_id = 61 AND id != 87"
	    // )->result_array();
	 
	    // $otherIds = array_column($categories, 'id');
	    // $otherIds[] = 61; // also catch direct entries on cat 61 itself
	 
	    // $bCY = 0;
	    // $bPY = 0;
	    // foreach ($otherIds as $id) {
	    //     $bCY += $getOutstanding($id, $start_date, $end_date);
	    //     $bPY += $getOutstanding($id, $prev_start, $prev_end);
	    // }
	 
	    // Build output rows
	    $output = [];

	    $output[] = [
		    '5', 'Other long-term liabilities',
		    '', '',
		    'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
		];
	 
	    $output[] = [
	        '(a)', 'Advance from customers',
	        $fmtCell($aCY), $fmtCell($aPY),
	    ];
	 
	    // $output[] = [
	    //     '(b)', 'Others',
	    //     $fmtCell($bCY), $fmtCell($bPY),
	    // ];
	 
	    $totalCY = $aCY;
	    $totalPY = $aPY;
	 
	    $output[] = [
	        '', 'Total Other long-term liabilities',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];
	 
	    return $output;
	}

	private function build_provisions_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    $getOutstanding = function ($catId, $from, $to) use ($CI) {
	        $receipts = $CI->db->query(
	            "SELECT id, received_amount FROM " . db_prefix() . "receipts
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->result_array();
	 
	        if (empty($receipts)) return 0;
	 
	        $receiptIds    = array_column($receipts, 'id');
	        $totalReceived = array_sum(array_column($receipts, 'received_amount'));
	 
	        $rpPlaceholders = implode(',', array_fill(0, count($receiptIds), '?'));
	        $repaid = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE category_id = 16
	             AND receipt_id IN ($rpPlaceholders)
	             AND date BETWEEN ? AND ?",
	            array_merge($receiptIds, [$from, $to])
	        )->row()->total;
	 
	        return max(0, $totalReceived - $repaid);
	    };
	 
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Note 6 — Provisions
	    // (a) Provision for employee benefits → LT: cat 114, ST: cat 116
	    // (b) Other provisions                → LT: cat 115, ST: cat 117
	    // ----------------------------------------------------------------
	 
	    $rows = [
	        // serial, label,                            ltId, stId
	        ['(a)', 'Provision for employee benefits',   112,  114],
	        ['(b)', 'Other provisions',                  113,  115],
	    ];
	 
	    $output = [];
	 
	    // Title header row
	    $output[] = [
	        '6', 'Provisions',
	        '', '', '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];
	 
	    $totalLtCY = 0;
	    $totalLtPY = 0;
	    $totalStCY = 0;
	    $totalStPY = 0;
	 
	    foreach ($rows as $row) {
	        list($serial, $label, $ltId, $stId) = $row;
	 
	        $ltCY = $getOutstanding($ltId, $start_date, $end_date);
	        $ltPY = $getOutstanding($ltId, $prev_start, $prev_end);
	        $stCY = $getOutstanding($stId, $start_date, $end_date);
	        $stPY = $getOutstanding($stId, $prev_start, $prev_end);
	 
	        $totalLtCY += $ltCY;
	        $totalLtPY += $ltPY;
	        $totalStCY += $stCY;
	        $totalStPY += $stPY;
	 
	        $output[] = [
	            $serial,
	            $label,
	            $fmtCell($ltCY),
	            $fmtCell($ltPY),
	            $fmtCell($stCY),
	            $fmtCell($stPY),
	        ];
	    }
	 
	    // Total Provisions
	    $output[] = [
	        '', 'Total Provisions',
	        $fmtCell($totalLtCY),
	        $fmtCell($totalLtPY),
	        $fmtCell($totalStCY),
	        $fmtCell($totalStPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];
	 
	    return $output;
	}

	private function build_other_current_liabilities_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    $getOutstanding = function ($catId, $from, $to) use ($CI) {
	        $receipts = $CI->db->query(
	            "SELECT id, received_amount FROM " . db_prefix() . "receipts
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->result_array();
	 
	        if (empty($receipts)) return 0;
	 
	        $receiptIds    = array_column($receipts, 'id');
	        $totalReceived = array_sum(array_column($receipts, 'received_amount'));
	 
	        $rpPlaceholders = implode(',', array_fill(0, count($receiptIds), '?'));
	        $repaid = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE category_id = 16
	             AND receipt_id IN ($rpPlaceholders)
	             AND date BETWEEN ? AND ?",
	            array_merge($receiptIds, [$from, $to])
	        )->row()->total;
	 
	        return max(0, $totalReceived - $repaid);
	    };
	 
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Note 8 — Other current liabilities
	    // Parent: cat 65 (Other-current liabilities, child of 63)
	    // All leaf cats directly under 65
	    // ----------------------------------------------------------------
	    $rows = [
	        // serial, label,                                              catId
	        ['(a)', 'Current maturities of finance lease obligations',    104],
	        ['(b)', 'Interest accrued but not due on borrowings',         105],
	        ['(c)', 'Interest accrued and due on borrowings',             106],
	        ['(d)', 'Income received in advance',                         107],
	        ['(e)', 'Unearned revenue',                                   108],
	        ['(f)', 'Goods and Service tax payable',                      109],
	        ['(g)', 'TDS payable',                                        110],
	        ['(h)', 'Other payables',                                     111],
	    ];
	 
	    $output = [];
	 
	    // Title header row
	    $output[] = [
	        '8', 'Other current liabilities',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];
	 
	    $totalCY = 0;
	    $totalPY = 0;
	 
	    foreach ($rows as $row) {
	        list($serial, $label, $catId) = $row;
	 
	        $cy = $getOutstanding($catId, $start_date, $end_date);
	        $py = $getOutstanding($catId, $prev_start, $prev_end);
	 
	        $totalCY += $cy;
	        $totalPY += $py;
	 
	        $output[] = [
	            $serial,
	            $label,
	            $fmtCell($cy),
	            $fmtCell($py),
	        ];
	    }
	 
	    // Total
	    $output[] = [
	        '', 'Total Other current liabilities',
	        $fmtCell($totalCY),
	        $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];
	 
	    return $output;
	}

	private function build_term_loans_advances_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    // Assets = tblpayments outflows
	    $getAssetBalance = function ($catId, $from, $to) use ($CI) {
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };
	 
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Note 11 structure
	    // [serial, label, ltId, stId, isGroup]
	    // ----------------------------------------------------------------
	    $sections = [
	        'A' => [
	            'title'  => 'Secured',
	            'rows'   => [
	                ['(a)',    'Capital advances',                            null, null, true ],
	                ['(i)',    'Considered good',                            124,  158,  false],
	                ['(ii)',   'Doubtful',                                   125,  159,  false],
	                ['(b)',    'Loans advances to partners or relative of partners', 126, 160, false],
	                ['(c)',    'Other loans and advances',                   null, null, true ],
	                ['(i)',    'Prepaid expenses',                           128,  162,  false],
	                ['(ii)',   'CENVAT credit receivable',                   129,  163,  false],
	                ['(iii)',  'VAT credit receivable',                      130,  164,  false],
	                ['(iv)',   'Service tax credit receivable',              131,  165,  false],
	                ['(v)',    'GST input credit receivable',                132,  166,  false],
	                ['(vi)',   'Security Deposits',                          133,  167,  false],
	                ['(vii)', 'Balance with government authorities',         134,  168,  false],
	            ],
	        ],
	        'B' => [
	            'title'  => 'Unsecured',
	            'rows'   => [
	                ['(a)',    'Capital advances',                            null, null, true ],
	                ['(i)',    'Considered good',                            136,  170,  false],
	                ['(ii)',   'Doubtful',                                   137,  171,  false],
	                ['(b)',    'Loans advances to partners or relative of partners', 138, 172, false],
	                ['(c)',    'Other loans and advances',                   null, null, true ],
	                ['(i)',    'Prepaid expenses',                           140,  174,  false],
	                ['(ii)',   'CENVAT credit receivable',                   141,  175,  false],
	                ['(iii)',  'VAT credit receivable',                      142,  176,  false],
	                ['(iv)',   'Service tax credit receivable',              143,  177,  false],
	                ['(v)',    'GST input credit receivable',                144,  178,  false],
	                ['(vi)',   'Security Deposits',                          145,  179,  false],
	                ['(vii)', 'Balance with government authorities',         146,  180,  false],
	            ],
	        ],
	    ];
	 
	    $output = [];
	 
	    // Title header
	    $output[] = [
	        '11', 'Loans and Advances',
	        '', '', '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];
	 
	    $grandLtCY = 0;
	    $grandLtPY = 0;
	    $grandStCY = 0;
	    $grandStPY = 0;
	 
	    foreach ($sections as $sectionKey => $section) {
	 
	        // Section header — A (Secured) / B (Unsecured)
	        $output[] = [
	            $sectionKey,
	            $section['title'],
	            '', '', '', '',
	            'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:14px;']
	        ];
	 
	        $secLtCY = 0;
	        $secLtPY = 0;
	        $secStCY = 0;
	        $secStPY = 0;
	 
	        foreach ($section['rows'] as $row) {
	            list($serial, $label, $ltId, $stId, $isGroup) = $row;
	 
	            if ($isGroup) {
	                $output[] = [
	                    $serial, $label, '', '', '', '',
	                    'DT_RowAttr' => ['style' => 'font-weight:bold;']
	                ];
	                continue;
	            }
	 
	            $ltCY = $getAssetBalance($ltId, $start_date, $end_date);
	            $ltPY = $getAssetBalance($ltId, $prev_start, $prev_end);
	            $stCY = $getAssetBalance($stId, $start_date, $end_date);
	            $stPY = $getAssetBalance($stId, $prev_start, $prev_end);
	 
	            $secLtCY += $ltCY;
	            $secLtPY += $ltPY;
	            $secStCY += $stCY;
	            $secStPY += $stPY;
	 
	            $output[] = [
	                $serial, $label,
	                $fmtCell($ltCY), $fmtCell($ltPY),
	                $fmtCell($stCY), $fmtCell($stPY),
	            ];
	        }
	 
	        // Total (A) or Total (B)
	        $output[] = [
	            '', 'Total (' . $sectionKey . ')',
	            $fmtCell($secLtCY), $fmtCell($secLtPY),
	            $fmtCell($secStCY), $fmtCell($secStPY),
	            'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	        ];
	 
	        // Spacer
	        $output[] = ['', '', '', '', '', ''];
	 
	        $grandLtCY += $secLtCY;
	        $grandLtPY += $secLtPY;
	        $grandStCY += $secStCY;
	        $grandStPY += $secStPY;
	    }
	 
	    // Grand Total (A + B)
	    $output[] = [
	        '', 'Total (A + B)',
	        $fmtCell($grandLtCY), $fmtCell($grandLtPY),
	        $fmtCell($grandStCY), $fmtCell($grandStPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:14px; border-top:2px solid #333;']
	    ];

	    return $output;
	}

	private function build_other_non_current_assets_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    // ----------------------------------------------------------------
	    // Assets come from tblpayments (money going OUT to acquire assets)
	    // Balance = total paid out for this asset category
	    // No repayment concept here — straight sum of received_amount paid
	    // ----------------------------------------------------------------
	    $getAssetBalance = function ($catId, $from, $to) use ($CI) {
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	 
	        return (float) $result->total;
	    };
	 
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Note 12 — Other non-current assets
	    // Parent: cat 149
	    //   (a) Security Deposits → cat 150
	    //   (b) Prepaid expenses  → cat 151
	    // ----------------------------------------------------------------
	    $rows = [
	        ['(a)', 'Security Deposits', 148],
	        ['(b)', 'Prepaid expenses',  149],
	    ];
	 
	    $output = [];
	 
	    // Title header row
	    $output[] = [
	        '12', 'Other non-current assets',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];
	 
	    $totalCY = 0;
	    $totalPY = 0;
	 
	    foreach ($rows as $row) {
	        list($serial, $label, $catId) = $row;
	 
	        $cy = $getAssetBalance($catId, $start_date, $end_date);
	        $py = $getAssetBalance($catId, $prev_start, $prev_end);
	 
	        $totalCY += $cy;
	        $totalPY += $py;
	 
	        $output[] = [
	            $serial,
	            $label,
	            $fmtCell($cy),
	            $fmtCell($py),
	        ];
	    }
	 
	    // Total
	    $output[] = [
	        '', 'Total other non-current assets',
	        $fmtCell($totalCY),
	        $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    return $output;
	}

	private function build_other_current_assets_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    $getAssetBalance = function ($catId, $from, $to) use ($CI) {
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };
	 
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // ----------------------------------------------------------------
	    // Note 15 — Other current assets
	    // Parent: cat 183
	    //   (a) Interest accrued but not due on deposits → cat 182
	    //   (b) Interest accrued and due on deposits     → cat 183
	    // ----------------------------------------------------------------
	    $rows = [
	        ['(a)', 'Interest accrued but not due on deposits', 182],
	        ['(b)', 'Interest accrued and due on deposits',     183],
	    ];
	 
	    $output = [];
	 
	    // Title header row
	    $output[] = [
	        '15', 'Other current assets',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];
	 
	    $totalCY = 0;
	    $totalPY = 0;
	 
	    foreach ($rows as $row) {
	        list($serial, $label, $catId) = $row;
	 
	        $cy = $getAssetBalance($catId, $start_date, $end_date);
	        $py = $getAssetBalance($catId, $prev_start, $prev_end);
	 
	        $totalCY += $cy;
	        $totalPY += $py;
	 
	        $output[] = [
	            $serial, $label,
	            $fmtCell($cy), $fmtCell($py),
	        ];
	    }
	 
	    // Total
	    $output[] = [
	        '', 'Total other current assets',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    return $output;
	}

	private function build_cash_and_bank_balances_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI = &get_instance();
		$fmt = 'formatAmount';

		$getAccountBalance = function($accountType, $asOnDate) use ($CI) {
			$accounts = $CI->db->where('bank_type', $accountType)
				->get(db_prefix() . 'bank_accounts')
				->result();

			$total = 0;

			foreach($accounts as $acc) {
				$row = $CI->db->query("SELECT total_balance
					FROM " . db_prefix() . "transaction
					WHERE account_id = ?
					AND date <= ?
					ORDER BY date DESC, id DESC
					LIMIT 1",
					[
						$acc->id,
						$asOnDate
					])->row();

				if($row) {
					$total += (float) $row->total_balance;
				} else {
					$total += (float) $acc->opening_balance;
				}
			}

			return $total;
		};

		$getCategoryBalance = function($catId, $asOnDate) use ($CI) {
			$result = $CI->db->query("SELECT COALESCE(SUM(received_amount), 0) total
				FROM " . db_prefix() . "payments
				WHERE date <= ?
				AND (
					category_id = ?
					OR sub_category_id = ?
					OR sub_sub_category_id = ?
					OR sub_sub_sub_category_id = ?
				)", [
					$asOnDate,
					$catId,
					$catId,
					$catId,
					$catId,
				]
			)->row();

			return (float) $result->total;
		};

		$fmtCell = function($value) use ($fmt) {
			if((float) $value == 0) {
				return '-';
			}

			return $fmt($value);
		};

		$output = [];

		$output[] = [
	        '14',
	        'Cash and Bank Balances',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:15px;border-bottom:2px solid #333;'
	        ]
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | Section A
	    |--------------------------------------------------------------------------
	    */

	    $output[] = [
	        'A',
	        'Cash and cash equivalents',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;font-size:14px;'
	        ]
	    ];

	    $secA_CY = 0;
	    $secA_PY = 0;

	    /*
	    |--------------------------------------------------------------------------
	    | (a) On Current Accounts (Bank)
	    |--------------------------------------------------------------------------
	    */

	    $bankCY = $getAccountBalance(
	        'Bank',
	        $end_date
	    );

	    $bankPY = $getAccountBalance(
	        'Bank',
	        $prev_end
	    );

	    $secA_CY += $bankCY;
	    $secA_PY += $bankPY;

	    $output[] = [
	        '(a)',
	        'On current accounts / Savings Account',
	        $fmtCell($bankCY),
	        $fmtCell($bankPY)
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | (b)
	    |--------------------------------------------------------------------------
	    */

	    $output[] = [
	        '(b)',
	        'Cash credit account (Debit balance)',
	        '-',
	        '-'
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | (c)
	    |--------------------------------------------------------------------------
	    */

	    $output[] = [
	        '(c)',
	        'Fixed Deposits (original maturity less than 3 months)',
	        '-',
	        '-'
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | (d)
	    |--------------------------------------------------------------------------
	    */

	    $output[] = [
	        '(d)',
	        'Cheques, drafts on hand',
	        '-',
	        '-'
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | (e) Cash on Hand (Wallet)
	    |--------------------------------------------------------------------------
	    */

	    $walletCY = $getAccountBalance(
	        'Wallet',
	        $end_date
	    );

	    $walletPY = $getAccountBalance(
	        'Wallet',
	        $prev_end
	    );

	    $secA_CY += $walletCY;
	    $secA_PY += $walletPY;

	    $output[] = [
	        '(e)',
	        'Cash on hand',
	        $fmtCell($walletCY),
	        $fmtCell($walletPY)
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | Total I
	    |--------------------------------------------------------------------------
	    */

	    $output[] = [
	        '',
	        'Total (I)',
	        $fmtCell($secA_CY),
	        $fmtCell($secA_PY),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;border-top:1px solid #aaa;'
	        ]
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | Section B
	    |--------------------------------------------------------------------------
	    */

	    $output[] = [
	        'B',
	        'Other bank balances',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;font-size:14px;'
	        ]
	    ];

	    $output[] = [
	        '(a)',
	        'Bank Deposits',
	        '',
	        '',
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;'
	        ]
	    ];

	    $secB_rows = [
	        [
	            '(i)',
	            'Earmarked Bank Deposits',
	            193
	        ],
	        [
	            '(ii)',
	            'Deposits with original maturity for more than 3 months but less than 12 months from reporting date',
	            194
	        ],
	        [
	            '(iii)',
	            'Margin money or deposits under lien',
	            195
	        ],
	        [
	            '(iv)',
	            'Others (specify nature)',
	            196
	        ]
	    ];

	    $secB_CY = 0;
	    $secB_PY = 0;

	    foreach ($secB_rows as $row) {

	        list(
	            $serial,
	            $label,
	            $catId
	        ) = $row;

	        $cy = $getCategoryBalance(
	            $catId,
	            $end_date
	        );

	        $py = $getCategoryBalance(
	            $catId,
	            $prev_end
	        );

	        $secB_CY += $cy;
	        $secB_PY += $py;

	        $output[] = [
	            $serial,
	            $label,
	            $fmtCell($cy),
	            $fmtCell($py)
	        ];
	    }

	    $output[] = [
	        '',
	        'Total other bank balances (II)',
	        $fmtCell($secB_CY),
	        $fmtCell($secB_PY),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bold;border-top:1px solid #aaa;'
	        ]
	    ];

	    /*
	    |--------------------------------------------------------------------------
	    | Grand Total
	    |--------------------------------------------------------------------------
	    */

	    $totalCY = $secA_CY + $secB_CY;
	    $totalPY = $secA_PY + $secB_PY;

	    $output[] = [
	        '',
	        'Total Cash and bank balances (I+II)',
	        $fmtCell($totalCY),
	        $fmtCell($totalPY),
	        'DT_RowAttr' => [
	            'style' => 'font-weight:bolder;font-size:14px;border-top:2px solid #333;'
	        ]
	    ];

	    return $output;
	}

	private function build_investments_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$CI = &get_instance();
		$fmt = 'formatAmount';

		$getAmt = function($catId, $from, $to) use ($CI) {
			return (float) $CI->db->query(
				"SELECT COALESCE(SUM(received_amount), 0) AS total
				FROM " . db_prefix() . "payments
				WHERE date BETWEEN ? AND ?
				AND (category_id = ? OR sub_category_id = ?
					OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?)",
				[$from, $to, $catId, $catId, $catId, $catId]
			)->row()->total;
		};

		$fmtCell = fn($v) => $v == 0 ? ' - ' : $fmt($v);
		$bold = ['style' => 'font-weight: bolder; background: #f5f5f5;'];
		$total = ['style' => 'font-weight: bolder; border-top: 1px solid #aaa;'];
		$title = ['style' => 'font-weight: bolder; font-size: 15px; border-bottom: 2px solid #333;'];

		$output = [];
		$output[] = ['10', 'Investments - Non-current and Current', '', '', 'DT_RowAttr' => $title];

		$output[] = ['', 'Non-current investments', '', '', 'DT_RowAttr' => $bold];
		$ncCY = $ncPY = 0;

		foreach([[117, '(a)', 'Trade Investments-Quoted'], [118, '(b)', 'Trade Investments-Unquoted'], [119, '(c)', 'Other Investments']] as [$id, $key, $lbl]) {
			$cy = $getAmt($id, $start_date, $end_date);
			$py = $getAmt($id, $prev_start, $prev_end);

			$ncCY += $cy;
			$ncPY += $py;

			$output[] = [$key, $lbl, $fmtCell($cy), $fmtCell($py)];
		}
		$output[] = ['', 'Total Non-Current Investments', $fmtCell($ncCY), $fmtCell($ncPY), 'DT_RowAttr' => $total];

		$output[] = ['', 'Current Investments', '', '', 'DT_RowAttr' => $bold];
		$cuCY = $cuPY = 0;

		foreach([[151, '(a)', 'Trade Investments-Quoted'], [152, '(b)', 'Trade Investments-Unquoted'], [153, '(c)', 'Other Investments']] as [$id, $key, $lbl]) {
			$cy = $getAmt($id, $start_date, $end_date);
			$py = $getAmt($id, $prev_start, $prev_end);

			$cuCY += $cy;
			$cuPY += $py;

			$output[] = [$key, $lbl, $fmtCell($cy), $fmtCell($py)];
		}
		$output[] = ['', 'Total Current Investments', $fmtCell($cuCY), $fmtCell($cuPY), 'DT_RowAttr' => $total];

		$output[] = ['', 'Total Investments', $fmtCell($ncCY + $cuCY), $fmtCell($ncPY + $cuPY), 'DT_RowAttr' => ['style' => 'font-weight: bolder; border-top: 2px solid #333;']];

		return $output;
	}

	private function build_payables_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$fmt = 'formatAmount';
		$fmtCell = fn($v) => $v == 0 ? ' - ' : $fmt($v);

		$payable_current = $this->db->select_sum('balance')
			->where('category_id !=', 16)
			->where('balance IS NOT NULL')
			->where('balance !=', 0)
			->where('is_fully_paid', 0)
			->where('date >=', $start_date)
			->where('date <=', $end_date)
			->get(db_prefix() . 'payments')
			->row()->balance ?? 0;

		$payable_previous = $this->db->select_sum('balance')
			->where('category_id !=', 16)
			->where('balance IS NOT NULL')
			->where('balance !=', 0)
			->where('is_fully_paid', 0)
			->where('date >=', $prev_start)
			->where('date <=', $prev_end)
			->get(db_prefix() . 'payments')
			->row()->balance ?? 0;

		$output = [];

		$output[] = [
	        '7', 'Payables',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    $output[] = [
	        '(a)',
	        'Total outstanding dues of micro, small and medium enterprises',
	        $fmtCell($payable_current),
	        $fmtCell($payable_previous),
	    ];

	    $output[] = [
	        '(b)',
	        'Total outstanding dues of creditors other than micro, small and medium enterprises',
	        '',
	        '',
	    ];

	    $output[] = ['', 'Total Payables', $fmtCell($payable_current), $fmtCell($payable_previous), 'DT_RowAttr' => ['style' => 'font-weight: bolder; border-top: 1px solid #aaa;']];

	    return $output;
	}

	private function build_receivables_notes_data($start_date, $end_date, $prev_start, $prev_end) {
		$fmt = 'formatAmount';
		$fmtCell = fn($v) => $v == 0 ? ' - ' : $fmt($v);

		$receivable_current = $this->db->select_sum('balance')
			->where('balance IS NOT NULL')
			->where('balance !=', 0)
			->where('is_fully_received', 0)
			->where('date >=', $start_date)
			->where('date <=', $end_date)
			->get(db_prefix() . 'receipts')
			->row()->balance ?? 0;

		$receivable_previous = $this->db->select_sum('balance')
			->where('balance IS NOT NULL')
			->where('balance !=', 0)
			->where('is_fully_received', 0)
			->where('date >=', $prev_start)
			->where('date <=', $prev_end)
			->get(db_prefix() . 'receipts')
			->row()->balance ?? 0;

		$output = [];

		$output[] = [
	        '13', 'Receivables',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    $output[] = [
	        '(a)',
	        'Donations/grants receivable',
	        $fmtCell($receivable_current),
	        $fmtCell($receivable_previous),
	    ];

	    $output[] = [
	        '(b)',
	        'Others',
	        '',
	        '',
	    ];

	    $output[] = ['', 'Total Receivables', $fmtCell($receivable_current), $fmtCell($receivable_previous), 'DT_RowAttr' => ['style' => 'font-weight: bolder; border-top: 1px solid #aaa;']];

	    return $output;
	}

	public function get_npo_funds_notes()
	{
	    if (!$this->input->is_ajax_request()) {
	        return;
	    }

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $rows = $this->build_npo_funds_notes_data($start_date, $end_date, $prev_start, $prev_end);

	    echo json_encode([
	        'draw' => (int) $this->input->post('draw'),
	        'recordsTotal' => count($rows),
	        'recordsFiltered' => count($rows),
	        'data' => $rows,
	    ]);

	    die;
	}

	public function get_borrowings_notes()
	{
	    if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_borrowings_notes_data($start_date, $end_date, $prev_start, $prev_end);

	    echo json_encode([
	        'draw' => (int) $this->input->post('draw'),
	        'recordsTotal' => count($rows),
	        'recordsFiltered' => count($rows),
	        'data' => $rows,
	    ]);

	    die;
	}

	public function get_other_long_term_liabilities_notes() {
		if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_other_long_term_liabilities_notes_data($start_date, $end_date, $prev_start, $prev_end);
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);
	    die;
	}

	public function get_provisions_notes()
	{
	    if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_provisions_notes_data($start_date, $end_date, $prev_start, $prev_end);
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);
	    die;
	}

	public function get_other_current_liabilities_notes() {
		if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_other_current_liabilities_notes_data($start_date, $end_date, $prev_start, $prev_end);
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);
	    die;
	}

	public function get_term_loans_advances_notes() {
		if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_term_loans_advances_notes_data($start_date, $end_date, $prev_start, $prev_end);
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);
	    die;
	}

	public function get_other_non_current_assets_notes() {
		if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_other_non_current_assets_notes_data($start_date, $end_date, $prev_start, $prev_end);
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);
	    die;
	}

	public function get_other_current_assets_notes() {
		if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $rows = $this->build_other_current_assets_notes_data($start_date, $end_date, $prev_start, $prev_end);
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);
	    die;
	}

	public function get_cash_and_bank_balances_notes() {
		if(!$this->input->is_ajax_request()) {
			return;
		}

		list($start_date, $end_date) = $this->get_report_start_end_dates();
		list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

		$rows = $this->build_cash_and_bank_balances_notes_data($start_date, $end_date, $prev_start, $prev_end);

	    echo json_encode([
	        'draw'            => (int)$this->input->post('draw'),
	        'recordsTotal'    => count($rows),
	        'recordsFiltered' => count($rows),
	        'data'            => $rows,
	    ]);

	    die;
	}

	public function get_investments_notes() {
		if(!$this->input->is_ajax_request()) {
			return;
		}

		list($start_date, $end_date) = $this->get_report_start_end_dates();
		list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

		$rows = $this->build_investments_notes_data($start_date, $end_date, $prev_start, $prev_end);

		echo json_encode([
			'draw' => (int) $this->input->post('draw'),
			'recordsTotal' => count($rows),
			'recordsFiltered' => count($rows),
			'data' => $rows,
		]);
		die;
	}

	public function get_payables_notes() {
		if(!$this->input->is_ajax_request()) {
			return;
		}

		list($start_date, $end_date) = $this->get_report_start_end_dates();
		list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

		$rows = $this->build_payables_notes_data($start_date, $end_date, $prev_start, $prev_end);

	    echo json_encode([
			'draw' => (int) $this->input->post('draw'),
			'recordsTotal' => count($rows),
			'recordsFiltered' => count($rows),
			'data' => $rows,
		]);
		die;
	}

	public function get_receivables_notes() {
		if(!$this->input->is_ajax_request()) {
			return;
		}

		list($start_date, $end_date) = $this->get_report_start_end_dates();
		list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

		$rows = $this->build_receivables_notes_data($start_date, $end_date, $prev_start, $prev_end);

	    echo json_encode([
			'draw' => (int) $this->input->post('draw'),
			'recordsTotal' => count($rows),
			'recordsFiltered' => count($rows),
			'data' => $rows,
		]);
		die;
	}

	public function export_balance_sheet_workbook() {
		if(!staff_can('view', NGO_TRUST_MODULE_NAME)) {
			access_denied(NGO_TRUST_MODULE_NAME);
		}

		list($start_date, $end_date) = $this->get_report_start_end_dates();
		list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

		$spreadsheet = new Spreadsheet();

		$balance_sheet = $this->build_balance_sheet_report_new_data($start_date, $end_date, $prev_start, $prev_end);

		$sheetMap = [
			'Balance Sheet' => isset($balance_sheet['aaData']) ? $balance_sheet['aaData'] : [],
			'Notes - 3' => $this->build_npo_funds_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 4' => $this->build_borrowings_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 5' => $this->build_other_long_term_liabilities_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 6' => $this->build_provisions_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 7' => $this->build_payables_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 8' => $this->build_other_current_liabilities_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 10' => $this->build_investments_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 11' => $this->build_term_loans_advances_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 12' => $this->build_other_non_current_assets_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 13' => $this->build_receivables_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 14' => $this->build_cash_and_bank_balances_notes_data($start_date, $end_date, $prev_start, $prev_end),
			'Notes - 15' => $this->build_other_current_assets_notes_data($start_date, $end_date, $prev_start, $prev_end),
		];

		$sheetIndex = 0;

		foreach($sheetMap as $title => $rows) {
			if($sheetIndex == 0) {
				$sheet = $spreadsheet->getActiveSheet();
			} else {
				$sheet = $spreadsheet->createSheet();
			}

			$sheet->setTitle(substr($title, 0, 31));
			$this->writeSheetData($sheet, $rows);
			$sheetIndex++;
		}

		$filename = 'Balance_Sheet_Workbook_' . $start_date . ' _to_' . $end_date . '.xlsx';

		while(ob_get_level()) {
			ob_end_clean();
		}

		header('Content-Disposition: attachment; filename="' . $filename .'"');

		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

	public function profit_loss_report() {
		if (!$this->input->is_ajax_request()) {
	        return;
	    }
	 
	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);
	 
	    $categories = $this->db->get(db_prefix() . 'categories')->result_array();
	    $catMap = [];
	    $tree   = [];
	    foreach ($categories as $cat) {
	        $catMap[$cat['id']] = $cat;
	        $tree[$cat['parent_id']][] = $cat['id'];
	    }
	 
	    $CI  = &get_instance();
	    $fmt = 'formatAmount';
	 
	    // All restricted fund category IDs (cat 30 + all descendants)
	    $restricted_ids = getChildIds(30, $tree);
	 
	    // ----------------------------------------------------------------
	    // INCOME HELPERS
	    // ----------------------------------------------------------------
	 
	    // Unrestricted income: receipts in given cats WHERE
	    // the receipt does NOT belong to restricted fund tree
	    $getIncomeUnrestricted = function ($catIds, $from, $to) use ($CI, $restricted_ids) {
	        if (empty($catIds)) return 0;
	        $ph = implode(',', array_fill(0, count($catIds), '?'));
	        $rph = implode(',', array_fill(0, count($restricted_ids), '?'));
	 
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "receipts
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id IN ($ph)
	                 OR sub_category_id IN ($ph)
	                 OR sub_sub_category_id IN ($ph)
	                 OR sub_sub_sub_category_id IN ($ph)
	             )
	             AND category_id NOT IN ($rph)
	             AND (sub_category_id NOT IN ($rph) OR sub_category_id IS NULL)",
	            array_merge([$from, $to], $catIds, $catIds, $catIds, $catIds, $restricted_ids, $restricted_ids)
	        )->row();
	        return (float) $result->total;
	    };
	 
	    // Restricted income: receipts in given cats WHERE
	    // receipt category traces to cat 30
	    $getIncomeRestricted = function ($catIds, $from, $to) use ($CI, $restricted_ids) {
	        if (empty($catIds)) return 0;
	        $ph  = implode(',', array_fill(0, count($catIds), '?'));
	        $rph = implode(',', array_fill(0, count($restricted_ids), '?'));
	 
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "receipts
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id IN ($ph)
	                 OR sub_category_id IN ($ph)
	                 OR sub_sub_category_id IN ($ph)
	                 OR sub_sub_sub_category_id IN ($ph)
	             )
	             AND (
	                 category_id IN ($rph)
	                 OR sub_category_id IN ($rph)
	             )",
	            array_merge([$from, $to], $catIds, $catIds, $catIds, $catIds, $restricted_ids, $restricted_ids)
	        )->row();
	        return (float) $result->total;
	    };
	 
	    // ----------------------------------------------------------------
	    // EXPENSE HELPERS
	    // ----------------------------------------------------------------
	 
	    // Unrestricted expense: payments in given cats WHERE
	    // receipt_category_id does NOT trace to restricted funds (or is null)
	    $getExpenseUnrestricted = function ($catIds, $from, $to) use ($CI, $restricted_ids) {
	        if (empty($catIds)) return 0;
	        $ph  = implode(',', array_fill(0, count($catIds), '?'));
	        $rph = implode(',', array_fill(0, count($restricted_ids), '?'));
	 
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id IN ($ph)
	                 OR sub_category_id IN ($ph)
	                 OR sub_sub_category_id IN ($ph)
	                 OR sub_sub_sub_category_id IN ($ph)
	             )
	             AND (
	                 receipt_category_id IS NULL
	                 OR receipt_category_id NOT IN ($rph)
	             )",
	            array_merge([$from, $to], $catIds, $catIds, $catIds, $catIds, $restricted_ids)
	        )->row();
	        return (float) $result->total;
	    };
	 
	    // Restricted expense: payments WHERE receipt_category_id
	    // traces to restricted fund (cat 30 tree)
	    $getExpenseRestricted = function ($catIds, $from, $to) use ($CI, $restricted_ids) {
	        if (empty($catIds)) return 0;
	        $ph  = implode(',', array_fill(0, count($catIds), '?'));
	        $rph = implode(',', array_fill(0, count($restricted_ids), '?'));
	 
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id IN ($ph)
	                 OR sub_category_id IN ($ph)
	                 OR sub_sub_category_id IN ($ph)
	                 OR sub_sub_sub_category_id IN ($ph)
	             )
	             AND receipt_category_id IN ($rph)",
	            array_merge([$from, $to], $catIds, $catIds, $catIds, $catIds, $restricted_ids)
	        )->row();
	        return (float) $result->total;
	    };
	 
	    // ----------------------------------------------------------------
	    // FORMAT HELPERS
	    // ----------------------------------------------------------------
	    $fmtCell = function ($val) use ($fmt) {
	        if ($val == 0) return '-';
	        return $fmt($val);
	    };
	 
	    // Build a standard 9-col data row
	    // [#, Particulars, Note, Unres-CY, Res-CY, Total-CY, Unres-PY, Res-PY, Total-PY]
	    $makeRow = function (
	        $serial, $label, $note,
	        $unCY, $resCY,
	        $unPY, $resPY,
	        $rowAttr = []
	    ) use ($fmtCell) {
	        $totalCY = $unCY + $resCY;
	        $totalPY = $unPY + $resPY;
	        $row = [
	            $serial, $label, $note,
	            $fmtCell($unCY),   $fmtCell($resCY),   $fmtCell($totalCY),
	            $fmtCell($unPY),   $fmtCell($resPY),   $fmtCell($totalPY),
	        ];
	        if (!empty($rowAttr)) {
	            $row['DT_RowAttr'] = $rowAttr;
	        }
	        return $row;
	    };
	 
	    // Header row (no amounts)
	    $makeHeader = function ($serial, $label, $style) {
	        return [
	            $serial, $label, '', '', '', '', '', '', '',
	            'DT_RowAttr' => ['style' => $style]
	        ];
	    };
	 
	    // Total row (bold)
	    $makeTotalRow = function ($serial, $label, $unCY, $resCY, $unPY, $resPY, $style = '') use ($fmt) {
	        $totalCY = $unCY + $resCY;
	        $totalPY = $unPY + $resPY;
	        $fmtV = function($v) use ($fmt) { return $v == 0 ? '-' : $fmt($v); };
	        return [
	            $serial, $label, '',
	            $fmtV($unCY),  $fmtV($resCY),  $fmtV($totalCY),
	            $fmtV($unPY),  $fmtV($resPY),  $fmtV($totalPY),
	            'DT_RowAttr' => ['style' => $style ?: 'font-weight:bold; border-top:1px solid #aaa;']
	        ];
	    };
	 
	    $spacer = ['', '', '', '', '', '', '', '', ''];
	 
	    $output = [];
	 
	    // ================================================================
	    // I. INCOME
	    // ================================================================
	    $output[] = $makeHeader('I', 'Income', 'font-weight:bolder; font-size:16px;');
	 
	    // ================================================================
		// (a) Donations and Grants — cumulative total of all income streams
		// ================================================================
		// Income categories to include (unrestricted portion):
		//   Cat 1 = Member Contribution
		//   Cat 2 = Donation
		//   Cat 3 = Grant
		// Restricted portion: Cat 30 (Restricted funds) + all its descendants
		// Receivables: receipts in ANY of the above cats where balance > 0
		//              (i.e. amount pledged but not yet fully received)
		// All figures are summed into ONE cumulative row — no sub-headers.
		// ================================================================

		$don_cats = [1, 2, 3]; // Member Contribution, Donation, Grant

		// --- Unrestricted income: cat 1, 2, 3 receipts NOT in restricted tree ---
		$don_unCY  = $getIncomeUnrestricted($don_cats, $start_date, $end_date);
		$don_unPY  = $getIncomeUnrestricted($don_cats, $prev_start, $prev_end);

		// --- Restricted income: cat 30 + all its children ---
		$don_resCY = getReceiptAmount($CI, $restricted_ids, $start_date, $end_date);
		$don_resPY = getReceiptAmount($CI, $restricted_ids, $prev_start, $prev_end);

		// --- Receivables: outstanding (balance > 0) from receipts in ALL
		//     donation-type categories (cat 1,2,3) AND restricted fund tree.
		//     These are amounts recognised but not yet received.
		//     Unrestricted receivables go to un-column; restricted to res-column.
		// ---
		$all_don_ids     = array_merge($don_cats, $restricted_ids);
		$all_don_ids_ph  = implode(',', array_fill(0, count($all_don_ids), '?'));
		$rph             = implode(',', array_fill(0, count($restricted_ids), '?'));

		// Unrestricted receivables (cat 1/2/3, NOT in restricted tree, balance > 0)
		$recv_un_result = $CI->db->query(
		    "SELECT COALESCE(SUM(balance), 0) as total
		     FROM " . db_prefix() . "receipts
		     WHERE date BETWEEN ? AND ?
		     AND balance > 0
		     AND (
		         category_id IN (?,?,?)
		         OR sub_category_id IN (?,?,?)
		         OR sub_sub_category_id IN (?,?,?)
		         OR sub_sub_sub_category_id IN (?,?,?)
		     )
		     AND category_id NOT IN ($rph)
		     AND (sub_category_id NOT IN ($rph) OR sub_category_id IS NULL)",
		    array_merge(
		        [$start_date, $end_date],
		        [1,2,3], [1,2,3], [1,2,3], [1,2,3],   // cat 1,2,3 placeholders
		        $restricted_ids, $restricted_ids         // NOT IN restricted
		    )
		)->row();
		$recv_unCY = (float) $recv_un_result->total;

		$recv_un_result_py = $CI->db->query(
		    "SELECT COALESCE(SUM(balance), 0) as total
		     FROM " . db_prefix() . "receipts
		     WHERE date BETWEEN ? AND ?
		     AND balance > 0
		     AND (
		         category_id IN (?,?,?)
		         OR sub_category_id IN (?,?,?)
		         OR sub_sub_category_id IN (?,?,?)
		         OR sub_sub_sub_category_id IN (?,?,?)
		     )
		     AND category_id NOT IN ($rph)
		     AND (sub_category_id NOT IN ($rph) OR sub_category_id IS NULL)",
		    array_merge(
		        [$prev_start, $prev_end],
		        [1,2,3], [1,2,3], [1,2,3], [1,2,3],
		        $restricted_ids, $restricted_ids
		    )
		)->row();
		$recv_unPY = (float) $recv_un_result_py->total;

		// Restricted receivables (cat 30 tree, balance > 0)
		$recv_res_result = $CI->db->query(
		    "SELECT COALESCE(SUM(balance), 0) as total
		     FROM " . db_prefix() . "receipts
		     WHERE date BETWEEN ? AND ?
		     AND balance > 0
		     AND (
		         category_id IN ($rph)
		         OR sub_category_id IN ($rph)
		     )",
		    array_merge([$start_date, $end_date], $restricted_ids, $restricted_ids)
		)->row();
		$recv_resCY = (float) $recv_res_result->total;

		$recv_res_result_py = $CI->db->query(
		    "SELECT COALESCE(SUM(balance), 0) as total
		     FROM " . db_prefix() . "receipts
		     WHERE date BETWEEN ? AND ?
		     AND balance > 0
		     AND (
		         category_id IN ($rph)
		         OR sub_category_id IN ($rph)
		     )",
		    array_merge([$prev_start, $prev_end], $restricted_ids, $restricted_ids)
		)->row();
		$recv_resPY = (float) $recv_res_result_py->total;

		// --- Cumulative totals: received income + outstanding receivables ---
		$don_total_unCY  = $don_unCY  + $recv_unCY;   // Unrestricted: received + receivable
		$don_total_resCY = $don_resCY + $recv_resCY;   // Restricted:  received + receivable
		$don_total_unPY  = $don_unPY  + $recv_unPY;
		$don_total_resPY = $don_resPY + $recv_resPY;

		// Single cumulative row — no sub-headers, per requirement
		$output[] = $makeRow(
		    '(a)',
		    'Donations and Grants',
		    '',
		    $don_total_unCY,
		    $don_total_resCY,
		    $don_total_unPY,
		    $don_total_resPY
		);
	 
	    // (b) Fees from Rendering of Services — cat 31 (always unrestricted)
	    $fee_ids = getChildIds(31, $tree);
	    $fee_ids[] = 31;
	    $fee_unCY  = $getIncomeUnrestricted($fee_ids, $start_date, $end_date);
	    $fee_unPY  = $getIncomeUnrestricted($fee_ids, $prev_start, $prev_end);
	 
	    $output[] = $makeRow('(b)', 'Fees from Rendering of Services', '', $fee_unCY, 0, $fee_unPY, 0);
	 
	    // (c) Sale of Goods — cat 32 (always unrestricted)
	    $sale_ids = getChildIds(32, $tree);
	    $sale_ids[] = 32;
	    $sale_unCY = $getIncomeUnrestricted($sale_ids, $start_date, $end_date);
	    $sale_unPY = $getIncomeUnrestricted($sale_ids, $prev_start, $prev_end);
	 
	    $output[] = $makeRow('(c)', 'Sale of Goods', '', $sale_unCY, 0, $sale_unPY, 0);
	 
	    // Subtotal Section I — use cumulative donation variables
		$sec1_unCY  = $don_total_unCY  + $fee_unCY  + $sale_unCY;
		$sec1_resCY = $don_total_resCY;
		$sec1_unPY  = $don_total_unPY  + $fee_unPY  + $sale_unPY;
		$sec1_resPY = $don_total_resPY;
	 
	    // ================================================================
	    // II. OTHER INCOME — Note 16 (always unrestricted)
	    // ================================================================
	    $other_ids = getChildIds(5, $tree);
	    $other_ids[] = 5;
	    $other_unCY = $getIncomeUnrestricted($other_ids, $start_date, $end_date);
	    $other_unPY = $getIncomeUnrestricted($other_ids, $prev_start, $prev_end);
	 
	    $output[] = $makeRow('II', 'Other Income', '16', $other_unCY, 0, $other_unPY, 0,
	        ['style' => 'font-weight:bold;']);
	 
	    // ================================================================
	    // III. TOTAL INCOME (I + II)
	    // ================================================================
	    $total_inc_unCY  = $sec1_unCY  + $other_unCY;
	    $total_inc_resCY = $sec1_resCY;
	    $total_inc_unPY  = $sec1_unPY  + $other_unPY;
	    $total_inc_resPY = $sec1_resPY;
	 
	    $output[] = $makeTotalRow(
	        'III', 'Total Income (I+II)',
	        $total_inc_unCY, $total_inc_resCY,
	        $total_inc_unPY, $total_inc_resPY,
	        'font-weight:bolder; font-size:14px; border-top:2px solid #333;'
	    );
	 
	    $output[] = $spacer;
	 
	    // ================================================================
	    // IV. EXPENSES
	    // ================================================================
	    $output[] = $makeHeader('IV', 'Expenses', 'font-weight:bolder; font-size:16px;');
	 
	    $total_exp_unCY = 0;
	    $total_exp_resCY = 0;
	    $total_exp_unPY  = 0;
	    $total_exp_resPY = 0;
	 
	    // Expense rows definition
	    // [serial, label, note, catId]
	    $expense_rows = [
	        ['(a)', 'Material consumed/distributed',         '17', 37],
	        ['(b)', 'Donations/contributions paid',          '',   38],
	        ['(c)', 'Employee benefits expense',             '18', 9 ],
	        ['(e)', 'Finance costs',                         '20', 39],
	        ['(f)', 'Other expenses',                        '21', 15],
	        ['(g)', 'Religion/charitable expenses',          '',   59],
	    ];
	 
	    foreach ($expense_rows as $erow) {
	        list($serial, $label, $note, $catId) = $erow;
	 
	        $exp_ids = getChildIds($catId, $tree);
	        $exp_ids[] = $catId;
	 
	        $unCY  = $getExpenseUnrestricted($exp_ids, $start_date, $end_date);
	        $resCY = $getExpenseRestricted($exp_ids, $start_date, $end_date);
	        $unPY  = $getExpenseUnrestricted($exp_ids, $prev_start, $prev_end);
	        $resPY = $getExpenseRestricted($exp_ids, $prev_start, $prev_end);
	 
	        $total_exp_unCY  += $unCY;
	        $total_exp_resCY += $resCY;
	        $total_exp_unPY  += $unPY;
	        $total_exp_resPY += $resPY;
	 
	        $output[] = $makeRow($serial, $label, $note, $unCY, $resCY, $unPY, $resPY);
	    }
	 
	    // (d) Depreciation — placeholder (needs asset module)
	    $output[] = $makeRow('(d)', 'Depreciation and amortization expense', '19', 0, 0, 0, 0);
	 
	    // Total Expenses
	    $output[] = $makeTotalRow(
	        '', 'Total Expenses',
	        $total_exp_unCY, $total_exp_resCY,
	        $total_exp_unPY, $total_exp_resPY,
	        'font-weight:bolder; font-size:14px; border-top:2px solid #333;'
	    );
	 
	    $output[] = $spacer;
	 
	    // ================================================================
	    // V. Excess of Income over Expenditure (III - IV)
	    // ================================================================
	    $exc_unCY  = $total_inc_unCY  - $total_exp_unCY;
	    $exc_resCY = $total_inc_resCY - $total_exp_resCY;
	    $exc_unPY  = $total_inc_unPY  - $total_exp_unPY;
	    $exc_resPY = $total_inc_resPY - $total_exp_resPY;
	 
	    $output[] = $makeTotalRow(
	        'V',
	        'Excess of Income over Expenditure before exceptional and extraordinary items (III-IV)',
	        $exc_unCY, $exc_resCY, $exc_unPY, $exc_resPY,
	        'font-weight:bold; border-top:1px solid #aaa;'
	    );
	 
	    $output[] = $spacer;
	 
	    // ================================================================
	    // VI. Exceptional Items
	    // ================================================================
	    $output[] = $makeRow('VI', 'Exceptional items', '', 0, 0, 0, 0);
	 
	    // ================================================================
	    // VII. Excess before extraordinary items (V - VI)
	    // ================================================================
	    $output[] = $makeTotalRow(
	        'VII',
	        'Excess of Income over Expenditure before extraordinary items (V-VI)',
	        $exc_unCY, $exc_resCY, $exc_unPY, $exc_resPY,
	        'font-weight:bold; border-top:1px solid #aaa;'
	    );
	 
	    $output[] = $spacer;
	 
	    // ================================================================
	    // VIII. Extraordinary Items
	    // ================================================================
	    $output[] = $makeRow('VIII', 'Extraordinary Items', '', 0, 0, 0, 0);
	 
	    // ================================================================
	    // IX. Excess of Income over Expenditure for the year (VII - VIII)
	    // ================================================================
	    $output[] = $makeTotalRow(
	        'IX',
	        'Excess of Income over Expenditure for the year (VII-VIII)',
	        $exc_unCY, $exc_resCY, $exc_unPY, $exc_resPY,
	        'font-weight:bolder; font-size:14px; border-top:2px solid #333;'
	    );
	 
	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	public function get_other_income_notes() {
		if (!$this->input->is_ajax_request()) return;

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $CI  = &get_instance();
	    $fmt = 'formatAmount';

	    // Income comes from tblreceipts — same pattern as get_other_current_assets_notes
	    // but queries tblreceipts instead of tblpayments
	    $getIncomeAmount = function ($catId, $from, $to) use ($CI) {
	        $result = $CI->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) as total
	             FROM " . db_prefix() . "receipts
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };

	    $fmtCell = function ($val) use ($fmt) {
	        return $val == 0 ? '-' : $fmt($val);
	    };

	    // cat 5  = Other Income (parent)
	    //   cat 33 = Interest Income
	    //   cat 34 = Dividend Income
	    //   cat 35 = Net gain on sale of investments
	    //   cat 36 = Other non-operating income
	    $rows = [
	        ['(a)', 'Interest income',                         33],
	        ['(b)', 'Dividend income',                         34],
	        ['(c)', 'Net gain on sale of investments',         35],
	        ['(d)', 'Other non-operating income',              36],
	    ];

	    $output = [];

	    $output[] = [
	        '16', 'Other Income',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    $totalCY = 0;
	    $totalPY = 0;

	    foreach ($rows as $row) {
	        list($serial, $label, $catId) = $row;
	        $cy = $getIncomeAmount($catId, $start_date, $end_date);
	        $py = $getIncomeAmount($catId, $prev_start, $prev_end);
	        $totalCY += $cy;
	        $totalPY += $py;
	        $output[] = [$serial, $label, $fmtCell($cy), $fmtCell($py)];
	    }

	    $output[] = [
	        '', 'Total other income',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	public function get_material_consumed_notes() {
		if (!$this->input->is_ajax_request()) return;

	    list($start_date, $end_date)   = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end)   = $this->get_previous_report_dates($start_date, $end_date);

	    $fmt     = 'formatAmount';
	    $fmtCell = function ($val) use ($fmt) { return $val == 0 ? '-' : $fmt($val); };

	    // Helper: sum payments for a category (any level) in a date range
	    $getAmount = function ($catId, $from, $to) {
	        $result = $this->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) AS total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };

	    // cat 37 = Material consumed/distributed
	    // Sub-structure mirrors Excel Note 17:
	    //   (A) Materials consumed/distributed  → direct payments to cat 37
	    //   (B) Purchases of stock-in-trade     → (no sub-cat yet; placeholder)
	    //   (C) Changes in inventories          → (no sub-cat yet; placeholder)

	    $matCY = $getAmount(37, $start_date, $end_date);
	    $matPY = $getAmount(37, $prev_start, $prev_end);

	    $output = [];

	    // Note header
	    $output[] = [
	        '17', 'Cost of goods sold / Materials Consumed / Distributed',
	        '', '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    // Section A header
	    $output[] = [
	        '(A)', 'Materials consumed / distributed',
	        '', '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bold;']
	    ];

	    // Section A rows — raw material consumed
	    $output[] = ['(i)',   'Inventory at the beginning of the year', '',        $fmtCell(0),     $fmtCell(0)];
	    $output[] = ['(ii)',  'Add: Purchases / receipts during the year', '',     $fmtCell($matCY), $fmtCell($matPY)];
	    $output[] = ['(iii)', 'Less: Inventory at the end of the year', '',        $fmtCell(0),     $fmtCell(0)];
	    $output[] = [
	        '', 'Cost of material consumed / distributed (A)', '(I)',
	        $fmtCell($matCY), $fmtCell($matPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    // Section B header
	    $output[] = [
	        '(B)', 'Purchases of stock-in-trade',
	        '', '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bold; padding-top:10px;']
	    ];
	    $output[] = ['(i)',   '…', '', $fmtCell(0), $fmtCell(0)];
	    $output[] = [
	        '', 'Total (B)', '',
	        $fmtCell(0), $fmtCell(0),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    // Section C header
	    $output[] = [
	        '(C)', 'Changes in inventories of finished goods, work in progress and stock-in-trade',
	        '', '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bold; padding-top:10px;']
	    ];
	    $output[] = ['', 'Inventories at the beginning of the year:', '', '', ''];
	    $output[] = ['(i)',   'Stock-in-trade',  '', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['(ii)',  'Work in progress','', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['(iii)', 'Finished goods',  '', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['', '', '(I)', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['', 'Inventories at the end of the year:', '', '', ''];
	    $output[] = ['(i)',   'Stock-in-trade',  '', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['(ii)',  'Work in progress','', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['(iii)', 'Finished goods',  '', $fmtCell(0), $fmtCell(0)];
	    $output[] = ['', '', '(II)', $fmtCell(0), $fmtCell(0)];
	    $output[] = [
	        '', '(Increase)/decrease in inventories (C)', '',
	        $fmtCell(0), $fmtCell(0),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    // Grand total
	    $output[] = [
	        '', 'Total (A+B+C)', '',
	        $fmtCell($matCY), $fmtCell($matPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:2px solid #333; border-bottom:2px solid #333;']
	    ];

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	public function get_employee_benefits_notes() {
		if (!$this->input->is_ajax_request()) return;

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $fmt     = 'formatAmount';
	    $fmtCell = function ($val) use ($fmt) { return $val == 0 ? '-' : $fmt($val); };

	    // Helper: sum payments for a category (any level) in a date range
	    // cat 9 = Employee benefits expense (root); sub-cats when added will auto-resolve
	    $getAmount = function ($catId, $from, $to) {
	        $result = $this->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) AS total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };

	    // Sub-category IDs (map to Excel Note 18 line items).
	    // When sub-cats are created under cat 9, replace 0 with their IDs.
	    // Currently cat 9 has no sub-categories, so total is summed at root level.
	    $rows = [
	        ['(a)', 'Salaries, wages, bonus and other allowances', 0],
	        ['(b)', 'Contribution to provident and other funds',   0],
	        ['(c)', 'Gratuity expenses',                           0],
	        ['(d)', 'Staff welfare expenses',                      0],
	    ];

	    $output = [];

	    // Note header
	    $output[] = [
	        '18', 'Employee benefits expense',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];
	    $output[] = [
	        '', '(Including contract labour)',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-style:italic; color:#666;']
	    ];

	    $totalCY = $getAmount(9, $start_date, $end_date);
	    $totalPY = $getAmount(9, $prev_start, $prev_end);

	    foreach ($rows as $row) {
	        list($serial, $label, $catId) = $row;
	        // If sub-category exists, query it; otherwise show placeholder dash
	        $cy = $catId > 0 ? $getAmount($catId, $start_date, $end_date) : 0;
	        $py = $catId > 0 ? $getAmount($catId, $prev_start, $prev_end) : 0;
	        $output[] = [$serial, $label, $fmtCell($cy), $fmtCell($py)];
	    }

	    $output[] = [
	        '', 'Total Employee benefits expense',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	public function get_depreciation_notes() {
		if (!$this->input->is_ajax_request()) return;

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $fmt     = 'formatAmount';
	    $fmtCell = function ($val) use ($fmt) { return $val == 0 ? '-' : $fmt($val); };

	    // Tangible asset names (descendants of cat 25)
	    $tangibleNames = $this->db->select('name')
	        ->where_in('id', [19, 20, 21, 22, 23, 24, 26])
	        ->get(db_prefix() . 'categories')
	        ->result_array();
	    $tangibleNames = array_column($tangibleNames, 'name');

	    // Intangible asset names (descendants of cat 199)
	    $intangibleNames = $this->db->select('name')
	        ->where_in('id', [200, 201, 202, 203, 204])
	        ->get(db_prefix() . 'categories')
	        ->result_array();
	    $intangibleNames = array_column($intangibleNames, 'name');

	    // Core depreciation calc: sum dep for given asset names over a date range
	    $calcDep = function ($assetNames, $from, $to) {
	        if (empty($assetNames)) return 0.0;

	        $startMonth = date('Y-m', strtotime($from));

	        $nameList = implode(',', array_map(function ($n) {
	            return $this->db->escape($n);
	        }, $assetNames));

	        $latestAmountSql = "
	            SELECT ad.category, ad.amount
	            FROM " . db_prefix() . "asset_depreciation ad
	            INNER JOIN (
	                SELECT category, MAX(month_year) AS max_month
	                FROM " . db_prefix() . "asset_depreciation
	                WHERE month_year < " . $this->db->escape($startMonth) . "
	                GROUP BY category
	            ) latest ON latest.category = ad.category
	                    AND latest.max_month = ad.month_year
	        ";

	        $latestRateSql = "
	            SELECT ad.category, ad.rate
	            FROM " . db_prefix() . "asset_depreciation ad
	            INNER JOIN (
	                SELECT category, MAX(month_year) AS max_month
	                FROM " . db_prefix() . "asset_depreciation
	                GROUP BY category
	            ) latest ON latest.category = ad.category
	                    AND latest.max_month = ad.month_year
	        ";

	        $rows = $this->db->query("
	            SELECT
	                SUM(CASE WHEN MONTH(t.date) BETWEEN 4 AND 9 THEN t.received_amount ELSE 0 END) AS first_half,
	                SUM(CASE WHEN MONTH(t.date) NOT BETWEEN 4 AND 9 THEN t.received_amount ELSE 0 END) AS second_half,
	                COALESCE(ad_amount.amount, 0) AS wd_value,
	                COALESCE(ad_rate.rate, 0) AS rate
	            FROM " . db_prefix() . "transaction t
	            LEFT JOIN ($latestAmountSql) ad_amount ON ad_amount.category = t.asset_category
	            LEFT JOIN ($latestRateSql) ad_rate ON ad_rate.category = t.asset_category
	            WHERE t.type = 2
	              AND t.received_amount IS NOT NULL
	              AND t.received_amount != 0
	              AND t.asset_category IN ($nameList)
	              AND t.date BETWEEN " . $this->db->escape($from) . " AND " . $this->db->escape($to) . "
	            GROUP BY t.asset_category, ad_amount.amount, ad_rate.rate
	        ")->result();

	        $total = 0.0;
	        foreach ($rows as $row) {
	            $total += round(($row->wd_value + $row->first_half) * ($row->rate / 100), 2)
	                    + round($row->second_half * ($row->rate / 2 / 100), 2);
	        }
	        return $total;
	    };

	    $tangCY  = $calcDep($tangibleNames,   $start_date, $end_date);
	    $tangPY  = $calcDep($tangibleNames,   $prev_start, $prev_end);
	    $intanCY = $calcDep($intangibleNames, $start_date, $end_date);
	    $intanPY = $calcDep($intangibleNames, $prev_start, $prev_end);

	    $totalCY = $tangCY  + $intanCY;
	    $totalPY = $tangPY  + $intanPY;

	    $output = [];

	    $output[] = [
	        '19', 'Depreciation and amortization expense',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    $output[] = ['(a)', 'On tangible assets (Refer note 11)',   $fmtCell($tangCY),  $fmtCell($tangPY)];
	    $output[] = ['(b)', 'On intangible assets (Refer note 11)', $fmtCell($intanCY), $fmtCell($intanPY)];

	    $output[] = [
	        '', 'Total Depreciation and amortization expense',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	public function get_finance_cost_notes() {
		if (!$this->input->is_ajax_request()) return;

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $fmt     = 'formatAmount';
	    $fmtCell = function ($val) use ($fmt) { return $val == 0 ? '-' : $fmt($val); };

	    $getAmount = function ($catId, $from, $to) {
	        $result = $this->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) AS total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };

	    // cat 39 = Finance costs (root)
	    //   cat 40 = Interest expense on bank loan
	    //   cat 41 = Interest on assets on finance lease
	    //   cat 42 = Other borrowing costs
	    //   cat 43 = Loss on foreign exchange transaction

	    $bankLoanCY  = $getAmount(40, $start_date, $end_date);
	    $bankLoanPY  = $getAmount(40, $prev_start, $prev_end);
	    $leaseCY     = $getAmount(41, $start_date, $end_date);
	    $leasePY     = $getAmount(41, $prev_start, $prev_end);
	    $otherCY     = $getAmount(42, $start_date, $end_date);
	    $otherPY     = $getAmount(42, $prev_start, $prev_end);
	    $fxCY        = $getAmount(43, $start_date, $end_date);
	    $fxPY        = $getAmount(43, $prev_start, $prev_end);

	    $intSubTotalCY = $bankLoanCY + $leaseCY;
	    $intSubTotalPY = $bankLoanPY + $leasePY;
	    $totalCY       = $intSubTotalCY + $otherCY + $fxCY;
	    $totalPY       = $intSubTotalPY + $otherPY + $fxPY;

	    $output = [];

	    // Note header
	    $output[] = [
	        '20', 'Finance cost',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    // (a) Interest expense — section header, no amount
	    $output[] = [
	        '(a)', 'Interest expense',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bold;']
	    ];

	    $output[] = ['(i)',  'On bank loan',                    $fmtCell($bankLoanCY), $fmtCell($bankLoanPY)];
	    $output[] = ['(ii)', 'On assets on finance lease',      $fmtCell($leaseCY),    $fmtCell($leasePY)];

	    $output[] = [
	        '', 'Sub-total (a)',
	        $fmtCell($intSubTotalCY), $fmtCell($intSubTotalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #ddd;']
	    ];

	    // (b) Other borrowing costs
	    $output[] = ['(b)', 'Other borrowing costs', $fmtCell($otherCY), $fmtCell($otherPY)];

	    // (c) Loss on foreign exchange
	    $output[] = [
	        '(c)', 'Loss on foreign exchange transactions and translations considered as finance cost (net)',
	        $fmtCell($fxCY), $fmtCell($fxPY)
	    ];

	    // Total
	    $output[] = [
	        '', 'Total Finance cost',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	public function get_other_expense_notes() {
		if (!$this->input->is_ajax_request()) return;

	    list($start_date, $end_date) = $this->get_report_start_end_dates();
	    list($prev_start, $prev_end) = $this->get_previous_report_dates($start_date, $end_date);

	    $fmt     = 'formatAmount';
	    $fmtCell = function ($val) use ($fmt) { return $val == 0 ? '-' : $fmt($val); };

	    $getAmount = function ($catId, $from, $to) {
	        $result = $this->db->query(
	            "SELECT COALESCE(SUM(received_amount), 0) AS total
	             FROM " . db_prefix() . "payments
	             WHERE date BETWEEN ? AND ?
	             AND (
	                 category_id = ? OR sub_category_id = ?
	                 OR sub_sub_category_id = ? OR sub_sub_sub_category_id = ?
	             )",
	            [$from, $to, $catId, $catId, $catId, $catId]
	        )->row();
	        return (float) $result->total;
	    };

	    // Section (a): Religious/charitable — cat 59
	    $relCY = $getAmount(59, $start_date, $end_date);
	    $relPY = $getAmount(59, $prev_start, $prev_end);

	    // Section (b): Other Expenses — cat 15 sub-cats
	    // [serial, label, cat_id]
	    $bRows = [
	        ['(i)',     'Consumption of stores and spare parts',              44],
	        ['(ii)',    'Power and fuel',                                      45],
	        ['(iii)',   'Rent',                                                10],
	        ['(iv)',    'Repairs and maintenance - Buildings',                 46],
	        ['(v)',     'Repairs and maintenance - Machinery',                 47],
	        ['(vi)',    'Insurance',                                           48],
	        ['(vii)',   'Rent, Rates and taxes, excluding, taxes on income',   49],
	        ['(viii)',  'Labour charges',                                      50],
	        ['(ix)',    'Travelling expenses',                                 12],
	        ['(x)',     "Auditor's remuneration",                              51],
	        ['(xi)',    'Printing and stationery',                             13],
	        ['(xii)',   'Communication expenses',                              52],
	        ['(xiii)',  'Legal and professional charges',                      53],
	        ['(xiv)',   'Advertisement and publicity',                         54],
	        ['(xv)',    'Business promotion expenses',                         55],
	        ['(xvi)',   'Commission',                                          56],
	        ['(xvii)',  'Clearing and forwarding charges',                     57],
	        ['(xviii)', 'Miscellaneous expenses',                              58],
	    ];

	    $bLinesCY = [];
	    $bLinesPY = [];
	    $bTotalCY = 0.0;
	    $bTotalPY = 0.0;

	    foreach ($bRows as $r) {
	        $cy = $getAmount($r[2], $start_date, $end_date);
	        $py = $getAmount($r[2], $prev_start, $prev_end);
	        $bLinesCY[$r[2]] = $cy;
	        $bLinesPY[$r[2]] = $py;
	        $bTotalCY += $cy;
	        $bTotalPY += $py;
	    }

	    $totalCY = $relCY + $bTotalCY;
	    $totalPY = $relPY + $bTotalPY;

	    $output = [];

	    // Note header
	    $output[] = [
	        '21', 'Other Expenses',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bolder; font-size:15px; border-bottom:2px solid #333;']
	    ];

	    // Section (a): Religious/charitable
	    $output[] = [
	        '(a)', 'Religious/charitable',
	        $fmtCell($relCY), $fmtCell($relPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold;']
	    ];

	    // Section (b) header
	    $output[] = [
	        '(b)', 'Other Expenses',
	        '', '',
	        'DT_RowAttr' => ['style' => 'font-weight:bold; padding-top:8px;']
	    ];

	    // Section (b) line items
	    foreach ($bRows as $r) {
	        $output[] = [
	            $r[0], $r[1],
	            $fmtCell($bLinesCY[$r[2]]),
	            $fmtCell($bLinesPY[$r[2]])
	        ];
	    }

	    // Section (b) sub-total
	    $output[] = [
	        '', 'Sub-total (b)',
	        $fmtCell($bTotalCY), $fmtCell($bTotalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #ddd;']
	    ];

	    // Grand total
	    $output[] = [
	        '', 'Total Other Expenses',
	        $fmtCell($totalCY), $fmtCell($totalPY),
	        'DT_RowAttr' => ['style' => 'font-weight:bold; border-top:1px solid #aaa;']
	    ];

	    echo json_encode([
	        'draw'            => (int) $this->input->post('draw'),
	        'recordsTotal'    => count($output),
	        'recordsFiltered' => count($output),
	        'data'            => $output,
	    ]);
	    die;
	}

	private function writeSheetData($sheet, $rows) {
		$rowNo = 1;

		foreach($rows as $row) {
			$colNo = 1;

			$rowStyle = '';
			if(isset($row['DT_RowAttr']['style'])) {
				$rowStyle = $row['DT_RowAttr']['style'];
			}

			foreach($row as $key => $value) {
				if($key == 'DT_RowAttr') {
					continue;
				}

				$column = Coordinate::stringFromColumnIndex($colNo);
				$cellAddress = $column . $rowNo;
				$sheet->setCellValue($cellAddress, is_array($value) ? '' : $value);

				$colNo++;
			}

			$lastColumn = Coordinate::stringFromColumnIndex($colNo - 1);
			if(!empty($rowStyle)) {
				$range = "A{$rowNo}:{$lastColumn}{$rowNo}";

				if(strpos($rowStyle, 'font-weight:bolder') !== false || strpos($rowStyle, 'font-weight: bolder') !== false) {
					$sheet->getStyle($range)->getFont()->setBold(true);
				}

				if(strpos($rowStyle, 'font-size:14px') !== false) {
					$sheet->getStyle($range)->getFont()->setSize(14);
				}

				if(strpos($rowStyle, 'font-size:16px') !== false) {
					$sheet->getStyle($range)->getFont()->setSize(16);
				}
			}

			$rowNo++;
		}

		$highestColumn = $sheet->getHighestColumn();

		foreach(range('A', $highestColumn) as $column) {
			$sheet->getColumnDimension($column)->setAutoSize(true);
		}

		$sheet->freezePane('A2');
	}

	public function transaction_details() {
		$ids = explode(',', $this->input->post('transaction_ids'));

		$transactions = $this->db->where_in('id', $ids)->order_by('date', 'ASC')->get(db_prefix() . 'transaction')->result_array();
		echo json_encode($transactions);
		die;
	}

	private function get_general_fund_amount($start_date)
	{
	    $total = ['surplus' => 0.0, 'deficit' => 0.0];
	 
	    $catTable     = db_prefix() . 'categories';
	    $receiptTable = db_prefix() . 'receipts';
	    $paymentTable = db_prefix() . 'payments';
	    $deprTable    = db_prefix() . 'asset_depreciation';
	 
	    // =========================================================
	    // HELPER: Recursively collect all child category IDs
	    // =========================================================
	    $getChildCategoryIds = function ($parentIds) use (&$getChildCategoryIds, $catTable) {
	        if ( ! is_array($parentIds)) {
	            $parentIds = [$parentIds];
	        }
	        if (empty($parentIds)) {
	            return [];
	        }
	        $children = $this->db->select('id')
	            ->where_in('parent_id', $parentIds)
	            ->get($catTable)
	            ->result_array();
	 
	        $childIds = array_column($children, 'id');
	        if ( ! empty($childIds)) {
	            $childIds = array_unique(
	                array_merge($childIds, $getChildCategoryIds($childIds))
	            );
	        }
	        return $childIds;
	    };
	 
	    // =========================================================
	    // CATEGORY GROUPS  (must mirror balance_sheet_report exactly)
	    // =========================================================
	 
	    // Corpus: category 27 (Unrestricted funds) + children [28=Corpus, 29=Designated]
	    $corpusParentIds  = [27];
	    $corpusAllIds     = array_unique(
	        array_merge($corpusParentIds, $getChildCategoryIds($corpusParentIds))
	    );
	 
	    // Loan receipts: category 4 (Non-current liabilities) + all its children
	    $loanReceiptParentIds = [4];
	    $loanReceiptAllIds    = array_unique(
	        array_merge($loanReceiptParentIds, $getChildCategoryIds($loanReceiptParentIds))
	    );
	 
	    // Loan repayment payments: category 16 only
	    $loanPaymentIds = [16];
	 
	    // Asset categories: 197 (Non-current assets) + 198 (Current assets) + all children
	    $assetParentIds = [197, 198];
	    $assetAllIds    = array_unique(
	        array_merge($assetParentIds, $getChildCategoryIds($assetParentIds))
	    );
	 
	    // =========================================================
	    // PRIOR INCOME
	    // tblreceipts — all receipts BEFORE $start_date
	    // Exclude: corpus categories + loan receipt categories
	    // =========================================================
	    $excludeFromIncome = array_unique(
	        array_merge($corpusAllIds, $loanReceiptAllIds)
	    );
	 
	    $priorIncome = 0.0;
	    if ( ! empty($excludeFromIncome)) {
	        $row = $this->db
	            ->select_sum('received_amount')
	            ->where_not_in('category_id', $excludeFromIncome)
	            ->where('date <', $start_date)
	            ->get($receiptTable)
	            ->row();
	        $priorIncome = (float) ($row->received_amount ?? 0);
	    } else {
	        $row = $this->db
	            ->select_sum('received_amount')
	            ->where('date <', $start_date)
	            ->get($receiptTable)
	            ->row();
	        $priorIncome = (float) ($row->received_amount ?? 0);
	    }
	 
	    // =========================================================
	    // PRIOR EXPENDITURE
	    // tblpayments — all payments BEFORE $start_date
	    // Exclude: loan repayment categories + all asset categories
	    // =========================================================
	    $excludeFromExpenditure = array_unique(
	        array_merge($loanPaymentIds, $assetAllIds)
	    );
	 
	    $priorExpenditure = 0.0;
	    if ( ! empty($excludeFromExpenditure)) {
	        $row = $this->db
	            ->select_sum('received_amount')
	            ->where_not_in('category_id', $excludeFromExpenditure)
	            ->where('date <', $start_date)
	            ->get($paymentTable)
	            ->row();
	        $priorExpenditure = (float) ($row->received_amount ?? 0);
	    } else {
	        $row = $this->db
	            ->select_sum('received_amount')
	            ->where('date <', $start_date)
	            ->get($paymentTable)
	            ->row();
	        $priorExpenditure = (float) ($row->received_amount ?? 0);
	    }
	 
	    // =========================================================
	    // PRIOR RECEIVABLES
	    // tblreceipts — amounts still outstanding from BEFORE $start_date
	    // (receipts not fully collected yet)
	    // =========================================================
	    $row = $this->db
	        ->select_sum('balance')
	        ->where('is_fully_received', 0)
	        ->where('balance !=', 0)
	        ->where('date <', $start_date)
	        ->get($receiptTable)
	        ->row();
	    $priorReceivable = (float) ($row->balance ?? 0);
	 
	    // =========================================================
	    // PRIOR PAYABLES
	    // tblpayments — amounts still outstanding from BEFORE $start_date
	    // (payments not fully settled yet)
	    // =========================================================
	    $row = $this->db
	        ->select_sum('balance')
	        ->where('is_fully_paid', 0)
	        ->where('balance !=', 0)
	        ->where('date <', $start_date)
	        ->get($paymentTable)
	        ->row();
	    $priorPayable = (float) ($row->balance ?? 0);
	 
	    // =========================================================
	    // PRIOR DEPRECIATION
	    // Based on asset purchases in tblpayments BEFORE $start_date.
	    // WDV method with half-year convention:
	    //   - Assets purchased in Apr–Sep (first half of Indian FY): full WDV rate
	    //   - Assets purchased in Oct–Mar (second half): half rate
	    // =========================================================
	    $priorDepreciation = 0.0;
	 
	    if ( ! empty($assetAllIds)) {
	 
	        $startMonth = date('Y-m', strtotime($start_date));
	 
	        // Latest WDV book value per category before this period
	        $latestAmountSql = "
	            SELECT ad.category, ad.amount
	            FROM {$deprTable} ad
	            INNER JOIN (
	                SELECT category, MAX(month_year) AS max_month
	                FROM {$deprTable}
	                WHERE month_year < " . $this->db->escape($startMonth) . "
	                GROUP BY category
	            ) latest
	                ON latest.category   = ad.category
	                AND latest.max_month = ad.month_year
	        ";
	 
	        // Latest depreciation rate per category (all-time latest)
	        $latestRateSql = "
	            SELECT ad.category, ad.rate
	            FROM {$deprTable} ad
	            INNER JOIN (
	                SELECT category, MAX(month_year) AS max_month
	                FROM {$deprTable}
	                GROUP BY category
	            ) latest
	                ON latest.category   = ad.category
	                AND latest.max_month = ad.month_year
	        ";
	 
	        $assetIdList = implode(',', array_map('intval', $assetAllIds));
	 
	        $deprRows = $this->db->query("
	            SELECT
	                p.category_id,
	                SUM(
	                    CASE WHEN MONTH(p.date) BETWEEN 4 AND 9
	                    THEN p.received_amount ELSE 0 END
	                ) AS first_half,
	                SUM(
	                    CASE WHEN MONTH(p.date) NOT BETWEEN 4 AND 9
	                    THEN p.received_amount ELSE 0 END
	                ) AS second_half,
	                COALESCE(ad_amount.amount, 0) AS wd_value,
	                COALESCE(ad_rate.rate,    0) AS rate
	            FROM {$paymentTable} p
	            LEFT JOIN ({$latestAmountSql}) ad_amount
	                ON ad_amount.category = p.category_id
	            LEFT JOIN ({$latestRateSql}) ad_rate
	                ON ad_rate.category = p.category_id
	            WHERE p.category_id IN ({$assetIdList})
	                AND p.received_amount IS NOT NULL
	                AND p.received_amount != 0
	                AND p.date < " . $this->db->escape($start_date) . "
	            GROUP BY p.category_id
	        ")->result();
	 
	        foreach ($deprRows as $row) {
	            // Full WDV rate on opening WDV + first-half additions
	            $wdvDep  = (($row->wd_value + $row->first_half) * $row->rate) / 100;
	            // Half rate on second-half additions
	            $halfDep = ($row->second_half * ($row->rate / 2)) / 100;
	            $priorDepreciation += round($wdvDep + $halfDep, 2);
	        }
	    }
	 
	    // =========================================================
	    // COMPUTE GENERAL FUND
	    // =========================================================
	    $totalPriorIncome      = $priorIncome + $priorReceivable;
	    $totalPriorExpenditure = $priorExpenditure + $priorPayable;
	 
	    if ($totalPriorIncome >= $totalPriorExpenditure) {
	        $total['surplus'] = ($totalPriorIncome - $totalPriorExpenditure) - $priorDepreciation;
	        $total['deficit'] = 0.0;
	    } else {
	        $total['surplus'] = 0.0;
	        $total['deficit'] = ($totalPriorExpenditure - $totalPriorIncome) + $priorDepreciation;
	    }
	 
	    return $total;
	}

	private function get_where_report_period($field = 'date')
    {
        $months_report      = $this->input->post('report_months');
        $custom_date_select = '';
        if ($months_report != '') {
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $from_date . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
        }

        return $custom_date_select;
    }

    private function get_report_start_end_dates() {
    	$months_report = $this->input->post('report_months');

    	$start_date = $end_date = null;

    	if($months_report != '') {
    		if(is_numeric($months_report)) {
    			// last month
    			if($months_report == '1') {
    				$start_date = date('Y-m-01', strtotime('first day of last month'));
    				$end_date = date('Y-m-t', strtotime('last day of last month'));
    			} else {
    				$months_report = (int) $months_report - 1;
    				$start_date = date('Y-m-01', strtotime("-$months_report MONTH"));
    				$end_date = date('Y-m-t');
    			}
    		} elseif($months_report == 'this_month') {
    			$start_date = date('Y-m-01');
    			$end_date = date('Y-m-t');
    		} elseif($months_report == 'this_year') {
    			$start_date = date('Y-01-01');
    			$end_date = date('Y-m-t');
    		} elseif($months_report == 'last_year') {
    			$year = date('Y', strtotime('last year'));
    			$start_date = $year . '-01-01';
    			$end_date = $year . '-12-31';
    		} elseif($months_report == 'custom') {
    			$start_date = to_sql_date($this->input->post('report_from'));
    			$end_date = to_sql_date($this->input->post('report_to'));
    		}
    	}

    	if(!$start_date || !$end_date) {
    		$start_date = date('Y-m-01');
    		$end_date = date('Y-m-d');
    	}

    	return [$start_date, $end_date];
    }

    public function get_previous_report_dates($start_date, $end_date) {
    	$start = new DateTime($start_date);
    	$end = new DateTime($end_date);

    	$total_days = $start->diff($end)->days + 1;

    	$prev_end = clone $start;
    	$prev_end->modify('-1 day');

    	$prev_start = clone $prev_end;
    	$prev_start->modify('-' . ($total_days - 1) . ' days');

    	return [$prev_start->format('Y-m-d'), $prev_end->format('Y-m-d')];
    }

    public function categoryMatch() {
    	$categories = [];

    	$category_records = $this->db
		    ->order_by('id', 'ASC')
		    ->get(db_prefix() . 'categories')
		    ->result_array();

		$categories = [];

		foreach ($category_records as $category) {

		    $categories[] = [
		        'id' => (int) $category['id'],
		        'name' => $category['name'],
		        'type' => (int) $category['type'],
		        'parent_id' => (int) $category['parent_id'],
		        'is_default' => (int) $category['is_default'],
		        'datecreated' => date('Y-m-d H:i:s'),
		    ];
		}

		echo '<pre>';

		echo '$categories = [' . PHP_EOL;

		foreach ($categories as $category) {

		    echo '    [' . PHP_EOL;
		    echo "        'id' => " . $category['id'] . "," . PHP_EOL;
		    echo "        'name' => '" . addslashes($category['name']) . "'," . PHP_EOL;
		    echo "        'type' => " . $category['type'] . "," . PHP_EOL;
		    echo "        'parent_id' => " . $category['parent_id'] . "," . PHP_EOL;
		    echo "        'is_default' => " . $category['is_default'] . "," . PHP_EOL;
		    echo "        'datecreated' => date('Y-m-d H:i:s')," . PHP_EOL;
		    echo '    ],' . PHP_EOL;
		}

		echo '];';

		echo '</pre>';

		exit;
    }
}