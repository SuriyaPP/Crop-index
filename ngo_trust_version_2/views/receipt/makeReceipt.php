<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'makeReceipt-form']); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo render_input('pending_amount', 'receivable_amount', $receipt->balance ?? '', 'number', ['placeholder' => 'Enter total amount', 'readonly' => true]); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_select('category_id', $categories, ['id', 'name'], 'category', $receipt->category_id, ['disabled' => true]); ?>
    </div>

    <?php if(!empty($receipt->sub_category_id)) { ?>
        <div class="col-md-6">
            <?php
                echo render_select('sub_category_id', $sub_categories, ['id', 'name'], 'sub_category', $receipt->sub_category_id, ['disabled' => true]);
            ?>
        </div>
    <?php } ?>

    <?php if(!empty($receipt->sub_sub_category_id)) { ?>
        <div class="col-md-6">
            <?php
                echo render_select('sub_sub_category_id', $sub_sub_categories, ['id', 'name'], 'sub_sub_category', $receipt->sub_sub_category_id, ['disabled' => true]);
            ?>
        </div>
    <?php } ?>

    <?php if(!empty($receipt->sub_sub_sub_category_id)) { ?>
        <div class="col-md-6">
            <?php
                echo render_select('sub_sub_sub_category_id', $sub_sub_sub_categories, ['id', 'name'], 'sub_sub_sub_category', $receipt->sub_sub_sub_category_id, ['disabled' => true]);
            ?>
        </div>
    <?php } ?>

    <div class="col-md-6 hide" id="payment_category">
        <?php echo render_select('payment_category_id', $payment_categories, ['id', 'name'], 'payment_category'); ?>
    </div>
    <div class="col-md-6 hide" id="asset_category">
        <?php echo render_select('asset_category_id', $asset_categories, ['id', 'name'], 'asset_category', $payment->asset_category_id); ?>
    </div>

    <div class="col-md-6">
        <?php echo render_date_input('date', 'receipt_date', _d(date('Y-m-d'))); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('received_amount', 'amount', '', 'number', ['placeholder' => 'Enter amount to be received']); ?>
    </div>

    <?php
        $count = 0;

        if(!empty($receipt->sub_category_id)) {
            $count++;
        }

        if(!empty($receipt->sub_sub_category_id)) {
            $count++;
        }

        if(!empty($receipt->sub_sub_sub_category_id)) {
            $count++;
        }
    ?>

    <div class="col-md-<?php echo ($count % 2 == 0) ? 12 : 6; ?>">
        <?php echo render_select('account_id', $accounts, ['id', 'bank_name'], 'account', $receipt->account_id); ?>
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
        $category = $('select[name="category_id"]');

        $.validator.addMethod('lessThanOrEqualToAmount', function(value, element) {
            var pending_amount = parseFloat($('input[name="pending_amount"]').val());
            var received = parseFloat(value);

            if(isNaN(pending_amount) || isNaN(received)) {
                return true;
            }

            return received <= pending_amount;
        }, '<?php echo _l('received_amount_cannot_exceed_amount') ?>');

        appValidateForm($('#makeReceipt-form'), {
            pending_amount: 'required',
            category_id: 'required',
            sub_category_id: 'required',
            sub_sub_category_id: 'required',
            sub_sub_sub_category_id: 'required',
            payment_category_id: {
                required: function() {
                    return $category.val() == '27' || $category.val() == '30';
                }
            },
            asset_category_id: {
                required: function() {
                    return $category.val() == '27' || $category.val() == '30';
                }
            },
            date: 'required',
            account_id: 'required',
            received_amount: {
                required: true,
                number: true,
                min: 0,
                lessThanOrEqualToAmount: true
            }
        });
    });
</script>

<script>
    $(function () {
        function toggleAssetCategory() {
            var selectedCategory = $('#category_id').val();
            var $paymentSelect = $('#payment_category_id');
            var $assetSelect = $('#asset_category_id');

            if(selectedCategory == '27' || selectedCategory == '30') {
                $('#payment_category').removeClass('hide');
                $('#asset_category').removeClass('hide');
                $paymentSelect.selectpicker('val', '17').prop('disabled', true).selectpicker('refresh');
                $assetSelect.prop('disabled', true).selectpicker('refresh');
            } else {
                $('#payment_category').addClass('hide');
                $('#asset_category').addClass('hide');
                $paymentSelect.selectpicker('val', '').prop('disabled', false).selectpicker('refresh');
                $assetSelect.prop('disabled', false).selectpicker('refresh');
            }
        }

        toggleAssetCategory();

        $('#category_id').on('change', toggleAssetCategory);
    });
</script>