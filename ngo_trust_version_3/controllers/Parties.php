<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Parties extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Parties_model');
    }

    public function index()
    {
        $this->load->view('parties/donors_vendors');
    }

    // ==================== DONORS ====================

    public function get_donors()
    {
        $page   = (int)$this->input->get('page')  ?: 1;
        $limit  = (int)$this->input->get('limit') ?: 10;
        $search = $this->input->get('search');
        $offset = ($page - 1) * $limit;

        echo json_encode(
            $this->Parties_model->get_paginated_donors($limit, $offset, $search)
        );
    }

    public function create_donor()
    {
        $data = $this->input->post();
        $id   = $this->Parties_model->add_donor($data);

        echo json_encode([
            'success' => $id ? true : false,
            'message' => $id ? 'Donor added successfully' : 'Failed to add donor'
        ]);
    }

    public function update_donor($id)
    {
        $data    = $this->input->post();
        $success = $this->Parties_model->update_donor($id, $data);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Donor updated successfully' : 'Failed to update donor'
        ]);
    }

    public function delete_donor($id)
    {
        $success = $this->Parties_model->delete_donor($id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Donor deleted successfully' : 'Failed to delete donor'
        ]);
    }

    // ==================== VENDORS ====================

    public function get_vendors()
    {
        $page   = (int)$this->input->get('page')  ?: 1;
        $limit  = (int)$this->input->get('limit') ?: 10;
        $search = $this->input->get('search');
        $offset = ($page - 1) * $limit;

        echo json_encode(
            $this->Parties_model->get_paginated_vendors($limit, $offset, $search)
        );
    }

    public function create_vendor()
    {
        $data = $this->input->post();
        $id   = $this->Parties_model->add_vendor($data);

        echo json_encode([
            'success' => $id ? true : false,
            'message' => $id ? 'Vendor added successfully' : 'Failed to add vendor'
        ]);
    }

    public function update_vendor($id)
    {
        $data    = $this->input->post();
        $success = $this->Parties_model->update_vendor($id, $data);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Vendor updated successfully' : 'Failed to update vendor'
        ]);
    }

    public function delete_vendor($id)
    {
        $success = $this->Parties_model->delete_vendor($id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Vendor deleted successfully' : 'Failed to delete vendor'
        ]);
    }
}