<?php
/**
 * MatrixCMS / OpenEMR seeder
 *
 * Run from the project root via:
 *   php _seeder/seed_all.php
 *
 * Creates a complete realistic test dataset:
 *  - 4 staff users (1 doctor, 1 cardiologist, 1 nurse, 1 receptionist)
 *  - 3 test patients
 *  - 5 appointments across the next 2 weeks
 *  - 2 internal messages
 *  - 2 dated reminders (one personal, one assigned to nurse)
 *  - 2 recalls (one due in 3 months, one due tomorrow so it shows in Recall Board)
 *
 * Idempotent: re-running deletes the seeded test rows by username/MR# and recreates them.
 */

declare(strict_types=1);

// ---- Direct MySQL connection (we're CLI, no OpenEMR globals) ----
$host = 'localhost';
$user = 'openemr';
$pass = 'root';
$db   = 'openemr';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function q(mysqli $db, string $sql, array $params = [], string $types = '') {
    $stmt = $db->prepare($sql);
    if (!$stmt) throw new RuntimeException("Prepare failed: {$db->error}\nSQL: $sql");
    if ($params) {
        if (!$types) $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $insertId = $db->insert_id;
    $stmt->close();
    return ['rows' => $rows, 'id' => $insertId];
}

function info($msg) { echo "  → $msg\n"; }
function section($msg) { echo "\n== $msg ==\n"; }

// ============================================================
// 1. CREATE 4 STAFF USERS
// ============================================================
section('Creating staff users');

$facilityCSAB   = 3;  // CSAB Clinic
$facilityMatrix = 4;  // Matrix Health Clinic

$groupPhysicians  = 13;
$groupClinicians  = 12;
$groupFrontOffice = 14;

$users = [
    [
        'username'  => 'dr.smith',
        'password'  => 'Doctor@123',
        'fname'     => 'John',
        'lname'     => 'Smith',
        'valedictory' => 'MD',
        'title'     => 'Dr.',
        'specialty' => 'Family Medicine',
        'authorized'=> 1,  // provider
        'calendar'  => 1,
        'see_auth'  => 3,
        'facility_id' => $facilityCSAB,
        'group_id'  => $groupPhysicians,
        'taxonomy'  => '207Q00000X',
        'email'     => 'john.smith@matrixbricks.test',
    ],
    [
        'username'  => 'dr.kumar',
        'password'  => 'Doctor@123',
        'fname'     => 'Anil',
        'lname'     => 'Kumar',
        'valedictory' => 'MD',
        'title'     => 'Dr.',
        'specialty' => 'Cardiology',
        'authorized'=> 1,
        'calendar'  => 1,
        'see_auth'  => 3,
        'facility_id' => $facilityMatrix,
        'group_id'  => $groupPhysicians,
        'taxonomy'  => '207RC0000X',
        'email'     => 'anil.kumar@matrixbricks.test',
    ],
    [
        'username'  => 'nurse.jane',
        'password'  => 'Nurse@123',
        'fname'     => 'Jane',
        'lname'     => 'Doe',
        'valedictory' => 'RN',
        'title'     => 'Nurse',
        'specialty' => 'General Practice',
        'authorized'=> 0,
        'calendar'  => 0,
        'see_auth'  => 1,
        'facility_id' => $facilityCSAB,
        'group_id'  => $groupClinicians,
        'taxonomy'  => '163W00000X',
        'email'     => 'jane.doe@matrixbricks.test',
    ],
    [
        'username'  => 'reception.maya',
        'password'  => 'Reception@123',
        'fname'     => 'Maya',
        'lname'     => 'Patel',
        'valedictory' => '',
        'title'     => 'Ms.',
        'specialty' => 'Front Office',
        'authorized'=> 0,
        'calendar'  => 0,
        'see_auth'  => 1,
        'facility_id' => $facilityCSAB,
        'group_id'  => $groupFrontOffice,
        'taxonomy'  => '',
        'email'     => 'maya.patel@matrixbricks.test',
    ],
];

$userIds = [];

foreach ($users as $u) {
    // idempotent: remove any existing copy of this user
    q($mysqli,
        "DELETE FROM gacl_groups_aro_map WHERE aro_id IN (SELECT id FROM gacl_aro WHERE section_value='users' AND value=?)",
        [$u['username']]);
    q($mysqli, "DELETE FROM gacl_aro WHERE section_value='users' AND value=?", [$u['username']]);
    q($mysqli, "DELETE FROM users_secure WHERE username=?", [$u['username']]);
    q($mysqli, "DELETE FROM users WHERE username=?", [$u['username']]);

    // facility name lookup
    $r = q($mysqli, "SELECT name FROM facility WHERE id=?", [$u['facility_id']], 'i');
    $facilityName = $r['rows'][0]['name'] ?? '';

    // UUID
    $uuid = random_bytes(16);
    $uuid[6] = chr((ord($uuid[6]) & 0x0f) | 0x40);  // v4
    $uuid[8] = chr((ord($uuid[8]) & 0x3f) | 0x80);

    // Insert into users — use direct escaped SQL (simpler than prepared statements
    // for the wide set of columns with NOT NULL defaults)
    $sql = sprintf(
        "INSERT INTO users (uuid, username, authorized, fname, lname, valedictory, title, specialty, facility, facility_id, see_auth, active, calendar, taxonomy, email, main_menu_role, patient_menu_role, abook_type) VALUES (UNHEX('%s'), '%s', %d, '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, 1, %d, '%s', '%s', 'standard', 'standard', '')",
        bin2hex($uuid),
        $mysqli->real_escape_string($u['username']),
        (int)$u['authorized'],
        $mysqli->real_escape_string($u['fname']),
        $mysqli->real_escape_string($u['lname']),
        $mysqli->real_escape_string($u['valedictory']),
        $mysqli->real_escape_string($u['title']),
        $mysqli->real_escape_string($u['specialty']),
        $mysqli->real_escape_string($facilityName),
        (int)$u['facility_id'],
        (int)$u['see_auth'],
        (int)$u['calendar'],
        $mysqli->real_escape_string($u['taxonomy']),
        $mysqli->real_escape_string($u['email'])
    );
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "User insert failed: {$mysqli->error}\nSQL: $sql\n");
        exit(1);
    }
    $userId = $mysqli->insert_id;
    $userIds[$u['username']] = $userId;

    // password into users_secure (bcrypt)
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    $sql = sprintf(
        "INSERT INTO users_secure (id, username, password, last_update_password) VALUES (%d, '%s', '%s', NOW())",
        $userId,
        $mysqli->real_escape_string($u['username']),
        $mysqli->real_escape_string($hash)
    );
    $mysqli->query($sql);

    // ACL entry
    $aroName = trim($u['title'] . ' ' . $u['fname'] . ' ' . $u['lname']);
    $sql = sprintf(
        "INSERT INTO gacl_aro (id, section_value, value, order_value, name, hidden) VALUES ((SELECT IFNULL(MAX(id),0)+1 FROM (SELECT id FROM gacl_aro) AS x), 'users', '%s', 10, '%s', 0)",
        $mysqli->real_escape_string($u['username']),
        $mysqli->real_escape_string($aroName)
    );
    $mysqli->query($sql);
    $aroId = $mysqli->insert_id ?: (int)$mysqli->query("SELECT id FROM gacl_aro WHERE section_value='users' AND value='" . $mysqli->real_escape_string($u['username']) . "'")->fetch_assoc()['id'];

    // group mapping
    $sql = sprintf(
        "INSERT INTO gacl_groups_aro_map (group_id, aro_id) VALUES (%d, %d)",
        $u['group_id'],
        $aroId
    );
    $mysqli->query($sql);

    // CRITICAL: OpenEMR login also requires a row in the `groups` table
    // (not gacl_groups). Without this, login fails with "Invalid username
    // or password" because confirmUserPassword() bails on getAuthGroupForUser().
    $mysqli->query("DELETE FROM groups WHERE user='" . $mysqli->real_escape_string($u['username']) . "'");
    $mysqli->query("INSERT INTO groups (name, user) VALUES ('Default', '" . $mysqli->real_escape_string($u['username']) . "')");

    info("Created user: {$u['username']} (id={$userId})  → group={$u['group_id']}, password={$u['password']}");
}

