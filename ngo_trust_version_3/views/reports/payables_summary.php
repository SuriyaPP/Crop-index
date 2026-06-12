<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="payables_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-payables-report scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('receipt_date'); ?></th>
         <th><?php echo _l('account'); ?></th>
         <th><?php echo _l('category'); ?></th>
         <th><?php echo _l('asset_category'); ?></th>
         <th><?php echo _l('total_amount'); ?></th>
         <th><?php echo _l('paid'); ?></th>
         <th><?php echo _l('payable'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="amount"></td>
         <td class="paid"></td>
         <td class="balance"></td>
      </tr>
   </tfoot>
</table>
</div>