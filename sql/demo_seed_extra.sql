-- ───────────────────────────────────────────────────────────────
-- OpenEMR Demo Seed – Extras
-- Adds Vitals, Prescriptions, Billing codes, Insurance assignments
-- for the five demo patients created by demo_seed_data.sql
-- (pids 2–6: Rajesh, Priya, Amit, Neha, Vikram)
--
-- Safe to re-run?  NO — INSERTs new rows every time.
-- ───────────────────────────────────────────────────────────────

-- ===== 1. VITALS (one set per recent encounter) ===============
INSERT INTO form_vitals
    (date, pid, user, groupname, authorized, activity,
     bps, bpd, weight, height, temperature, temp_method, pulse, respiration,
     BMI, BMI_status, oxygen_saturation, note)
VALUES
(NOW() - INTERVAL 14 DAY, 2, 'admin', 'Default', 1, 1,
 '128', '82', 78.0,  175.0, 36.6, 'Oral', 76, 16, 25.5,  'Overweight',     98.0, 'Routine annual; mild HTN noted'),
(NOW() - INTERVAL 10 DAY, 3, 'admin', 'Default', 1, 1,
 '142', '92', 64.0,  160.0, 36.8, 'Oral', 84, 18, 25.0,  'Overweight',     97.0, 'Elevated BP, recheck in 4 wk'),
(NOW() -  INTERVAL 7 DAY, 4, 'admin', 'Default', 1, 1,
 '136', '88', 92.0,  178.0, 37.0, 'Oral', 90, 18, 29.0,  'Overweight',     96.0, 'HbA1c sent; counselling done'),
(NOW() -  INTERVAL 3 DAY, 5, 'admin', 'Default', 1, 1,
 '118', '76', 55.0,  162.0, 38.4, 'Oral', 102, 22, 21.0, 'Normal',         95.0, 'Acute pharyngitis'),
(NOW() -  INTERVAL 1 DAY, 6, 'admin', 'Default', 1, 1,
 '146', '94', 82.0,  170.0, 36.7, 'Oral', 78, 16, 28.4,  'Overweight',     97.0, 'Chronic back pain, PT referral');

-- Register the vitals form against each encounter
INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir, provider_id)
SELECT v.date, e.encounter, 'Vitals', v.id, v.pid, 'admin', 'Default', 1, 0, 'vitals', 1
FROM form_vitals v
JOIN form_encounter e ON e.pid = v.pid
WHERE v.pid BETWEEN 2 AND 6;


-- ===== 2. PRESCRIPTIONS =======================================
INSERT INTO prescriptions
    (patient_id, provider_id, encounter, start_date, drug, drug_id, form, dosage, quantity, size, unit, route, `interval`, refills, active, datetime, `user`, date_added)
VALUES
(3, 1, 102, DATE(NOW() - INTERVAL 10 DAY), 'Amlodipine 5mg',  4, 1, '1 tablet', '30', '5', 'mg', 'oral', 1, 2, 1, NOW(), 'admin', NOW()),
(4, 1, 103, DATE(NOW() -  INTERVAL 7 DAY), 'Metformin 500mg', 3, 1, '1 tablet twice daily', '60', '500', 'mg', 'oral', 2, 3, 1, NOW(), 'admin', NOW()),
(5, 1, 104, DATE(NOW() -  INTERVAL 3 DAY), 'Amoxicillin 250mg', 2, 1, '1 capsule three times daily', '21', '250', 'mg', 'oral', 3, 0, 1, NOW(), 'admin', NOW()),
(6, 1, 105, DATE(NOW() -  INTERVAL 1 DAY), 'Paracetamol 500mg', 1, 1, '1 tablet as needed', '20', '500', 'mg', 'oral', 1, 1, 1, NOW(), 'admin', NOW()),
(2, 1, 101, DATE(NOW() - INTERVAL 14 DAY), 'Atorvastatin 10mg', 5, 1, '1 tablet at night', '30', '10', 'mg', 'oral', 1, 2, 1, NOW(), 'admin', NOW());


