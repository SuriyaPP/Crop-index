<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'receipts')) { ?>
                <div class="tw-mb-2">
                    <a href="#" class="btn btn-primary" onclick="newReceipt(); return false;">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('record_receipt'); ?>
                    </a>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                        _l('receipt_date'),
                        _l('amount'),
                        _l('account'),
                        _l('donor'),
                        _l('category'),
                        _l('reference'),
                        _l('description'),
                        _l('attachment'),
                        ], 'receipts'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal Form -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('record_receipt'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="receipt-form-container">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Payment Modal Form -->
<div class="modal fade" id="receiptPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('amount_to_received'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="receiptPaymentModal-form-container">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Receipt Modal Form -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('receipt_form'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="view-form-container">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-receipts', window.location.href, [6], [6]);
});
</script>

<script>
function newReceipt(id = '') {
    let url = admin_url + 'ngo_trust/receipt/create/' + id;

    if(id == '') {
        $('.modal-title').text('<?php echo _l('new_receipt'); ?>');
    } else {
        $('.modal-title').text('<?php echo _l('edit_receipt'); ?>');
    }

    $('#receiptModal').modal('show');

    $('#receipt-form-container').load(url, function() {
        $('.selectpicker').selectpicker('refresh');
    });
}

function receiptModal(id = '') {
    let url = admin_url + 'ngo_trust/receipt/makeReceipt/' + id;

    $('#receiptPaymentModal').modal('show');

    $('#receiptPaymentModal-form-container').load(url, function() {
        $('.selectpicker').selectpicker('refresh');
    });
}

function viewReceipt(id = '') {
    let url = admin_url + 'ngo_trust/receipt/view/' + id;

    $('#viewModal').modal('show');

    $('#view-form-container').load(url);
}
</script>

</body>

</html>