-- ============================================================
-- Update facilities with proper realistic data
-- Both serve as Service AND Billing locations
-- ============================================================

-- Facility 1: CSAB Clinic → MatrixCMS Andheri Clinic
UPDATE facility SET
    name             = 'MatrixCMS Andheri Clinic',
    phone            = '022-2670-4500',
    fax              = '022-2670-4501',
    street           = 'Plot 21, Saki Vihar Road',
    city             = 'Mumbai',
    state            = 'MH',
    postal_code      = '400072',
    country_code     = 'IN',
    federal_ein      = '27ABCDE1234F1Z5',
    website          = 'https://matrixcms.in/andheri',
    email            = 'andheri@matrixcms.in',
    service_location = 1,
    billing_location = 1,
    accepts_assignment = 1,
    pos_code         = 11,
    attn             = 'Reception',
    facility_npi     = '1234567890',
    facility_taxonomy= '207Q00000X',
    tax_id_type      = 'EI',
    color            = '#1976d2',
    primary_business_entity = 1,
    facility_code    = 'AND',
    extra_validation = 1,
    mail_street      = 'Plot 21, Saki Vihar Rd',
    mail_city        = 'Mumbai',
    mail_state       = 'MH',
    mail_zip         = '400072',
    info             = 'Andheri East Branch — primary care and family medicine',
    iban             = '',
    inactive         = 0
WHERE id = 3;

-- Facility 2: Matrix Health Clinic → MatrixCMS Bandra Clinic
UPDATE facility SET
    name             = 'MatrixCMS Bandra Clinic',
    phone            = '022-2640-9300',
    fax              = '022-2640-9301',
    street           = '14 Hill Road, Bandra West',
    city             = 'Mumbai',
    state            = 'MH',
    postal_code      = '400050',
    country_code     = 'IN',
    federal_ein      = '27FGHIJ5678K2L9',
    website          = 'https://matrixcms.in/bandra',
    email            = 'bandra@matrixcms.in',
    service_location = 1,
    billing_location = 1,
    accepts_assignment = 1,
    pos_code         = 11,
    attn             = 'Reception',
    facility_npi     = '9876543210',
    facility_taxonomy= '207RC0000X',
    tax_id_type      = 'EI',
    color            = '#cc0000',
    primary_business_entity = 1,
    facility_code    = 'BAN',
    extra_validation = 1,
    mail_street      = '14 Hill Road',
    mail_city        = 'Mumbai',
    mail_state       = 'MH',
    mail_zip         = '400050',
    info             = 'Bandra West Branch — cardiology and specialist services',
    iban             = '',
    inactive         = 0
WHERE id = 4;

-- Keep users' facility name in sync with the renamed facilities
UPDATE users u
JOIN facility f ON f.id = u.facility_id
SET u.facility = f.name;

-- Verify
SELECT id, name, phone, city, state, postal_code, facility_npi, pos_code, color,
       service_location, billing_location
FROM facility ORDER BY id;
