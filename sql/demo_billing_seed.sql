-- =====================================================================
-- Demo Billing Seed
-- Populates Fee Sheet, Payment, Checkout, Billing Manager,
-- Batch Payments, Posting Payments, and Claim File Tracker tabs.
--
-- Targets the 5 demo patients (pid 2..6) and their encounters (101..105).
-- Idempotent: deletes any previously-seeded demo rows before inserting.
--
-- Run via phpMyAdmin or:
--   mysql -uopenemr -proot openemr < sql/demo_billing_seed.sql
-- Rollback: sql/demo_billing_rollback.sql
-- =====================================================================

USE openemr;

START TRANSACTION;

-- ---------------------------------------------------------------------
-- 1. FEE SHEET: register the form against each encounter so it shows
--    up in the encounter sidebar. Billing rows are already seeded.
-- ---------------------------------------------------------------------
DELETE FROM forms
 WHERE formdir = 'fee_sheet'
   AND encounter IN (101, 102, 103, 104, 105);

INSERT INTO forms
    (date, encounter, form_name, form_id, pid, user, groupname,
     authorized, deleted, formdir, provider_id)
VALUES
    ('2026-04-10 18:29:50', 101, 'Fee Sheet', 101, 2, 'admin', 'Default', 1, 0, 'fee_sheet', 1),
    ('2026-04-14 18:29:50', 102, 'Fee Sheet', 102, 3, 'admin', 'Default', 1, 0, 'fee_sheet', 1),
    ('2026-04-17 18:29:50', 103, 'Fee Sheet', 103, 4, 'admin', 'Default', 1, 0, 'fee_sheet', 1),
    ('2026-04-21 18:29:50', 104, 'Fee Sheet', 104, 5, 'admin', 'Default', 1, 0, 'fee_sheet', 1),
    ('2026-04-23 18:29:50', 105, 'Fee Sheet', 105, 6, 'admin', 'Default', 1, 0, 'fee_sheet', 1);

-- ---------------------------------------------------------------------
-- 2. PAYMENT BATCH (ar_session) - container for the partial payment.
--    Drives the Batch Payments + Posting Payments screens.
-- ---------------------------------------------------------------------
DELETE FROM ar_session WHERE reference = 'DEMO-AMIT-001';

INSERT INTO ar_session
    (payer_id, user_id, closed, reference, check_date, deposit_date,
     pay_total, modified_time, global_amount, payment_type, description,
     adjustment_code, post_to_date, patient_id, payment_method)
VALUES
    (0, 1, 0, 'DEMO-AMIT-001', '2026-04-17', '2026-04-17',
     50.00, NOW(), 0.00, 'patient', 'Partial payment at checkout',
     'patient_payment', '2026-04-17', 4, 'cash');

SET @demo_session_id := LAST_INSERT_ID();

-- ---------------------------------------------------------------------
-- 3. PAYMENT (ar_activity) - $50 partial against Amit Patel's
--    CPT 99214 ($130). Outstanding balance after: $80.
-- ---------------------------------------------------------------------
DELETE FROM ar_activity
 WHERE pid = 4 AND encounter = 103 AND memo = 'DEMO-CHECKOUT';

INSERT INTO ar_activity
    (pid, encounter, sequence_no, code_type, code, modifier,
     payer_type, post_time, post_user, session_id, memo,
     pay_amount, adj_amount, modified_time, follow_up,
     account_code, post_date)
VALUES
    (4, 103, 1, 'CPT4', '99214', '',
     0, '2026-04-17 18:45:00', 1, @demo_session_id, 'DEMO-CHECKOUT',
     50.00, 0.00, NOW(), 'n',
     'PCP', '2026-04-17');

-- ---------------------------------------------------------------------
-- 4. CLAIMS (claim_file_tracker) - one row per encounter so the
--    Claim File Tracker shows status for all 5 demo patients.
--    status=0 = pending (not yet submitted to clearinghouse).
-- ---------------------------------------------------------------------
DELETE FROM claims
 WHERE encounter_id IN (101, 102, 103, 104, 105)
   AND patient_id   IN (2, 3, 4, 5, 6);

INSERT INTO claims
    (patient_id, encounter_id, version, payer_id, status, payer_type,
     bill_process, x12_partner_id)
VALUES
    (2, 101, 1, 8,  0, 0, 0, 12),
    (3, 102, 1, 9,  0, 0, 0, 12),
    (4, 103, 1, 10, 0, 0, 0, 12),
    (5, 104, 1, 8,  0, 0, 0, 12),
    (6, 105, 1, 9,  0, 0, 0, 12);

-- ---------------------------------------------------------------------
-- 5. Ensure Billing Manager sees these as "pending" by clearing any
--    accidental billed flag on the existing fee-sheet rows.
-- ---------------------------------------------------------------------
UPDATE billing
   SET billed = 0,
       bill_process = 0,
       activity = 1
 WHERE encounter IN (101, 102, 103, 104, 105);

COMMIT;

-- Summary
SELECT 'forms (fee_sheet)' AS what, COUNT(*) AS rows_now
  FROM forms WHERE formdir='fee_sheet' AND encounter IN (101,102,103,104,105)
UNION ALL
SELECT 'ar_session (demo)', COUNT(*) FROM ar_session WHERE reference='DEMO-AMIT-001'
UNION ALL
SELECT 'ar_activity (Amit $50)', COUNT(*) FROM ar_activity WHERE memo='DEMO-CHECKOUT'
UNION ALL
SELECT 'claims (tracker)', COUNT(*) FROM claims WHERE encounter_id IN (101,102,103,104,105)
UNION ALL
SELECT 'billing pending', COUNT(*) FROM billing WHERE encounter IN (101,102,103,104,105) AND billed=0;