// ============================================================
// 2. CREATE 3 TEST PATIENTS
// ============================================================
section('Creating test patients');

$patients = [
    [
        'pubpid' => 'P1001',
        'fname'  => 'Amit',
        'lname'  => 'Sharma',
        'mname'  => '',
        'DOB'    => '1985-03-15',
        'sex'    => 'Male',
        'phone'  => '9876543210',
        'email'  => 'amit.sharma@test.com',
        'street' => '12 Park Street',
        'city'   => 'Mumbai',
        'state'  => 'Maharashtra',
        'zip'    => '400001',
        'providerID' => $userIds['dr.smith'],
    ],
    [
        'pubpid' => 'P1002',
        'fname'  => 'Priya',
        'lname'  => 'Verma',
        'mname'  => '',
        'DOB'    => '1990-07-22',
        'sex'    => 'Female',
        'phone'  => '9123456789',
        'email'  => 'priya.verma@test.com',
        'street' => '45 MG Road',
        'city'   => 'Pune',
        'state'  => 'Maharashtra',
        'zip'    => '411001',
        'providerID' => $userIds['dr.smith'],
    ],
    [
        'pubpid' => 'P1003',
        'fname'  => 'Rohan',
        'lname'  => 'Mehta',
        'mname'  => '',
        'DOB'    => '1975-11-08',
        'sex'    => 'Male',
        'phone'  => '9988776655',
        'email'  => 'rohan.mehta@test.com',
        'street' => '78 Lake View',
        'city'   => 'Bengaluru',
        'state'  => 'Karnataka',
        'zip'    => '560001',
        'providerID' => $userIds['dr.kumar'],
    ],
];

