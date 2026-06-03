-- =====================================================================
-- DEMO PATIENT JOURNEY SEED - PART 2  (fills remaining dashboard panels)
-- Patient: Rajesh Mehta (pid 23, encounter 34)
-- Adds: Medications, Prescriptions, Insurance, Care Team, Messages,
--       Immunization, and a Lab result (CBC).
-- Run:  mysql ... openemr < _docs/seed_patient_journey_part2.sql
-- =====================================================================
SET @pid       = 23;
SET @enc       = 34;
SET @prov      = 11;   -- dr.smith
SET @nurse     = 13;   -- nurse.jane
SET @reception = 14;   -- reception.maya
SET @today     = CURDATE();
SET @now       = NOW();

-- ---- MEDICATION (lists -> "Medications" panel) ---------------------
INSERT INTO lists
    (uuid, date, type, title, begdate, activity, comments, pid, user, groupname)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @now, 'medication', 'Amoxicillin 500mg',
     @today, 1, 'Take 1 tablet twice daily for 10 days', @pid, 'dr.smith', 'Default');

-- ---- PRESCRIPTION (prescriptions -> "Prescriptions" panel) ---------
INSERT INTO prescriptions
    (uuid, patient_id, provider_id, encounter, date_added, datetime, start_date,
     drug, form, dosage, quantity, size, unit, route, `interval`, refills, per_refill,
     note, active, user)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @pid, @prov, @enc, @now, @now, @today,
     'Amoxicillin 500mg', 2, '1 tablet', '20', '500', 7, 'oral', 1, 1, 20,
     'Take with food. Complete the full course.', 1, 'dr.smith');

-- ---- INSURANCE (insurance_data -> "Insurance" panel) ---------------
-- provider = insurance_companies.id (8 = Star Health Insurance)
INSERT INTO insurance_data
    (uuid, type, provider, plan_name, policy_number, group_number,
     subscriber_lname, subscriber_fname, subscriber_relationship, subscriber_DOB,
     subscriber_sex, subscriber_street, subscriber_city, subscriber_state,
     subscriber_postal_code, subscriber_phone, copay, date, pid,
     accept_assignment, policy_type)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), 'primary', '8', 'Star Health Family Optima', 'SH-POL-100023', 'GRP-77',
     'Mehta', 'Rajesh', 'self', '1990-05-15',
     'Male', '12 Hill Road', 'Mumbai', 'MH',
     '400058', '9876500023', '20', @today, @pid,
     'TRUE', '');

-- ---- CARE TEAM (patient_data -> "Care Team" panel) -----------------
UPDATE patient_data
   SET care_team_provider = @prov,
       care_team_facility = '3',
       care_team_status   = 'active'
 WHERE pid = @pid;

-- ---- MESSAGE / PATIENT NOTE (pnotes -> "Messages" panel) -----------
INSERT INTO pnotes
    (date, body, pid, user, groupname, activity, authorized, title,
     assigned_to, deleted, message_status)
VALUES
    (@now, 'Patient requested a follow-up call regarding antibiotic course and lab results.',
     @pid, 'reception.maya', 'Default', 1, 1, 'Follow-up Call',
     'dr.smith', 0, 'New');

-- ---- IMMUNIZATION (immunizations -> "Immunizations" panel) ---------
INSERT INTO immunizations
    (uuid, patient_id, administered_date, cvx_code, manufacturer, lot_number,
     administered_by_id, administered_by, note, create_date,
     amount_administered, amount_administered_unit, route, administration_site,
     completion_status, ordering_provider, encounter_id)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @pid, @now, '140', 'Sanofi Pasteur', 'FL2026A',
     @nurse, 'Jane Doe', 'Influenza, seasonal, injectable', @now,
     0.5, 'mL', 'IM', 'Left deltoid',
     'Complete', @prov, @enc);

-- ---- LAB RESULT (procedure_order + code + report + result) ---------
INSERT INTO procedure_order
    (uuid, provider_id, patient_id, encounter_id, date_collected, date_ordered,
     order_priority, order_status, activity, lab_id, clinical_hx, order_diagnosis,
     procedure_order_type)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @prov, @pid, @enc, @now, @now,
     'normal', 'completed', 1, 0, 'Fever and sore throat', 'ICD10:J06.9',
     'laboratory');
SET @order_id = LAST_INSERT_ID();

INSERT INTO procedure_order_code
    (procedure_order_id, procedure_order_seq, procedure_code, procedure_name,
     procedure_source, diagnoses, procedure_order_title, procedure_type)
VALUES
    (@order_id, 1, 'CBC', 'Complete Blood Count', '1', 'ICD10:J06.9',
     'laboratory_test', 'laboratory');

INSERT INTO procedure_report
    (uuid, procedure_order_id, procedure_order_seq, date_collected, date_report,
     report_status, review_status, report_notes)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @order_id, 1, @now, @now,
     'final', 'reviewed', 'All values within normal range.');
SET @report_id = LAST_INSERT_ID();

INSERT INTO procedure_result
    (uuid, procedure_report_id, result_data_type, result_code, result_text, date,
     units, result, `range`, abnormal, result_status)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @report_id, 'N', 'WBC', 'White Blood Cell Count', @now,
     '10^3/uL', '7.2', '4.0-11.0', '', 'final'),
    (UNHEX(REPLACE(UUID(),'-','')), @report_id, 'N', 'HGB', 'Hemoglobin', @now,
     'g/dL', '14.1', '13.0-17.0', '', 'final'),
    (UNHEX(REPLACE(UUID(),'-','')), @report_id, 'N', 'PLT', 'Platelet Count', @now,
     '10^3/uL', '250', '150-400', '', 'final');

SELECT 'part2 done' AS status, @order_id AS lab_order_id;
