<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="receivables_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-receivables-report scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('receipt_date'); ?></th>
         <th><?php echo _l('account'); ?></th>
         <th><?php echo _l('category'); ?></th>
         <th><?php echo _l('total_amount'); ?></th>
         <th><?php echo _l('received'); ?></th>
         <th><?php echo _l('receivable'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td></td>
         <td></td>
         <td></td>
         <td class="amount"></td>
         <td class="received"></td>
         <td class="balance"></td>
      </tr>
   </tfoot>
</table>
</div>