$patientIds = [];
foreach ($patients as $p) {
    // idempotent delete (also kill any half-baked rows with pid=0 from earlier failures)
    $mysqli->query("DELETE FROM patient_data WHERE pubpid = '" . $mysqli->real_escape_string($p['pubpid']) . "' OR pid = 0");

    // Generate next pid via OpenEMR's sequences table (thread-safe counter)
    $mysqli->query("UPDATE sequences SET id = LAST_INSERT_ID(id + 1)");
    $row = $mysqli->query("SELECT LAST_INSERT_ID() AS next_pid")->fetch_assoc();
    $nextPid = (int)$row['next_pid'];

    $uuid = random_bytes(16);
    $uuid[6] = chr((ord($uuid[6]) & 0x0f) | 0x40);
    $uuid[8] = chr((ord($uuid[8]) & 0x3f) | 0x80);

    $sql = sprintf(
        "INSERT INTO patient_data
            (uuid, pid, pubpid, fname, lname, mname, DOB, sex, phone_home, phone_cell, email,
             street, city, state, postal_code, date, providerID, language, financial,
             country_code, status)
         VALUES
            (UNHEX('%s'), %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
             '%s', '%s', '%s', '%s', NOW(), %d, 'English', 'self',
             'US', 'married')",
        bin2hex($uuid),
        $nextPid,
        $mysqli->real_escape_string($p['pubpid']),
        $mysqli->real_escape_string($p['fname']),
        $mysqli->real_escape_string($p['lname']),
        $mysqli->real_escape_string($p['mname']),
        $p['DOB'],
        $mysqli->real_escape_string($p['sex']),
        $mysqli->real_escape_string($p['phone']),
        $mysqli->real_escape_string($p['phone']),
        $mysqli->real_escape_string($p['email']),
        $mysqli->real_escape_string($p['street']),
        $mysqli->real_escape_string($p['city']),
        $mysqli->real_escape_string($p['state']),
        $mysqli->real_escape_string($p['zip']),
        (int)$p['providerID']
    );
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "Patient insert failed: {$mysqli->error}\nSQL: $sql\n");
        exit(1);
    }
    $patientIds[$p['pubpid']] = $nextPid;
    info("Created patient: {$p['fname']} {$p['lname']} (PID={$nextPid}, MRN={$p['pubpid']})");
}

// ============================================================
// 3. CREATE APPOINTMENTS (next 2 weeks)
// ============================================================
section('Creating appointments');

$today = new DateTime();
$tomorrow = (clone $today)->modify('+1 day')->format('Y-m-d');
$dayAfter = (clone $today)->modify('+2 days')->format('Y-m-d');
$threeDays = (clone $today)->modify('+3 days')->format('Y-m-d');
$nextWeek = (clone $today)->modify('+7 days')->format('Y-m-d');

