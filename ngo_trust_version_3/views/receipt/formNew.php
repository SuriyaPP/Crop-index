<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'receipt-form']); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo render_date_input('date', 'receipt_date', isset($receipt->date) ? _d($receipt->date) : _d(date('Y-m-d'))); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('amount', 'amount', $receipt->amount ?? '', 'number', ['placeholder' => 'Enter total amount']); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('account_id', $accounts, ['id', 'bank_name'], 'account', $receipt->account_id); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('donor_id', $donors, ['userid', 'company'], 'donor', $receipt->donor_id); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('category_id', $categories, ['id', 'name'], 'category', $receipt->category_id); ?>
    </div>

    <div class="col-md-6 hide" id="sub_category">
        <?php echo render_select('sub_category_id', $sub_categories, ['id', 'name'], 'sub_category', $receipt->sub_category_id); ?>
    </div>
    <div class="col-md-6 hide" id="sub_sub_category">
        <?php echo render_select('sub_sub_category_id', [], ['id', 'name'], 'sub_sub_category', $receipt->sub_sub_category_id); ?>
    </div>
    <div class="col-md-6 hide" id="sub_sub_sub_category">
        <?php echo render_select('sub_sub_sub_category_id', [], ['id', 'name'], 'sub_sub_sub_category', $receipt->sub_sub_sub_category_id); ?>
    </div>

    <div class="col-md-6 hide" id="payment_category">
        <?php echo render_select('payment_category_id', $payment_categories, ['id', 'name'], 'payment_category'); ?>
    </div>
    <div class="col-md-6 hide" id="asset_category">
        <?php echo render_select('asset_category_id', $asset_categories, ['id', 'name'], 'asset_category', $payment->asset_category_id); ?>
    </div>

    <div class="col-md-6">
        <?php echo render_input('reference', 'reference', $receipt->reference ?? '', 'text', ['placeholder' => 'Enter reference / transaction ID']); ?>
    </div>
    <div class="col-md-12" id="received_amount_group">
        <?php
        $attr = [
            'placeholder' => 'Enter received amount',
        ];
        if(isset($receipt)) {
            $attr['readonly'] = true;
        }

        echo render_input('received_amount', 'received_amount', $receipt->received_amount ?? '', 'number', $attr); ?>
    </div>

    <?php if(isset($receipt) && !empty($receipt_trans)) { ?>
        <div class="col-md-12">
            <hr>
            <h4>Receipt Transactions</h4>

            <table class="table table-borderless" id="receipt-transaction">
                <thead>
                    <tr>
                        <th width="25%">Date</th>
                        <th width="30%">Account</th>
                        <th width="25%">Received Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($receipt_trans as $i => $txn) { ?>
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
                            <?php echo form_hidden("transactions[$i][id]", $txn->id); ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <div class="col-md-12 <?php echo isset($receipt) ? 'hide' : ''; ?> ">
        <div class="row">
            <div class="col-md-6">
                <div class="checkbox checkbox-primary mtop15">
                    <input type="checkbox" id="is_fully_received" name="is_fully_received" value="1">
                    <label for="is_fully_received"><?php echo _l('is_fully_received'); ?></label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="checkbox checkbox-primary mtop15">
                    <input type="checkbox" id="is_nill_received" name="is_nill_received" value="1">
                    <label for="is_nill_received"><?php echo _l('is_nill_received'); ?></label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <?php echo render_textarea('description', 'description', $receipt->description ?? '', ['placeholder' => 'Enter receipt description or notes']); ?>
    </div>
    <div class="col-md-12">
        <?php echo render_input('attachment', 'other_attachments', '', 'file'); ?>

        <?php if(isset($receipt) && !empty($receipt->attachment)) { ?>
            <p class="mtop15">
                <a href="<?php echo base_url($receipt->attachment); ?>" target="_blank">
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
        $('.selectpicker').selectpicker();

        const $form = $('#receipt-form');
        const $amount = $('input[name="amount"]');
        const $received = $('input[name="received_amount"]');
        const $category = $('#category_id');
        const $sub_category = $('#sub_category_id');

        const isEditMode = $('input[name^="transactions"][name$="[received_amount]"]').length > 0;

        let hasSubcategory = false;
        let hasSubSubcategory = false;
        let hasSubSubSubcategory = false;

        const selectedSubCategory = "<?php echo $receipt->sub_category_id ?? ''; ?>";
        const selectedSubSubCategory = "<?php echo $receipt->sub_sub_category_id ?? ''; ?>";
        const selectedSubSubSubCategory = "<?php echo $receipt->sub_sub_sub_category_id ?? ''; ?>";

        function resetField(target, wrapper) {
            $(wrapper).addClass('hide');
            $(target).html('').prop('disabled', true).selectpicker('refresh');
        }

        function updateFlags(target, state) {
            if(target == '#sub_category_id') {
                hasSubcategory = state;
            } else if(target == '#sub_sub_category_id') {
                hasSubSubcategory = state;
            } else if(target == '#sub_sub_sub_category_id') {
                hasSubSubSubcategory = state;
            }
        }

        function loadChild(parentId, target, wrapper, selectedValue = '', callback = null) {
            if(!parentId) {
                resetField(target, wrapper);
                updateFlags(target, false);
                return;
            }

            $.post(
                admin_url + 'ngo_trust/receipt/get_child_categories',
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

                    $('#received_amount_group').removeClass('col-md-12').addClass('col-md-6');

                    if(typeof callback == 'function') {
                        callback();
                    }
                } else {
                    resetField(target, wrapper);
                    updateFlags(target, false);

                    $('#received_amount_group').removeClass('col-md-6').addClass('col-md-12');
                }
            });
        }

        if(isEditMode && $category.val()) {
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
            toggleAssetCategory('');
        });

        $('#sub_category_id').on('change', function() {
            let val = $(this).val();

            resetField('#sub_sub_category_id', '#sub_sub_category');
            resetField('#sub_sub_sub_category_id', '#sub_sub_sub_category');

            loadChild(val, '#sub_sub_category_id', '#sub_sub_category');
            toggleAssetCategory(val);
        });

        $('#sub_sub_category_id').on('change', function() {
            let val = $(this).val();

            resetField('#sub_sub_sub_category_id', '#sub_sub_sub_category');

            loadChild(val, '#sub_sub_sub_category_id', '#sub_sub_sub_category');
        });

        function recalcFormTransaction() {
            let total = 0;

            $('input[name^="transactions"][name$="[received_amount]"]').each(function () {
                const val = parseFloat($(this).val());
                if(!isNaN(val)) {
                    total += val;
                }
            });

            $received.val(total.toFixed(0));
        }

        if(isEditMode) {
            $(document).on('input', 'input[name^="transactions"][name$="[received_amount]"]', recalcFormTransaction);
            $amount.on('input', recalcFormTransaction);
            recalcFormTransaction();
        }

        if(!isEditMode) {
            const $full = $('#is_fully_received');
            const $nill = $('#is_nill_received');
            let previousReceived = '';

            function disableReceived(val) {
                previousReceived = $received.val();
                $received.val(val);
            }

            function enableReceived() {
                $received.val(previousReceived);
            }

            function syncState() {
                const amount = parseFloat($amount.val());
                const received = parseFloat($received.val());

                $full.prop('checked', false);
                $nill.prop('checked', false);

                if(!isNaN(amount) && !isNaN(received)) {
                    if(received == amount && amount > 0) {
                        $full.prop('checked', true);
                    } else if(received == 0) {
                        $nill.prop('checked', true);
                    }
                }
            }

            $full.on('change', function() {
                if(this.checked) {
                    $nill.prop('checked', false);
                    disableReceived($amount.val());
                } else {
                    enableReceived();
                }
                syncState();
            });

            $nill.on('change', function() {
                if(this.checked) {
                    $full.prop('checked', false);
                    disableReceived(0);
                } else {
                    enableReceived();
                }
                syncState();
            });

            $amount.on('input', syncState);
            $received.on('input', syncState);

            syncState();
        }

        function toggleAssetCategory(sub_category) {
            const $paymentSelect = $('#payment_category_id');
            const $assetSelect = $('#asset_category_id');

            if(sub_category == '28' || sub_category == '29') {
                $('#payment_category, #asset_category').removeClass('hide');
                $paymentSelect.selectpicker('val', '17').prop('disabled', true).selectpicker('refresh');
                $assetSelect.prop('disabled', false).selectpicker('refresh');
            } else if(sub_category == '5') {
                $('#payment_category, #asset_category').addClass('hide');
                $paymentSelect.prop('disabled', true).selectpicker('refresh');
                $assetSelect.prop('disabled', true).selectpicker('refresh');
            } else {
                $('#payment_category, #asset_category').addClass('hide');
                $paymentSelect.prop('disabled', true).selectpicker('refresh');
                $assetSelect.prop('disabled', true).selectpicker('refresh');
            }
        }

        toggleAssetCategory($sub_category.val());

        $.validator.addMethod('validateAmount', function(value) {
            const amount = parseFloat($amount.val());
            const received = parseFloat(value);

            if(isNaN(amount) || isNaN(received)) {
                return true;
            }

            return received <= amount;
        }, '<?php echo _l('received_amount_cannot_exceed_amount'); ?>');

        appValidateForm($form, {
            date: 'required',
            amount: {
                required: true,
                number: true,
                min: 0,
            },
            account_id: 'required',
            category_id: 'required',
            sub_category_id: {
                required: function() {
                    return hasSubcategory && !$('#sub_category').hasClass('hide');
                }
            },
            sub_sub_category_id: {
                required: function() {
                    return hasSubSubcategory && !$('#sub_sub_category').hasClass('hide');
                }
            },
            sub_sub_sub_category_id: {
                required: function() {
                    return hasSubSubSubcategory && !$('#sub_sub_sub_category').hasClass('hide');
                }
            },
            payment_category_id: {
                required: function() {
                    return $('#sub_category_id').val() == '28' || $('#sub_category_id').val() == '29';
                }
            },
            asset_category_id: {
                required: function() {
                    return $('#sub_category_id').val() == '28' || $('#sub_category_id').val() == '29';
                }
            },
            received_amount: {
                required: true,
                number: true,
                min: 0,
                validateAmount: true
            }
        });
    });
</script>