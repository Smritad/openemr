<?php

/**
 * One-shot SMTP configuration script for OpenEMR.
 *
 * Run ONCE in browser: http://localhost/open_cms/configure_smtp.php
 * Then DELETE THIS FILE — it contains a plaintext SMTP password.
 */

$ignoreAuth = true;
require_once(__DIR__ . "/interface/globals.php");

use OpenEMR\Common\Crypto\CryptoGen;

header('Content-Type: text/plain; charset=utf-8');

$settings = [
    'EMAIL_METHOD'                  => 'SMTP',
    'SMTP_HOST'                     => 'smtp.gmail.com',
    'SMTP_PORT'                     => '587',
    'SMTP_USER'                     => 'mbitestmail2@gmail.com',
    'SMTP_SECURE'                   => 'tls',
    'patient_reminder_sender_email' => 'mbitestmail2@gmail.com',
    'patient_reminder_sender_name'  => 'OpenEMR Clinic',
];

$smtpPasswordPlain = 'ihpowmzjcinegjdu';

$crypto = new CryptoGen();
$settings['SMTP_PASS'] = $crypto->encryptStandard($smtpPasswordPlain);

foreach ($settings as $name => $value) {
    sqlStatement(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)",
        [$name, $value]
    );
    echo "OK  $name\n";
}

echo "\nAll SMTP settings written to `globals` table.\n";
echo "Now DELETE this file (configure_smtp.php) before doing anything else.\n";
echo "\nNext step: Administration -> Background Services -> ensure EmailService is Active.\n";
