-- ============================================================
-- MatrixCMS — Sample medical billing codes for testing
--
-- Loads ~50 common CPT, ICD-10, and HCPCS codes so the Fee Sheet
-- search box returns useful results.
--
-- These are SAMPLE/TEST codes only. For production use, you must
-- license the official AMA CPT and CMS ICD-10 databases.
-- ============================================================

-- Clean previous test seed (idempotent)
DELETE FROM codes WHERE id BETWEEN 9000 AND 9999;

-- Make sure ICD10 reads from the local `codes` table, not from the empty
-- external icd10_dx_order_code table.
UPDATE code_types SET ct_external = 0 WHERE ct_key IN ('ICD10', 'CPT4', 'HCPCS');

-- ─────── CPT4 PROCEDURE CODES (code_type=1) ───────

INSERT INTO codes (id, code_type, code, code_text, code_text_short, active, financial_reporting) VALUES
-- E/M Office Visits — Established Patient
(9001, 1, '99211', 'Office visit, established, minimal complexity (5 min)',  'Office visit lvl 1 est',  1, 0),
(9002, 1, '99212', 'Office visit, established, brief (10 min)',              'Office visit lvl 2 est',  1, 0),
(9003, 1, '99213', 'Office visit, established, low complexity (15 min)',    'Office visit lvl 3 est',  1, 0),
(9004, 1, '99214', 'Office visit, established, moderate complexity (25 min)','Office visit lvl 4 est', 1, 0),
(9005, 1, '99215', 'Office visit, established, high complexity (40 min)',   'Office visit lvl 5 est',  1, 0),

-- E/M Office Visits — New Patient
(9006, 1, '99201', 'Office visit, new patient, minimal (10 min)',           'Office visit lvl 1 new',  1, 0),
(9007, 1, '99202', 'Office visit, new patient, brief (20 min)',             'Office visit lvl 2 new',  1, 0),
(9008, 1, '99203', 'Office visit, new patient, low complexity (30 min)',    'Office visit lvl 3 new',  1, 0),
(9009, 1, '99204', 'Office visit, new patient, moderate complexity (45 min)','Office visit lvl 4 new', 1, 0),
(9010, 1, '99205', 'Office visit, new patient, high complexity (60 min)',   'Office visit lvl 5 new',  1, 0),

-- Preventive visits
(9011, 1, '99385', 'Preventive visit, new patient, ages 18-39',             'Preventive new 18-39',    1, 0),
(9012, 1, '99386', 'Preventive visit, new patient, ages 40-64',             'Preventive new 40-64',    1, 0),
(9013, 1, '99395', 'Preventive visit, established, ages 18-39',             'Preventive est 18-39',    1, 0),
(9014, 1, '99396', 'Preventive visit, established, ages 40-64',             'Preventive est 40-64',    1, 0),

-- Common lab tests
(9015, 1, '85025', 'Complete blood count (CBC) with differential',          'CBC w/diff',              1, 0),
(9016, 1, '80053', 'Comprehensive metabolic panel (CMP)',                   'CMP',                     1, 0),
(9017, 1, '80061', 'Lipid panel',                                           'Lipid panel',             1, 0),
(9018, 1, '83036', 'Hemoglobin A1c (diabetes)',                             'HbA1c',                   1, 0),
(9019, 1, '84443', 'Thyroid stimulating hormone (TSH)',                     'TSH',                     1, 0),
(9020, 1, '81001', 'Urinalysis, automated, with microscopy',                'Urinalysis',              1, 0),
(9021, 1, '87880', 'Strep A rapid test',                                    'Strep A test',            1, 0),
(9022, 1, '87804', 'Influenza A/B rapid test',                              'Flu rapid',               1, 0),
(9023, 1, '86703', 'HIV antibody screen',                                   'HIV screen',              1, 0),
(9024, 1, '82270', 'Fecal occult blood test',                               'FOBT',                    1, 0),

-- Cardiac / diagnostic procedures
(9025, 1, '93000', 'ECG / EKG, 12 lead with interpretation',                'ECG 12-lead',             1, 0),
(9026, 1, '93005', 'ECG, tracing only, no interpretation',                  'ECG tracing only',        1, 0),
(9027, 1, '93306', 'Echocardiogram, complete with Doppler',                 'Echo complete',           1, 0),

-- Vaccines (administration codes)
(9028, 1, '90471', 'Vaccine administration, one injection',                 'Vaccine admin x1',        1, 0),
(9029, 1, '90472', 'Vaccine administration, each additional',               'Vaccine admin each addl', 1, 0),
(9030, 1, '90686', 'Flu vaccine, quadrivalent, IM',                         'Flu vax quad',            1, 0),

-- Procedures
(9031, 1, '12001', 'Simple repair, scalp/neck/axillae, <2.5cm',             'Simple wound repair',     1, 0),
(9032, 1, '17110', 'Destruction of benign lesions, <14 lesions',            'Lesion destruction',      1, 0),
(9033, 1, '20610', 'Joint injection, major joint',                          'Joint injection',         1, 0),
(9034, 1, '69210', 'Cerumen (ear wax) removal',                             'Ear wax removal',         1, 0);

