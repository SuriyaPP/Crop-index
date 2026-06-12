<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
   .table-balance_sheet-report-new tbody td{
      border-top: none !important;
   }

   .report-header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
   }

   .report-header-bar .btn {
      margin-right: 10px;
   }
</style>

<div class="report-header-bar">
   <div>
      <button id="toggle_blnce_notes" class="btn btn-info mb-5">
          View Notes
      </button>

      <button id="export_balance_sheet_workbook" class="btn btn-success mb-5">
         <i class="fa fa-file-excel"></i>
         Export Workbook
      </button>
   </div>
</div>

<div id="balance_sheet_summary_new" class="hide">
   <div class="row">
   <div class="clearfix"></div>
</div>
   <table class="table table-balance_sheet-report-new scroll-responsive">
      <thead>
         <tr>
            <th>#</th>
            <th><?php echo _l('particulars'); ?></th>
            <th><?php echo _l('note'); ?></th>
            <th class="bs-date-col" data-col="current"><?php echo '31 March 20XX'; ?></th>
            <th class="bs-date-col" data-col="previous"><?php echo '31 March 20XX'; ?></th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 3 Npo Funds -->
<div id="npo_funds_summary" class="hide">
   <!-- <h4>npo_funds to Accounts</h4> -->

   <table id="npo_funds_table" class="table table-npo_funds scroll-responsive">
      <thead>
         <tr>
            <th>#</th>
            <th style="width: 50%;">Particulars</th>
            <th class="bs-date-col" data-col="opening">As at 1st April 20XX (Opening Balance)</th>
            <th>Funds transferred/received during the year</th>
            <th>Funds Utilised during the year</th>
            <th class="bs-date-col" data-col="current">As at 31st March 20XX (Closing Balance)</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 4 Borrowings (Long, Short Term) -->
<div id="borrowings_summary" class="hide">
   <!-- <h4>borrowings to Accounts</h4> -->

   <table id="borrowings_table" class="table table-borrowings scroll-responsive">
      <thead>
         <tr>
            <th rowspan="2" style="width: 5%;">#</th>
            <th rowspan="2" class="text-center" style="width: 35%;">Particulars</th>
            <th colspan="2" class="text-center">Long Term</th>
            <th colspan="2" class="text-center">Short Term</th>
         </tr>
         <tr>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 5 Long Term Liabilities -->
<div id="other_long_term_liabilities_summary" class="hide">
   <!-- <h4>other_long_term_liabilities to Accounts</h4> -->

   <table id="other_long_term_liabilities_table" class="table table-other_long_term_liabilities scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 6 Provisions (Long, Short Term) -->
<div id="provisions_summary" class="hide">
   <!-- <h4>provisions to Accounts</h4> -->

   <table id="provisions_table" class="table table-provisions scroll-responsive">
      <thead>
         <tr>
            <th rowspan="2" style="width: 5%;">#</th>
            <th rowspan="2" class="text-center" style="width: 35%;">Particulars</th>
            <th colspan="2" class="text-center">Long Term</th>
            <th colspan="2" class="text-center">Short Term</th>
         </tr>
         <tr>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 7 Payables -->
<div id="payables_notes_summary" class="hide">
   <!-- <h4>payables to Accounts</h4> -->

   <table id="payables_notes_table" class="table table-payables_notes scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 8 Other Current Liabilities -->
<div id="other_current_liabilities_summary" class="hide">
   <!-- <h4>other_current_liabilities to Accounts</h4> -->

   <table id="other_current_liabilities_table" class="table table-other_current_liabilities scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 10 Investments (Non-Current, Current) -->
<div id="investments_summary" class="hide">
   <!-- <h4>investments to Accounts</h4> -->

   <table id="investments_table" class="table table-investments scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 11 Term Loans and Advances -->
<div id="term_loans_and_advances_summary" class="hide">
   <!-- <h4>term_loans_and_advances to Accounts</h4> -->

   <table id="term_loans_and_advances_table" class="table table-term_loans_and_advances scroll-responsive">
      <thead>
         <tr>
            <th rowspan="2" style="width: 5%;">#</th>
            <th rowspan="2" class="text-center" style="width: 35%;">Particulars</th>
            <th colspan="2" class="text-center">Long Term</th>
            <th colspan="2" class="text-center">Short Term</th>
         </tr>
         <tr>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 12 Other Non-Current Assets -->
<div id="other_non_current_assets_summary" class="hide">
   <!-- <h4>other_non_current_assets to Accounts</h4> -->

   <table id="other_non_current_assets_table" class="table table-other_non_current_assets scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 13 Receivables -->
<div id="receivables_notes_summary" class="hide">
   <!-- <h4>receivables_notes to Accounts</h4> -->

   <table id="receivables_notes_table" class="table table-receivables_notes scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 14 Cash and Bank Balances -->
<div id="cash_and_bank_balances_summary" class="hide">
   <!-- <h4>cash_and_bank_balances to Accounts</h4> -->

   <table id="cash_and_bank_balances_table" class="table table-cash_and_bank_balances scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>

<!-- Notes : 15 Other Current Assets -->
<div id="other_current_assets_summary" class="hide">
   <!-- <h4>other_current_assets to Accounts</h4> -->

   <table id="other_current_assets_table" class="table table-other_current_assets scroll-responsive">
      <thead>
         <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Particulars</th>
            <th class="bs-date-col" data-col="current">31 March 20XX</th>
            <th class="bs-date-col" data-col="previous">31 March 20XX</th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>