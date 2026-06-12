<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'holder_name',
    'bank_name',
    'bank_type',
    'bank_branch',
    'account_number',
    'opening_balance',
    'bank_address',
    'contact_number',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'bank_accounts';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'holder_name') {
            $_data = '<a href="' . admin_url(NGO_TRUST_MODULE_NAME. '/bank_account/create/' . $aRow['id']) . '" class="tw-font-medium">' . e($_data) . '</a>';
            $_data .= '<div class="row-options">';
            // $_data .= '<a href="' . admin_url(NGO_TRUST_MODULE_NAME. '/bank_account/create/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            $_data .= '<a href="#" onclick="newBankAccount('. $aRow['id'] .'); return false;">' . _l('edit') . '</a>';

            if (staff_can('delete',  'bank_accounts')) {
                $_data .= ' | <a href="' . admin_url(NGO_TRUST_MODULE_NAME. '/bank_account/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'end_date') {
            $_data = e(_d($_data));
        } elseif ($aColumns[$i] == 'goal_type') {
            $_data = e(format_goal_type($_data));
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}