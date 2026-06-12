<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();  ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
              <!-- Table report -->
              <div class="col-md-4 border-right">
                <h4 class="no-margin font-medium"><i class="fa fa-balance-scale" aria-hidden="true"></i> <?php echo _l('reports'); ?></h4>
                <hr />
                <p><a href="#" class="font-medium" onclick="init_report(this,'liability_summary'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('liability_summary'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'asset_summary'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('asset_summary'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'payment_receipt'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('payment_receipt'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'receivables_summary'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('receivables_summary'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'payables_summary'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('payables_summary'); ?></a></p>
              </div>
              <!-- End table report -->
              <!-- Chart report -->
              <div class="col-md-4 border-right">
                <!-- <h4 class="no-margin font-medium"><i class="fa fa-area-chart" aria-hidden="true"></i> <?php echo _l('charts_based_report'); ?></h4> -->
                <hr />
                <p><a href="#" class="font-medium" onclick="init_report(this,'sources_application'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('sources_application'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'surplus_deficit'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('surplus_deficit'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'asset_depreciation'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('asset_depreciation'); ?></a></p>
                <hr class="hr-10" />
                <p><a href="#" class="font-medium" onclick="init_report(this,'balance_sheet'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('balance_sheet'); ?></a></p>
                <hr class="hr-10" />

                <p><a href="#" class="font-medium" onclick="init_report(this,'balance_sheet_new'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('balance_sheet_new'); ?></a></p>
                <hr class="hr-10" />

                <p><a href="#" class="font-medium" onclick="init_report(this,'profit_loss'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo _l('profit_loss'); ?></a></p>
              </div>
              <!-- End chart report -->


              <div class="col-md-4">
                <div class="bg-light-gray border-radius-4">
                  <div class="p8">

                    <!-- <div id="currency" class="form-group hide">
                      <label for="currency"><i class="fa fa-question-circle" data-toggle="tooltip" title="<?php echo _l('report_sales_base_currency_select_explanation'); ?>"></i> <?php echo _l('currency'); ?></label><br />
                      <select class="selectpicker" name="currency" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">

                      </select>
                    </div> -->


                    <div class="form-group" id="report-time">
                        <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                        <select class="selectpicker" name="months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                           <option value="this_month"><?php echo _l('this_month'); ?></option>
                           <option value="1"><?php echo _l('last_month'); ?></option>
                           <option value="this_year"><?php echo _l('this_year'); ?></option>
                           <option value="last_year"><?php echo _l('last_year'); ?></option>
                           <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                           <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                           <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                           <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                        </select>
                     </div>

                     <div id="date-range" class="hide mbot15">
                          <div class="row">
                             <div class="col-md-6">
                                <label for="report-from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                                <div class="input-group date">
                                   <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                                   <div class="input-group-addon">
                                      <i class="fa fa-calendar calendar-icon"></i>
                                   </div>
                                </div>
                             </div>
                             <div class="col-md-6">
                                <label for="report-to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                                <div class="input-group date">
                                   <input type="text" class="form-control datepicker" disabled="disabled" id="report-to" name="report-to">
                                   <div class="input-group-addon">
                                      <i class="fa fa-calendar calendar-icon"></i>
                                   </div>
                                </div>
                             </div>
                          </div>
                       </div>

                       <?php $current_year = date('Y');
                              $y0 = (int)$current_year;
                              $y1 = (int)$current_year - 1;
                              $y2 = (int)$current_year - 2;
                              $y3 = (int)$current_year - 3;
                           ?>
                       <div class="form-group hide" id="year_requisition">
                          <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                          <select  name="year_requisition" id="year_requisition"  class="selectpicker"  data-width="100%" data-none-selected-text="<?php echo _l('filter_by').' '._l('year'); ?>">
                                <option value="<?php echo html_entity_decode($y0); ?>" <?php echo 'selected' ?>><?php echo _l('year').' '. html_entity_decode($y0) ; ?></option>
                                <option value="<?php echo html_entity_decode($y1); ?>"><?php echo _l('year').' '. html_entity_decode($y1) ; ?></option>
                                <option value="<?php echo html_entity_decode($y2); ?>"><?php echo _l('year').' '. html_entity_decode($y2) ; ?></option>
                                <option value="<?php echo html_entity_decode($y3); ?>"><?php echo _l('year').' '. html_entity_decode($y3) ; ?></option>

                          </select>
                       </div>

                    <div id="date-range" class="hide mbot15">
                      <div class="row">
                        <div class="col-md-6">
                          <?php echo render_date_input('report-from', 'report_sales_from_date'); ?>
                        </div>
                        <div class="col-md-6">
                          <?php echo render_date_input('report-to', 'report_sales_to_date'); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="report" class="hide">
              <hr class="hr-panel-heading" />
              <div class="row">
                <center>
                  <h4 class="title_table"></h4>
                </center>
              </div>
              <?php $this->load->view('reports/liability_summary.php'); ?>
              <?php $this->load->view('reports/sources_application.php'); ?>
              <?php $this->load->view('reports/payment_receipt.php'); ?>
              <?php $this->load->view('reports/asset_summary.php'); ?>
              <?php $this->load->view('reports/receivables_summary.php'); ?>
              <?php $this->load->view('reports/payables_summary.php'); ?>
              <?php //$this->load->view('reports/check_in_out_progress.php'); ?>
              <?php $this->load->view('reports/surplus_deficit.php'); ?>
              <?php $this->load->view('reports/asset_depreciation.php'); ?>
              <?php $this->load->view('reports/balance_sheet.php'); ?>
              <?php $this->load->view('reports/balance_sheet_new.php'); ?>
              <?php $this->load->view('reports/profit_loss.php'); ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<?php require 'modules/ngo_trust/assets/js/report_jsNew.php'; ?>
</body>

</html>