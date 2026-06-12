<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'payments')) { ?>
                <div class="tw-mb-2">
                    <a href="#" class="btn btn-primary" onclick="newPayment(); return false;">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('record_payment'); ?>
                    </a>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                        _l('payment_date'),
                        _l('amount'),
                        _l('account'),
                        _l('donor'),
                        _l('category'),
                        _l('reference'),
                        _l('description'),
                        _l('attachment'),
                        ], 'payments'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal Form -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('record_payment'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="payment-form-container">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Payment Receipt Modal Form -->
<div class="modal fade" id="paymentReceiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('amount_to_paid'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="paymentReceiptModal-form-container">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--View Payment Modal Form -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('payment_form'); ?></h4>
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
    initDataTable('.table-payments', window.location.href, [6], [6]);
});
</script>

<script>
function newPayment(id = '') {
    let url = admin_url + 'ngo_trust/payment/create/' + id;

    if(id == '') {
        $('.modal-title').text('<?php echo _l('new_payment'); ?>');
    } else {
        $('.modal-title').text('<?php echo _l('edit_payment'); ?>');
    }

    $('#paymentModal').modal('show');

    $('#payment-form-container').load(url, function() {
        $('.selectpicker').selectpicker('refresh');
    });
}

function paymentModal(id= "") {
    let url = admin_url + 'ngo_trust/payment/makePayment/' + id;

    $('#paymentReceiptModal').modal('show');

    $('#paymentReceiptModal-form-container').load(url, function() {
        $('.selectpicker').selectpicker('refresh');
    });
}

function viewPayment(id = '') {
    let url = admin_url + 'ngo_trust/payment/view/' + id;

    $('#viewModal').modal('show');

    $('#view-form-container').load(url);
}
</script>

</body>

</html>