<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Parties_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ==================== DONORS ====================

    public function add_donor($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'donors', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Donor Added [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function update_donor($id, $data)
    {
        $this->db->where('donorid', $id);
        $this->db->update(db_prefix() . 'donors', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Donor Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_donor($id)
    {
        return $this->db
            ->where('donorid', $id)
            ->get(db_prefix() . 'donors')
            ->row();
    }

    public function delete_donor($id)
    {
        $this->db->where('donorid', $id);
        $this->db->delete(db_prefix() . 'donors');
        if ($this->db->affected_rows() > 0) {
            log_activity('Donor Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_paginated_donors($limit = 10, $offset = 0, $search = '')
    {
        $this->db->from(db_prefix() . 'donors');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phonenumber', $search);
            $this->db->group_end();
        }

        $total = $this->db->count_all_results('', false);

        $this->db->order_by('donorid', 'DESC');
        $this->db->limit($limit, $offset);
        $data = $this->db->get()->result_array();

        return ['total' => (int)$total, 'data' => $data];
    }

    // ==================== VENDORS ====================

    public function add_vendor($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'vendors', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Vendor Added [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function update_vendor($id, $data)
    {
        $this->db->where('vendorid', $id);
        $this->db->update(db_prefix() . 'vendors', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Vendor Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_vendor($id)
    {
        return $this->db
            ->where('vendorid', $id)
            ->get(db_prefix() . 'vendors')
            ->row();
    }

    public function delete_vendor($id)
    {
        $this->db->where('vendorid', $id);
        $this->db->delete(db_prefix() . 'vendors');
        if ($this->db->affected_rows() > 0) {
            log_activity('Vendor Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_paginated_vendors($limit = 10, $offset = 0, $search = '')
    {
        $this->db->from(db_prefix() . 'vendors');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phonenumber', $search);
            $this->db->group_end();
        }

        $total = $this->db->count_all_results('', false);

        $this->db->order_by('vendorid', 'DESC');
        $this->db->limit($limit, $offset);
        $data = $this->db->get()->result_array();

        return ['total' => (int)$total, 'data' => $data];
    }
}