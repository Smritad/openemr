-- =====================================================================
-- Demo Billing Rollback
-- Undoes sql/demo_billing_seed.sql.
--
-- Removes the seeded forms / ar_session / ar_activity / claims rows
-- for the 5 demo patients (pid 2..6, encounter 101..105).
-- Leaves the original billing rows intact.
-- =====================================================================

USE openemr;

START TRANSACTION;

DELETE FROM ar_activity
 WHERE pid = 4 AND encounter = 103 AND memo = 'DEMO-CHECKOUT';

DELETE FROM ar_session
 WHERE reference = 'DEMO-AMIT-001';

DELETE FROM forms
 WHERE formdir = 'fee_sheet'
   AND encounter IN (101, 102, 103, 104, 105);

DELETE FROM claims
 WHERE encounter_id IN (101, 102, 103, 104, 105)
   AND patient_id   IN (2, 3, 4, 5, 6);

COMMIT;

SELECT 'Rollback complete' AS status;
