<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
   .table-asset_depreciation-report tbody td {
       border-top: none !important;
   }

   .table-asset_depreciation-report thead th {
       background: #f9fafb;
       font-weight: 600;
       text-align: center;
   }

   .table-asset_depreciation-report td.text-right,
   .table-asset_depreciation-report th.text-right {
       text-align: right;
   }

   .table-asset_depreciation-report .total-row th {
       font-weight: 700;
       border-top: 3px solid #000 !important;
       background: #fdfdfd;
   }
</style>

<div id="asset_depreciation_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-asset_depreciation-report scroll-responsive">
   <thead>
      <tr>
         <th rowspan="3"><?php echo _l('description'); ?></th>
         <th rowspan="3"><?php echo _l('wdv_on'); ?></th>

         <th colspan="2"><?php echo _l('additions'); ?></th>

         <th rowspan="3"><?php echo _l('total'); ?></th>
         <th rowspan="3"><?php echo _l('rate'); ?></th>

         <th colspan="2"><?php echo _l('depreciation'); ?></th>

         <th rowspan="3"><?php echo _l('total_depreciation'); ?></th>
         <th rowspan="3"><?php echo _l('wdv_on'); ?></th>
      </tr>
      <tr>
         <th><?php echo _l('before'); ?></th>
         <th><?php echo _l('after'); ?></th>
         
         <th><?php echo _l('more_than'); ?></th>
         <th><?php echo _l('less_than'); ?></th>
      </tr>
      <tr>
         <th class="text-muted"><?php echo _l('upto_30_sep'); ?></th>
         <th class="text-muted"><?php echo _l('after_30_sep'); ?></th>
         
         <th>180 <?php echo _l('days'); ?></th>
         <th>180 <?php echo _l('days'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td></td>
         <td class="text-right"></td>
         <td class="text-right"></td>
         <td class="text-right"></td>
         <td class="text-right"></td>
         <td></td>
         <td class="text-right"></td>
         <td class="text-right"></td>
         <td class="text-right"></td>
         <td class="text-right"></td>
      </tr>
   </tfoot>
</table>
</div>