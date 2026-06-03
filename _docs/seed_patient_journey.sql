-- =====================================================================
-- DEMO PATIENT JOURNEY SEED  (MatrixCMS / OpenEMR)
-- Patient: Rajesh Mehta  | Provider: dr.smith (id 11) | Facility: Andheri (id 3)
-- Walks one visit through every step so it shows in Fee Sheet, Checkout
-- and the Sales / Collections reports.
-- Run:  mysql ... openemr < _docs/seed_patient_journey.sql
-- =====================================================================

-- ---- fixed references for this demo --------------------------------
SET @pid        = 23;          -- next free pid (max was 22)
SET @prov       = 11;          -- dr.smith
SET @reception  = 14;          -- reception.maya
SET @facility   = 3;           -- MatrixCMS Andheri Clinic
SET @today      = CURDATE();
SET @now        = NOW();

-- ---- 1) REGISTRATION : patient_data --------------------------------
INSERT INTO patient_data
    (uuid, title, fname, lname, DOB, sex, street, city, state, postal_code,
     phone_cell, email, status, providerID, ref_providerID,
     pubpid, pid, date, regdate, care_team_provider, care_team_facility)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), 'Mr.', 'Rajesh', 'Mehta', '1990-05-15', 'Male',
     '12 Hill Road', 'Mumbai', 'MH', '400058',
     '9876500023', 'rajesh.mehta@example.com', 'married', @prov, @prov,
     @pid, @pid, @now, @now, @prov, @facility);

-- ---- 2) APPOINTMENT : openemr_postcalendar_events ------------------
-- pc_apptstatus '>' = Checked out (the visit is complete: Arrived -> ... -> Checked out)
INSERT INTO openemr_postcalendar_events
    (uuid, pc_catid, pc_aid, pc_pid, pc_title, pc_time, pc_hometext,
     pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, pc_startTime, pc_endTime,
     pc_alldayevent, pc_apptstatus, pc_facility, pc_billing_location, pc_eventstatus, pc_sharing)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), 5, @prov, @pid, 'Office Visit', @now, 'Fever and sore throat',
     @today, @today, 900, 0, '10:00:00', '10:15:00',
     0, '>', @facility, @facility, 1, 0);

-- ---- 3) ENCOUNTER : new encounter number from sequences ------------
UPDATE sequences SET id = LAST_INSERT_ID(id + 1);
SET @enc = LAST_INSERT_ID();   -- = 34

INSERT INTO form_encounter
    (uuid, date, reason, facility, facility_id, billing_facility, pid, encounter,
     pc_catid, provider_id, sensitivity, pos_code, class_code)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @now, 'Fever and sore throat',
     'MatrixCMS Andheri Clinic', @facility, @facility, @pid, @enc,
     5, @prov, 'normal', 11, 'AMB');
SET @enc_form_id = LAST_INSERT_ID();

-- registry row that makes the encounter appear in the patient chart
INSERT INTO forms
    (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir, provider_id)
VALUES
    (@now, @enc, 'New Patient Encounter', @enc_form_id, @pid, 'dr.smith', 'Default', 1, 0, 'newpatient', @prov);

-- ---- 4) ALLERGY : lists (type allergy) -----------------------------
INSERT INTO lists
    (uuid, date, type, title, begdate, activity, comments, pid, user, groupname, severity_al, reaction)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @now, 'allergy', 'Penicillin', @today, 1,
     'Skin rash on exposure', @pid, 'dr.smith', 'Default', 'moderate', 'Rash');

-- ---- 4b) MEDICAL PROBLEM : lists (type medical_problem) ------------
INSERT INTO lists
    (uuid, date, type, title, diagnosis, begdate, activity, comments, pid, user, groupname)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @now, 'medical_problem', 'Hypertension', 'ICD10:I10',
     @today, 1, 'Controlled on medication', @pid, 'dr.smith', 'Default');

-- ---- 5) VITALS : form_vitals + registry ----------------------------
INSERT INTO form_vitals
    (uuid, date, pid, user, groupname, authorized, activity,
     bps, bpd, weight, height, temperature, temp_method, pulse, respiration,
     oxygen_saturation, BMI, BMI_status)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), @now, @pid, 'nurse.jane', 'Default', 1, 1,
     '120', '80', 154.000000, 67.000000, 99.100000, 'Oral', 76.000000, 16.000000,
     98.00, 24.10, 'Normal');
SET @vit_form_id = LAST_INSERT_ID();

INSERT INTO forms
    (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir, provider_id)
VALUES
    (@now, @enc, 'Vitals', @vit_form_id, @pid, 'nurse.jane', 'Default', 1, 0, 'vitals', @prov);

-- ---- 6) FEE SHEET : billing (charges) ------------------------------
-- diagnosis line (ICD10) - no charge
INSERT INTO billing
    (date, code_type, code, pid, provider_id, user, groupname, authorized, encounter,
     code_text, billed, activity, units, fee, justify)
VALUES
    (@now, 'ICD10', 'J06.9', @pid, @prov, @prov, 'Default', 1, @enc,
     'Acute upper respiratory infection, unspecified', 0, 1, 1, 0.00, '');

-- service line (CPT) - the charge that hits the Sales report
INSERT INTO billing
    (date, code_type, code, pid, provider_id, user, groupname, authorized, encounter,
     code_text, billed, activity, units, fee, justify)
VALUES
    (@now, 'CPT4', '99213', @pid, @prov, @prov, 'Default', 1, @enc,
     'Office/outpatient visit, established patient, level 3', 0, 1, 1, 75.00, 'J06.9');

-- ---- 7) CHECKOUT : payment (ar_session + ar_activity) --------------
INSERT INTO ar_session
    (payer_id, user_id, closed, reference, check_date, deposit_date, pay_total,
     global_amount, payment_type, description, adjustment_code, post_to_date,
     patient_id, payment_method)
VALUES
    (0, @reception, 0, 'CASH-DEMO-23', @today, @today, 75.00,
     '', 'patient', 'Front-desk co-pay at checkout', 'patient_payment', @today,
     @pid, 'cash');
SET @sess = LAST_INSERT_ID();

INSERT INTO ar_activity
    (pid, encounter, sequence_no, code_type, code, payer_type, post_time, post_user,
     session_id, memo, pay_amount, adj_amount, account_code, post_date)
VALUES
    (@pid, @enc, 1, 'CPT4', '99213', 0, @now, @reception,
     @sess, 'Cash payment at checkout', 75.00, 0.00, 'PCP', @today);

SELECT @pid AS new_pid, @enc AS new_encounter, @sess AS payment_session;
