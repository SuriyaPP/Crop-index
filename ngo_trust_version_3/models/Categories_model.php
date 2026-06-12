<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Categories_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'categories')->row();
        }

        return $this->db->get(db_prefix() . 'categories')->result_array();
    }

    public function get_by_type($type, $only_parent = false) {
        $this->db->where('type', (int) $type);

        if($only_parent) {
            $this->db->group_start();
            $this->db->where('parent_id', 0);
            $this->db->or_where('parent_id IS NULL', null, false);
            $this->db->group_end();
        }

        return $this->db->get(db_prefix() . 'categories')->result_array();
    }

    public function get_sub_categories($parent_id) {
        $this->db->where('parent_id', $parent_id);
        return $this->db->get(db_prefix() . 'categories')->result_array();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'categories', array_merge($data, [
            'datecreated' => date('Y-m-d H:i:s'),
        ]));
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Category Added [ID:' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    public function update($data, $id) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'categories', $data);
        if($this->db->affected_rows() > 0) {
            log_activity('Category Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'categories');
        if($this->db->affected_rows() > 0) {
            log_activity('Category Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }
}