$appts = [
    ['date' => $tomorrow,   'time' => '09:00:00', 'duration' => 1800,  'patient' => 'P1001', 'provider' => 'dr.smith', 'title' => 'Office Visit', 'catid' => 5, 'facility' => $facilityCSAB],
    ['date' => $tomorrow,   'time' => '10:00:00', 'duration' => 1800,  'patient' => 'P1002', 'provider' => 'dr.smith', 'title' => 'Office Visit', 'catid' => 5, 'facility' => $facilityCSAB],
    ['date' => $dayAfter,   'time' => '14:00:00', 'duration' => 2700,  'patient' => 'P1003', 'provider' => 'dr.kumar', 'title' => 'Cardiac Follow-Up', 'catid' => 5, 'facility' => $facilityMatrix],
    ['date' => $threeDays,  'time' => '11:00:00', 'duration' => 1800,  'patient' => 'P1001', 'provider' => 'dr.smith', 'title' => 'Lab Review',  'catid' => 5, 'facility' => $facilityCSAB],
    ['date' => $nextWeek,   'time' => '15:30:00', 'duration' => 3600,  'patient' => 'P1002', 'provider' => 'dr.smith', 'title' => 'Annual Physical', 'catid' => 9, 'facility' => $facilityCSAB],
];

foreach ($appts as $a) {
    $pid = $patientIds[$a['patient']];
    $aid = $userIds[$a['provider']];
    $endTime = date('H:i:s', strtotime($a['time']) + $a['duration']);
    $sql = sprintf(
        "INSERT INTO openemr_postcalendar_events
            (pc_catid, pc_aid, pc_pid, pc_title, pc_eventDate, pc_endDate, pc_duration,
             pc_startTime, pc_endTime, pc_alldayevent, pc_facility, pc_billing_location,
             pc_apptstatus, pc_eventstatus, pc_sharing, pc_time, pc_topic,
             pc_recurrtype, pc_recurrfreq, pc_recurrspec, pc_location, pc_conttel,
             pc_contname, pc_contemail, pc_website, pc_fee, pc_informant,
             pc_hometext)
         VALUES
            (%d, '%d', '%d', '%s', '%s', '%s', %d, '%s', '%s', 0, %d, %d,
             '-', 1, 1, NOW(), 1, 0, 0, '', '', '', '', '', '', '', '%d', '')",
        (int)$a['catid'],
        (int)$aid,
        (int)$pid,
        $mysqli->real_escape_string($a['title']),
        $a['date'],
        $a['date'],
        (int)$a['duration'],
        $a['time'],
        $endTime,
        (int)$a['facility'],
        (int)$a['facility'],
        (int)$aid
    );
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "Appt insert failed: {$mysqli->error}\nSQL: $sql\n");
        continue;
    }
    info("  📅 {$a['date']} {$a['time']} — patient={$a['patient']} provider={$a['provider']} title={$a['title']}");
}

// ============================================================
// 4. CREATE INTERNAL MESSAGES (pnotes)
// ============================================================
section('Creating internal messages');

$msgs = [
    [
        'from'   => 'dr.smith',
        'to'     => 'nurse.jane',
        'patient'=> 'P1001',
        'title'  => 'Chart Note',
        'body'   => 'Please call Amit Sharma to confirm fasting before tomorrow\'s lab work. He had questions about his diet restrictions.',
    ],
    [
        'from'   => 'reception.maya',
        'to'     => 'dr.smith',
        'patient'=> 'P1002',
        'title'  => 'Phone',
        'body'   => 'Priya Verma called — needs to reschedule her appointment. Please review when you have time and let me know preferred slot.',
    ],
];

foreach ($msgs as $m) {
    $pid = $patientIds[$m['patient']];
    $fromUserId = $userIds[$m['from']];
    $sql = sprintf(
        "INSERT INTO pnotes (date, body, pid, user, groupname, activity, authorized, title, assigned_to, message_status, update_by, update_date)
         VALUES (NOW(), '%s', %d, '%s', 'Default', 1, 1, '%s', '%s', 'New', %d, NOW())",
        $mysqli->real_escape_string($m['body']),
        (int)$pid,
        $mysqli->real_escape_string($m['from']),
        $mysqli->real_escape_string($m['title']),
        $mysqli->real_escape_string($m['to']),
        (int)$fromUserId
    );
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "Pnote insert failed: {$mysqli->error}\n");
        continue;
    }
    info("  ✉  from={$m['from']} → to={$m['to']} about={$m['patient']}: {$m['title']}");
}

