<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="payment_receipt_summary" class="hide">
   <div class="row">
      <div class="col-md-4">
         <div class="form-group">
            <label for="account"><?php echo _l('account'); ?></label>
            <select name="account_id" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('report_all'); ?>">
               <?php foreach($accounts as $acc){ ?>
                <option value="<?php echo html_entity_decode($acc['id']); ?>"><?php echo html_entity_decode($acc['bank_name']); ?></option>
            <?php } ?>
         </select>
      </div>
   </div>

   <div class="col-md-4">
      <div class="form-group">
            <label for="category"><?php echo _l('category'); ?></label>
            <select name="category_id" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('report_all'); ?>">
               <?php foreach($categories as $cat){ ?>
                <option value="<?php echo html_entity_decode($cat['id']); ?>"><?php echo html_entity_decode($cat['name']); ?></option>
            <?php } ?>
         </select>
      </div>
   </div>
   <div class="clearfix"></div>
</div>
<table class="table table-payment_receipt-report scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('receipt_date'); ?></th>
         <th><?php echo _l('account'); ?></th>
         <th><?php echo _l('type'); ?></th>
         <th><?php echo _l('notes'); ?></th>
         <th><?php echo _l('amount'); ?></th>
         <th><?php echo _l('credit'); ?></th>
         <th><?php echo _l('debit'); ?></th>
         <th><?php echo _l('balance'); ?></th>
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
      </tr>
   </tfoot>
</table>
</div>