-- ===== 3. BILLING (CPT visit + ICD diagnosis codes) ===========
INSERT INTO billing (date, code_type, code, pid, provider_id, user, groupname, authorized, encounter, code_text, billed, activity, fee, units, justify)
VALUES
(NOW() - INTERVAL 14 DAY, 'CPT4',  '99395', 2, 1, 1, 'Default', 1, 101, 'Periodic preventive exam, 18-39 yr',     0, 1, 150.00, 1, ''),
(NOW() - INTERVAL 14 DAY, 'ICD10', 'I10',   2, 1, 1, 'Default', 1, 101, 'Essential (primary) hypertension',       0, 1,   0.00, 1, ''),
(NOW() - INTERVAL 10 DAY, 'CPT4',  '99213', 3, 1, 1, 'Default', 1, 102, 'Office/outpatient visit, established',   0, 1,  90.00, 1, ''),
(NOW() - INTERVAL 10 DAY, 'ICD10', 'I10',   3, 1, 1, 'Default', 1, 102, 'Essential (primary) hypertension',       0, 1,   0.00, 1, ''),
(NOW() -  INTERVAL 7 DAY, 'CPT4',  '99214', 4, 1, 1, 'Default', 1, 103, 'Office/outpatient visit, moderate',      0, 1, 130.00, 1, ''),
(NOW() -  INTERVAL 7 DAY, 'ICD10', 'E11.9', 4, 1, 1, 'Default', 1, 103, 'Type 2 diabetes w/o complications',      0, 1,   0.00, 1, ''),
(NOW() -  INTERVAL 3 DAY, 'CPT4',  '99202', 5, 1, 1, 'Default', 1, 104, 'New patient visit, low complexity',      0, 1,  80.00, 1, ''),
(NOW() -  INTERVAL 3 DAY, 'ICD10', 'J02.9', 5, 1, 1, 'Default', 1, 104, 'Acute pharyngitis, unspecified',         0, 1,   0.00, 1, ''),
(NOW() -  INTERVAL 1 DAY, 'CPT4',  '99213', 6, 1, 1, 'Default', 1, 105, 'Office/outpatient visit, established',   0, 1,  90.00, 1, ''),
(NOW() -  INTERVAL 1 DAY, 'ICD10', 'M54.5', 6, 1, 1, 'Default', 1, 105, 'Low back pain',                          0, 1,   0.00, 1, '');


-- ===== 4. INSURANCE ASSIGNMENTS (one primary per patient) =====
INSERT INTO insurance_data
    (type, provider, plan_name, policy_number, group_number,
     subscriber_lname, subscriber_fname, subscriber_relationship, subscriber_DOB,
     subscriber_sex, pid, date)
VALUES
('primary',  '8',  'Star Family Health Optima', 'STAR-100020001', 'GRP-MH-001', 'Kumar',  'Rajesh', 'self', '1982-06-15', 'Male',   2, DATE(NOW() - INTERVAL 1 YEAR)),
('primary',  '9',  'ICICI Lombard Complete',    'ICICI-200030002','GRP-DL-002', 'Sharma', 'Priya',  'self', '1990-11-22', 'Female', 3, DATE(NOW() - INTERVAL 1 YEAR)),
('primary',  '10', 'HDFC ERGO Optima Restore',  'HDFC-300040003', 'GRP-GJ-003', 'Patel',  'Amit',   'self', '1975-02-09', 'Male',   4, DATE(NOW() - INTERVAL 1 YEAR)),
('primary',  '8',  'Star Comprehensive',        'STAR-400050004', 'GRP-KA-004', 'Gupta',  'Neha',   'self', '1998-08-30', 'Female', 5, DATE(NOW() - INTERVAL 1 YEAR)),
('primary',  '9',  'ICICI Lombard Senior',      'ICICI-500060005','GRP-WB-005', 'Singh',  'Vikram', 'self', '1965-04-03', 'Male',   6, DATE(NOW() - INTERVAL 2 YEAR));

-- Done.
