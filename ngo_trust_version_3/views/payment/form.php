<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'payment-form']); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo render_date_input('date', 'payment_date', isset($payment->date) ? _d($payment->date) : _d(date('Y-m-d'))); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('amount', 'amount', $payment->amount ?? '', 'number', ['placeholder' => 'Enter total amount']); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('account_id', $accounts, ['id', 'bank_name'], 'account', $payment->account_id); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('vendorid', $vendors, ['vendorid', 'company'], 'vendor', $payment->vendorid); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('category_id', $categories, ['id', 'name'], 'category', $payment->category_id); ?>
    </div>

    <div class="col-md-6 hide" id="sub_category">
        <?php echo render_select('sub_category_id', $sub_categories, ['id', 'name'], 'sub_category', $payment->sub_category_id); ?>
    </div>
    <div class="col-md-6 hide" id="sub_sub_category">
        <?php echo render_select('sub_sub_category_id', [], ['id', 'name'], 'sub_sub_category', $payment->sub_sub_category_id); ?>
    </div>
    <div class="col-md-6 hide" id="sub_sub_sub_category">
        <?php echo render_select('sub_sub_sub_category_id', [], ['id', 'name'], 'sub_sub_sub_category', $payment->sub_sub_sub_category_id); ?>
    </div>

    <div class="col-md-6" id="asset_category_group">
        <?php echo render_select('asset_category_id', $asset_categories, ['id', 'name'], 'asset_category', $payment->asset_category_id); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('reference', 'reference', $payment->reference ?? '', 'text', ['placeholder' => 'Enter reference / transaction ID']); ?>
    </div>
    <div class="col-md-6">
        <?php
            $attr = [];
            if(isset($payment)) {
                $attr['disabled'] = true;
            }
        
            echo render_select('receipt_category_id', $receipt_categories, ['id', 'name'], 'paid_out_of', $payment->receipt_category_id, $attr);
        ?>
    </div>
    <div class="col-md-12" id="paid_amount_group">
        <?php
        $attr = [
            'placeholder' => 'Enter Paid amount',
            ];
            if(isset($payment)) {
                $attr['readonly'] = true;
            }

            echo render_input('received_amount', 'paid_amount', $payment->received_amount ?? '', 'number', $attr);
        ?>
    </div>

    <?php if(isset($payment) && !empty($payment_trans)) { ?>
        <div class="col-md-12">
            <hr>
            <h4>Payment Transactions</h4>

            <table class="table table-borderless" id="payment-transaction">
                <thead>
                    <tr>
                        <th width="20%">Date</th>
                        <th width="25%">Account</th>
                        <th width="20%">Received Amount</th>
                        <th width="20%">Paid Out Of</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payment_trans as $i => $txn) { ?>
                        <tr>
                            <td>
                                <?php echo render_date_input("transactions[$i][date]", '', isset($txn->date) ? _d($txn->date) : _d(date('Y-m-d')), ['class' => 'txn-date']); ?>
                            </td>

                            <td>
                                <?php echo render_select("transactions[$i][account_id]", $accounts, ['id', 'bank_name'], '', $txn->account_id, ['class' => 'selectpicker txn-account', 'data-live-search' => 'true']); ?>
                            </td>

                            <td>
                                <?php echo render_input("transactions[$i][received_amount]", '', $txn->received_amount, 'number', ['step' => '1', 'class' => 'txn-amount', 'min' => '0']); ?>
                            </td>

                            <td>
                                <?php
                                    $receiptCategoryId = Null;
                                    foreach($receipt_categories as $rcat) {
                                        if($rcat['name'] === $txn->receipt_category) {
                                            $receiptCategoryId = $rcat['id'];
                                            break;
                                        }
                                    }

                                    echo render_select("transactions[$i][receipt_category_id]", $receipt_categories, ['id', 'name'], '', $receiptCategoryId, ['class' => 'selectpicker txn-receipt_category', 'data-live-search' => 'true']); ?>
                            </td>
                            <?php echo form_hidden("transactions[$i][id]", $txn->id); ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <div class="col-md-12 <?php echo isset($payment) ? 'hide' : ''; ?> ">
        <div class="row">
            <div class="col-md-6">
                <div class="checkbox checkbox-primary mtop15">
                    <input type="checkbox" id="is_fully_paid" name="is_fully_paid" value="1">
                    <label for="is_fully_paid"><?php echo _l('is_fully_paid'); ?></label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="checkbox checkbox-primary mtop15">
                    <input type="checkbox" id="is_nill_paid" name="is_nill_paid" value="1">
                    <label for="is_nill_paid"><?php echo _l('is_nill_paid'); ?></label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <?php echo render_textarea('description', 'description', $payment->description ?? '', ['placeholder' => 'Enter payment description or notes']); ?>
    </div>
    <div class="col-md-12">
        <?php echo render_input('attachment', 'other_attachments', '', 'file'); ?>

        <?php if(isset($payment) && !empty($payment->attachment)) { ?>
            <p class="mtop15">
                <a href="<?php echo base_url($payment->attachment); ?>" target="_blank">
                    <i class="fa fa-paperclip"></i> View existing attachment
                </a>
            </p>
        <?php } ?>
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
    $(function () {
        init_datepicker();

        const $form = $('#payment-form');
        const $amount = $('input[name="amount"]');
        const $paid = $('input[name="received_amount"]');
        const $account = $('select[name="account_id"]');
        const $category = $('select[name="category_id"]');
        const $receipt = $('select[name="receipt_category_id"]');
        const $assetCat = $('select[name="asset_category_id"]');
        const $full = $('#is_fully_paid');
        const $nill = $('#is_nill_paid');
        const $submitBtn = $form.find('button[type="submit"]');
        const paymentId = '<?php echo $payment->id ?? Null ; ?>';

        let prevPaid = '';

        let hasSubCategory = false;
        let hasSubSubCategory = false;
        let hasSubSubSubCategory = false;

        const selectedSubCategory = "<?php echo $payment->sub_category_id ?? ''; ?>";
        const selectedSubSubCategory = "<?php echo $payment->sub_sub_category_id ?? ''; ?>";
        const selectedSubSubSubCategory = "<?php echo $payment->sub_sub_sub_category_id ?? ''; ?>";

        function resetField(target, wrapper) {
            $(wrapper).addClass('hide');
            $(target).html('').prop('disabled', true).selectpicker('refresh');
        }

        function updateFlags(target, state) {
            if(target == '#sub_category_id') {
                hasSubCategory = state;
            } else if(target == '#sub_sub_category_id') {
                hasSubSubCategory = state;
            } else if(target == '#sub_sub_sub_category_id') {
                hasSubSubSubCategory = state;
            }
        }

        function loadChild(parentId, target, wrapper, selectedValue = '', callback = null) {
            if(!parentId) {
                resetField(target, wrapper);
                updateFlags(target, false);
                return;
            }

            $.post(
                admin_url + 'ngo_trust/payment/get_child_categories',
                { category_id: parentId }
            ).done(function(response) {
                let res = JSON.parse(response);

                if(res.length > 0) {
                    let html = '<option value=""></option>';

                    $.each(res, function(i, item) {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    });

                    $(target).html(html);
                    $(wrapper).removeClass('hide');
                    $(target).prop('disabled', false).selectpicker('refresh');

                    if(selectedValue) {
                        $(target).selectpicker('val', selectedValue).selectpicker('refresh');
                    }

                    updateFlags(target, true);

                    $('#paid_amount_group').removeClass('col-md-12').addClass('col-md-6');

                    if(typeof callback == 'function') {
                        callback();
                    }
                } else {
                    resetField(target, wrapper);
                    updateFlags(target, false);

                    $('#paid_amount_group').removeClass('col-md-6').addClass('col-md-12');
                }
            });
        }

        if($category.val()) {
            loadChild($category.val(), '#sub_category_id', '#sub_category', selectedSubCategory, function() {
                if(selectedSubCategory) {
                    loadChild(selectedSubCategory, '#sub_sub_category_id', '#sub_sub_category', selectedSubSubCategory, function() {
                        if(selectedSubSubCategory) {
                            loadChild(selectedSubSubCategory, '#sub_sub_sub_category_id', '#sub_sub_sub_category', selectedSubSubSubCategory);
                        }
                    });
                }
            });
        }

        $('#category_id').on('change', function() {
            let val = $(this).val();

            resetField('#sub_category_id', '#sub_category');
            resetField('#sub_sub_category_id', '#sub_sub_category');
            resetField('#sub_sub_sub_category_id', '#sub_sub_sub_category');

            loadChild(val, '#sub_category_id', '#sub_category');
            toggleAssetCategory(val);
        });

        $('#sub_category_id').on('change', function() {
            let val = $(this).val();

            resetField('#sub_sub_category_id', '#sub_sub_category');
            resetField('#sub_sub_sub_category_id', '#sub_sub_sub_category');

            loadChild(val, '#sub_sub_category_id', '#sub_sub_category');
        });

        $('#sub_sub_category_id').on('change', function() {
            let val = $(this).val();

            resetField('#sub_sub_sub_category_id', '#sub_sub_sub_category');

            loadChild(val, '#sub_sub_sub_category_id', '#sub_sub_sub_category');
        });

        function disablePaid(val) {
            prevPaid = $paid.val();
            $paid.val(val);
        }

        function enablePaid() {
            $paid.val(prevPaid);
        }

        function syncState() {
            const amount = parseFloat($amount.val());
            const paid = parseFloat($paid.val());

            $full.prop('checked', false);
            $nill.prop('checked', false);
            $paid.prop('disabled', false);

            if(!isNaN(amount) && !isNaN(paid)) {
                if(paid == amount && amount > 0) {
                    $full.prop('checked', true);
                    disablePaid(amount);
                } else if(paid == 0) {
                    $nill.prop('checked', true);
                    disablePaid(0);
                }
            }
        }

        $full.on('change', function() {
            if(this.checked) {
                $nill.prop('checked', false);
                disablePaid($amount.val());
            } else {
                enablePaid();
            }
        });

        $nill.on('change', function() {
            if(this.checked) {
                $full.prop('checked', false);
                disablePaid(0);
            } else {
                enablePaid();
            }
        });

        $amount.on('input', syncState);
        $paid.on('input', syncState);

        syncState();

        function toggleAssetCategory(category) {
            if(category == '17') {
                $('#asset_category_group').show();
                $assetCat.prop('disabled', false);
                $('#paid_amount_group').removeClass('col-md-6').addClass('col-md-12');
            } else if(category == '15' || category == '39') {
                $('#asset_category_group').hide();
                $assetCat.prop('disabled', true);
            } else {
                $('#asset_category_group').hide();
                $assetCat.prop('disabled', true).val('');
                $('#paid_amount_group').removeClass('col-md-12').addClass('col-md-6');
            }
        }

        toggleAssetCategory($category.val());

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
                payment_id: paymentId,
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
        $category.on('change', validateAmount);
        $receipt.on('change', validateAmount);

        $.validator.addMethod(
            'lessThanOrEqualToAmount',
            function(value) {
                return parseFloat(value) <= parseFloat($amount.val());
            },
            '<?php echo _l('paid_amount_cannot_exceed_amount') ?>'
        );

        appValidateForm($form, {
            date: 'required',
            amount: {
                required: true,
                number: true,
                min: 0
            },
            account_id: 'required',
            category_id: 'required',
            sub_category_id: {
                required: function() {
                    return hasSubCategory && !$('#sub_category_id').hasClass('hide');
                }
            },
            sub_sub_category_id: {
                required: function() {
                    return hasSubSubCategory && !$('#sub_sub_category_id').hasClass('hide');
                }
            },
            sub_sub_sub_category_id: {
                required: function() {
                    return hasSubSubSubCategory && !$('#sub_sub_sub_category_id').hasClass('hide');
                }
            },
            asset_category_id: {
                required: function() {
                    return $category.val() == 17;
                }
            },
            receipt_category_id: 'required',
            received_amount: {
                required: true,
                number: true,
                min: 0,
                lessThanOrEqualToAmount: true
            }
        });

        $('#payment-form').on('submit', function() {
            $paid.prop('disabled', false);
        });
    });
</script>