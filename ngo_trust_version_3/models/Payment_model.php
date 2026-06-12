<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'payments')->row();
        }

        return $this->db->get(db_prefix() . 'payments')->result_array();
    }

    public function get_by_receipt_id($receipt_id) {
        return $this->db->where('receipt_id', $receipt_id)->get(db_prefix() . 'payments')->row();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'payments', array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
        ]));
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Payment Added [ID:' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    public function update($data, $id) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'payments', $data);
        if($this->db->affected_rows() > 0) {
            log_activity('Payment Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function delete($id, $type = 1, $deleteTrans = false) {
        $this->db->trans_begin();

        $payment = $this->get($id);
        if(!$payment) {
            return false;
        }

        $this->load->model('BankAccount_model');

        // Fetch all related transaction (type = 2 => Payment)
        $this->db->where('track_id', $id);
        $this->db->where('type', 2);
        $paymentTrans = $this->db->get(db_prefix() . 'transaction')->result();

        if(!empty($paymentTrans)) {
            foreach($paymentTrans as $txn) {
                if((float) $txn->received_amount > 0) {
                    $this->BankAccount_model->accountBalance($txn->account_id, $txn->received_amount, 'credit');
                }
            }

            if($deleteTrans) {
                $this->db->where('track_id', $id);
                $this->db->where('type', 2);
                $this->db->delete(db_prefix() . 'transaction');
            }
        }

        if(!empty($payment->attachment) && file_exists(FCPATH . $payment->attachment)) {
            unlink(FCPATH . $payment->attachment);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'payments');

        if($this->db->trans_status() == false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }
}