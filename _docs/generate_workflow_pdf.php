<?php

/**
 * Generates a presentation-ready workflow diagram PDF for MatrixCMS (OpenEMR).
 * Standalone: uses the bundled FPDF, no database/globals required.
 *
 * Run:  php _docs/generate_workflow_pdf.php
 * Out:  _docs/MatrixCMS_Workflow.pdf
 */

require_once __DIR__ . '/../library/classes/fpdf/fpdf.php';

// ---- Brand palette -------------------------------------------------------
$RED    = [204, 0, 0];
$DARK   = [26, 26, 26];
$GREY   = [240, 240, 240];
$BORDER = [180, 180, 180];
$WHITE  = [255, 255, 255];
$BLUE   = [33, 102, 172];
$GREEN  = [33, 138, 74];
$AMBER  = [200, 130, 0];
$PURPLE = [120, 60, 150];

class Flow extends FPDF
{
    function box($x, $y, $w, $h, $text, $fill, $textColor = [255, 255, 255], $size = 9, $style = 'B')
    {
        $this->SetFillColor($fill[0], $fill[1], $fill[2]);
        $this->SetDrawColor(120, 120, 120);
        $this->SetLineWidth(0.3);
        $this->Rect($x, $y, $w, $h, 'DF');
        $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $this->SetFont('Arial', $style, $size);
        $lines = explode("\n", $text);
        $lh = $size * 0.46;
        $startY = $y + ($h - count($lines) * $lh) / 2;
        foreach ($lines as $i => $line) {
            $this->SetXY($x, $startY + $i * $lh);
            $this->Cell($w, $lh, $line, 0, 0, 'C');
        }
    }

    function arrowDown($x, $y1, $y2, $color = [120, 120, 120])
    {
        $this->SetDrawColor($color[0], $color[1], $color[2]);
        $this->SetLineWidth(0.5);
        $this->Line($x, $y1, $x, $y2);
        $this->Line($x - 1.6, $y2 - 2.4, $x, $y2);
        $this->Line($x + 1.6, $y2 - 2.4, $x, $y2);
    }

    function arrowRight($x1, $x2, $y, $color = [120, 120, 120])
    {
        $this->SetDrawColor($color[0], $color[1], $color[2]);
        $this->SetLineWidth(0.5);
        $this->Line($x1, $y, $x2, $y);
        $this->Line($x2 - 2.4, $y - 1.6, $x2, $y);
        $this->Line($x2 - 2.4, $y + 1.6, $x2, $y);
    }

    function heading($text, $sub = '')
    {
        $this->SetFillColor(204, 0, 0);
        $this->Rect(0, 0, 210, 22, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 15);
        $this->SetXY(15, 5);
        $this->Cell(180, 7, $text, 0, 2, 'L');
        if ($sub) {
            $this->SetFont('Arial', '', 9);
            $this->Cell(180, 5, $sub, 0, 0, 'L');
        }
        $this->SetTextColor(0, 0, 0);
        $this->SetY(30);
    }

    function footer_note($pageLabel)
    {
        $this->SetY(-14);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(140, 140, 140);
        $this->Cell(0, 5, 'MatrixCMS (OpenEMR) Workflow   |   ' . $pageLabel, 0, 0, 'C');
    }
}

$pdf = new Flow('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);

// =========================================================================
// PAGE 1 — TITLE + ROLES
// =========================================================================
$pdf->AddPage();
$pdf->SetFillColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->Rect(0, 0, 210, 297, 'F');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 26);
$pdf->SetXY(15, 70);
$pdf->Cell(180, 14, 'MatrixCMS', 0, 2, 'L');
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(180, 10, 'User & Patient Workflow', 0, 2, 'L');

$pdf->SetTextColor(200, 200, 200);
$pdf->SetFont('Arial', '', 11);
$pdf->SetXY(15, 105);
$pdf->Cell(180, 7, 'How each role logs in and what they do - end to end', 0, 2, 'L');

// Roles table
$pdf->SetXY(15, 135);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(180, 8, 'Roles in this system', 0, 2, 'L');

