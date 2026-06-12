<?php

defined('BASEPATH') or exit('No direct script access allowed');

class BankAccount_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'bank_accounts')->row();
        }

        return $this->db->get(db_prefix() . 'bank_accounts')->result_array();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'bank_accounts', array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
        ]));
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Bank Account Added [ID:' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    public function update($data, $id) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'bank_accounts', $data);
        if($this->db->affected_rows() > 0) {
            log_activity('Bank Account Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'bank_accounts');
        if($this->db->affected_rows() > 0) {
            log_activity('Bank Account Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function accountBalance($id, $amount, $type) {
        $bankAccount = $this->get($id);
        if($bankAccount) {
            if($type == 'credit') {
                $oldBalance = $bankAccount->opening_balance;
                $bankAccount->opening_balance = $oldBalance + $amount;
                $this->update($bankAccount, $id);
            } elseif($type == 'debit') {
                $oldBalance = $bankAccount->opening_balance;
                $bankAccount->opening_balance = $oldBalance - $amount;
                $this->update($bankAccount, $id);
            }
        }
    }
}