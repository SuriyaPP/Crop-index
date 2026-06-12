<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open($this->uri->uri_string(), ['id' => 'bank-account-form']); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo render_select('bank_type', [['id' => 'Bank', 'name' => 'Bank'], ['id' => 'Wallet', 'name' => 'Wallet']], ['id', 'name'], 'bank_type', $bank_account->bank_type); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('holder_name', 'holder_name', $bank_account->holder_name ?? ''); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('bank_name', 'bank_name', $bank_account->bank_name ?? ''); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('account_number', 'account_number', $bank_account->account_number ?? ''); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('contact_number', 'contact_number', $bank_account->contact_number ?? ''); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('opening_balance', 'opening_balance', $bank_account->opening_balance ?? '', 'number'); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('bank_branch', 'bank_branch', $bank_account->bank_branch ?? ''); ?>
    </div>
    <div class="col-md-12">
        <?php echo render_textarea('bank_address', 'bank_address', $bank_account->bank_address ?? ''); ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <?php echo _l('close'); ?>
    </button>
    <button type="submit" class="btn btn-primary">
        <?php echo _l('save'); ?>
    </button>
</div>

<?php echo form_close(); ?>

<script>
$(function() {
    appValidateForm($('form'), {
        holder_name: 'required',
        bank_name: 'required',
        account_number: 'required',
        opening_balance: 'required',
        bank_branch: 'required',
        bank_address: 'required',
    });
});
</script>