$rows = [
    ['Administrator', 'FTS-admin-63', 'Andheri', $RED],
    ['Doctor - Family Medicine', 'dr.smith', 'Andheri', $BLUE],
    ['Doctor - Cardiology', 'dr.kumar', 'Bandra', $BLUE],
    ['Nurse (Clinician)', 'nurse.jane', 'Andheri', $GREEN],
    ['Receptionist (Front Office)', 'reception.maya', 'Andheri', $AMBER],
];
$y = $pdf->GetY() + 2;
foreach ($rows as $r) {
    $pdf->box(15, $y, 6, 8, '', $r[3]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9.5);
    $pdf->SetXY(24, $y);
    $pdf->Cell(78, 8, $r[0], 0, 0, 'L');
    $pdf->SetFont('Courier', '', 9.5);
    $pdf->SetTextColor(255, 210, 90);
    $pdf->Cell(55, 8, $r[1], 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(200, 200, 200);
    $pdf->Cell(40, 8, $r[2], 0, 0, 'L');
    $y += 10;
}

$pdf->SetXY(15, $y + 6);
$pdf->SetFont('Arial', 'I', 8.5);
$pdf->SetTextColor(170, 170, 170);
$pdf->MultiCell(180, 5, "Login URL:  http://localhost/open_cms/interface/login/login.php\n\nThe role (set by the Administrator) decides which menu tabs and buttons each user sees after login - the same login page gives every role a different screen.", 0, 'L');

// =========================================================================
// PAGE 2 — END TO END FLOW (the big picture)
// =========================================================================
$pdf->AddPage();
$pdf->heading('1. The Big Picture - End to End', 'One patient visit, from registration to payment');

$cx = 105;            // center column
$w = 150;
$x = $cx - $w / 2;
$h = 16;
$gap = 9;
$y = 36;

$steps = [
    ["ADMIN", "Creates users & assigns role + facility", $RED],
    ["RECEPTIONIST", "Registers patient  +  Schedules appointment (Calendar)", $AMBER],
    ["NURSE", "Check-in 'arrived'  +  Records Vitals / allergies", $GREEN],
    ["DOCTOR", "Opens Encounter > SOAP notes > Prescriptions > Orders", $BLUE],
    ["DOCTOR", "Fee Sheet (CPT/ICD codes)  >  Sign-off encounter", $BLUE],
    ["RECEPTIONIST", "Checkout  +  Collect co-pay / payment", $AMBER],
];
foreach ($steps as $i => $s) {
    $label = $s[0] . "\n" . $s[1];
    $pdf->box($x, $y, $w, $h, $label, $s[2], [255, 255, 255], 9);
    if ($i < count($steps) - 1) {
        $pdf->arrowDown($cx, $y + $h, $y + $h + $gap);
    }
    $y += $h + $gap;
}

$pdf->SetXY(15, $y + 4);
$pdf->SetFont('Arial', 'B', 9.5);
$pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->MultiCell(180, 5.5, "In one line:  Receptionist registers & books  >  Nurse checks in & takes vitals  >  Doctor documents, prescribes & codes  >  Receptionist takes payment.  The Admin sits above all of this, creating the logins.", 0, 'L');
$pdf->footer_note('Page 2 - Big Picture');

// =========================================================================
// PAGE 3 — USER CREATION + LOGIN PROCESS
// =========================================================================
$pdf->AddPage();
$pdf->heading('2. User Creation & Login Process', 'How the Admin sets up an account, and what login does');

// Left column: user creation
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetXY(15, 30);
$pdf->Cell(90, 6, 'A) Admin creates a user', 0, 0, 'L');

$lx = 18; $lw = 76; $ly = 40; $lh = 14; $lgap = 7;
$create = [
    ["Administration > Users", "Click 'Add User'", $RED],
    ["Username + Password", "e.g. dr.smith / Doctor@123", $DARK],
    ["'Authorized' checkbox", "TICK = doctor/provider\n(untick for nurse & reception)", $DARK],
    ["Default Facility", "Andheri or Bandra", $DARK],
    ["Access Control (ACL role)", "Physicians / Clinicians /\nFront Office / Administrators", $GREEN],
    ["Save", "Account + role stored", $GREEN],
];
foreach ($create as $i => $s) {
    $pdf->box($lx, $ly, $lw, $lh, $s[0] . "\n" . $s[1], $s[2], [255, 255, 255], 8);
    if ($i < count($create) - 1) {
        $pdf->arrowDown($lx + $lw / 2, $ly + $lh, $ly + $lh + $lgap);
    }
    $ly += $lh + $lgap;
}

// Right column: login process
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetXY(115, 30);
$pdf->Cell(90, 6, 'B) Any user logs in', 0, 0, 'L');

$rx = 118; $rw = 76; $ry = 40; $rh = 14; $rgap = 7;
$login = [
    ["Open Login page", "Enter username, password,\nlanguage", $AMBER],
    ["System authenticates", "Checks password", $DARK],
    ["Reads ACL role + facility", "Decides what is allowed", $BLUE],
    ["Builds the main screen", "Left menu filtered to the\nrole's allowed tabs", $BLUE],
    ["Lands on Calendar /\nDashboard", "Ready to work", $GREEN],
];
foreach ($login as $i => $s) {
    $pdf->box($rx, $ry, $rw, $rh, $s[0] . "\n" . $s[1], $s[2], [255, 255, 255], 8);
    if ($i < count($login) - 1) {
        $pdf->arrowDown($rx + $rw / 2, $ry + $rh, $ry + $rh + $rgap);
    }
    $ry += $rh + $rgap;
}

$pdf->SetXY(15, 192);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->MultiCell(180, 5.5, "Key point for your boss:  every role uses the SAME login page, but the ACL role chosen at user-creation time controls which tabs appear. A Front-Office user never sees clinical notes; a Bandra doctor only sees Bandra patients.", 0, 'L');
$pdf->footer_note('Page 3 - Setup & Login');

// =========================================================================
// PAGE 4 — DOCTOR DETAILED FLOW
// =========================================================================
$pdf->AddPage();
$pdf->heading('3. Doctor Flow in Detail', 'Login as dr.smith (Doctor@123) - the main demo');

$cx = 70; $w = 110; $x = $cx - $w / 2; $h = 14; $gap = 7; $y = 34;
$doc = [
    ["1. Login", "Lands on main screen (Andheri)", $BLUE],
    ["2. Calendar tab", "See today's appointments", $BLUE],
    ["3. Click appointment", "Opens the patient", $BLUE],
    ["4. Patient Summary", "Problems, allergies, meds, insurance", $BLUE],
    ["5. New / Open Encounter", "One visit on one date", $RED],
    ["6. Vitals", "BP, pulse, temp (often nurse already did)", $GREEN],
    ["7. SOAP / Clinical Notes", "Subjective, Objective, Assessment, Plan", $GREEN],
    ["8. Issues / Problem List", "Diagnoses, allergies", $GREEN],
    ["9. Prescriptions", "Write / refill medication", $GREEN],
    ["10. Orders / Procedures", "Labs, imaging", $GREEN],
    ["11. Fee Sheet", "CPT / ICD codes for billing", $AMBER],
    ["12. Sign / Close encounter", "Visit saved to history", $RED],
];
foreach ($doc as $i => $s) {
    $pdf->box($x, $y, $w, $h, $s[0] . "  -  " . $s[1], $s[2], [255, 255, 255], 8, 'B');
    if ($i < count($doc) - 1) {
        $pdf->arrowDown($cx, $y + $h, $y + $h + $gap);
    }
    $y += $h + $gap;
}

// Side panel: other roles
$px = 135; $pw = 60;
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetXY($px, 34);
$pdf->Cell($pw, 6, 'Same screen, other roles', 0, 2, 'L');

$side = [
    ["dr.kumar", "Identical flow, but FACILITY = Bandra. Only sees Bandra calendar & patients.", $BLUE],
    ["nurse.jane", "Pre-doctor: check-in 'arrived', enter Vitals, update allergies/meds. Not a provider.", $GREEN],
    ["reception.maya", "Front desk: register patients, schedule on Calendar, check-in/out, collect co-pay. No clinical notes.", $AMBER],
];
$sy = 43;
foreach ($side as $s) {
    $pdf->SetFillColor($s[2][0], $s[2][1], $s[2][2]);
    $pdf->Rect($px, $sy, $pw, 6, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Courier', 'B', 9);
    $pdf->SetXY($px + 2, $sy + 1);
    $pdf->Cell($pw - 4, 4, $s[0], 0, 0, 'L');
    $pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($px, $sy + 7);
    $pdf->MultiCell($pw, 4.2, $s[1], 0, 'L');
    $sy = $pdf->GetY() + 5;
}

$pdf->SetXY($px, $sy + 2);
$pdf->SetFont('Arial', 'B', 8.5);
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->MultiCell($pw, 4.5, "Summary:\nLogin > Calendar > click appt > Summary > Encounter > Vitals/SOAP/Rx/Orders > Fee Sheet > Sign-off.", 0, 'L');

$pdf->footer_note('Page 4 - Doctor Flow');

// ---- Output --------------------------------------------------------------
$out = __DIR__ . '/MatrixCMS_Workflow.pdf';
$pdf->Output('F', $out);
echo "PDF written to: $out\n";
