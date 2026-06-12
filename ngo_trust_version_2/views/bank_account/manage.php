<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'bank_accounts')) { ?>
                <div class="tw-mb-2">
                    <a href="#" class="btn btn-primary" onclick="newBankAccount(); return false;">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_bank_account'); ?>
                    </a>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                        _l('holder_name'),
                        _l('bank_name'),
                        _l('bank_type'),
                        _l('bank_branch'),
                        _l('account_number'),
                        _l('current_balance'),
                        _l('bank_address'),
                        _l('contact_number'),
                        ], 'bank_accounts'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bankAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('new_bank_account'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="bank-account-form-container">
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
    initDataTable('.table-bank_accounts', window.location.href, [6], [6]);
    // $('.table-bank_accounts').DataTable().on('draw', function() {
    //     var rows = $('.table-bank_accounts').find('tr');
    //     $.each(rows, function() {
    //         var td = $(this).find('td').eq(6);
    //         var percent = $(td).find('input[name="percent"]').val();
    //         $(td).find('.account-progress').circleProgress({
    //             value: percent,
    //             size: 45,
    //             animation: false,
    //             fill: {
    //                 gradient: ["#28b8da", "#059DC1"]
    //             }
    //         })
    //     })
    // })
});
</script>

<script>
function newBankAccount(id = '') {
    let url = admin_url + 'ngo_trust/bank_account/create/' + id;

    if(id == '') {
        $('.modal-title').text('<?php echo _l('new_bank_account'); ?>');
    } else {
        $('.modal-title').text('<?php echo _l('edit_bank_account'); ?>');
    }

    $('#bankAccountModal').modal('show');

    // $('#bank-account-form-container').load(
    //     '<?php echo admin_url("ngo_trust/bank_account/create"); ?>'
    // );
    $('#bank-account-form-container').load(url, function() {
        $('.selectpicker').selectpicker('refresh');
    });
}
</script>

</body>

</html>