<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .payment-view p {
        margin-bottom: 6px;
    }

    .payment-view table th {
        font-weight: 600;
    }

    .payment-divider {
        border: 0;
        border-top: 2px solid #1f2937;
        margin: 16px 0;
    }

    .transaction-title {
        font-weight: 700;
        color: #111827;
        letter-spacing: 0.3px;
    }
</style>

<div class="payment-view">

    <!-- Header -->
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
        <a href="<?php echo admin_url('ngo_trust/payment/download/'.$payment->id); ?>" title="Download payment"
           class="btn btn-success btn-icon">
            <i class="fa fa-download"></i>
        </a>
    </div>

    <hr class="payment-divider">

    <!-- payment Info -->
    <div class="row tw-text-sm">
        <div class="col-md-6">
            <p><strong>Total Amount :</strong> <?php echo app_format_money($payment->amount, $base_currency->name); ?></p>
            <p><strong>Total Amount Paid :</strong> <?php echo app_format_money($payment->received_amount, $base_currency->name); ?></p>
            <p><strong>Amount to be Pay :</strong> <?php echo app_format_money($payment->balance, $base_currency->name); ?></p>
            <p><strong>Payment Date :</strong> <?php echo _d($payment->date); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Vendor :</strong> <?php echo $payment->vendor ?? '-'; ?></p>
            <p><strong>Category :</strong> <?php echo $payment->category_name; ?></p>
            <?php if(isset($payment->asset_category_name)) { ?>
                <p><strong>Asset Category :</strong> <?php echo $payment->asset_category_name; ?></p>
            <?php } ?>
            <p><strong>Reference :</strong> <?php echo $payment->reference ?: '-'; ?></p>
            <p><strong>Description :</strong> <?php echo $payment->description ?: '-'; ?></p>
        </div>
    </div>

    <hr class="payment-divider">

    <!-- Transactions -->
    <h4 class="transaction-title"><?php echo _l('transaction_details'); ?></h4>

    <table class="table table-bordered">
        <thead style="background:#16a34a;color:#fff;">
            <tr>
                <th class="text-center"><?php echo _l('payment_date'); ?></th>
                <th class="text-center"><?php echo _l('account'); ?></th>
                <th class="text-right"><?php echo _l('amount'); ?></th>
                <th class="text-center"><?php echo _l('paid_out_of'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $row) { ?>
                <tr>
                    <td class="text-center"><?php echo _d($row['date']); ?></td>
                    <td class="text-center"><?php echo $row['account_name']; ?></td>
                    <td class="text-right"><?php echo app_format_money($row['received_amount'], $base_currency->name); ?></td>
                    <td class="text-center"><?php echo $row['receipt_category']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>