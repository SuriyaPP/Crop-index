<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'type',
    'is_default',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'categories';

$where = [];
$search = $this->ci->input->post('search');

if(isset($search['value']) && $search['value'] != '') {
    $searchValue = trim($search['value']);

    if($searchValue == '1' || $searchValue == '2' || $searchValue == '3') {
        $_POST['search']['value'] = '';
        $where[] = 'AND type = ' . (int)$searchValue;
    }
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if($aColumns[$i] == 'id') {
            $_data = '<span class="text-muted">' . $aRow['id'] . '</span>';
        }

        if ($aColumns[$i] == 'name') {
            $is_default = (int) $aRow['is_default'];

            // Category name
            if($is_default == 1) {
                $_data = '<span class="tw-font-medium">' . e($_data) . '</span>';
            } else {
                $_data = '<a href="#" onclick="newCategory(' . $aRow['id'] . '); return false;" class="tw-font-medium">' . e($_data) . '</a>';
            }

            // Row options
            $_data .= '<div class="row-options">';

            if($is_default == 1) {
                // System category label
                $_data .= '<span class="badge badge-info">' . _l('system') . '</span>';
            } else {
                $_data .= '<a href="#" onclick="newCategory(' . $aRow['id'] . '); return false;">' . _l('edit') . '</a>';

                if (staff_can('delete',  'categories')) {
                    $_data .= ' | <a href="' . admin_url(NGO_TRUST_MODULE_NAME. '/category/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }
            }

            $_data .= '</div>';
        } elseif($aColumns[$i] == 'type') {
            // $_data = category_type($_data);
            $_data = '<span class="label label-default category-filter" data-value="' . $aRow['type'] . '" style="cursor:pointer">' . category_type($_data) . '</span>';
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}