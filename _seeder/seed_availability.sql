-- ============================================================
-- Provider availability (In Office hours) seeder
-- Sets Dr. Smith and Dr. Kumar as available Mon-Fri 9 AM - 5 PM
-- Recurring every week for the next year
-- ============================================================

-- Cleanup any previous availability rows for these providers
DELETE FROM openemr_postcalendar_events
WHERE pc_aid IN (
    SELECT id FROM users WHERE username IN ('dr.smith', 'dr.kumar')
)
AND pc_catid = 2          -- 2 = In Office category
AND pc_pid IS NULL;       -- only delete provider events (no patient)

-- Dr. Smith — Mon-Fri 9 AM to 5 PM at Andheri Clinic (8-hour blocks)
INSERT INTO openemr_postcalendar_events
    (pc_catid, pc_aid, pc_pid, pc_title, pc_eventDate, pc_endDate,
     pc_duration, pc_startTime, pc_endTime, pc_alldayevent,
     pc_facility, pc_billing_location, pc_apptstatus, pc_eventstatus,
     pc_sharing, pc_time, pc_topic, pc_recurrtype, pc_recurrfreq,
     pc_recurrspec)
VALUES
    (2,
     (SELECT id FROM users WHERE username='dr.smith'),
     NULL, 'In Office',
     '2026-05-25',         -- start Monday of last week
     '2027-12-31',         -- end far in future
     28800,                -- 8 hours (8 * 3600)
     '09:00:00', '17:00:00', 0,
     (SELECT id FROM facility WHERE facility_code='AND'),
     (SELECT id FROM facility WHERE facility_code='AND'),
     '-', 1, 1, NOW(), 1,
     1,                    -- recurrtype: 1 = recurring
     0,
     'a:6:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"3";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";s:6:"exdate";s:0:"";}');

-- Dr. Kumar — Mon-Fri 10 AM to 6 PM at Bandra Clinic (8-hour blocks)
INSERT INTO openemr_postcalendar_events
    (pc_catid, pc_aid, pc_pid, pc_title, pc_eventDate, pc_endDate,
     pc_duration, pc_startTime, pc_endTime, pc_alldayevent,
     pc_facility, pc_billing_location, pc_apptstatus, pc_eventstatus,
     pc_sharing, pc_time, pc_topic, pc_recurrtype, pc_recurrfreq,
     pc_recurrspec)
VALUES
    (2,
     (SELECT id FROM users WHERE username='dr.kumar'),
     NULL, 'In Office',
     '2026-05-25',         -- start same day
     '2027-12-31',
     28800,                -- 8 hours
     '10:00:00', '18:00:00', 0,
     (SELECT id FROM facility WHERE facility_code='BAN'),
     (SELECT id FROM facility WHERE facility_code='BAN'),
     '-', 1, 1, NOW(), 1,
     1,
     0,
     'a:6:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"3";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";s:6:"exdate";s:0:"";}');

-- Verify
SELECT u.username,
       pc_eventDate AS first_date,
       pc_startTime AS start, pc_endTime AS end_t,
       pc_recurrtype AS recurs,
       (SELECT name FROM facility WHERE id=pc_facility) AS facility
FROM openemr_postcalendar_events e
JOIN users u ON u.id = e.pc_aid
WHERE pc_catid = 2 AND pc_pid IS NULL
ORDER BY u.username;
