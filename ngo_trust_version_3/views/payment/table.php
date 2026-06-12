<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'date',
    'amount',
    'bank_name',
    'company',
    'name',
    'reference',
    'description',
    'attachment',
    'is_fully_paid',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'payments';

$joins = [
    'LEFT JOIN ' . db_prefix() . 'bank_accounts acc ON acc.id = ' . db_prefix() . 'payments.account_id',
    'LEFT JOIN ' . db_prefix() . 'clients don ON don.userid = ' . db_prefix() . 'payments.vendorid',
    'LEFT JOIN ' . db_prefix() . 'categories cat ON cat.id = ' . db_prefix() . 'payments.category_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $joins, [], [db_prefix() . 'payments.id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == 'date') {
            $_data = '<a href="#" onclick="viewPayment(' . $aRow['id'] . '); return false;" class="tw-font-medium">' . e($_data) . '</a>';
            $_data .= '<div class="row-options">';

            if(!$aRow['is_fully_paid']) {
                $_data .= '<a href="#" onclick="paymentModal('. $aRow['id'] .'); return false;">' . _l('pay') . '</a>';
                $_data .= ' | <a href="#" onclick="newPayment('. $aRow['id'] .'); return false;">' . _l('edit') . '</a>';
            } else {
                $_data .= '<a href="#" onclick="newPayment('. $aRow['id'] .'); return false;">' . _l('edit') . '</a>';
            }

            if (staff_can('delete',  'payments')) {
                $_data .= ' | <a href="' . admin_url(NGO_TRUST_MODULE_NAME. '/payment/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        }

        if($aColumns[$i] == 'attachment') {
            if(!empty($_data) && file_exists(FCPATH . $_data)) {
                $fileUrl = base_url($_data);
                $ext = pathinfo($_data, PATHINFO_EXTENSION);

                if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
                    $_data = '<a href="' . $fileUrl . '" target="_blank">
                        <img src="' . $fileUrl . '" style="width:60px; height:60px; border-radius:4px;" >
                    </a>';
                } else {
                    $_data = '<a href="' . $fileUrl . '" target="_blank">
                        <i class="fa fa-paperclip"></i> View
                    </a>';
                }
            } else {
                $_data = '-';
            }
        }

        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}