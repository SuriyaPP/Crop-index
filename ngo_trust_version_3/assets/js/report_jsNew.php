<script>
	var report_liability,
		report_asset,
		report_payment_receipt,
		report_receivables,
		report_payables,
		report_sources_application,
		report_surplus_deficit,
		report_asset_depreciation,
		report_balance_sheet,
		report_balance_sheet_new,
		report_profit_loss,
		report_from_choose,
		fnServerParams;

	var report_from = $('input[name="report-from"]');
	var report_to = $('input[name="report-to"]');
	var date_range = $('#date-range');

	var notesBtn = $('#toggle_blnce_notes');
	var notesLoaded = false;

	var plNotesBtn = $('#toggle_pl_notes');
	var plNotesLoaded = false;

	var NOTE_PANEL_IDS = [
		'#npo_funds_summary',
		'#borrowings_summary',
		'#other_long_term_liabilities_summary',
		'#provisions_summary',
		'#payables_notes_summary',
		'#other_current_liabilities_summary',
		'#investments_summary',
		'#term_loans_and_advances_summary',
		'#other_non_current_assets_summary',
		'#cash_and_bank_balances_summary',
		'#receivables_notes_summary',
		'#other_current_assets_summary',
	];

	var NOTES_TABLE = {
		'.table-npo_funds' : 'get_npo_funds_notes',
		'.table-borrowings' : 'get_borrowings_notes',
		'.table-other_long_term_liabilities' : 'get_other_long_term_liabilities_notes',
		'.table-provisions' : 'get_provisions_notes',
		'.table-payables_notes' : 'get_payables_notes',
		'.table-other_current_liabilities' : 'get_other_current_liabilities_notes',
		'.table-investments' : 'get_investments_notes',
		'.table-term_loans_and_advances' : 'get_term_loans_advances_notes',
		'.table-other_non_current_assets' : 'get_other_non_current_assets_notes',
		'.table-cash_and_bank_balances' : 'get_cash_and_bank_balances_notes',
		'.table-receivables_notes' : 'get_receivables_notes',
		'.table-other_current_assets' : 'get_other_current_assets_notes',
	};

	var NOTES_TABLE_IDS = [
		'#npo_funds_table',
		'#borrowings_table',
		'#other_long_term_liabilities_table',
		'#provisions_table',
		'#payables_notes_table',
		'#other_current_liabilities_table',
		'#investments_table',
		'#term_loans_and_advances_table',
		'#other_non_current_assets_table',
		'#other_current_assets_table',
		'#cash_and_bank_balances_table',
		'#receivables_notes_table',
	];

	var PL_NOTE_PANEL_IDS = [
		'#other_income_summary',
		'#material_consumed_summary',
		'#employee_benefits_summary',
		'#depreciation_summary',
		'#finance_cost_summary',
		'#other_expense_summary',
	];

	var PL_NOTES_TABLE = {
		'.table-other_income' : 'get_other_income_notes',
		'.table-material_consumed' : 'get_material_consumed_notes',
		'.table-employee_benefits' : 'get_employee_benefits_notes',
		'.table-depreciation' : 'get_depreciation_notes',
		'.table-finance_cost' : 'get_finance_cost_notes',
		'.table-other_expense' : 'get_other_expense_notes',
	};

	var PL_NOTES_TABLE_IDS = [
		'#other_income_table',
		'#material_consumed_table',
		'#employee_benefits_table',
		'#depreciation_table',
		'#finance_cost_table',
		'#other_expense_table',
	];

	var REPORT_CONFIG = {
		liability_summary : { panel : 'report_liability', notesBtn: false, plNotesBtn: false },
		asset_summary : { panel : 'report_asset', notesBtn: false, plNotesBtn: false },
		payment_receipt : { panel : 'report_payment_receipt', notesBtn: false, plNotesBtn: false },
		receivables_summary : { panel : 'report_receivables', notesBtn: false, plNotesBtn: false },
		payables_summary : { panel : 'report_payables', notesBtn: false, plNotesBtn: false },
		sources_application : { panel : 'report_sources_application', notesBtn: false, plNotesBtn: false },
		surplus_deficit : { panel : 'report_surplus_deficit', notesBtn: false, plNotesBtn: false },
		asset_depreciation : { panel : 'report_asset_depreciation', notesBtn: false, plNotesBtn: false },
		balance_sheet : { panel : 'report_balance_sheet', notesBtn: false, plNotesBtn: false },
		balance_sheet_new : { panel : 'report_balance_sheet_new', notesBtn: true, plNotesBtn: false },
		profit_loss : { panel : 'report_profit_loss', notesBtn: false, plNotesBtn: true },
	};

	var PANEL_MAP = [];

	var TFOOT_CONFIG = {
		'.table-liability-report' : [{c: 'amount', k: 'amount'}, {c: 'credit', k: 'credit'}, {c: 'debit', k: 'debit'}, {c: 'balance', k: 'balance'}],
		'.table-asset-report' : [{c: 'amount', k: 'amount'}, {c: 'credit', k: 'credit'}, {c: 'debit', k: 'debit'}, {c: 'balance', k: 'balance'}],
		'.table-payment_receipt-report' : [{c: 'amount', k: 'amount'}, {c: 'credit', k: 'credit'}, {c: 'debit', k: 'debit'}, {c: 'balance', k: 'balance'}],
		'.table-receivables-report' : [{c: 'amount', k: 'amount'}, {c: 'received', k: 'received'}, {c: 'balance', k: 'balance'}],
		'.table-payables-report' : [{c: 'amount', k: 'amount'}, {c: 'paid', k: 'paid'}, {c: 'balance', k: 'balance'}],
	};

	(function($) {
		report_asset = $('#asset_summary');
		report_liability = $('#liability_summary');
		report_payment_receipt = $('#payment_receipt_summary');
		report_receivables = $('#receivables_summary');
		report_payables = $('#payables_summary');
		report_sources_application = $('#sources_application_summary');
		report_surplus_deficit = $('#surplus_deficit_summary');
		report_asset_depreciation = $('#asset_depreciation_summary');
		report_balance_sheet = $('#balance_sheet_summary');
		report_balance_sheet_new = $('#balance_sheet_summary_new');
		report_profit_loss = $('#profit_loss_summary');
		report_from_choose = $('#from_choose_summary');

		PANEL_MAP = {
			report_liability : report_liability,
			report_asset : report_asset,
			report_payment_receipt : report_payment_receipt,
			report_receivables : report_receivables,
			report_payables : report_payables,
			report_sources_application : report_sources_application,
			report_surplus_deficit : report_surplus_deficit,
			report_asset_depreciation : report_asset_depreciation,
			report_balance_sheet : report_balance_sheet,
			report_balance_sheet_new : report_balance_sheet_new,
			report_profit_loss : report_profit_loss,
		};

		fnServerParams = {
			account : '[name="account_id"]',
			category : '[name="category_id"]',
			report_months : '[name="months-report"]',
			report_from : '[name="report-from"]',
			report_to : '[name="report-to"]',
			year_requisition : '[name="year_requisition"]',
		};

		$('select[name="months-report"]').on('change', function() {
			var val = $(this).val();
			report_to.attr('disabled', true).val('');
			report_from.val('');

			if(val == 'custom') {
				date_range.addClass('fadeIn').removeClass('hide');
				return;
			}

			date_range.removeClass('fadeIn').addClass('hide');
			gen_reports();
		});

		$('select[name="year_requisition"], select[name="account_id"], select[name="category_id"]').on('change', function() {
			gen_reports();
		});

		report_from.on('change', function() {
			var val = $(this).val();
			if(val != '') {
				report_to.attr('disabled', false);
				if(report_to.val() != '') {
					gen_reports();
				}
			} else {
				report_to.attr('disabled', true);
			}
		});

		report_to.on('change', function() {
			if($(this).val() != '') {
				gen_reports();
			}
		});

		$.each(TFOOT_CONFIG, function(tableClass, cols) {
			$(tableClass).on('draw.dt', function() {
				var dt = $(this).DataTable();
				var sums = dt.ajax.json().sums;
				var $tf = $(this).find('tfoot').addClass('bold');
				$tf.find('td').eq(0).html('<?php echo _("report_total"); ?>(<?php echo _l("per_page"); ?>)');
				$.each(cols, function(i, col) {
					$tf.find('td.' + col.c).html(sums[col.k]);
				});
			});
		});

		$('#toggle_blnce_notes').on('click', function() {
			var btn = $(this);

			if(!report_balance_sheet_new.hasClass('hide')) {
				report_balance_sheet_new.addClass('hide');
				toggleNotePanels(NOTE_PANEL_IDS, false);
				btn.text('Balance Sheet');

				update_bs_date_headers();

				if(!notesLoaded) {
					loadNotesTables(NOTES_TABLE);
					notesLoaded = true;
				} else {
					reloadNotesTables(NOTES_TABLE_IDS);
				}
			} else {
				toggleNotePanels(NOTE_PANEL_IDS, true);
				report_balance_sheet_new.removeClass('hide');
				btn.text('View Notes');
			}
		});

		$('#toggle_pl_notes').on('click', function() {
			var btn = $(this);

			if(!report_profit_loss.hasClass('hide')) {
				report_profit_loss.addClass('hide');
				toggleNotePanels(PL_NOTE_PANEL_IDS, false);
				btn.text('Profi & Loss');

				update_bs_date_headers();

				if(!plNotesLoaded) {
					loadNotesTables(PL_NOTES_TABLE);
					plNotesLoaded = true;
				} else {
					reloadNotesTables(PL_NOTES_TABLE_IDS);
				}
			} else {
				toggleNotePanels(PL_NOTE_PANEL_IDS, true);
				report_profit_loss.removeClass('hide');
				btn.text('View Notes');
			}
		});
	})(jQuery);

	// Generic shared helpers — work for both BS Notes and P&L Notes
	// by accepting the relevant array/object as a parameter

	function toggleNotePanels(panelIds, hide) {
		$.each(panelIds, function(i, id) {
			hide ? $(id).addClass('hide') : $(id).removeClass('hide');
		});
	}

	function loadNotesTables(notesTableObj) {
		$.each(notesTableObj, function(tableClass, endpoint) {
			initNotesTable(tableClass, endpoint);
		});
	}

	function reloadNotesTables(tableIdsArr) {
		$.each(tableIdsArr, function(i, tableId) {
			var $t = $(tableId);
			if($t.length && $.fn.DataTable.isDataTable($t)) {
				$t.DataTable().ajax.reload();
			}
		});
	}

	function initNotesTable(tableClass, endpoint) {
		if($.fn.DataTable.isDataTable(tableClass)) {
			$(tableClass).DataTable().destroy();
		}

		initDataTable(tableClass, admin_url + 'ngo_trust/reports/' + endpoint, false, false, fnServerParams);
	}

	function initReportTable(tableClass, endpoint) {
		if($.fn.DataTable.isDataTable(tableClass)) {
			$(tableClass).DataTable().destroy();
		}

		initDataTable(tableClass, admin_url + 'ngo_trust/reports/' + endpoint, false, false, fnServerParams);
	}

	function init_report(e, type) {
		'use strict';

		var $wrapper = $('#report');
		if($wrapper.hasClass('hide')) {
			$wrapper.removeClass('hide');
		}

		$('head title').html($(e).text());
		$('#currency').removeClass('hide');

		$.each(PANEL_MAP, function(name, $el) {
			$el.addClass('hide');
		});

		// toggleNotePanels(true);
		report_from_choose.addClass('hide');
		$('#year_requisition').addClass('hide');
		notesBtn.addClass('hide');

		$('select[name="months-report"]').selectpicker('val', 'this_month');

		var cfg = REPORT_CONFIG[type];
		if(cfg) {
			PANEL_MAP[cfg.panel].removeClass('hide');
			report_from_choose.removeClass('hide');
			if(cfg.notesBtn) {
				notesBtn.removeClass('hide');
			}
			if(cfg.plNotesBtn) {
				$('#pl_notes_header_bar').removeClass('hide');
				$('#toggle_pl_notes').text('View Notes');
				plNotesLoaded = false;
				toggleNotePanels(PL_NOTE_PANEL_IDS, true);
			} else {
				$('#pl_notes_header_bar').addClass('hide');
				toggleNotePanels(PL_NOTE_PANEL_IDS, true);
			}
		}

		gen_reports();
	}

	function gen_reports() {
		'use strict';

		notesLoaded = false;
		plNotesLoaded = false;

		var dispatch = [
			{panel: report_liability, fn: liability_report},
			{panel: report_asset, fn: asset_report},
			{panel: report_payment_receipt, fn: payment_receipt_report},
			{panel: report_receivables, fn: receivables_report},
			{panel: report_payables, fn: payables_report},
			{panel: report_sources_application, fn: sources_application_report},
			{panel: report_surplus_deficit, fn: surplus_deficit_report},
			{panel: report_asset_depreciation, fn: asset_depreciation_report},
			{panel: report_balance_sheet, fn: balance_sheet_report},
			{panel: report_balance_sheet_new, fn: balance_sheet_report_new},
			{panel: report_profit_loss, fn: profit_loss_report},
		];

		for(var i = 0; i < dispatch.length; i++) {
			if(!dispatch[i].panel.hasClass('hide')) {
				dispatch[i].fn();
				break;
			}
		}
	}

	function liability_report() {
		initReportTable('.table-liability-report', 'liability_report');
	}

	function asset_report() {
		initReportTable('.table-asset-report', 'asset_report');
	}

	function payment_receipt_report() {
		initReportTable('.table-payment_receipt-report', 'payment_receipt_report');
	}

	function receivables_report() {
		initReportTable('.table-receivables-report', 'receivables_report');
	}

	function payables_report() {
		initReportTable('.table-payables-report', 'payables_report');
	}

	function sources_application_report() {
		initReportTable('.table-sources_application-report', 'sources_application_report');
	}

	function surplus_deficit_report() {
		initReportTable('.table-surplus_deficit-report', 'surplus_deficit_report');
	}

	function asset_depreciation_report() {
		initReportTable('.table-asset_depreciation-report', 'asset_depreciation_report');
	}

	function balance_sheet_report() {
		initReportTable('.table-balance_sheet-report', 'balance_sheet_report');
	}

	function balance_sheet_report_new() {
		update_bs_date_headers();
		initReportTable('.table-balance_sheet-report-new', 'balance_sheet_report_new');
	}

	function profit_loss_report() {
		initReportTable('.table-profit_loss-report', 'profit_loss_report');
	}

	function update_bs_date_headers() {
		var monthsReport = $('select[name="months-report"]').val();
		var fromInput = $('input[name="report-from"]').val();
		var toInput = $('input[name="report-to"]').val();

		var now = new Date();

		var startDate, endDate;

		function formatHeader(d) {
			return d.getDate() + ' ' + d.toLocaleString('en-IN', {month: 'long'}) + ' ' + d.getFullYear();
		}

		function parseInputDate(str) {
			if(!str) {
				return null;
			}

			if(str.indexOf('/') !== -1) {
				var p = str.split('/');
				return new Date(p[2], p[1] - 1, p[0]);
			}

			var p = str.split('-');
			return new Date(p[0], p[1] - 1, p[2]);
		}

		var periodMap = {
			this_month: function() {
				startDate = new Date(now.getFullYear(), now.getMonth(), 1);
				endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
			},
			'1': function() {
				startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
				endDate = new Date(now.getFullYear(), now.getMonth(), 0);
			},
			this_year: function() {
				startDate = new Date(now.getFullYear(), 0, 1);
				endDate = new Date(now.getFullYear(), 11, 31);
			},
			last_year: function() {
				startDate = new Date(now.getFullYear() - 1, 0, 1);
				endDate = new Date(now.getFullYear() - 1, 11, 31);
			},
			'3': function() {
				startDate = new Date(now.getFullYear(), now.getMonth() - 2, 1);
				endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
			},
			'6': function() {
				startDate = new Date(now.getFullYear(), now.getMonth() - 5, 1);
				endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
			},
			'12': function() {
				startDate = new Date(now.getFullYear(), now.getMonth() - 11, 1);
				endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
			},
		};

		if(periodMap[monthsReport]) {
			periodMap[monthsReport]();
		} else if(monthsReport == 'custom' && fromInput && toInput) {
			startDate = parseInputDate(fromInput);
			endDate = parseInputDate(toInput);
		} else {
			endDate = now;
			startDate = new Date(now.getFullYear() - 1, now.getMonth(), now.getDate());
		}

		if(!startDate || !endDate) {
			return;
		}

		var totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;

		var prevEndDate = new Date(startDate);
		prevEndDate.setDate(prevEndDate.getDate() - 1);

		var prevStartDate = new Date(prevEndDate);
		prevStartDate.setDate(prevStartDate.getDate() - (totalDays - 1));

		var openingDate = new Date(startDate);
		openingDate.setDate(openingDate.getDate() - 1);

		var labels = {
			current: formatHeader(endDate),
			previous: formatHeader(prevEndDate),
			opening: 'As at ' + formatHeader(openingDate) + '(Opening Balance)',
			closing: 'As at ' + formatHeader(endDate) + '(Closing Balance)',
		};

		$('.bs-date-col').each(function() {
			var col = $(this).data('col');
			if(col == 'current') {
				$(this).text($(this).text().indexOf('Closing') !== -1 ? labels.closing : labels.current);
			} else if(col == 'previous') {
				$(this).text(labels.previous);
			} else if(col == 'opening') {
				$(this).text(labels.opening);
			}
		});
	}

	function toggleDetails(rowEl) {
		var key = rowEl.dataset.key;
		var type = rowEl.dataset.type.trim();
		var ids = rowEl.dataset.ids;
		var report = rowEl.dataset.report;
		var icon = document.getElementById('toggle-icon-' + type + '-' + key);
		var $row = $(rowEl);

		if($row.hasClass('expanded')) {
			$row.removeClass('expanded');
			if(icon) {
				icon.innerHTML = '➕ ';
			}
			$row.next('.details-wrapper').remove();
			return;
		}

		$row.addClass('expanded');
		if(icon) {
			icon.innerHTML = '➖ ';
		}
		$row.next('.details-wrapper').remove();

		var col3 = (report == 'sources_application') ? 'Receipts' : 'Income';
		var col4 = (report == 'sources_application') ? 'Payments' : 'Expense';

		$row.after(
			'<tr class="details-wrapper">' +
	          '<td colspan="4" style="padding:0 40px 15px;">' +
	            '<table class="table table-borderless mb-0">' +
	              '<thead><tr style="background:#f7f9fb;font-weight:bold;">' +
	                '<th class="text-center">Particulars</th>' +
	                '<th class="text-center">Date</th>' +
	                '<th class="text-center">' + col3 + '</th>' +
	                '<th class="text-center">' + col4 + '</th>' +
	              '</tr></thead>' +
	              '<tbody id="details-body-' + type + '-' + key + '">' +
	                '<tr><td colspan="4">Loading...</td></tr>' +
	              '</tbody>' +
	            '</table>' +
	          '</td>' +
	        '</tr>'
		);

		$.ajax({
			url: admin_url + 'ngo_trust/reports/transaction_details',
			type: 'POST',
			dataType: 'json',
			data: {
				transaction_ids: ids,
				type: type
			},
			success: function(response) {
				var rows = response.map(function(txn) {
					return '<tr>' +
	                    '<td class="text-center">' + (txn.description || ' - ') + '</td>' +
	                    '<td class="text-center">' + txn.date + '</td>' +
	                    '<td class="text-center">' + (type === 'Receipt' ? txn.received_amount : ' - ') + '</td>' +
	                    '<td class="text-center">' + (type === 'Payment' ? txn.received_amount : ' - ') + '</td>' +
	                '</tr>';
				}).join('');

				$('#details-body-' + type + '-' + key).html(rows);
			}
		});
	}

	$(document).on('click', '#export_balance_sheet_workbook', function(e) {
		e.preventDefault();

		var form = $('<form>', {
			method: 'POST',
			action: admin_url + 'ngo_trust/reports/export_balance_sheet_workbook'
		});

		form.append($('<input>', {
			type: 'hidden',
			name: 'report_months',
			value: $('select[name="months-report"]').val()
		}));

		form.append($('<input>', {
			type: 'hidden',
			name: 'report_from',
			value: $('input[name="report-from"]').val()
		}));

		form.append($('<input>', {
			type: 'hidden',
			name: 'report_to',
			value: $('input[name="report-to"]').val()
		}));

		form.append($('<input>', {
			type: 'hidden',
			name: 'year_requisition',
			value: $('select[name="year_requisition"]').val()
		}));

		form.append($('<input>', {
			type: 'hidden',
			name: 'account',
			value: $('select[name="account_id"]').val()
		}));

		form.append($('<input>', {
			type: 'hidden',
			name: 'category',
			value: $('select[name="category_id"]').val()
		}));

		form.append($('<input>', {
			type: 'hidden',
			name: csrfData.token_name,
			value: csrfData.hash
		}));

		$('body').append(form);

		form.submit();
	});
</script>