<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Category extends AdminController
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('Categories_model');
    }

	public function index() {
		if (staff_cant('view', 'categories')) {
            access_denied('categories');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(NGO_TRUST_MODULE_NAME, 'category/table'));
        }

        $data['title']                 = _l('category');
        $this->load->view('category/manage', $data);
	}

	public function create($id = '') {
		if (staff_cant('view', 'categories')) {
            access_denied('categories');
        }

        if($this->input->post()) {
        	if($id == '') {
        		if (staff_cant('create', 'categories')) {
                    access_denied('categories');
                }
	        	$id = $this->Categories_model->add($this->input->post());
	        	if ($id) {
	                set_alert('success', _l('added_successfully', _l('category')));
	                redirect(admin_url('ngo_trust/category'));
	            }
        	} else {
        		if(staff_cant('edit', 'categories')) {
        			access_denied('categories');
        		}
        		$success = $this->Categories_model->update($this->input->post(), $id);
        		if($success) {
        			set_alert('success', _l('updated_successfully', _l('category')));
        		}
        		redirect(admin_url('ngo_trust/category'));
        	}
        }

        if($id != '') {
        	$data['category'] = $this->Categories_model->get($id);

        	$this->db->where('type', $data['category']->type);
        	$this->db->where('parent_id', 0);
        	$data['parent_category'] = $this->db->get(db_prefix() . 'categories')->result_array();
        } else {
        	$data = [];
        	$data['parent_category'] = [];
        }

        $this->load->view('category/form', $data);
	}

	public function get_parent_category() {
		$type = $this->input->post('type');

		$this->db->where('type', $type);
		// $this->db->where('parent_id', 0);
		$categories = $this->db->get(db_prefix() . 'categories')->result_array();
		echo json_encode($categories);
	}

	public function delete($id) {
		if(staff_cant('delete', 'categories')) {
			access_denied('categories');
		}

		if(!$id) {
			redirect(admin_url('ngo_trust/category'));
		}

		$response = $this->Categories_model->delete($id);
		if($response == true) {
			set_alert('success', _l('deleted', _l('category')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('category')));
		}
		redirect(admin_url('ngo_trust/category'));
	}
}