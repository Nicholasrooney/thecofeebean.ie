<?php
/*
 * Click and collect checkout: an untrusted POST in, either errors or one
 * saved, emailed order out. Pricing and validation live in order.php.
 */

require_once __DIR__ . '/order.php';
require_once __DIR__ . '/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}
if (empty($_SESSION['order_token'])) {
    $_SESSION['order_token'] = bin2hex(random_bytes(16));
}

$now         = new DateTimeImmutable('now', new DateTimeZone('Europe/Dublin'));
$orderErrors = [];
$orderOld    = ['name' => '', 'phone' => '', 'email' => '', 'notes' => '', 'collect_time' => '', 'basket' => ''];
$orderDone   = $_SESSION['order_done'] ?? null;
unset($_SESSION['order_done']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($orderOld as $k => $v) {
        $orderOld[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    if (!hash_equals($_SESSION['order_token'], (string) ($_POST['token'] ?? ''))) {
        $orderErrors['form'] = 'Your session timed out. Please try again — your basket is still here.';
    } elseif (trim((string) ($_POST['website'] ?? '')) !== '') {
        // Honeypot filled in: a bot. Pretend all is well and do nothing.
        header('Location: order.php?done=1', true, 303);
        exit;
    } elseif (empty($ordering['accepting'])) {
        $orderErrors['form'] = 'We are not taking online orders right now. Please ring us instead.';
    } else {
        [$order, $orderErrors] = order_validate($_POST, $now);

        if ($order) {
            $saved = order_save_csv($order);
            if (!$saved) {
                error_log('Order ' . $order['ref'] . ' could not be written to orders.csv');
            }
            $toCafe = mail_order_alert($order);
            mail_order_confirmation($order);

            if (!$toCafe && !$saved) {
                // Neither saved nor emailed: do not tell the customer it worked.
                $orderErrors['form'] = 'Sorry, your order could not be sent. Please ring us on ' . $site['phone'] . '.';
            } else {
                $_SESSION['order_done'] = [
                    'ref'   => $order['ref'],
                    'when'  => $order['collect_label'],
                    'name'  => $order['name'],
                    'total' => order_money($order['total']),
                    'email' => $order['email'],
                ];
                $_SESSION['order_token'] = bin2hex(random_bytes(16));
                header('Location: order.php?done=1', true, 303);
                exit;
            }
        }
    }
}

function mail_order_alert(array $o): bool
{
    global $ordering, $site;
    $body = implode("\n", [
        'NEW CLICK & COLLECT ORDER  ' . $o['ref'],
        '',
        'Collect: ' . $o['collect_label'],
        'Name:    ' . $o['name'],
        'Phone:   ' . $o['phone'],
        'Email:   ' . $o['email'],
        '',
        order_lines_text($o),
        '',
        'TOTAL: ' . order_money($o['total']) . '  (to pay on collection)',
        $o['notes'] !== '' ? "\nNotes: " . $o['notes'] : '',
        $o['marketing'] ? "\nHappy to get offers by email." : '',
    ]);
    return mail_send_alert(
        'Order ' . $o['ref'] . ' · ' . $o['collect_time'] . ' · ' . $o['name'] . ' · ' . order_money($o['total']),
        $body,
        ['reply_to' => $o['email']],
        mail_notify_recipients($ordering['notify_email'] ?? null)
    );
}

function mail_order_confirmation(array $o): bool
{
    global $site;
    $body = implode("\n", [
        'Hi ' . mail_first_name($o['name']) . ',',
        '',
        'Thanks for your order. Here it is:',
        '',
        'Order ' . $o['ref'],
        'Collect from ' . $site['name'] . ', ' . $site['address'] . ' — ' . $o['collect_label'],
        '',
        order_lines_text($o),
        '',
        'Total: ' . order_money($o['total']) . ', paid when you collect (card or cash).',
        $o['notes'] !== '' ? "\nYour note: " . $o['notes'] : '',
        '',
        'Need to change something? Ring us on ' . $site['phone'] . ' and quote ' . $o['ref'] . '.',
        '',
        'See you soon,',
        $site['name'],
    ]);
    return mail_send($o['email'], 'Your order ' . $o['ref'] . ' — ' . $site['name'], $body);
}
