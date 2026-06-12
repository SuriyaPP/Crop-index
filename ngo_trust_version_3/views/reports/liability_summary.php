<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="liability_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-liability-report scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('receipt_date'); ?></th>
         <th><?php echo _l('account'); ?></th>
         <th><?php echo _l('category'); ?></th>
         <th><?php echo _l('type'); ?></th>
         <th><?php echo _l('amount'); ?></th>
         <th><?php echo _l('credit'); ?></th>
         <th><?php echo _l('debit'); ?></th>
         <th><?php echo _l('balance'); ?></th>
         <th><?php echo _l('acc_balance'); ?></th>
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
         <td class="credit"></td>
         <td class="debit"></td>
         <td class="balance"></td>
         <td></td>
      </tr>
   </tfoot>
</table>
</div>