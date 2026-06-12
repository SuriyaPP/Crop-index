<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .receipt-view p {
        margin-bottom: 6px;
    }

    .receipt-view table th {
        font-weight: 600;
    }

    .receipt-divider {
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

<div class="receipt-view">

    <!-- Header -->
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
        <a href="<?php echo admin_url('ngo_trust/receipt/download/'.$receipt->id); ?>" title="Download Receipt"
           class="btn btn-success btn-icon">
            <i class="fa fa-download"></i>
        </a>
    </div>

    <hr class="receipt-divider">

    <!-- Receipt Info -->
    <div class="row tw-text-sm">
        <div class="col-md-6">
            <p><strong>Total Amount :</strong> <?php echo app_format_money($receipt->amount, $base_currency->name); ?></p>
            <p><strong>Total Amount Received :</strong> <?php echo app_format_money($receipt->received_amount, $base_currency->name); ?></p>
            <p><strong>Amount to be Receive :</strong> <?php echo app_format_money($receipt->balance, $base_currency->name); ?></p>
            <!-- <p><strong>Status :</strong> <?php echo ($receipt->is_fully_received == 1) ? 'Received' : 'Partial'; ?></p> -->
            <p>
                <strong>Status :</strong>
                <?php
                    if($receipt->is_fully_received == 1) {
                        echo 'Fully Received';
                    } else if(($receipt->received_amount != 0) && ($receipt->received_amount < $receipt->amount)) {
                        echo 'Partially Received';
                    } else if($receipt->is_fully_received == 0) {
                        echo 'Pending';
                    } else {
                        echo 'Partially Received';
                    }
                ?>
            </p>
        </div>
        <div class="col-md-6">
            <p><strong>Receipt Date :</strong> <?php echo _d($receipt->date); ?></p>
            <p><strong>Donor :</strong> <?php echo $receipt->client ?? '-'; ?></p>
            <p><strong>Reference :</strong> <?php echo $receipt->reference ?: '-'; ?></p>
            <p><strong>Description :</strong> <?php echo $receipt->description ?: '-'; ?></p>
        </div>

        <div class="col-md-12">
            <p>
                <strong>Category :</strong>
                <?php
                    echo $receipt->category_name;

                    if(!empty($receipt->sub_category_name)) {
                        echo ' → ' . $receipt->sub_category_name;
                    }

                    if(!empty($receipt->sub_sub_category_name)) {
                        echo ' → ' . $receipt->sub_sub_category_name;
                    }

                    if(!empty($receipt->sub_sub_sub_category_name)) {
                        echo ' → ' . $receipt->sub_sub_sub_category_name;
                    }
                ?>
            </p>
        </div>
    </div>

    <hr class="receipt-divider">

    <!-- Transactions -->
    <h4 class="transaction-title"><?php echo _l('transaction_details'); ?></h4>

    <table class="table table-bordered">
        <thead style="background:#16a34a;color:#fff;">
            <tr>
                <th><?php echo _l('receipt_date'); ?></th>
                <th><?php echo _l('account'); ?></th>
                <th class="text-right"><?php echo _l('amount'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $row) { ?>
                <tr>
                    <td><?php echo _d($row['date']); ?></td>
                    <td><?php echo $row['account_name']; ?></td>
                    <td class="text-right"><?php echo app_format_money($row['received_amount'], $base_currency->name); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>