-- ─────── HCPCS CODES (code_type=3) ───────

INSERT INTO codes (id, code_type, code, code_text, code_text_short, active, financial_reporting) VALUES
(9050, 3, 'J3490', 'Unclassified drug (injectable)',                        'Unclassified inj drug',   1, 0),
(9051, 3, 'J1885', 'Ketorolac (Toradol) injection',                         'Toradol inj',             1, 0),
(9052, 3, 'A4253', 'Glucose test strips, box of 50',                        'Glucose strips',          1, 0),
(9053, 3, 'E0118', 'Crutches, underarm, adjustable, pair',                  'Crutches',                1, 0),
(9054, 3, 'A4550', 'Surgical tray',                                         'Surgical tray',           1, 0);

-- ─────── ICD-10 DIAGNOSIS CODES (code_type=102) ───────

INSERT INTO codes (id, code_type, code, code_text, code_text_short, active, financial_reporting) VALUES
-- Routine / preventive
(9100, 102, 'Z00.00', 'Encounter for general adult medical exam',          'Adult medical exam',      1, 0),
(9101, 102, 'Z01.419','Encounter for gyn exam',                            'Gyn exam',                1, 0),
(9102, 102, 'Z23',    'Encounter for immunization',                         'Immunization',            1, 0),

-- Common infections
(9103, 102, 'J02.9',  'Acute pharyngitis, unspecified (sore throat)',      'Sore throat',             1, 0),
(9104, 102, 'J06.9',  'Acute upper respiratory infection, unspecified',     'Upper resp infection',    1, 0),
(9105, 102, 'J11.1',  'Influenza with respiratory manifestations',          'Flu',                     1, 0),
(9106, 102, 'J20.9',  'Acute bronchitis, unspecified',                      'Bronchitis',              1, 0),
(9107, 102, 'N39.0',  'Urinary tract infection (UTI), site unspecified',    'UTI',                     1, 0),

-- Chronic conditions
(9108, 102, 'I10',    'Essential (primary) hypertension',                   'Hypertension',            1, 0),
(9109, 102, 'E11.9',  'Type 2 diabetes mellitus, no complications',         'Type 2 diabetes',         1, 0),
(9110, 102, 'E78.5',  'Hyperlipidemia (high cholesterol)',                  'High cholesterol',        1, 0),
(9111, 102, 'E03.9',  'Hypothyroidism, unspecified',                        'Hypothyroidism',          1, 0),
(9112, 102, 'E66.9',  'Obesity, unspecified',                               'Obesity',                 1, 0),

-- Pain / musculoskeletal
(9113, 102, 'M54.5',  'Low back pain',                                      'Low back pain',           1, 0),
(9114, 102, 'M25.50', 'Joint pain, unspecified',                            'Joint pain',              1, 0),
(9115, 102, 'R51',    'Headache',                                           'Headache',                1, 0),
(9116, 102, 'R07.9',  'Chest pain, unspecified',                            'Chest pain',              1, 0),

-- GI
(9117, 102, 'K21.9',  'GERD without esophagitis',                           'GERD',                    1, 0),
(9118, 102, 'R10.9',  'Abdominal pain, unspecified',                        'Abdominal pain',          1, 0),
(9119, 102, 'K59.00', 'Constipation, unspecified',                          'Constipation',            1, 0),

-- Mental health
(9120, 102, 'F41.9',  'Anxiety disorder, unspecified',                      'Anxiety',                 1, 0),
(9121, 102, 'F32.9',  'Major depressive disorder, unspecified',             'Depression',              1, 0),
(9122, 102, 'G47.00', 'Insomnia, unspecified',                              'Insomnia',                1, 0),

-- Skin
(9123, 102, 'L70.0',  'Acne vulgaris',                                      'Acne',                    1, 0),
(9124, 102, 'L30.9',  'Dermatitis, unspecified',                            'Dermatitis',              1, 0),

-- Women's health
(9125, 102, 'Z39.2',  'Encounter for routine postpartum follow-up',         'Postpartum follow-up',    1, 0),
(9126, 102, 'N91.2',  'Amenorrhea, unspecified',                            'Amenorrhea',              1, 0),

-- Pediatric
(9127, 102, 'H66.90', 'Otitis media (ear infection), unspecified',          'Ear infection',           1, 0),
(9128, 102, 'B34.9',  'Viral infection, unspecified',                       'Viral infection',         1, 0);

-- Verify
SELECT code_type, COUNT(*) AS code_count
FROM codes
GROUP BY code_type
ORDER BY code_type;

SELECT 'Sample CPT' AS info, code, code_text_short FROM codes WHERE code_type=1   LIMIT 5;
SELECT 'Sample ICD' AS info, code, code_text_short FROM codes WHERE code_type=102 LIMIT 5;
