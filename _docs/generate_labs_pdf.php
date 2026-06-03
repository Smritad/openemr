<?php

/**
 * Generates a presentation-ready Labs / Procedures workflow PDF for MatrixCMS.
 * Standalone: uses the bundled FPDF, no database/globals required.
 *
 * Run:  php _docs/generate_labs_pdf.php
 * Out:  _docs/MatrixCMS_Labs_Workflow.pdf
 */

require_once __DIR__ . '/../library/classes/fpdf/fpdf.php';

$RED   = [204, 0, 0];
$DARK  = [26, 26, 26];
$BLUE  = [33, 102, 172];
$GREEN = [33, 138, 74];
$AMBER = [200, 130, 0];
$PURPLE = [120, 60, 150];
$GREY  = [110, 110, 110];

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
        $this->Cell(0, 5, 'MatrixCMS (OpenEMR) - Labs / Procedures   |   ' . $pageLabel, 0, 0, 'C');
    }
}

$pdf = new Flow('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);

// =========================================================================
// PAGE 1 — TITLE + MENU MAP
// =========================================================================
$pdf->AddPage();
$pdf->SetFillColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->Rect(0, 0, 210, 60, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetXY(15, 18);
$pdf->Cell(180, 12, 'MatrixCMS - Labs & Procedures', 0, 2, 'L');
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(180, 8, 'What each menu does, who uses it, and the order flow', 0, 2, 'L');

$pdf->SetY(70);
$pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetX(15);
$pdf->Cell(180, 7, 'The Procedures menu = 2 jobs: SET UP the lab, then USE it', 0, 2, 'L');

// Two columns: Configuration vs Daily use
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor($PURPLE[0], $PURPLE[1], $PURPLE[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(15, 82); $pdf->Cell(88, 7, 'A) ONE-TIME SETUP (Admin)', 0, 0, 'C', true);
$pdf->SetFillColor($BLUE[0], $BLUE[1], $BLUE[2]);
$pdf->SetXY(107, 82); $pdf->Cell(88, 7, 'B) EVERY DAY (Doctor / Lab / Biller)', 0, 0, 'C', true);

$setup = [
    ['Procedure Providers', 'Register each LAB company (name, NPI, connection). = the vendor list.'],
    ['Configure Orders & Results', 'Build the catalog tree: Lab > Panel(CBC) > Results(WBC,HGB,PLT).'],
    ['Load Lab Compendium', 'Shortcut: UPLOAD the vendor file to auto-build that catalog.'],
];
$use = [
    ['Procedure Orders & Reports', 'Search / track every order + its report. The control desk.'],
    ['Pending Review', 'Inbox of NEW results waiting for a doctor to sign off.'],
    ['Patient Results', 'One patient\'s results inside the chart (what you saw: WBC/HGB/PLT).'],
    ['Batch / Process Results', 'Import many electronic results at once + match to orders.'],
    ['Lab Documents', 'PDF result files received from the lab, listed by date.'],
];
$y = 91;
foreach ($setup as $i => $s) {
    $pdf->box(15, $y, 88, 16, $s[0], $PURPLE, [255, 255, 255], 8.5);
    $pdf->SetXY(15, $y); // overlay description below title handled by two-line box
    $y += 18;
}
// redo setup boxes with title + desc
$y = 91;
foreach ($setup as $s) {
    $pdf->SetFillColor($PURPLE[0], $PURPLE[1], $PURPLE[2]);
    $pdf->Rect(15, $y, 88, 16, 'DF');
    $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetXY(17, $y + 1.5); $pdf->Cell(84, 4, $s[0], 0, 0, 'L');
    $pdf->SetFont('Arial', '', 7.3);
    $pdf->SetXY(17, $y + 6); $pdf->MultiCell(84, 3.4, $s[1], 0, 'L');
    $y += 18;
}
$y = 91;
foreach ($use as $s) {
    $pdf->SetFillColor($BLUE[0], $BLUE[1], $BLUE[2]);
    $pdf->Rect(107, $y, 88, 16, 'DF');
    $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetXY(109, $y + 1.5); $pdf->Cell(84, 4, $s[0], 0, 0, 'L');
    $pdf->SetFont('Arial', '', 7.3);
    $pdf->SetXY(109, $y + 6); $pdf->MultiCell(84, 3.4, $s[1], 0, 'L');
    $y += 18;
}

$pdf->SetXY(15, 200);
$pdf->SetFont('Arial', 'I', 8.5);
$pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->MultiCell(180, 5, "Rule of thumb:  the LEFT column you touch once when setting up a new lab.  The RIGHT column is the daily clinical + billing work. You can build the catalog by hand (Configure Orders & Results) OR import it (Load Lab Compendium) - same result.", 0, 'L');
$pdf->footer_note('Page 1 - Menu Map');

// =========================================================================
// PAGE 2 — THE LAB ORDER LIFECYCLE (who does what)
// =========================================================================
$pdf->AddPage();
$pdf->heading('The Lab Order Lifecycle', 'From the doctor ordering a test to a signed result - and who does each step');

$cx = 70; $w = 110; $x = $cx - $w / 2; $h = 15; $gap = 7; $y = 34;
$life = [
    ["1. DOCTOR orders a test", "In the encounter, picks 'CBC' from the catalog", $BLUE],
    ["2. Order created", "procedure_order + procedure_order_code", $BLUE],
    ["3. LAB receives / runs it", "Specimen collected, sample analysed", $AMBER],
    ["4. Result comes back", "Typed in, or imported (Batch / Compendium)", $AMBER],
    ["5. Result stored", "procedure_report + procedure_result rows", $GREEN],
    ["6. PENDING REVIEW", "Result sits in the doctor's review inbox", $RED],
    ["7. DOCTOR signs results", "Reviewed & acknowledged -> patient chart", $GREEN],
    ["8. Visible in PATIENT RESULTS", "WBC 7.2 / HGB 14.1 / PLT 250", $GREEN],
];
foreach ($life as $i => $s) {
    $pdf->box($x, $y, $w, $h, $s[0] . "\n" . $s[1], $s[2], [255, 255, 255], 8);
    if ($i < count($life) - 1) {
        $pdf->arrowDown($cx, $y + $h, $y + $h + $gap);
    }
    $y += $h + $gap;
}

// Side: who & which menu
$px = 132; $pw = 63;
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetXY($px, 34); $pdf->Cell($pw, 6, 'Who + which menu', 0, 2, 'L');
$who = [
    ["DOCTOR", "Orders the test inside the encounter (Procedure Order form)."],
    ["LAB TECH", "Runs the test, enters results - or they arrive electronically."],
    ["Procedure Orders & Reports", "Biller/admin tracks ALL orders & reports here."],
    ["Pending Review", "Doctor's inbox of unsigned results."],
    ["Patient Results", "Result shown inside that patient's chart."],
    ["Lab Documents", "Any PDF the lab sent, by date range."],
];
$sy = 42;
foreach ($who as $s) {
    $pdf->SetFillColor($DARK[0], $DARK[1], $DARK[2]);
    $pdf->Rect($px, $sy, $pw, 5.5, 'F');
    $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($px + 1.5, $sy + 1); $pdf->Cell($pw - 3, 4, $s[0], 0, 0, 'L');
    $pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]); $pdf->SetFont('Arial', '', 7.5);
    $pdf->SetXY($px, $sy + 6); $pdf->MultiCell($pw, 3.6, $s[1], 0, 'L');
    $sy = $pdf->GetY() + 3.5;
}
$pdf->footer_note('Page 2 - Order Lifecycle');

// =========================================================================
// PAGE 3 — WHAT WE SEEDED (the demo data) + how to search
// =========================================================================
$pdf->AddPage();
$pdf->heading('The Demo Data We Created', 'Exactly what is now in the system for patient Rajesh Mehta');

$pdf->SetY(30);
$pdf->SetFont('Arial', 'B', 11); $pdf->SetTextColor($PURPLE[0], $PURPLE[1], $PURPLE[2]);
$pdf->SetX(15); $pdf->Cell(180, 6, 'Setup (left column of the menu)', 0, 2, 'L');

$pdf->SetFont('Arial', '', 9); $pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->SetX(18);
$pdf->MultiCell(177, 5,
  "- Procedure Provider:  'MatrixCMS Diagnostics Lab'  (NPI 1990000099, HL7)\n" .
  "- Compendium tree (Configure Orders & Results):\n" .
  "        MatrixCMS Diagnostics Lab   [Tier 1 - Lab]\n" .
  "             |__ Complete Blood Count (CBC)   [Tier 2 - Order]\n" .
  "                      |__ WBC  4.0-11.0 10^3/uL    [Tier 3 - Result]\n" .
  "                      |__ HGB  13.0-17.0 g/dL      [Tier 3 - Result]\n" .
  "                      |__ PLT  150-400 10^3/uL     [Tier 3 - Result]",
  0, 'L');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 11); $pdf->SetTextColor($BLUE[0], $BLUE[1], $BLUE[2]);
$pdf->SetX(15); $pdf->Cell(180, 6, 'Live order (right column of the menu)', 0, 2, 'L');
$pdf->SetFont('Arial', '', 9); $pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->SetX(18);
$pdf->MultiCell(177, 5,
  "- Order #1 for Rajesh Mehta, encounter 34, ordered by Dr. Smith\n" .
  "- Report status: final  ->  Results: WBC 7.2, HGB 14.1, PLT 250 (all normal)\n" .
  "- Now linked to MatrixCMS Diagnostics Lab and attached to the encounter form,\n" .
  "  so it appears in Patient Results AND Procedure Orders & Reports.",
  0, 'L');

$pdf->Ln(3);
$pdf->SetFont('Arial', 'B', 11); $pdf->SetTextColor($RED[0], $RED[1], $RED[2]);
$pdf->SetX(15); $pdf->Cell(180, 6, 'How to SEARCH (Procedure Orders & Reports)', 0, 2, 'L');
$pdf->SetFont('Arial', '', 9); $pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->SetX(18);
$pdf->MultiCell(177, 5,
  "1. Set the From / To date range (e.g. 2026-05-31 to 2026-06-03).\n" .
  "2. Pick the Lab (All Labs) and the Provider (John Smith).\n" .
  "3. Tick 'Current Patient Only' to limit to the open patient, or untick for everyone.\n" .
  "4. Click Filter.  Each row = one order with its procedure code and report date.",
  0, 'L');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 11); $pdf->SetTextColor($AMBER[0], $AMBER[1], $AMBER[2]);
$pdf->SetX(15); $pdf->Cell(180, 6, 'Lab Documents (the From/To you asked about)', 0, 2, 'L');
$pdf->SetFont('Arial', '', 9); $pdf->SetTextColor($DARK[0], $DARK[1], $DARK[2]);
$pdf->SetX(18);
$pdf->MultiCell(177, 5,
  "This screen lists PDF/scanned result files the lab SENT, filtered by upload date.\n" .
  "Empty = no document files uploaded yet (our results were typed in, not file uploads).\n" .
  "A file appears here once a lab result document is uploaded against the patient.",
  0, 'L');

$pdf->footer_note('Page 3 - Demo Data & Search');

$out = __DIR__ . '/MatrixCMS_Labs_Workflow.pdf';
$pdf->Output('F', $out);
echo "PDF written to: $out\n";
