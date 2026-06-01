-- ============================================================
-- Phase 1: CLEANUP — wipe all clinical/transactional data
-- Keeps: 4 system users, facilities, ACL, globals, list_options,
--        appointment categories, all system config
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Patients & demographics
DELETE FROM patient_data;
DELETE FROM history_data;
DELETE FROM insurance_data;
DELETE FROM employer_data;

-- Encounters & forms
DELETE FROM form_encounter;
DELETE FROM forms;
DELETE FROM form_vitals;
DELETE FROM form_misc_billing_options;

-- Lists (problems, meds, allergies), prescriptions, immunizations
DELETE FROM lists;
DELETE FROM lists_touch;
DELETE FROM issue_encounter;
DELETE FROM prescriptions;
DELETE FROM immunizations;

-- Procedures / labs / documents / billing
DELETE FROM procedure_order;
DELETE FROM procedure_order_code;
DELETE FROM procedure_report;
DELETE FROM procedure_result;
DELETE FROM documents;
DELETE FROM categories_to_documents;
DELETE FROM billing;
DELETE FROM claims;
DELETE FROM payments;
DELETE FROM ar_activity;
DELETE FROM ar_session;

-- Calendar / appointments
DELETE FROM openemr_postcalendar_events;

-- Messages / Reminders / Recalls
DELETE FROM pnotes;
DELETE FROM dated_reminders;
DELETE FROM dated_reminders_link;
DELETE FROM medex_outgoing;
DELETE FROM medex_recalls;

-- IP tracking (fixes earlier duplicate-PK bug too)
TRUNCATE TABLE ip_tracking;

-- Recent-patient lookup table (cleared so deleted PIDs don't render as ghost rows)
TRUNCATE TABLE recent_patients;

-- Delete the test user "User" (Riddhi) but KEEP the 4 system accounts.
-- ACL linkage uses gacl_aro (section_value='users', value=username) and
-- gacl_groups_aro_map for group membership.
DELETE FROM gacl_groups_aro_map
    WHERE aro_id IN (SELECT id FROM gacl_aro WHERE section_value='users' AND value='User');
DELETE FROM gacl_aro          WHERE section_value='users' AND value='User';
DELETE FROM users_secure      WHERE id IN (SELECT id FROM users WHERE username = 'User');
DELETE FROM users             WHERE username = 'User';

SET FOREIGN_KEY_CHECKS = 1;
