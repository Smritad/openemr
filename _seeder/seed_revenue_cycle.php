<?php
/**
 * MatrixCMS — Complete Revenue Cycle Seeder
 *
 * Builds out a realistic billing dataset so every Fees menu item
 * shows real data:
 *
 *   • Cleans duplicate $0 billing rows
 *   • Marks Rohan's encounter as SUBMITTED to insurance
 *   • Marks Amit's encounter as SUBMITTED to insurance
 *   • Creates 2 insurance batch payments (ar_session + ar_activity)
 *   • Applies the insurance payments to the encounters
 *   • Leaves Priya's encounter as "Unbilled" so user can practice
 *
 * Run:  php _seeder/seed_revenue_cycle.php
 */

declare(strict_types=1);

$db = new mysqli('localhost', 'openemr', 'root', 'openemr');
if ($db->connect_errno) { fwrite(STDERR, "DB: $db->connect_error\n"); exit(1); }
$db->set_charset('utf8mb4');

function info($s) { echo "  → $s\n"; }
function section($s) { echo "\n== $s ==\n"; }
function fetchId(mysqli $db, string $sql): int {
    $row = $db->query($sql)->fetch_assoc();
    return (int)($row['id'] ?? 0);
}

// ============================================================
// Lookup our test users + patients
// ============================================================
$dr_smith   = fetchId($db, "SELECT id FROM users WHERE username='dr.smith'");
$dr_kumar   = fetchId($db, "SELECT id FROM users WHERE username='dr.kumar'");
$amit_pid   = fetchId($db, "SELECT pid AS id FROM patient_data WHERE pubpid='P1001'");
$priya_pid  = fetchId($db, "SELECT pid AS id FROM patient_data WHERE pubpid='P1002'");
$rohan_pid  = fetchId($db, "SELECT pid AS id FROM patient_data WHERE pubpid='P1003'");
$fac_andheri= fetchId($db, "SELECT id FROM facility WHERE facility_code='AND'");
$fac_bandra = fetchId($db, "SELECT id FROM facility WHERE facility_code='BAN'");

if (!$dr_smith || !$amit_pid) {
    fwrite(STDERR, "Required seed data missing. Run seed_all.php first.\n");
    exit(1);
}

// ============================================================
// PHASE 1 — Clean up duplicate $0 billing rows + previous seed
// ============================================================
section('Phase 1: Cleaning duplicate $0 billing rows + previous AR data');

$db->query("DELETE FROM billing WHERE pid = $amit_pid AND fee = 0.00 AND code_type IN ('CPT4')");
info("Removed $0 CPT placeholder rows for Amit");

// Make seeder idempotent — clear previous AR sessions/activities for our test patients
$pids = "$amit_pid,$priya_pid,$rohan_pid";
$db->query("DELETE FROM ar_activity WHERE pid IN ($pids)");
$db->query("DELETE FROM ar_session WHERE reference LIKE 'BC-2026%' OR reference LIKE 'AET-2026%'");
$db->query("DELETE FROM claims WHERE patient_id IN ($pids)");
// Remove insurance-source payments only — keep cash copays
$db->query("DELETE FROM payments WHERE pid IN ($pids) AND source IN ('Blue Cross','Aetna','Cigna')");
info("Cleared previous AR/claims/insurance-payment seed data");

// ============================================================
// PHASE 2 — Get the encounters we'll work with
// ============================================================
section('Phase 2: Identifying active encounters');

