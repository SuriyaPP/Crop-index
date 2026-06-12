<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Asset_depreciation extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AssetDepreciation_model');
    }

    public function index()
    {
        $this->load->view('assets/asset_depreciation');
    }

    public function get_categories()
    {
        echo json_encode([
            'status' => true,
            'data'   => $this->AssetDepreciation_model->get_categories()
        ]);
    }

    public function list()
    {
        $page  = (int)($this->input->get('page')  ?: 1);
        $limit = (int)($this->input->get('limit') ?: 5);

        $data = $this->AssetDepreciation_model->get_list($page, $limit);

        echo json_encode([
            'status' => true,
            'data'   => $data,
            'page'   => $page
        ]);
    }

    public function save()
    {
        $id = $this->input->post('id');

        $data = [
            'client_id'  => get_client_user_id(), // or your session user id
            'category'   => $this->input->post('category'),
            'month_year' => $this->input->post('month_year'),
            'amount'     => $this->input->post('amount'),
            'rate'       => $this->input->post('rate'),
        ];

        if ($id) {
            $this->AssetDepreciation_model->update($id, $data);
            echo json_encode(['status' => true, 'message' => 'Updated successfully']);
        } else {
            $insertId = $this->AssetDepreciation_model->save($data);
            echo json_encode(['status' => true, 'message' => 'Saved successfully', 'id' => $insertId]);
        }
    }

    public function delete($id)
    {
        $this->AssetDepreciation_model->delete($id);
        echo json_encode(['status' => true, 'message' => 'Deleted successfully']);
    }
}