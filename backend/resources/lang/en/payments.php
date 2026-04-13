<?php

return [
    'errors' => [
        'unsupported_payable' => 'Unsupported payable type.',
        'order_not_owned' => 'You cannot pay for this order.',
        'order_not_payable' => 'This order is not payable in its current state.',
        'invalid_capture_state' => 'Payment cannot be captured in this state.',
        'invalid_refund_state' => 'Payment cannot be refunded in this state.',
        'refund_exceeds_payment' => 'Refund amount exceeds the payment total.',
        'payment_not_found' => 'Payment not found for this gateway reference.',
        'invalid_webhook_status' => 'Invalid webhook status value.',
        'not_owner' => 'You are not allowed to access this payment.',
    ],
];
