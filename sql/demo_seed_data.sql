-- ───────────────────────────────────────────────────────────────
-- OpenEMR Demo Seed Data
-- Adds realistic fake patients, encounters, appointments, problem
-- lists, allergies, medications, drugs, and insurers so the UI has
-- something to show while you learn the workflow.
--
-- Safe to re-run?  NO — it INSERTs new rows every time.
-- Existing facility id=3, existing provider id=1 (admin) are reused.
-- ───────────────────────────────────────────────────────────────

-- ===== 1. PATIENTS ============================================
-- pid is a NOT NULL primary key (NOT auto_increment). Set explicitly.
SET @pid_rajesh = 2;
SET @pid_priya  = 3;
SET @pid_amit   = 4;
SET @pid_neha   = 5;
SET @pid_vikram = 6;

INSERT INTO patient_data
    (pid, pubpid, title, language, financial, fname, lname, mname, DOB, street, postal_code, city, state, country_code,
     drivers_license, ss, phone_home, phone_biz, phone_contact, phone_cell, status, contact_relationship,
     date, sex, referrer, referrerID, providerID, email, email_direct, ethnoracial, race, ethnicity, religion,
     interpretter, migrantseasonal)
VALUES
(@pid_rajesh, '2', 'Mr.', 'English', 'Self', 'Rajesh', 'Kumar', 'A',   '1982-06-15', '42 MG Road',    '400001', 'Mumbai',   'MH', 'IN', '', '', '9876543210', '', '', '9876543210', 'married',  'spouse',  NOW(), 'Male',   '', '', 1, 'rajesh.kumar@example.com', '', '', '', '', '', '', ''),
(@pid_priya,  '3', 'Mrs.','English', 'Self', 'Priya',  'Sharma', 'S',  '1990-11-22', '7 Park Lane',   '110001', 'Delhi',    'DL', 'IN', '', '', '9811122233', '', '', '9811122233', 'married',  'spouse',  NOW(), 'Female', '', '', 1, 'priya.sharma@example.com', '', '', '', '', '', '', ''),
(@pid_amit,   '4', 'Mr.', 'English', 'Self', 'Amit',   'Patel',  'V',  '1975-02-09', '15 Ring Road',  '380001', 'Ahmedabad','GJ', 'IN', '', '', '9798765432', '', '', '9798765432', 'single',   'friend',  NOW(), 'Male',   '', '', 1, 'amit.patel@example.com',   '', '', '', '', '', '', ''),
(@pid_neha,   '5', 'Ms.', 'English', 'Self', 'Neha',   'Gupta',  'R',  '1998-08-30', '22 Lake View',  '560001', 'Bengaluru','KA', 'IN', '', '', '9900011223', '', '', '9900011223', 'single',   'parent',  NOW(), 'Female', '', '', 1, 'neha.gupta@example.com',   '', '', '', '', '', '', ''),
(@pid_vikram, '6', 'Mr.', 'English', 'Self', 'Vikram', 'Singh',  'J',  '1965-04-03', '9 Fort Rd',     '700001', 'Kolkata',  'WB', 'IN', '', '', '9830045678', '', '', '9830045678', 'widowed',  'child',   NOW(), 'Male',   '', '', 1, 'vikram.singh@example.com', '', '', '', '', '', '', '');


-- ===== 2. FACILITY (extra clinic) =============================
INSERT INTO facility (name, phone, fax, street, city, state, postal_code, country_code,
                      federal_ein, website, email, service_location, billing_location, color)
VALUES ('Matrix Health Clinic', '022-2222-3333', '022-2222-3334', '100 Brand Avenue',
        'Mumbai', 'MH', '400002', 'IN', '', 'https://matrixhealth.example', 'info@matrixhealth.example',
        1, 1, '#cc0000');


-- ===== 3. INSURANCE COMPANIES =================================
-- insurance_companies.id is PRIMARY but NOT auto_increment.
INSERT INTO insurance_companies (id, name, attn, cms_id, ins_type_code, x12_receiver_id, x12_default_partner_id, alt_cms_id)
VALUES
(8,  'Star Health Insurance', NULL, NULL, 2, NULL, NULL, ''),
(9,  'ICICI Lombard General', NULL, NULL, 2, NULL, NULL, ''),
(10, 'HDFC ERGO Health',      NULL, NULL, 2, NULL, NULL, '');


-- ===== 4. ENCOUNTERS (one per new patient, recent dates) =====
INSERT INTO form_encounter (date, reason, facility, facility_id, pid, encounter, pc_catid, provider_id, billing_facility, class_code)
VALUES
(NOW() - INTERVAL 14 DAY, 'Annual physical exam',               'CSAB Clinic', 3, @pid_rajesh, 101, 5, 1, 3, 'AMB'),
(NOW() - INTERVAL 10 DAY, 'Follow-up for hypertension',         'CSAB Clinic', 3, @pid_priya,  102, 5, 1, 3, 'AMB'),
(NOW() -  INTERVAL 7 DAY, 'Diabetes consultation',              'CSAB Clinic', 3, @pid_amit,   103, 5, 1, 3, 'AMB'),
(NOW() -  INTERVAL 3 DAY, 'Fever and sore throat',              'CSAB Clinic', 3, @pid_neha,   104, 5, 1, 3, 'AMB'),
(NOW() -  INTERVAL 1 DAY, 'Chronic back pain evaluation',       'CSAB Clinic', 3, @pid_vikram, 105, 5, 1, 3, 'AMB');


