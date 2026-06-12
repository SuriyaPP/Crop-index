<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bank_account extends AdminController
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('BankAccount_model');
    }

	public function index() {
        if (staff_cant('view', 'bank_accounts')) {
            access_denied('bank_accounts');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(NGO_TRUST_MODULE_NAME, 'bank_account/table'));
        }

        $data['title']                 = _l('bank_accounts');
        $this->load->view('bank_account/manage', $data);
    }

	public function create($id = '') {
		if (staff_cant('view', 'bank_accounts')) {
            access_denied('bank_accounts');
        }

        if($this->input->post()) {
        	if($id == '') {
        		if (staff_cant('create', 'bank_accounts')) {
                    access_denied('bank_accounts');
                }
	        	$id = $this->BankAccount_model->add($this->input->post());
	        	if ($id) {
	                set_alert('success', _l('added_successfully', _l('bank_account')));
	                redirect(admin_url('ngo_trust/bank_account'));
	            }
        	} else {
        		if(staff_cant('edit', 'bank_accounts')) {
        			access_denied('bank_accounts');
        		}
        		$success = $this->BankAccount_model->update($this->input->post(), $id);
        		if($success) {
        			set_alert('success', _l('updated_successfully', _l('bank_account')));
        		}
        		redirect(admin_url('ngo_trust/bank_account'));
        	}
        }

        if($id != '') {
        	$data['bank_account'] = $this->BankAccount_model->get($id);
        } else {
        	$data = [];
        }

        $this->load->view('bank_account/form', $data);
	}

	public function delete($id) {
		if(staff_cant('delete', 'bank_accounts')) {
			access_denied('bank_accounts');
		}

		if(!$id) {
			redirect(admin_url('ngo_trust/bank_account'));
		}

		$response = $this->BankAccount_model->delete($id);
		if($response == true) {
			set_alert('success', _l('deleted', _l('bank_account')));
		} else {
			set_alert('warning', _l('problem_deleting', _l('bank_account')));
		}
		redirect(admin_url('ngo_trust/bank_account'));
	}
}