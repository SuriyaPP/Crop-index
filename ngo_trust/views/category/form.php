<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open($this->uri->uri_string(), ['id' => 'category-form']); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo render_input('name', 'name', $category->name ?? ''); ?>
    </div>
    <?php
        $category_types = [
            ['id' => 1, 'name' => 'Receipt'],
            ['id' => 2, 'name' => 'Payment'],
            ['id' => 3, 'name' => 'Asset'],
        ];
    ?>
    <div class="col-md-12">
        <?php echo render_select('type', $category_types, ['id', 'name'], 'type', $category->type ?? ''); ?>
    </div>
    <div class="col-md-12">
        <?php echo render_select('parent_id', $parent_category, ['id', 'name'], 'parent_category', $category->parent_id ?? ''); ?>
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
    $('select[name="type"] option[value="3"]').prop('disabled', true);

    appValidateForm($('form'), {
        name: 'required',
        type: 'required',
    });

    let $parentSelect = $('#parent_id');
    let typeVal = $('select[name="type"]').val();

    if(typeVal && typeVal != '') {
        $parentSelect.prop('disabled', false);
    } else {
        $parentSelect.prop('disabled', true);
    }

    $parentSelect.selectpicker('refresh');

    $('select[name="type"]').on('change', function() {
        let type = $(this).val();

        $parentSelect.prop('disabled', true).selectpicker('refresh');

        $.post(
            admin_url + 'ngo_trust/category/get_parent_category',
            { type: type },
            function(response) {
                let data = JSON.parse(response);
                let html = '<option value=""></option>';

                data.forEach(function(item) {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });

                $parentSelect.html(html).prop('disabled', false).selectpicker('refresh');
            }
        );
    });
});
</script>