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

   .table-sources_application-report tbody td{
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
</style>

<div id="sources_application_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-sources_application-report scroll-responsive">
   <thead>
      <tr>
         <th width="5%"></th>
         <th width="55%"><?php echo _l('particulars'); ?></th>
         <th width="20%"><?php echo _l('receipts'); ?></th>
         <th width="20%"><?php echo _l('payments'); ?></th>
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