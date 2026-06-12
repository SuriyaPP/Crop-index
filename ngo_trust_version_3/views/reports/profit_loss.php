<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
   .table-profit_loss-report tbody td {
      border-top: none !important;
   }
   .table-pl-notes tbody td {
      border-top: none !important;
   }
</style>

<div id="pl_notes_header_bar" class="report-header-bar hide">
   <div>
      <button id="toggle_pl_notes" class="btn btn-info mb-5">
         View Notes
      </button>
   </div>
</div>

<div id="profit_loss_summary" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
<table class="table table-profit_loss-report scroll-responsive">
   <thead>
      <tr>
         <th rowspan="2">#</th>
         <th rowspan="2"><?php echo _l('particulars'); ?></th>
         <th rowspan="2"><?php echo _l('note'); ?></th>
         <th class="text-center" colspan="3"><?php echo '31 March 20XX'; ?></th>
         <th class="text-center" colspan="3"><?php echo '31 March 20XX'; ?></th>
      </tr>
      <tr>
         <th><?php echo _l('unrestricted_funds'); ?></th>
         <th><?php echo _l('restricted_funds'); ?></th>
         <th><?php echo _l('total'); ?></th>
         <th><?php echo _l('unrestricted_funds'); ?></th>
         <th><?php echo _l('restricted_funds'); ?></th>
         <th><?php echo _l('total'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td></td><td></td><td></td><td></td><td></td>
         <td></td><td></td><td></td><td></td>
      </tr>
   </tfoot>
</table>
</div>

<!-- Note 16: Other Income -->
<div id="other_income_summary" class="hide">
   <table id="other_income_table" class="table table-pl-notes table-other_income scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 55%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr><td></td><td></td><td></td><td></td></tr>
      </tfoot>
   </table>
</div>

<!-- Note 17: Materials Consumed/Distributed -->
<div id="material_consumed_summary" class="hide">
   <table id="material_consumed_table" class="table table-pl-notes table-material_consumed scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 50%;">Particulars</th>
            <th style="width: 5%;"></th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr><td></td><td></td><td></td><td></td><td></td></tr>
      </tfoot>
   </table>
</div>

<!-- Note 18: Employee Benefits Expense -->
<div id="employee_benefits_summary" class="hide">
   <table id="employee_benefits_table" class="table table-employee_benefits table-pl-note-18 scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 55%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr><td></td><td></td><td></td><td></td></tr>
      </tfoot>
   </table>
</div>

<!-- Note 19: Depreciation and Amortization -->
<div id="depreciation_summary" class="hide">
   <table id="depreciation_table" class="table table-depreciation table-pl-note-19 scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 55%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr><td></td><td></td><td></td><td></td></tr>
      </tfoot>
   </table>
</div>

<!-- Note 20: Finance Costs -->
<div id="finance_cost_summary" class="hide">
   <table id="finance_cost_table" class="table table-finance_cost table-pl-note-20 scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 55%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr><td></td><td></td><td></td><td></td></tr>
      </tfoot>
   </table>
</div>

<!-- Note 21: Other Expenses -->
<div id="other_expense_summary" class="hide">
   <table id="other_expense_table" class="table table-other_expense table-pl-note-21 scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 55%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr><td></td><td></td><td></td><td></td></tr>
      </tfoot>
   </table>
</div>