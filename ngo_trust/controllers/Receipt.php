<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Receipt extends AdminController
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('BankAccount_model');
        $this->load->model('categories_model');
        $this->load->model('Receipt_model');
        $this->load->model('payment_model');
        $this->load->model('transaction_model');
        $this->load->model('currencies_model');
    }

	public function index() {
        if (staff_cant('view', 'receipt')) {
            access_denied('receipt');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(NGO_TRUST_MODULE_NAME, 'receipt/table'));
        }

        $data['title']                 = _l('receipt');
        $this->load->view('receipt/manage', $data);
    }

    public function create($id = '') {
        if(staff_cant('view', 'receipt')) {
            access_denied('receipt');
        }

        if($this->input->post()) {
            $data = $this->input->post();
            $amount = (float) $data['amount'];

            if($id == '') {
                if(staff_cant('create', 'receipt')) {
                    access_denied('receipt');
                }

                $received = isset($data['received_amount']) ? (float) $data['received_amount'] : 0;
                if($received > $amount) {
                    $received = $amount;
                }

                if($this->input->post('is_fully_received')) {
                    $received = $amount;
                    $balance = 0;
                    $is_full = 1;
                } elseif($this->input->post('is_nill_received')) {
                    $received = 0;
                    $balance = $amount;
                    $is_full = 0;
                } elseif($this->input->post('received_amount')) {
                    $balance = $amount - $received;
                    $is_full = $balance > 0 ? 0 : 1;
                }

                $data['received_amount'] = $received;
                $data['balance'] = $balance;
                $data['is_fully_received'] = $is_full;
                $data['reference'] = !empty($data['reference']) ? $data['reference'] : Null;
                $data['description'] = !empty($data['description']) ? $data['description'] : Null;
                $data['donor_id'] = !empty($data['donor_id']) ? $data['donor_id'] : Null;
                $data['attachment'] = $this->handle_receipt_attachment('receipts');

                $asset_category_id = !empty($data['asset_category_id']) ? $data['asset_category_id'] : Null;
                unset($data['asset_category_id'], $data['payment_category_id'], $data['is_nill_received']);

                $receipt_id = $this->Receipt_model->add($data);
                if(!$receipt_id) {
                    set_alert('danger', _l('receipt_creation_failed'));
                    redirect(admin_url('ngo_trust/receipt'));
                }

                // Transaction - Receipt
                $account = $this->BankAccount_model->get($data['account_id']);
                $category = $this->categories_model->get($data['category_id']);
                $sub_category = $this->categories_model->get($data['sub_category_id']);
                $sub_sub_category = $this->categories_model->get($data['sub_sub_category_id']);
                $sub_sub_sub_category = $this->categories_model->get($data['sub_sub_sub_category_id']);

                $this->transaction_model->add([
                    'track_id' => $receipt_id,
                    'type' => 1,
                    'category' => $category->name,
                    'sub_category' => !empty($sub_category->name) ? $sub_category->name : Null,
                    'sub_sub_category' => !empty($sub_sub_category->name) ? $sub_sub_category->name : Null,
                    'sub_sub_sub_category' => !empty($sub_sub_sub_category->name) ? $sub_sub_sub_category->name : Null,
                    'account_id' => $data['account_id'],
                    'amount' => $amount,
                    'received_amount' => $received,
                    'balance' => $balance,
                    'date' => $data['date'],
                    'previous_balance' => $account->opening_balance,
                    'total_balance' => $account->opening_balance + $received,
                    'is_fully_paid_or_received' => $is_full,
                ]);

                if($received > 0) {
                    $this->BankAccount_model->accountBalance($data['account_id'], $received, 'credit');
                }

                if(($data['sub_category_id'] == 28 || $data['sub_category_id'] == 29) && $received > 0) {
                    $payment_id = $this->payment_model->add([
                        'date' => $data['date'],
                        'amount' => $amount,
                        'received_amount' => $received,
                        'balance' => $balance,
                        'account_id' => $data['account_id'],
                        'vendorid' => Null,
                        'category_id' => 17,
                        'receipt_category_id' => $data['sub_category_id'],
                        'asset_category_id' => $asset_category_id,
                        'receipt_id' => $receipt_id,
                        'reference' => $data['reference'],
                        'description' => $data['description'],
                        'attachment' => $this->handle_receipt_attachment('payments'),
                        'is_fully_paid' => $is_full,
                    ]);

                    if($payment_id) {
                        $account = $this->BankAccount_model->get($data['account_id']);
                        $paymentCategory = $this->categories_model->get(17);
                        $receiptCategory = $this->categories_model->get($data['sub_category_id']);
                        $assetCategory = $this->categories_model->get($asset_category_id);

                        $this->transaction_model->add([
                            'track_id' => $payment_id,
                            'type' => 2,
                            'category' => $paymentCategory->name,
                            'receipt_category' => $receiptCategory->name,
                            'asset_category' => $assetCategory->name,
                            'account_id' => $data['account_id'],
                            'amount' => $amount,
                            'received_amount' => $received,
                            'balance' => $balance,
                            'date' => $data['date'],
                            'previous_balance' => $account->opening_balance,
                            'total_balance' => $account->opening_balance - $received,
                            'is_fully_paid_or_received' => $is_full,
                        ]);

                        if($received > 0) {
                            $this->BankAccount_model->accountBalance($data['account_id'], $received, 'debit');
                        }
                    }
                }

                set_alert('success', _l('added_successfully', 'receipt'));
                redirect(admin_url('ngo_trust/receipt'));
            } else {
                if(staff_cant('edit', 'receipt')) {
                    access_denied('receipt');
                }

                $receipt = $this->Receipt_model->get($id);
                if(!$receipt) {
                    show_404();
                }

                $asset_category_id = !empty($data['asset_category_id']) ? $data['asset_category_id'] : Null;

                $this->db->trans_start();

                // Revert Receipt transaction
                $oldReceiptTxns = $this->transaction_model->get_by_track_id($id, 1, 'DESC');
                foreach($oldReceiptTxns as $txn) {
                    if($txn->received_amount > 0) {
                        $this->BankAccount_model->accountBalance($txn->account_id, $txn->received_amount, 'debit');
                    }
                }

                // Revert Payment transaction
                $oldPayment = $this->payment_model->get_by_receipt_id($id);
                if($oldPayment) {
                    $oldPaymentTxns = $this->transaction_model->get_by_track_id($oldPayment->id, 2, 'DESC');
                    foreach($oldPaymentTxns as $txn) {
                        if($txn->received_amount > 0) {
                            $this->BankAccount_model->accountBalance($txn->account_id, $txn->received_amount, 'credit');
                        }
                    }
                }

                $transactions = $this->input->post('transactions');

                $data['reference'] = !empty($data['reference']) ? $data['reference'] : $receipt->reference;
                $data['description'] = !empty($data['description']) ? $data['description'] : $receipt->description;
                $data['donor_id'] = !empty($data['donor_id']) ? $data['donor_id'] : $receipt->donor_id;
                $data['attachment'] = $this->handle_receipt_attachment('receipts', !empty($receipt->attachment) ? $receipt->attachment : Null);

                unset($data['received_amount'], $data['balance'], $data['is_fully_received'], $data['payment_category_id'], $data['asset_category_id'], $data['transactions'], $data['is_nill_received']);

                $data['sub_category_id'] = isset($data['sub_category_id']) && $data['sub_category_id'] != '' ? $data['sub_category_id'] : Null;
                $data['sub_sub_category_id'] = isset($data['sub_sub_category_id']) && $data['sub_sub_category_id'] != '' ? $data['sub_sub_category_id'] : Null;
                $data['sub_sub_sub_category_id'] = isset($data['sub_sub_sub_category_id']) && $data['sub_sub_sub_category_id'] != '' ? $data['sub_sub_sub_category_id'] : Null;

                $this->Receipt_model->update($data, $id);

                $category = $this->categories_model->get($data['category_id']);
                $sub_category = $this->categories_model->get($data['sub_category_id']);
                $sub_sub_category = $this->categories_model->get($data['sub_sub_category_id']);
                $sub_sub_sub_category = $this->categories_model->get($data['sub_sub_sub_category_id']);

                $runningReceived = $totalReceived = 0;

                foreach($transactions as $txn) {
                    $receivedAmt = (float) $txn['received_amount'];
                    $runningReceived += $receivedAmt;
                    $totalReceived += $receivedAmt;

                    if($receivedAmt <= 0) {
                        $this->transaction_model->delete($txn['id']);
                        continue;
                    }

                    if($totalReceived > $amount) {
                        $this->db->trans_rollback();
                        set_alert('danger', _l('received_amount_cannot_exceed_amount'));
                        redirect(admin_url('ngo_trust/receipt'));
                    }

                    $txnBalance = $amount - $runningReceived;
                    if($txnBalance < 0) {
                        $txnBalance = 0;
                    }

                    $account = $this->BankAccount_model->get($txn['account_id']);
                    $prevBalance = $account->opening_balance;
                    $totalBalance = $account->opening_balance + $receivedAmt;

                    $this->transaction_model->update([
                        'date' => $txn['date'],
                        'account_id' => $txn['account_id'],
                        'category' => $category->name,
                        'sub_category' => !empty($sub_category->name) ? $sub_category->name : Null,
                        'sub_sub_category' => !empty($sub_sub_category->name) ? $sub_sub_category->name : Null,
                        'sub_sub_sub_category' => !empty($sub_sub_sub_category->name) ? $sub_sub_sub_category->name : Null,
                        'received_amount' => $receivedAmt,
                        'balance' => $txnBalance,
                        'previous_balance' => $prevBalance,
                        'total_balance' => $totalBalance,
                        'is_fully_paid_or_received' => $txnBalance > 0 ? 0 : 1,
                    ], $txn['id']);

                    if($receivedAmt > 0) {
                        $this->BankAccount_model->accountBalance($txn['account_id'], $receivedAmt, 'credit');
                    }
                }

                $finalBalance = $amount - $totalReceived;
                $is_full = $finalBalance > 0 ? 0 : 1;

                $this->Receipt_model->update([
                    'received_amount' => $totalReceived,
                    'balance' => $finalBalance,
                    'is_fully_received' => $is_full,
                ], $id);

                if(($data['sub_category_id'] == 28 || $data['sub_category_id'] == 29) && $totalReceived > 0) {
                    $paymentCategory = $this->categories_model->get(17);
                    $receiptCategory = $this->categories_model->get($data['sub_category_id']);
                    $assetCategory = $this->categories_model->get($asset_category_id);

                    $oldPaymentTxns = $this->transaction_model->get_by_track_id($oldPayment->id, 2);

                    $paymentData = [
                        'date' => $data['date'],
                        'amount' => $amount,
                        'received_amount' => $totalReceived,
                        'balance' => $finalBalance,
                        'account_id' => $data['account_id'],
                        'category_id' => 17,
                        'receipt_category_id' => $data['sub_category_id'],
                        'asset_category_id' => $asset_category_id,
                        'reference' => !empty($data['reference']) ? $data['reference'] : Null,
                        'description' => !empty($data['description']) ? $data['description'] : Null,
                        'is_fully_paid' => $is_full,
                    ];

                    if($oldPayment) {
                        $this->payment_model->update($paymentData, $oldPayment->id);
                        $payment_id = $oldPayment->id;
                    } else {
                        $paymentData['receipt_id'] = $id;
                        $payment_id = $this->payment_model->add($paymentData);
                    }

                    $runningPaid = 0;

                    foreach($transactions as $key => $txn) {
                        $paidAmt = (float) $txn['received_amount'];
                        $runningPaid += $paidAmt;

                        if($paidAmt <= 0) {
                            if(isset($oldPaymentTxns[$key])) {
                                $this->transaction_model->delete($oldPaymentTxns[$key]->id);
                                continue;
                            }
                        }

                        $txnBalance = $amount - $runningPaid;
                        if($txnBalance < 0) {
                            $txnBalance = 0;
                        }

                        $account = $this->BankAccount_model->get($txn['account_id']);
                        $prevBalance = $account->opening_balance;
                        $totalBalance = $account->opening_balance - $paidAmt;

                        if(isset($oldPaymentTxns[$key])) {
                            $this->transaction_model->update([
                                'date' => $txn['date'],
                                'account_id' => $txn['account_id'],
                                'category' => $paymentCategory->name,
                                'receipt_category' => $receiptCategory->name,
                                'asset_category' => $assetCategory->name,
                                'received_amount' => $paidAmt,
                                'previous_balance' => $prevBalance,
                                'total_balance' => $totalBalance,
                                'balance' => $txnBalance,
                                'is_fully_paid_or_received' => ($txnBalance > 0) ? 0 : 1,
                            ], $oldPaymentTxns[$key]->id);
                        } else {
                            $this->transaction_model->add([
                                'track_id' => $payment_id,
                                'type' => 2,
                                'category' => $paymentCategory->name,
                                'receipt_category' => $receiptCategory->name,
                                'asset_category' => $assetCategory->name,
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
                }

                if(($receipt->sub_category_id == 28 && $data['sub_category_id'] != 28) || ($receipt->sub_category_id == 29 && $data['sub_category_id'] != 29) && $oldPayment) {
                    $this->payment_model->delete($oldPayment->id, 2, true);
                }

                $this->db->trans_complete();

                set_alert('success', _l('updated_successfully', _l('receipt')));
                redirect(admin_url('ngo_trust/receipt'));
            }
        }

        if($id != '') {
            $receipt = $this->Receipt_model->get($id);
            if($receipt->sub_category_id == 28 || $receipt->sub_category_id == 29) {
                $data['payment'] = $this->payment_model->get_by_receipt_id($id);
            }
            $data['receipt'] = $receipt;
            $data['receipt_trans'] = $this->transaction_model->get_by_track_id($id, 1);
        }

        $data['accounts'] = $this->BankAccount_model->get();
        $data['donors'] = $this->clients_model->get();
        $data['categories'] = $this->categories_model->get_by_type(1, true);
        $data['payment_categories'] = $this->categories_model->get_by_type(2, true);
        $data['asset_categories'] = $this->categories_model->get_by_type(3, true);

        $this->load->view('receipt/formNew', $data);
    }

    public function get_sub_category() {
        $category_id = $this->input->post('category_id');
        $data = $this->categories_model->get_sub_categories($category_id);
        echo json_encode($data);
    }

    public function get_child_categories() {
        $category_id = $this->input->post('category_id');
        $data = $this->categories_model->get_sub_categories($category_id);
        echo json_encode($data);
    }

    public function view($id = '') {
        $receipt = $this->Receipt_model->get($id);
        if($receipt) {
            $data['receipt'] = $this->db->select('r.*, c.name as category_name, sc.name as sub_category_name, ssc.name as sub_sub_category_name, sssc.name as sub_sub_sub_category_name, d.company as client')
            ->from(db_prefix() . 'receipts r')
            ->join(db_prefix() . 'categories c', 'c.id = r.category_id', 'left')
            ->join(db_prefix() . 'categories sc', 'sc.id = r.sub_category_id', 'left')
            ->join(db_prefix() . 'categories ssc', 'ssc.id = r.sub_sub_category_id', 'left')
            ->join(db_prefix() . 'categories sssc', 'sssc.id = r.sub_sub_sub_category_id', 'left')
            ->join(db_prefix() . 'clients d', 'd.userid = r.donor_id', 'left')
            ->where('r.id', $id)
            ->get()
            ->row();

            $data['transactions'] = $this->db->select('t.*, a.bank_name as account_name')
                ->from(db_prefix() . 'transaction t')
                ->join(db_prefix() . 'bank_accounts a', 'a.id = t.account_id', 'left')
                ->where('t.type', 1)
                ->where('t.track_id', $id)
                ->get()
                ->result_array();

            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $this->load->view('receipt/show', $data);
        }
    }

    public function makeReceipt($id = '') {
        $receipt = $this->Receipt_model->get($id);
        if(!$receipt) {
            show_404();
        }

        if($this->input->post()) {
            $data = $this->input->post();
            $received_amount = $data['received_amount'];
            $account_id = !empty($data['account_id']) ? $data['account_id'] : Null;

            $this->db->trans_begin();

            $new_received = $receipt->received_amount + $received_amount;
            $new_balance = $receipt->balance - $received_amount;

            $updateReceipt = [
                'received_amount' => $new_received,
                'balance' => $new_balance,
                'is_fully_received' => ($new_balance == 0) ? 1 :0,
            ];
            $this->Receipt_model->update($updateReceipt, $id);

            $account = $this->BankAccount_model->get($account_id);
            $category = $this->categories_model->get($receipt->category_id);
            $sub_category = $this->categories_model->get($receipt->sub_category_id);
            $sub_sub_category = $this->categories_model->get($receipt->sub_sub_category_id);
            $sub_sub_sub_category = $this->categories_model->get($receipt->sub_sub_sub_category_id);

            $previous_balance = (float) $account->opening_balance;
            $total_balance = $previous_balance + $received_amount;

            $this->transaction_model->add([
                'track_id' => $id,
                'type' => 1,
                'category' => !empty($category) ? $category->name : null,
                'sub_category' => !empty($sub_category) ? $sub_category->name : null,
                'sub_sub_category' => !empty($sub_sub_category) ? $sub_sub_category->name : null,
                'sub_sub_sub_category' => !empty($sub_sub_sub_category) ? $sub_sub_sub_category->name : null,
                'account_id' => $account_id,
                'amount' => $receipt->amount,
                'received_amount' => $received_amount,
                'balance' => $new_balance,
                'date' => $data['date'],
                'previous_balance' => $previous_balance,
                'total_balance' => $total_balance,
                'is_fully_paid_or_received' => $updateReceipt['is_fully_received'],
            ]);

            $this->BankAccount_model->accountBalance($account_id, $received_amount, 'credit');

            if($receipt->sub_category_id == 28 || $receipt->sub_category_id == 29) {
                $payment = $this->payment_model->get_by_receipt_id($id);
                if($payment) {
                    $payment_new_received = $payment->received_amount + $received_amount;
                    $payment_new_balance = $payment->balance - $received_amount;

                    $updatePayment = [
                        'received_amount' => $payment_new_received,
                        'balance' => $payment_new_balance,
                        'is_fully_paid' => ($payment_new_balance == 0) ? 1 : 0,
                    ];
                    $this->payment_model->update($updatePayment, $payment->id);

                    $account = $this->BankAccount_model->get($account_id);
                    $paymentCategory = $this->categories_model->get(17);
                    $receiptCategory = $this->categories_model->get($receipt->sub_category_id);
                    $assetCategory = $this->categories_model->get($payment->asset_category_id);

                    $this->transaction_model->add([
                        'track_id' => $payment->id,
                        'type' => 2,
                        'category' => $paymentCategory ? $paymentCategory->name : null,
                        'receipt_category' => $receiptCategory ? $receiptCategory->name : null,
                        'asset_category' => $assetCategory ? $assetCategory->name : null,
                        'account_id' => $account_id,
                        'amount' => $payment->amount,
                        'received_amount' => $received_amount,
                        'balance' => $payment_new_balance,
                        'date' => $data['date'],
                        'previous_balance' => $account->opening_balance,
                        'total_balance' => $account->opening_balance - $received_amount,
                        'is_fully_paid_or_received' => $updatePayment['is_fully_paid'],
                    ]);

                    $this->BankAccount_model->accountBalance($account_id, $received_amount, 'debit');
                }
            }

            if($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                set_alert('danger', _l('something_went_wrong'));
            } else {
                $this->db->trans_commit();
                set_alert('success', _l('receipt_payment_added_successfully', _l('receipt')));
            }

            redirect(admin_url('ngo_trust/receipt'));
        }

        if($receipt->sub_category_id == 28 || $receipt->sub_category_id == 29) {
            $data['payment'] = $this->payment_model->get_by_receipt_id($id);
        }
        $data['title'] = _l('amount_to_received');
        $data['receipt'] = $receipt;
        $data['accounts'] = $this->BankAccount_model->get();
        $data['categories'] = $this->categories_model->get_by_type(1, true);
        $data['payment_categories'] = $this->categories_model->get_by_type(2, true);
        $data['asset_categories'] = $this->categories_model->get_by_type(3, true);

        $data['sub_categories'] = $this->categories_model->get_sub_categories($receipt->category_id);
        $data['sub_sub_categories'] = $this->categories_model->get_sub_categories($receipt->sub_category_id);
        $data['sub_sub_sub_categories'] = $this->categories_model->get_sub_categories($receipt->sub_sub_category_id);

        $this->load->view('receipt/makeReceipt', $data);
    }

    private function handle_receipt_attachment($type, $oldFile = Null) {
        if(!isset($_FILES['attachment']) || $_FILES['attachment']['name'] == '') {
            return $oldFile;
        }

        $upload_path = FCPATH . 'modules/ngo_trust/uploads/' . $type . '/';

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
            redirect(admin_url('ngo_trust/receipt'));
        }

        $uploadedData = $this->upload->data();

        if($oldFile && file_exists(FCPATH . $oldFile)) {
            unlink(FCPATH . $oldFile);
        }

        return 'modules/ngo_trust/uploads/' . $type . '/' . $uploadedData['file_name'];
    }

    public function delete($id) {
        if(staff_cant('delete', 'receipt')) {
            access_denied('receipt');
        }

        if(!$id) {
            redirect(admin_url('ngo_trust/receipt'));
        }

        $response = $this->Receipt_model->delete($id, true);
        if($response == true) {
            set_alert('success', _l('deleted', _l('receipt')));
        } else {
            set_alert('warning', _l('problem_deleting', _('receipt')));
        }
        redirect(admin_url('ngo_trust/receipt'));
    }
}