-- ===== 5. APPOINTMENTS (upcoming) =============================
INSERT INTO openemr_postcalendar_events
    (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate,
     pc_duration, pc_apptstatus)
VALUES
(9, 0, '1', @pid_rajesh, 'Lab review',          NOW() + INTERVAL 2 DAY,  'Cholesterol panel review',   DATE(NOW() + INTERVAL 2 DAY),  '0000-00-00', 900,  '-'),
(9, 0, '1', @pid_priya,  'BP recheck',          NOW() + INTERVAL 4 DAY,  'Monthly blood pressure',     DATE(NOW() + INTERVAL 4 DAY),  '0000-00-00', 900,  '-'),
(9, 0, '1', @pid_amit,   'HbA1c follow-up',     NOW() + INTERVAL 7 DAY,  'Quarterly diabetes review',  DATE(NOW() + INTERVAL 7 DAY),  '0000-00-00', 1800, '-'),
(9, 0, '1', @pid_neha,   'Throat culture result', NOW() + INTERVAL 1 DAY,'Share lab report',           DATE(NOW() + INTERVAL 1 DAY),  '0000-00-00', 900,  '-'),
(9, 0, '1', @pid_vikram, 'Physiotherapy referral', NOW() + INTERVAL 5 DAY,'Discuss PT plan',           DATE(NOW() + INTERVAL 5 DAY),  '0000-00-00', 1800, '-');


-- ===== 6. PROBLEM LIST / ALLERGIES / MEDICATIONS (table: lists) ====
-- type='medical_problem' / 'allergy' / 'medication'
INSERT INTO lists (date, type, title, begdate, diagnosis, activity, pid, user, outcome, subtype)
VALUES
(NOW() - INTERVAL 2 YEAR, 'medical_problem', 'Hypertension',         DATE(NOW() - INTERVAL 2 YEAR), 'ICD10:I10',   1, @pid_priya,  'admin', 0, ''),
(NOW() - INTERVAL 5 YEAR, 'medical_problem', 'Type 2 Diabetes',      DATE(NOW() - INTERVAL 5 YEAR), 'ICD10:E11.9', 1, @pid_amit,   'admin', 0, ''),
(NOW() - INTERVAL 1 YEAR, 'medical_problem', 'Chronic lower back pain', DATE(NOW() - INTERVAL 1 YEAR), 'ICD10:M54.5', 1, @pid_vikram, 'admin', 0, ''),
(NOW() - INTERVAL 3 YEAR, 'medical_problem', 'Seasonal allergic rhinitis', DATE(NOW() - INTERVAL 3 YEAR), 'ICD10:J30.1', 1, @pid_rajesh, 'admin', 0, '');

INSERT INTO lists (date, type, title, begdate, activity, pid, user, reaction, severity_al, outcome, subtype)
VALUES
(NOW() - INTERVAL 4 YEAR, 'allergy', 'Penicillin',       DATE(NOW() - INTERVAL 4 YEAR), 1, @pid_priya, 'admin', 'Skin rash',    'moderate', 0, ''),
(NOW() - INTERVAL 2 YEAR, 'allergy', 'Peanuts',          DATE(NOW() - INTERVAL 2 YEAR), 1, @pid_neha,  'admin', 'Hives, swelling','severe',  0, ''),
(NOW() - INTERVAL 6 YEAR, 'allergy', 'Sulfa drugs',      DATE(NOW() - INTERVAL 6 YEAR), 1, @pid_amit,  'admin', 'Itching',      'mild',     0, '');

INSERT INTO lists (date, type, title, begdate, activity, pid, user, outcome, subtype)
VALUES
(NOW() - INTERVAL 2 YEAR, 'medication', 'Amlodipine 5mg once daily',   DATE(NOW() - INTERVAL 2 YEAR), 1, @pid_priya,  'admin', 0, ''),
(NOW() - INTERVAL 5 YEAR, 'medication', 'Metformin 500mg twice daily', DATE(NOW() - INTERVAL 5 YEAR), 1, @pid_amit,   'admin', 0, ''),
(NOW() - INTERVAL 1 YEAR, 'medication', 'Paracetamol 500mg PRN',       DATE(NOW() - INTERVAL 1 YEAR), 1, @pid_vikram, 'admin', 0, '');


-- ===== 7. PHARMACY DRUGS (inventory) ==========================
INSERT INTO drugs (name, ndc_number, form, size, unit, route, active, dispensable, drug_code)
VALUES
('Paracetamol 500mg',      'NDC-00001-0001', '1', '500', 'mg', '1', 1, 1, 'PARA500'),
('Amoxicillin 250mg',      'NDC-00002-0002', '1', '250', 'mg', '1', 1, 1, 'AMOX250'),
('Metformin 500mg',        'NDC-00003-0003', '1', '500', 'mg', '1', 1, 1, 'MET500'),
('Amlodipine 5mg',         'NDC-00004-0004', '1', '5',   'mg', '1', 1, 1, 'AML005'),
('Atorvastatin 10mg',      'NDC-00005-0005', '1', '10',  'mg', '1', 1, 1, 'ATOR10');

-- Done.
