<?php
// Updates stock quantities for one item. Requires a signed-in user (header.php) and a valid CSRF token.
include __DIR__ . '/../header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    \Portal\Guard::postWithCsrf('/dashboard/fs/fs_stock_form.php');

    $itemId   = \Portal\Guard::id($_POST['item_id'] ?? null);
    $onHand   = filter_var($_POST['qty_on_hand'] ?? null, FILTER_VALIDATE_FLOAT);
    $orderQty = filter_var($_POST['order_qty'] ?? null, FILTER_VALIDATE_FLOAT);
    $received = filter_var($_POST['received_qty'] ?? null, FILTER_VALIDATE_FLOAT);

    if ($itemId === 0 || $onHand === false || $orderQty === false || $received === false
        || $onHand < 0 || $orderQty < 0 || $received < 0) {
        \Portal\Flash::set('Enter a valid item and non-negative quantities.', 'error');
        \Portal\Http\Response::redirect('/dashboard/fs/fs_stock_form.php');
    }

    try {
        $stmt = $con->prepare('UPDATE fs_stock_items SET qty_on_hand = ?, order_qty = ?, received_qty = ? WHERE item_id = ?');
        $stmt->bind_param('dddi', $onHand, $orderQty, $received, $itemId);
        $stmt->execute();
        $stmt->close();
        \Portal\Flash::set('Stock updated successfully.');
    } catch (\Throwable $e) {
        error_log('fs/fs_process_stock_report.php: ' . $e->getMessage());
        \Portal\Flash::set('The stock could not be updated. Please try again.', 'error');
    }
    \Portal\Http\Response::redirect('/dashboard/fs/fs_stock_form.php');
}

\Portal\Http\Response::redirect('/dashboard/fs/fs_stock_form.php');
