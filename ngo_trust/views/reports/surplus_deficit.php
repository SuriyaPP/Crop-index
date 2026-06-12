<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
   .report-divider {
       border-top: 3px solid #333;
       margin: 6px 0;
       width: 100%;
   }

   table.dataTable td .report-divider {
       position: relative;
       left: -124%;
       width: 300%;
   }

   .table-surplus_deficit-report tbody td{
      border-top: none !important;
   }

   .toggle-transactions i {
       cursor: pointer;
       font-size: 14px;
       margin-right: 6px;
   }

   .child-row {
       background: #f9fafb;
       border-left: 3px solid #3b82f6;
   }

   .surplus-row td {
      color: #0CAF60 ! important;
   }

   .deficit-row td {
      color: #ff0000 ! important;
   }
</style>

<div id="surplus_deficit_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-surplus_deficit-report scroll-responsive">
   <thead>
      <tr>
         <th width="5%"></th>
         <th width="55%"><?php echo _l('particulars'); ?></th>
         <th width="20%"><?php echo _l('income'); ?></th>
         <th width="20%"><?php echo _l('report_expense'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td colspan="2"></td>
         <td class="receipts text-right"></td>
         <td class="payments text-right"></td>
      </tr>
   </tfoot>
</table>
</div>