<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'categories')) { ?>
                <div class="tw-mb-2">
                    <a href="#" class="btn btn-primary" onclick="newCategory(); return false;">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_category'); ?>
                    </a>
                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                        _l('id'),
                        _l('name'),
                        _l('type'),
                        ], 'categories'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _l('new_category'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="category-form-container">
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
    initDataTable('.table-categories', window.location.href);
});
</script>

<script>
function newCategory(id = '') {
    let url = admin_url + 'ngo_trust/category/create/' + id;

    if(id == '') {
        $('.modal-title').text('<?php echo _l('new_category'); ?>');
    } else {
        $('.modal-title').text('<?php echo _l('edit_category'); ?>');
    }

    $('#categoryModal').modal('show');

    $('#category-form-container').load(url, function() {
        $('.selectpicker').selectpicker('refresh');
    });
}
</script>

<script>
    $(function () {
        var table = $('.table-categories').DataTable();
        var activeFilter = '';

        $(document).on('click', '.category-filter', function() {
            var value = $(this).data('value');

            if(activeFilter == value) {
                activeFilter = '';
                table.search('').draw();
                $('div.dataTables_filter input').val('');

                $('.category-filter').removeClass('active');
            } else {
                activeFilter = value;
                table.search(value).draw();
                $('div.dataTables_filter input').val($(this).text());

                $('category-filter').removeClass('active');
                $(this).addClass('active');
            }
        });
    });
</script>

</body>

</html>