<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transaction_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'transaction')->row();
        }

        return $this->db->get(db_prefix() . 'transaction')->result_array();
    }

    public function get_by_track_id($trackId, $type, $order_by = 'ASC') {
        $this->db->where('type', (int) $type);
        $this->db->where('track_id', $trackId);
        $this->db->order_by('id', $order_by);
        return $this->db->get(db_prefix() . 'transaction')->result();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'transaction', array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
        ]));
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Transaction Added [ID:' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    public function update($data, $id) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'transaction', $data);
        if($this->db->affected_rows() > 0) {
            log_activity('Transaction Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'transaction');
        if($this->db->affected_rows() > 0) {
            log_activity('Transaction Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function update_by_track_id($trackId, $type, $data) {
        $this->db->where('track_id', $trackId);
        $this->db->where('type', $type);
        return $this->db->update(db_prefix() . 'transaction', $data);
    }

    public function rollBackByTrackId($track_id, $deleteTrans = false) {
        if(empty($track_id)) {
            return false;
        }

        $this->load->model('BankAccount_model');
        $this->load->model('payment_model');

        // Fetch all related Receipt transactions
        $receiptTrans = $this->get_by_track_id($track_id, 1);
        if(empty($receiptTrans)) {
            return true;
        }

        foreach($receiptTrans as $txn) {
            if($txn->received_amount > 0) {
                $this->BankAccount_model->accountBalance($txn->account_id, $txn->received_amount, 'debit');
            }
        }

        if($deleteTrans) {
            $this->db->where('track_id', $track_id);
            $this->db->where('type', 1);
            $this->db->delete(db_prefix() . 'transaction');
        }

        // Fetch all related Payment transactions
        $payment = $this->payment_model->get_by_receipt_id($id);
        if(!$payment) {
            $paymentTrans = $this->get_by_track_id($payment->id, 2);
            if(empty($paymentTrans)) {
                return true;
            }

            if($deleteTrans) {
                $this->db->where('track_id', $payment->id);
                $this->db->where('type', 2);
                $this->db->delete(db_prefix() . 'transaction');
            }
        }

        return true;
    }
}