$res = $db->query("
    SELECT fe.id AS internal_id, fe.encounter, fe.pid, fe.date,
           (SELECT username FROM users WHERE id=fe.provider_id) AS provider,
           (SELECT CONCAT(fname,' ',lname) FROM patient_data WHERE pid=fe.pid) AS patient,
           SUM(b.fee) AS total_charges
    FROM form_encounter fe
    LEFT JOIN billing b ON b.encounter = fe.encounter AND b.pid = fe.pid AND b.fee > 0 AND b.activity = 1
    GROUP BY fe.id
    ORDER BY fe.id
");

$encounters = [];
while ($row = $res->fetch_assoc()) {
    $encounters[] = $row;
    info(sprintf("Encounter %d: %s with %s — total charges: $%.2f",
        $row['encounter'], $row['patient'], $row['provider'], $row['total_charges']));
}

if (count($encounters) < 2) {
    fwrite(STDERR, "Need at least 2 encounters. Run seed_all.php and complete Fee Sheet for Amit/Rohan first.\n");
    exit(1);
}

// ============================================================
// PHASE 3 — Mark claims as SUBMITTED to insurance
// ============================================================
section('Phase 3: Submitting claims to insurance (X12)');

$submitDate = date('Y-m-d H:i:s', strtotime('-3 days'));
$today = date('Y-m-d');

foreach ($encounters as $enc) {
    // Skip if patient is Priya — keep her encounter as "unbilled" for practice
    if (stripos($enc['patient'], 'Priya') !== false) {
        info("Skipping Priya — keeping unbilled for user to practice");
        continue;
    }

    $encId  = (int)$enc['encounter'];
    $pid    = (int)$enc['pid'];

    // Mark billing rows as billed
    $db->query("UPDATE billing
                SET billed = 1, bill_process = 2, bill_date = '$submitDate',
                    process_date = '$submitDate', process_file = CONCAT('X12-', $encId, '-', UNIX_TIMESTAMP())
                WHERE encounter = $encId AND pid = $pid");

    // Insert into claims table
    $db->query("DELETE FROM claims WHERE patient_id = $pid AND encounter_id = $encId");
    $db->query("INSERT INTO claims
                    (patient_id, encounter_id, version, payer_id, status, payer_type,
                     bill_process, bill_time, process_time, process_file, target,
                     x12_partner_id)
                VALUES
                    ($pid, $encId, 1, 0, 2, 1,
                     2, '$submitDate', '$submitDate',
                     CONCAT('X12-', $encId, '-batch'), 'normal',
                     0)");

    // Update encounter
    $db->query("UPDATE form_encounter SET last_level_billed = 1, last_level_closed = 4 WHERE encounter = $encId");

    info(sprintf("Submitted: encounter %d (%s) → claim sent to insurance",
        $encId, $enc['patient']));
}

// ============================================================
// PHASE 4 — Insurance check arrives (Batch Payment for Amit)
// ============================================================
section('Phase 4: Recording insurance batch payment — Amit (Blue Cross)');

// Find Amit's encounter
$amitEnc = null;
foreach ($encounters as $e) {
    if (stripos($e['patient'], 'Amit') !== false) {
        $amitEnc = $e;
        break;
    }
}

if ($amitEnc) {
    $payDate = date('Y-m-d');
    $checkNum = 'BC-2026-001';
    $totalAmit = (float)$amitEnc['total_charges'];
    // Insurance pays 80% of charges
    $insurancePay = round($totalAmit * 0.8, 2);
    $writeOff = round($totalAmit - $insurancePay - 25, 2); // patient already paid $25 copay

    // Create ar_session (batch payment record)
    $db->query(sprintf("INSERT INTO ar_session
        (payer_id, user_id, closed, reference, check_date, deposit_date, pay_total,
         created_time, modified_time, global_amount, payment_type, description,
         adjustment_code, post_to_date, patient_id, payment_method)
        VALUES
        (0, %d, 0, '%s', '%s', '%s', %.2f,
         NOW(), NOW(), 0.00, 'insurance', 'Blue Cross weekly payment',
         'insurance_adjustment', '%s', 0, 'check')",
        $dr_smith, $checkNum, $payDate, $payDate, $insurancePay, $payDate));
    $sessionId = $db->insert_id;
    info("Created batch session #$sessionId for Blue Cross check #$checkNum ($$insurancePay)");

    // Get the billing rows for Amit and create ar_activity entries
    $billingRows = $db->query("SELECT id, code_type, code, fee FROM billing
                               WHERE encounter = {$amitEnc['encounter']}
                                 AND pid = {$amitEnc['pid']}
                                 AND fee > 0 ORDER BY id");

    $seqNo = 1;
    $remaining = $insurancePay;
    while ($billRow = $billingRows->fetch_assoc()) {
        // Distribute proportionally
        $chargeFee = (float)$billRow['fee'];
        $payShare  = ($chargeFee / $totalAmit) * $insurancePay;
        $adjShare  = ($chargeFee / $totalAmit) * $writeOff;

        $db->query(sprintf("INSERT INTO ar_activity
            (pid, encounter, sequence_no, code_type, code, modifier, payer_type,
             post_time, post_user, session_id, memo, pay_amount, adj_amount,
             modified_time, follow_up, account_code, post_date)
            VALUES
            (%d, %d, %d, '%s', '%s', '', 1,
             NOW(), %d, %d, 'Insurance payment Blue Cross', %.2f, %.2f,
             NOW(), '', 'IPP', '%s')",
            $amitEnc['pid'], $amitEnc['encounter'], $seqNo++,
            $billRow['code_type'], $billRow['code'],
            $dr_smith, $sessionId, $payShare, $adjShare, $payDate));
    }

    // Record the actual payment in payments table too (for completeness)
    $db->query(sprintf("INSERT INTO payments
        (pid, dtime, encounter, user, method, source, amount1, amount2, posted1, posted2)
        VALUES
        (%d, NOW(), %d, '%s', 'check', 'Blue Cross', %.2f, 0, %.2f, 0)",
        $amitEnc['pid'], $amitEnc['encounter'], 'dr.smith', $insurancePay, $insurancePay));

    info(sprintf("Posted: insurance paid \$%.2f, wrote off \$%.2f → balance: \$%.2f",
        $insurancePay, $writeOff, $totalAmit - $insurancePay - $writeOff - 25));
}

// ============================================================
// PHASE 5 — Insurance check arrives (Batch Payment for Rohan)
// ============================================================
section('Phase 5: Recording insurance batch payment — Rohan (Aetna)');

$rohanEnc = null;
foreach ($encounters as $e) {
    if (stripos($e['patient'], 'Rohan') !== false) {
        $rohanEnc = $e;
        break;
    }
}

if ($rohanEnc) {
    $payDate = date('Y-m-d');
    $checkNum = 'AET-2026-001';
    $totalRohan = (float)$rohanEnc['total_charges'];
    $insurancePay = round($totalRohan * 0.75, 2); // 75%
    $writeOff = round($totalRohan - $insurancePay - 40, 2); // less $40 copay

    $db->query(sprintf("INSERT INTO ar_session
        (payer_id, user_id, closed, reference, check_date, deposit_date, pay_total,
         created_time, modified_time, global_amount, payment_type, description,
         adjustment_code, post_to_date, patient_id, payment_method)
        VALUES
        (0, %d, 0, '%s', '%s', '%s', %.2f,
         NOW(), NOW(), 0.00, 'insurance', 'Aetna weekly payment',
         'insurance_adjustment', '%s', 0, 'check')",
        $dr_kumar, $checkNum, $payDate, $payDate, $insurancePay, $payDate));
    $sessionId = $db->insert_id;
    info("Created batch session #$sessionId for Aetna check #$checkNum ($$insurancePay)");

    $billingRows = $db->query("SELECT id, code_type, code, fee FROM billing
                               WHERE encounter = {$rohanEnc['encounter']}
                                 AND pid = {$rohanEnc['pid']}
                                 AND fee > 0 ORDER BY id");

    $seqNo = 1;
    while ($billRow = $billingRows->fetch_assoc()) {
        $chargeFee = (float)$billRow['fee'];
        $payShare  = ($chargeFee / $totalRohan) * $insurancePay;
        $adjShare  = ($chargeFee / $totalRohan) * $writeOff;

        $db->query(sprintf("INSERT INTO ar_activity
            (pid, encounter, sequence_no, code_type, code, modifier, payer_type,
             post_time, post_user, session_id, memo, pay_amount, adj_amount,
             modified_time, follow_up, account_code, post_date)
            VALUES
            (%d, %d, %d, '%s', '%s', '', 1,
             NOW(), %d, %d, 'Insurance payment Aetna', %.2f, %.2f,
             NOW(), '', 'IPP', '%s')",
            $rohanEnc['pid'], $rohanEnc['encounter'], $seqNo++,
            $billRow['code_type'], $billRow['code'],
            $dr_kumar, $sessionId, $payShare, $adjShare, $payDate));
    }

    $db->query(sprintf("INSERT INTO payments
        (pid, dtime, encounter, user, method, source, amount1, amount2, posted1, posted2)
        VALUES
        (%d, NOW(), %d, '%s', 'check', 'Aetna', %.2f, 0, %.2f, 0)",
        $rohanEnc['pid'], $rohanEnc['encounter'], 'dr.kumar', $insurancePay, $insurancePay));

    info(sprintf("Posted: insurance paid \$%.2f, wrote off \$%.2f → balance: \$%.2f",
        $insurancePay, $writeOff, $totalRohan - $insurancePay - $writeOff - 40));
}

// ============================================================
// PHASE 6 — Final state report
// ============================================================
section('Phase 6: Final State');

$r = $db->query("
    SELECT 'Billing records' AS metric, COUNT(*) AS count FROM billing
    UNION ALL
    SELECT 'Claims submitted', COUNT(*) FROM claims WHERE status = 2
    UNION ALL
    SELECT 'AR sessions (batches)', COUNT(*) FROM ar_session
    UNION ALL
    SELECT 'AR activity (line items)', COUNT(*) FROM ar_activity
    UNION ALL
    SELECT 'Payments', COUNT(*) FROM payments
    UNION ALL
    SELECT 'Encounters', COUNT(*) FROM form_encounter
");

echo "\n+-------+ Summary +-------+\n";
while ($row = $r->fetch_assoc()) {
    printf("  %-25s : %s\n", $row['metric'], $row['count']);
}

echo "\n✅ Revenue cycle seed complete. Now you can see real data in:\n";
echo "  Fees → Billing Manager     → claims pending/submitted\n";
echo "  Fees → Posting Payments    → enter manual payments\n";
echo "  Fees → Batch Payments      → 2 batches visible\n";
echo "  Fees → EDI History         → submitted claim files\n";
echo "\n📋 Practice scenarios available:\n";
echo "  • Amit Sharma   — insurance paid (Blue Cross)\n";
echo "  • Rohan Mehta   — insurance paid (Aetna)\n";
echo "  • Priya Verma   — UNBILLED (practice submitting yourself)\n";