// ============================================================
// 5. CREATE DATED REMINDERS
// ============================================================
section('Creating dated reminders');

$reminders = [
    [
        'from' => 'dr.smith',
        'to'   => 'dr.smith',     // reminder for self
        'due'  => $threeDays,
        'priority' => 2,           // medium
        'message'  => 'Review Amit Sharma\'s lab results before Friday\'s appointment.',
    ],
    [
        'from' => 'dr.smith',
        'to'   => 'nurse.jane',
        'due'  => $tomorrow,
        'priority' => 1,           // high
        'message'  => 'Call Priya Verma in the morning to confirm consent form is signed.',
    ],
];

foreach ($reminders as $r) {
    $fromId = $userIds[$r['from']];
    $toId   = $userIds[$r['to']];
    $sql = sprintf(
        "INSERT INTO dated_reminders
            (dr_from_ID, dr_message_text, dr_message_sent_date, dr_message_due_date, pid,
             message_priority, message_processed, dr_processed_by)
         VALUES (%d, '%s', NOW(), '%s', 0, %d, 0, 0)",
        (int)$fromId,
        $mysqli->real_escape_string($r['message']),
        $r['due'],
        (int)$r['priority']
    );
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "Reminder insert failed: {$mysqli->error}\n");
        continue;
    }
    $drId = $mysqli->insert_id;
    $mysqli->query(sprintf("INSERT INTO dated_reminders_link (dr_id, to_id) VALUES (%d, %d)", $drId, $toId));
    info("  ⏰ from={$r['from']} → to={$r['to']} due={$r['due']} priority={$r['priority']}");
}

// ============================================================
// 6. CREATE RECALLS  (medex_recalls)
// ============================================================
section('Creating recalls');

$recallTomorrow = $tomorrow;     // shows on Recall Board immediately
$recall3Months = (clone $today)->modify('+3 months')->format('Y-m-d');

$recalls = [
    [
        'pid'      => 'P1001',
        'provider' => 'dr.smith',
        'facility' => $facilityCSAB,
        'date'     => $recallTomorrow,
        'reason'   => 'Annual physical follow-up — call to schedule',
    ],
    [
        'pid'      => 'P1003',
        'provider' => 'dr.kumar',
        'facility' => $facilityMatrix,
        'date'     => $recall3Months,
        'reason'   => 'Cardiac re-check in 3 months',
    ],
];

foreach ($recalls as $r) {
    $pid = $patientIds[$r['pid']];
    $providerId = $userIds[$r['provider']];
    $sql = sprintf(
        "INSERT INTO medex_recalls
            (r_PRACTID, r_pid, r_eventDate, r_facility, r_provider, r_reason)
         VALUES (1, %d, '%s', %d, %d, '%s')",
        (int)$pid,
        $r['date'],
        (int)$r['facility'],
        (int)$providerId,
        $mysqli->real_escape_string($r['reason'])
    );
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "Recall insert failed: {$mysqli->error}\n");
        continue;
    }
    info("  📞 patient={$r['pid']} due={$r['date']} reason={$r['reason']}");
}

// ============================================================
// 7. FINAL REPORT
// ============================================================
section('Seed complete');

$counts = q($mysqli, "
    SELECT
        (SELECT COUNT(*) FROM users WHERE active=1) AS users,
        (SELECT COUNT(*) FROM patient_data) AS patients,
        (SELECT COUNT(*) FROM openemr_postcalendar_events) AS appointments,
        (SELECT COUNT(*) FROM pnotes) AS messages,
        (SELECT COUNT(*) FROM dated_reminders) AS reminders,
        (SELECT COUNT(*) FROM medex_recalls) AS recalls
")['rows'][0];

echo "\n";
echo "+------+ FINAL COUNTS +------+\n";
foreach ($counts as $k => $v) {
    printf("  %-14s : %d\n", $k, (int)$v);
}

echo "\n+------+ LOGIN CREDENTIALS +------+\n";
echo "  Admin       : FTS-admin-63    (your existing password)\n";
echo "  Doctor #1   : dr.smith        / Doctor@123\n";
echo "  Doctor #2   : dr.kumar        / Doctor@123\n";
echo "  Nurse       : nurse.jane      / Nurse@123\n";
echo "  Receptionist: reception.maya  / Reception@123\n";
echo "\nDone. Open http://localhost/open_cms/ and log in to test.\n";
