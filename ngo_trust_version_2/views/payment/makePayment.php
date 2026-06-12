<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'makePayment-form']); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo render_input('pending_amount', 'payable_amount', $payment->balance ?? '', 'number', ['placeholder' => 'Enter total amount', 'readonly' => true]); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('category_id', $categories, ['id', 'name'], 'category', $payment->category_id, ['disabled' => true]); ?>
    </div>
    <?php if($payment->category_id == 17) { ?>
        <div class="col-md-6">
            <?php echo render_select('asset_category_id', $asset_categories, ['id', 'name'], 'asset_category', $payment->asset_category_id, ['disabled' => true]); ?>
        </div>
    <?php } ?>
    <div class="col-md-6">
        <?php echo render_date_input('date', 'receipt_date', _d(date('Y-m-d'))); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('account_id', $accounts, ['id', 'bank_name'], 'account', $payment->account_id); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('receipt_category_id', $receipt_categories, ['id', 'name'], 'paid_out_of', $payment->receipt_category_id); ?>
    </div>
    <?php
        $colSize = (!empty($payment->category_id) && $payment->category_id == 17) ? 12 : 6;
    ?>
    <div class="col-md-<?php echo $colSize; ?>">
        <?php echo render_input('received_amount', 'amount', '', 'number', ['placeholder' => 'Enter amount to be received']); ?>
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
        init_datepicker();

        const $form = $('#makePayment-form');
        const $pending_amount = $('input[name="pending_amount"]');
        const $paid = $('input[name="received_amount"]');
        const $account = $('select[name="account_id"]');
        const $category = $('select[name="category_id"]');
        const $receipt = $('select[name="receipt_category_id"]');
        const $submitBtn = $form.find('button[type="submit"]');

        function clearErrors() {
            $('.amount-error, .account_id-error, .category_id-error, .receipt_category_id-error').remove();
            $paid.removeClass('is-invalid');
            $account.removeClass('is-invalid');
            $category.removeClass('is-invalid');
            $receipt.removeClass('is-invalid');
        }

        function showError($field, cls, msg) {
            $('.' + cls).remove();
            $field.addClass('is-invalid');
            $field.after('<span class="' + cls + ' text-danger">' + msg + '</span>');
        }

        function validateAmount() {
            const data = {
                account_id: $account.val(),
                category_id: $category.val(),
                receipt_category_id: $receipt.val(),
                amount: $paid.val(),
            };

            if(!data.account_id || !data.category_id || !data.receipt_category_id || !data.amount) {
                return;
            }

            clearErrors();

            $.post(
                admin_url + 'ngo_trust/payment/validateAmount',
                data
            ).done(function(response) {
                response = JSON.parse(response);

                if(response.success !== true) {
                    if(response.type == 1) {
                        showError($paid, 'amount-error', response.message1);
                        showError($account, 'account_id-error', response.message2);
                        showError($receipt, 'receipt_category_id-error', response.message3);
                    }

                    if(response.type == 2) {
                        showError($paid, 'amount-error', response.message1);
                        showError($category, 'category_id-error', response.message2);
                    }

                    $submitBtn.prop('disabled', true);
                } else {
                    $submitBtn.prop('disabled', false);
                }
            });
        }

        $paid.on('keyup', validateAmount);
        $account.on('change', validateAmount);
        $receipt.on('change', validateAmount);

        $.validator.addMethod('lessThanOrEqualToAmount', function(value, element) {
            var pending_amount = parseFloat($('input[name="pending_amount"]').val());
            var received = parseFloat(value);

            if(isNaN(pending_amount) || isNaN(received)) {
                return true;
            }

            return received <= pending_amount;
        }, '<?php echo _l('received_amount_cannot_exceed_amount') ?>');

        appValidateForm($('#makePayment-form'), {
            pending_amount: 'required',
            category_id: 'required',
            asset_category_id: {
                required: function() {
                    return $('select[name="category_id"]').val() == 17;
                }
            },
            date: 'required',
            account_id: 'required',
            receipt_category_id: 'required',
            received_amount: {
                required: true,
                number: true,
                min: 0,
                lessThanOrEqualToAmount: true
            }
        });
    });
</script>