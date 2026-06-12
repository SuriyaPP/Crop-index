<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AssetDepreciation_model extends App_Model
{
    protected $table = 'tblasset_depreciation';

    public function get_categories()
    {
        $this->db->select('id, name');
        $this->db->where('type', 3);
        return $this->db->get('tblcategories')->result_array();
    }

    public function get_list($page = 1, $limit = 5)
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('d.*, c.name as category_name');
        $this->db->from($this->table . ' d');
        $this->db->join('tblcategories c', 'c.id = d.category', 'left');
        $this->db->order_by('d.datecreated', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    public function save($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}