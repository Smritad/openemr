-- =====================================================================
-- DEMO LAB / PROCEDURES SETUP  (MatrixCMS / OpenEMR)
-- Populates the Procedures menu so it is easy to demo:
--   1) Procedure Provider (the lab vendor)
--   2) Configure Orders and Results compendium  (Lab > CBC > WBC/HGB/PLT)
--   3) Links Rajesh Mehta's existing lab order to that lab
--   4) Adds the missing forms row so the order shows in Pending Review /
--      Patient Results / Procedure Orders and Reports
-- Run:  mysql ... openemr < _docs/seed_lab_setup.sql
-- =====================================================================
SET @prov = 11;   -- dr.smith (lab director / ordering provider)
SET @now  = NOW();

-- ---- 1) PROCEDURE PROVIDER (Procedures > Procedure Providers) -------
INSERT INTO procedure_providers
    (uuid, name, npi, DorP, direction, protocol, type, lab_director, active,
     notes, date_created)
VALUES
    (UNHEX(REPLACE(UUID(),'-','')), 'MatrixCMS Diagnostics Lab', '1990000099', 'P', 'B', 'HL7',
     'laboratory', @prov, 1, 'In-house demo laboratory', @now);
SET @ppid = LAST_INSERT_ID();

-- ---- 2) COMPENDIUM (Procedures > Configure Orders and Results) ------
-- Tier 1: the Laboratory
INSERT INTO procedure_type
    (parent, name, lab_id, procedure_type, procedure_code, seq, activity)
VALUES
    (0, 'MatrixCMS Diagnostics Lab', @ppid, 'lab', '', 10, 1);
SET @lab_node = LAST_INSERT_ID();

-- Tier 2: an orderable panel (CBC)
INSERT INTO procedure_type
    (parent, name, lab_id, procedure_type, procedure_code, standard_code, description, seq, activity)
VALUES
    (@lab_node, 'Complete Blood Count', @ppid, 'ord', 'CBC', 'LOINC:58410-2',
     'CBC panel with WBC, Hemoglobin and Platelets', 10, 1);
SET @cbc_node = LAST_INSERT_ID();

-- Tier 3: the discrete results inside CBC
INSERT INTO procedure_type
    (parent, name, lab_id, procedure_type, procedure_code, units, `range`, seq, activity)
VALUES
    (@cbc_node, 'White Blood Cell Count', @ppid, 'res', 'WBC', '10^3/uL', '4.0-11.0', 10, 1),
    (@cbc_node, 'Hemoglobin',             @ppid, 'res', 'HGB', 'g/dL',    '13.0-17.0', 20, 1),
    (@cbc_node, 'Platelet Count',         @ppid, 'res', 'PLT', '10^3/uL', '150-400',  30, 1);

-- Hide the stray orphan result row (no name/code) that showed the red warning
UPDATE procedure_type SET activity = 0
 WHERE procedure_type_id = 1 AND (name IS NULL OR name = '') AND parent = 0;

-- ---- 3) LINK Rajesh Mehta's order (id 1) to this lab ---------------
UPDATE procedure_order  SET lab_id = @ppid WHERE procedure_order_id = 1;

-- ---- 4) FORMS row so the order appears in the encounter / reviews --
INSERT INTO forms
    (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir, provider_id)
VALUES
    (@now, 34, 'Procedure Order', 1, 23, 'dr.smith', 'Default', 1, 0, 'procedure_order', @prov);

SELECT 'lab setup done' AS status, @ppid AS provider_id, @lab_node AS lab_node, @cbc_node AS cbc_node;
