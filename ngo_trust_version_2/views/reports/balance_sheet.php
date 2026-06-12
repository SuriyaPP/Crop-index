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

   .table-balance_sheet-report tbody td{
      border-top: none !important;
   }
</style>

<div id="balance_sheet_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-balance_sheet-report scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('particulars'); ?></th>
         <th><?php echo _l('liabilities'); ?></th>
         <th><?php echo _l('assets'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td></td>
         <td></td>
         <td></td>
      </tr>
   </tfoot>
</table>
</div>