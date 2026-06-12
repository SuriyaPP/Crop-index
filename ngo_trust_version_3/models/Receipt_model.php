<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'receipts')->row();
        }

        return $this->db->get(db_prefix() . 'receipts')->result_array();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'receipts', array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
        ]));
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Receipt Added [ID:' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    public function update($data, $id) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'receipts', $data);
        if($this->db->affected_rows() > 0) {
            log_activity('Receipt Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function deleteOld($id) {
        $this->db->trans_begin();

        $receipt = $this->get($id);
        if(!$receipt) {
            return false;
        }

        $this->db->where('track_id', $id);
        $this->db->where('type', 1);
        $transaction = $this->db->get(db_prefix() . 'transaction')->row();

        if($transaction && $transaction->received_amount > 0) {
            $this->load->model('BankAccount_model');

            $this->BankAccount_model->accountBalance($transaction->account_id, $transaction->received_amount, 'debit');
        }

        $this->db->where('track_id', $id);
        $this->db->where('type', 1);
        $this->db->delete(db_prefix() . 'transaction');

        if(!empty($receipt->attachment) && file_exists(FCPATH . $receipt->attachment)) {
            unlink(FCPATH . $receipt->attachment);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'receipts');

        if($this->db->trans_status() == false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    public function delete($id, $deleteTrans = false) {
        $this->db->trans_begin();

        $receipt = $this->get($id);
        if(!$receipt) {
            return false;
        }

        $this->load->model('BankAccount_model');
        $this->load->model('payment_model');

        // Fetch all related transaction (type = 1 => Receipt)
        $this->db->where('track_id', $id);
        $this->db->where('type', 1);
        $receiptTrans = $this->db->get(db_prefix() . 'transaction')->result();

        if(!empty($receiptTrans)) {
            foreach($receiptTrans as $txn) {
                if((float) $txn->received_amount > 0) {
                    $this->BankAccount_model->accountBalance($txn->account_id, $txn->received_amount, 'debit');
                }
            }

            if($deleteTrans) {
                $this->db->where('track_id', $id);
                $this->db->where('type', 1);
                $this->db->delete(db_prefix() . 'transaction');
            }
        }

        if($receipt->category_id == 27) {
            $payment = $this->payment_model->get_by_receipt_id($id);
            $this->payment_model->delete($payment->id, 1, $deleteTrans);
        }

        if(!empty($receipt->attachment) && file_exists(FCPATH . $receipt->attachment)) {
            unlink(FCPATH . $receipt->attachment);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'receipts');

        if($this->db->trans_status() == false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }
}