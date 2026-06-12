<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment extends AdminController
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('BankAccount_model');
        $this->load->model('categories_model');
        $this->load->model('payment_model');
        $this->load->model('transaction_model');
        $this->load->model('currencies_model');
    }

	public function index() {
        if (staff_cant('view', 'payment')) {
            access_denied('payment');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(NGO_TRUST_MODULE_NAME, 'payment/table'));
        }

        $data['title']                 = _l('payment');
        $this->load->view('payment/manage', $data);
    }

    public function create($id = '') {
    	if (staff_cant('view', 'payment')) {
            access_denied('payment');
        }

        if($this->input->post()) {
            $data = $this->input->post();
            $amount = (float) $data['amount'];
            $paid = isset($data['received_amount']) ? (float) $data['received_amount'] : 0;

            if($paid > $amount) {
                $paid = $amount;
            }

            if($this->input->post('is_fully_paid')) {
                $paid = $amount;
                $balance = 0;
                $is_full = 1;
            } elseif($this->input->post('is_nill_paid')) {
                $paid = 0;
                $balance = $amount;
                $is_full = 0;
            } else {
                $balance = $amount - $paid;
                $is_full = ($balance > 0) ? 0 : 1;
            }

            $data['received_amount'] = $paid;
            $data['balance'] = $balance;
            $data['is_fully_paid'] = $is_full;
            $data['reference'] = !empty($data['reference']) ? $data['reference'] : Null;
            $data['description'] = !empty($data['description']) ? $data['description'] : Null;
            $data['vendorid'] = !empty($data['vendorid']) ? $data['vendorid'] : Null;

            if($id == '') {
                if (staff_cant('create', 'payment')) {
                    access_denied('payment');
                }

                $data['attachment'] = $this->handle_payment_attachment();
                unset($data['is_nill_paid']);
                $payment_id = $this->payment_model->add($data);
                if(!$payment_id) {
                    set_alert('danger', _l('payment_creation_failed'));
                    redirect(admin_url('ngo_trust/payment'));
                }

                $account = $this->BankAccount_model->get($data['account_id']);
                $category = $this->categories_model->get($data['category_id']);
                $sub_category = $this->categories_model->get($data['sub_category_id']);
                $sub_sub_category = $this->categories_model->get($data['sub_sub_category_id']);
                $sub_sub_sub_category = $this->categories_model->get($data['sub_sub_sub_category_id']);
                $receipt_category = $this->categories_model->get($data['receipt_category_id']);
                $asset_category = !empty($data['asset_category_id']) ? $this->categories_model->get($data['asset_category_id']) : Null;

                $this->transaction_model->add([
                    'track_id' => $payment_id,
                    'type' => 2,
                    'category' => $category->name,
                    'sub_category' => !empty($sub_category->name) ? $sub_category->name : Null,
                    'sub_sub_category' => !empty($sub_sub_category->name) ? $sub_sub_category->name : Null,
                    'sub_sub_sub_category' => !empty($sub_sub_sub_category->name) ? $sub_sub_sub_category->name : Null,
                    'receipt_category' => $receipt_category->name,
                    'asset_category' => $asset_category->name,
                    'account_id' => $data['account_id'],
                    'amount' => $amount,
                    'received_amount' => $paid,
                    'balance' => $balance,
                    'date' => $data['date'],
                    'previous_balance' => $account->opening_balance,
                    'total_balance' => $account->opening_balance - $paid,
                    'is_fully_paid_or_received' => $is_full,
                ]);

                if($paid > 0) {
                    $this->BankAccount_model->accountBalance($data['account_id'], $paid, 'debit');
                }

                set_alert('success', _l('added_successfully', _l('payment')));
                redirect(admin_url('ngo_trust/payment'));
            } else {
                if(staff_cant('edit', 'payment')) {
                    access_denied('payment');
                }

                $payment = $this->payment_model->get($id);
                if(!$payment) {
                    show_404();
                }

                $this->db->trans_start();

                $oldTxns = $this->transaction_model->get_by_track_id($id, 2, 'DESC');
                foreach($oldTxns as $txn) {
                    if($txn->received_amount > 0) {
                        $this->BankAccount_model->accountBalance($txn->account_id, $txn->received_amount, 'credit');
                    }
                }

                $transactions = $this->input->post('transactions');

                $data['attachment'] = $this->handle_payment_attachment(!empty($payment->attachment) ? $payment->attachment : Null);

                unset($data['received_amount'], $data['balance'], $data['is_fully_paid'], $data['transactions'], $data['is_nill_paid']);

                $data['sub_category_id'] = isset($data['sub_category_id']) && $data['sub_category_id'] != '' ? $data['sub_category_id'] : Null;
                $data['sub_sub_category_id'] = isset($data['sub_sub_category_id']) && $data['sub_sub_category_id'] != '' ? $data['sub_sub_category_id'] : Null;
                $data['sub_sub_sub_category_id'] = isset($data['sub_sub_sub_category_id']) && $data['sub_sub_sub_category_id'] != '' ? $data['sub_sub_sub_category_id'] : Null;

                $this->payment_model->update($data, $id);

                $category = $this->categories_model->get($data['category_id']);
                $sub_category = $this->categories_model->get($data['sub_category_id']);
                $sub_sub_category = $this->categories_model->get($data['sub_sub_category_id']);
                $sub_sub_sub_category = $this->categories_model->get($data['sub_sub_sub_category_id']);

                $runningPaid = $totalPaid = 0;

                foreach($transactions as $txn) {
                    $paidAmt = (float) $txn['received_amount'];
                    $runningPaid += $paidAmt;
                    $totalPaid += $paidAmt;

                    if($paidAmt <= 0) {
                        $this->transaction_model->delete($txn['id']);
                        continue;
                    }

                    if($totalPaid > $amount) {
                        $this->db->trans_rollback();
                        set_alert('danger', _l('paid_amount_cannot_exceed_amount'));
                        redirect(admin_url('ngo_trust/payment'));
                    }

                    $txnBalance = max(0, $amount - $runningPaid);
                    if($txnBalance < 0) {
                        $txnBalance = 0;
                    }

                    $account = $this->BankAccount_model->get($txn['account_id']);
                    $prevBalance = $account->opening_balance;
                    $totalBalance = $account->opening_balance - $paidAmt;

                    $receipt_category = $this->categories_model->get($txn['receipt_category_id']);
                    $asset_category = !empty($data['asset_category_id']) ? $this->categories_model->get($data['asset_category_id']) : Null;

                    if(!empty($txn['id'])) {
                        $this->transaction_model->update([
                            'date' => $txn['date'],
                            'account_id' => $txn['account_id'],
                            'category' => $category->name,
                            'sub_category' => !empty($sub_category->name) ? $sub_category->name : Null,
                            'sub_sub_category' => !empty($sub_sub_category->name) ? $sub_sub_category->name : Null,
                            'sub_sub_sub_category' => !empty($sub_sub_sub_category->name) ? $sub_sub_sub_category->name : Null,
                            'receipt_category' => $receipt_category->name,
                            'asset_category' => $asset_category ? $asset_category->name : Null,
                            'received_amount' => $paidAmt,
                            'balance' => $txnBalance,
                            'previous_balance' => $prevBalance,
                            'total_balance' => $totalBalance,
                            'is_fully_paid_or_received' => ($txnBalance > 0) ? 0 : 1,
                        ], $txn['id']);
                    } else {
                        $this->transaction_model->add([
                            'track_id' => $id,
                            'type' => 2,
                            'category' => $category->name,
                            'sub_category' => !empty($sub_category->name) ? $sub_category->name : Null,
                            'sub_sub_category' => !empty($sub_sub_category->name) ? $sub_sub_category->name : Null,
                            'sub_sub_sub_category' => !empty($sub_sub_sub_category->name) ? $sub_sub_sub_category->name : Null,
                            'receipt_category' => $receipt_category->name,
                            'asset_category' => $asset_category ? $asset_category->name : Null,
                            'account_id' => $txn['account_id'],
                            'amount' => $amount,
                            'received_amount' => $paidAmt,
                            'balance' => $txnBalance,
                            'date' => $txn['date'],
                            'previous_balance' => $prevBalance,
                            'total_balance' => $totalBalance,
                            'is_fully_paid_or_received' => ($txnBalance > 0) ? 0 : 1,
                        ]);
                    }

                    if($paidAmt > 0) {
                        $this->BankAccount_model->accountBalance($txn['account_id'], $paidAmt, 'debit');
                    }
                }

                $finalBalance = $amount - $totalPaid;
                $is_full = ($finalBalance > 0) ? 0 : 1;

                $this->payment_model->update([
                    'received_amount' => $totalPaid,
                    'balance' => $finalBalance,
                    'is_fully_paid' => $is_full,
                ], $id);

                $this->db->trans_complete();

                set_alert('success', _l('updated_successfully', _l('payment')));
                redirect(admin_url('ngo_trust/payment'));
            }
        }

        if($id != '') {
        	$data['payment'] = $this->payment_model->get($id);
            $data['payment_trans'] = $this->transaction_model->get_by_track_id($id, 2);
        }

        $data['accounts'] = $this->BankAccount_model->get();
        // $data['vendors'] = $this->vendors_model->get();
        $data['categories'] = $this->categories_model->get_by_type(2, true);
        $data['receipt_categories'] = $this->categories_model->get_by_type(1, true);
        $data['asset_categories'] = $this->categories_model->get_by_type(3, true);

        $this->load->view('payment/form', $data);
    }

    public function get_child_categories() {
        $category_id = $this->input->post('category_id');
        $data = $this->categories_model->get_sub_categories($category_id);
        echo json_encode($data);
    }

    public function view($id = '') {
        $payment = $this->payment_model->get($id);
        if($payment) {
            $data['payment'] = $this->db->select('p.*, c.name as category_name, rc.name as receipt_category_name, ac.name as asset_category_name, v.name as vendor')
                ->from(db_prefix() . 'payments p')
                ->join(db_prefix() . 'categories c', 'c.id = p.category_id', 'left')
                ->join(db_prefix() . 'categories rc', 'rc.id = p.receipt_category_id', 'left')
                ->join(db_prefix() . 'categories ac', 'ac.id = p.asset_category_id', 'left')
                ->join(db_prefix() . 'vendors v', 'v.vendorid = p.vendorid', 'left')
                ->where('p.id', $id)
                ->get()
                ->row();

            $data['transactions'] = $this->db->select('t.*, a.bank_name as account_name')
                ->from(db_prefix() . 'transaction t')
                ->join(db_prefix() . 'bank_accounts a', 'a.id = t.account_id', 'left')
                ->where('t.type', 2)
                ->where('t.track_id', $id)
                ->get()
                ->result_array();

            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $this->load->view('payment/show', $data);
        }
    }

    public function makePayment($id = '') {
        $payment = $this->payment_model->get($id);
        if(!$payment) {
            show_404();
        }

        if($this->input->post()) {
            $data = $this->input->post();
            $received_amount = $data['received_amount'];
            $account_id = !empty($data['account_id']) ? $data['account_id'] : $payment->account_id;
            $receipt_category_id = !empty($data['receipt_category_id']) ? $data['receipt_category_id'] : $payment->receipt_category_id;

            $this->db->trans_begin();

            $update = [
                'received_amount' => $payment->received_amount + $received_amount,
                'balance' => $payment->balance - $received_amount,
                'is_fully_paid' => ($payment->balance - $received_amount == 0) ? 1 : 0,
            ];
            $this->payment_model->update($update, $id);

            $account = $this->BankAccount_model->get($account_id);
            $category = $this->categories_model->get($payment->category_id);
            $sub_category = $this->categories_model->get($payment->sub_category_id);
            $sub_sub_category = $this->categories_model->get($payment->sub_sub_category_id);
            $sub_sub_sub_category = $this->categories_model->get($payment->sub_sub_sub_category_id);
            $receipt_category = $this->categories_model->get($receipt_category_id);
            $asset_category = $this->categories_model->get($payment->asset_category_id);

            $previous_balance = (float) $account->opening_balance;
            $total_balance = $previous_balance - $received_amount;

            $trackData = [
                'track_id' => $id,
                'type' => 2,
                'category' => !empty($category) ? $category->name : null,
                'sub_category' => !empty($sub_category) ? $sub_category->name : null,
                'sub_sub_category' => !empty($sub_sub_category) ? $sub_sub_category->name : null,
                'sub_sub_sub_category' => !empty($sub_sub_sub_category) ? $sub_sub_sub_category->name : null,
                'receipt_category' => !empty($receipt_category) ? $receipt_category->name : null,
                'asset_category' => !empty($asset_category) ? $asset_category->name : null,
                'account_id' => $account_id,
                'amount' => $payment->amount,
                'received_amount' => $received_amount,
                'balance' => $update['balance'],
                'date' => $data['date'],
                'previous_balance' => $previous_balance,
                'total_balance' => $total_balance,
                'is_fully_paid_or_received' => $update['is_fully_paid'],
            ];

            $this->transaction_model->add($trackData);
            $this->BankAccount_model->accountBalance($account_id, $received_amount, 'debit');

            if($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                set_alert('danger', _l('something_went_wrong'));
            } else {
                $this->db->trans_commit();
                set_alert('success', _l('payment_added_successfully'), _l('payment'));
            }

            redirect(admin_url('ngo_trust/payment'));
        }

        $data['title'] = _l('amount_to_paid');
        $data['payment'] = $payment;
        $data['accounts'] = $this->BankAccount_model->get();
        $data['categories'] = $this->categories_model->get_by_type(2, true);
        $data['receipt_categories'] = $this->categories_model->get_by_type(1, true);
        $data['asset_categories'] = $this->categories_model->get_by_type(3, true);

        $this->load->view('payment/makePayment', $data);
    }

    private function handle_payment_attachment($oldFile = Null) {
        if(!isset($_FILES['attachment']) || $_FILES['attachment']['name'] == '') {
            return $oldFile;
        }

        $upload_path = FCPATH . 'modules/ngo_trust/uploads/payments/';

        if(!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|pdf|doc|docx',
            'max_size' => 5120,
            'encrypt_name' => true,
        ];

        $this->load->library('upload', $config);

        if(!$this->upload->do_upload('attachment')) {
            set_alert('danger', $this->upload->display_errors());
            redirect(admin_url('ngo_trust/payment'));
        }

        $uploadedData = $this->upload->data();

        if($oldFile && file_exists(FCPATH . $oldFile)) {
            unlink(FCPATH . $oldFile);
        }

        return 'modules/ngo_trust/uploads/payments/' . $uploadedData['file_name'];
    }

    public function validateAmount() {
        if(!$this->input->is_ajax_request()) {
            show_404();
        }

        $account_id = $this->input->post('account_id');
        $category_id = $this->input->post('category_id');
        $receipt_category_id = $this->input->post('receipt_category_id');
        $amount = $this->input->post('amount');
        $payment_id = $this->input->post('payment_id');

        if(empty($account_id) || empty($receipt_category_id) || empty($amount)) {
            echo json_encode(['success' => true, 'message' => 'Valid']);
            exit;
        }

        if($category_id == 16) {
            $categoryReceipt = $this->db->select('name')->from(db_prefix() . 'categories')->where('id', 4)->get()->row('name');
            $categoryPayment = $this->db->select('name')->from(db_prefix() . 'categories')->where('id', 16)->get()->row('name');

            if(method_exists($this, 'checkLoanRepaymentAmount')) {
                $response = $this->checkLoanRepaymentAmount($categoryReceipt, $categoryPayment, $amount, $payment_id);

                if($response) {
                    echo json_encode($response);
                    exit;
                }
            }
        }

        $receipt_category = $this->db->where('id', $receipt_category_id)->get(db_prefix() . 'categories')->row();
        if(!$receipt_category) {
            echo json_encode(['success' => true, 'message' => 'Valid']);
            exit;
        }

        $account_info = $this->db->where('id', $account_id)->get(db_prefix() . 'bank_accounts')->row();

        $this->db->select_sum('received_amount');
        $this->db->where('type', 1);
        $this->db->where('category', $receipt_category->name);
        $total_receipt = (float) $this->db->get(db_prefix() . 'transaction')->row()->received_amount;

        $this->db->select_sum('received_amount');
        $this->db->where('type', 1);
        $this->db->where('category', $receipt_category->name);
        $this->db->where('account_id', $account_id);
        $account_total_receipt = (float) $this->db->get(db_prefix() . 'transaction')->row()->received_amount;

        $this->db->select_sum('received_amount');
        $this->db->where('type', 2);
        $this->db->where('receipt_category', $receipt_category->name);
        if(!empty($payment_id)) {
            $this->db->where('track_id != ', $payment_id);
        }
        $total_payment = (float) $this->db->get(db_prefix() . 'transaction')->row()->received_amount;

        $this->db->select_sum('received_amount');
        $this->db->where('type', 2);
        $this->db->where('receipt_category', $receipt_category->name);
        $this->db->where('account_id', $account_id);
        if(!empty($payment_id)) {
            $this->db->where('track_id != ', $payment_id);
        }
        $account_total_payment = (float) $this->db->get(db_prefix() . 'transaction')->row()->received_amount;

        $total_balance = $total_receipt - $total_payment;
        $accountTotalBalance = $account_total_receipt - $account_total_payment;

        if($accountTotalBalance < $amount) {
            $accountName = !empty($account_info->bank_name) ? $account_info->bank_name : $account_info->holder_name;

            echo json_encode([
                'success' => false,
                'type' => 1,
                'message1' => 'The payment amount exceeds the <strong>' . $receipt_category->name . '</strong> amount.',
                'message2' => 'The available <strong>' . $receipt_category->name . '</strong> balance in <strong>' . $accountName . '</strong> is <strong>' . $accountTotalBalance . '</strong>.',
                'message3' => 'The total available <strong>' . $receipt_category->name . '</strong> balance is <strong>' . $total_balance . '</strong>.'
            ]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Valid']);
        exit;
    }

    private function checkLoanRepaymentAmount($loanCategoryName, $repaymentCategoryName, $amount, $payment_id = Null) {
        // Total Loan Receipt i.e id = 4
        $this->db->select_sum('received_amount');
        $this->db->where('type', 1);
        $this->db->where('category', $loanCategoryName);
        $loanReceipt = (float) $this->db->get(db_prefix() . 'transaction')->row()->received_amount;

        // Total Loan Repayment i.e id = 16
        $this->db->select_sum('received_amount');
        $this->db->where('type', 2);
        $this->db->where('category', $repaymentCategoryName);

        if(!empty($payment_id)) {
            $this->db->where('track_id !=', $payment_id);
        }
        $loanPayment = (float) $this->db->get(db_prefix() . 'transaction')->row()->received_amount;

        // Outstanding Loan
        $loanBalance = $loanReceipt - $loanPayment;
        if($loanBalance < $amount) {
            return [
                'success' => false,
                'type' => 2,
                'message1' => 'The <strong>Loan Repayment</strong> amount cannot exceed the outstanding <strong>Loan</strong>,',
                'message2' => 'The balance <strong>Loan Outstanding</strong> is <strong>' . $loanBalance . '</strong>',
            ];
        }
        return Null;
    }

    public function delete($id) {
        if(staff_cant('delete', 'payment')) {
            access_denied('payment');
        }

        if(!$id) {
            redirect(admin_url('ngo_trust/payment'));
        }

        $response = $this->payment_model->delete($id, 2, true);
        if($response == true) {
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _('payment')));
        }
        redirect(admin_url('ngo_trust/payment'));
    }
}