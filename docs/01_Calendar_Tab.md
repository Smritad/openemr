# OpenCMS — Tab 01: Calendar

> **Audience:** OpenCMS sales & implementation team.
> **Goal of this doc:** End-to-end explanation of the Calendar tab — what it is, what tables it writes to, what APIs read from it, and how it ties into every other tab in the product.
> **Doc family:** This is one in a series; each top-menu tab gets its own file (01_Calendar, 02_Finder, 03_Flow, 04_Recalls, 05_Messages, 06_Patient, 07_Fees, 08_Modules, 09_Procedures, 10_Admin).

---

## 1. One-line pitch (for the demo)

> *"The Calendar is the front door of the clinic — every patient visit, every provider's day, every billable encounter starts here. One click on a slot creates the appointment **and** queues the visit for the front desk, the doctor's flow board, and the billing department, all in one transaction."*

---

## 2. What the user sees

When the user clicks **Calendar** in the top menu, the iframe loads:

```
http://localhost/open_cms/interface/main/calendar/index.php
```

Three views (toggled by the icons above the grid):

| View | Template | What it shows |
|---|---|---|
| **Day** | `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/day/ajax_template.html` | One day, columns per provider, half-hour rows. |
| **Week** | `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/week/ajax_template.html` | 7 days × providers grid. |
| **Month** | `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/month/ajax_template.html` | Monthly overview, dots per appointment. |

The colour of each appointment block comes directly from the **category** (table `openemr_postcalendar_categories.pc_catcolor`). The "Today" cell is the red pill defined in `interface/themes/colors/style_matrix_bricks.scss` section 29.

---

## 3. The data model

### 3.1 Primary table: `openemr_postcalendar_events`

This is the **single source of truth** for every appointment, recurring slot, blocked time, and provider-day record.

| Column | Type | Purpose |
|---|---|---|
| `pc_eid` | int PK | Appointment ID (event id). Referenced everywhere — encounters, billing, reminders. |
| `pc_catid` | int FK → `openemr_postcalendar_categories.pc_catid` | Category (Office Visit, New Patient, No Show, Lunch, Holiday…). Drives colour. |
| `pc_pid` | varchar | **Patient ID** (joins to `patient_data.pid`). NULL for non-patient events like Holiday/Lunch. |
| `pc_aid` | varchar | **Provider/Attending ID** (joins to `users.id`). |
| `pc_gid` | int | Group ID (for group-therapy appointments). |
| `pc_title` | varchar | Free-text label shown on the block. |
| `pc_eventDate` | date | Calendar day. |
| `pc_endDate` | date | End day (same as start for non-recurring). |
| `pc_startTime` / `pc_endTime` | time | Slot bounds. |
| `pc_duration` | bigint | Duration in **seconds**. |
| `pc_alldayevent` | int | 1 = full-day block (holiday, vacation). |
| `pc_recurrtype` | int | 0=none, 1=daily, 2=weekly, 3=monthly. |
| `pc_recurrspec` | text | Serialized rule (e.g. `{"event_repeat_freq":"1","event_repeat_freq_type":"2"}`). |
| `pc_apptstatus` | varchar(15) | Status code from list `apptstat` — `^` Arrived, `@` In Room, `>` Out, `~` Cancelled, `-` Pending. **Drives the Flow board.** |
| `pc_facility` | int | Facility ID — feeds the billing facility on encounter creation. |
| `pc_billing_location` | smallint | Billing facility (can differ from `pc_facility`). |
| `pc_room` | varchar(20) | Exam room. |
| `pc_sendalertsms` / `pc_sendalertemail` | varchar(3) | YES/NO — drives the SMS/email reminder cron. |
| `uuid` | binary(16) | External UUID exposed via FHIR `Appointment.id`. |

### 3.2 Categories: `openemr_postcalendar_categories`

The 15 colour-coded categories (already seeded in your DB):

| ID | Code | Name | Colour |
|----|------|------|--------|
| 1  | no_show | No Show | #dee2e6 grey |
| 2  | in_office | In Office | #cce5ff light blue |
| 3  | out_of_office | Out Of Office | #fdb172 orange |
| 4  | vacation | Vacation | #e9ecef grey |
| 5  | office_visit | Office Visit | #ffecb4 yellow *(default)* |
| 6  | holidays | Holidays | #8663ba purple |
| 7  | closed | Closed | #2374ab dark blue |
| 8  | lunch | Lunch | #ffd351 amber |
| 9  | established_patient | Established Patient | #93d3a2 green |
| 10 | new_patient | New Patient | #a2d9e2 teal |
| 11 | reserved | Reserved | #b02a37 red |
| 12 | health_and_behavioral_assessment | Health & Behavioural | #ced4da grey |
| 13 | preventive_care_services | Preventive Care | #d3c6ec lavender |
| 14 | ophthalmological_services | Ophthalmological | #febe89 peach |
| 15 | group_therapy | Group Therapy | #adb5bd grey |

To add a custom category, INSERT into `openemr_postcalendar_categories` — no code change needed.

### 3.3 Linked side-tables

| Table | Why it matters |
|---|---|
| `patient_tracker` | One row per arrived patient per day. Drives the **Flow** tab (vital signs queue → exam → checkout). Auto-created when `pc_apptstatus` flips to `^` Arrived. |
| `patient_tracker_element` | Audit trail of every status change for a given tracker row (e.g. Arrived 09:55 → In Room 10:10 → Out 10:35). |
| `form_encounter` | When a provider clicks "Begin Encounter" on a calendar block, OpenCMS inserts a row here and stamps it with `pc_eid`. **This is the bridge between Calendar and Fees.** |
| `recall` | Future-dated appointments created from the Recalls tab — also write back into `openemr_postcalendar_events` once confirmed. |
| `users` (provider) | `pc_aid` joins to `users.id`. Provider colour palette comes from `users.calendar_color`. |
| `facility` | `pc_facility` joins to `facility.id`. Used for filtering and for the default billing facility. |

---

## 4. The files behind the tab

| File | Role |
|---|---|
| `interface/main/calendar/index.php` | **Entry point.** Sets up session vars (selected provider, facility), then loads the PostCalendar module. |
| `interface/main/calendar/add_edit_event.php` | The big appointment **create/edit dialog**. ~1500 lines. Handles validation, recurrence, ACL, fires events. |
| `interface/main/calendar/find_patient_popup.php` | Patient picker inside the add-event dialog. |
| `interface/main/calendar/find_appt_popup.php` | "Find available slot" search across providers. |
| `interface/main/calendar/modules/PostCalendar/pnuserapi.php` | The **read-side API** — `postcalendar_userapi_pcGetEvents()` returns the events for a given range/provider/facility. Called by the grid renderer. |
| `interface/main/calendar/modules/PostCalendar/pnadminapi.php` | The **write-side API** — `postcalendar_adminapi_submitEvent()` inserts/updates an event. |
| `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/*` | Smarty templates that produce the HTML grid. |
| `library/calendar.inc.php` | Shared helpers — slot-conflict detection, `fetchAppointments()` used by Patient Summary and the API. |

---

## 5. The PHP service layer

### `OpenEMR\Services\AppointmentService` ([src/Services/AppointmentService.php](../src/Services/AppointmentService.php))

Modern PSR-4 service used by REST and FHIR endpoints. Key methods:

| Method | Returns | Used by |
|---|---|---|
| `getAppointment($eid)` | Single appointment row | `GET /api/appointment/{eid}` |
| `getAppointmentsForPatient($pid)` | All appointments for one patient | `GET /api/patient/{pid}/appointment` |
| `insert($pid, $data)` | New `pc_eid` | `POST /api/patient/{pid}/appointment` |
| `validate($data)` | `ProcessingResult` with errors | Pre-insert validation |
| `deleteAppointmentRecord($eid)` | void | `DELETE /api/patient/{pid}/appointment/{eid}` |
| `search($filters)` | Paginated result | FHIR `Appointment` search |

### Legacy procedural layer

The Smarty grid still uses the **PostCalendar** functions (`postcalendar_userapi_pcGetEvents`). Both layers write to the same `openemr_postcalendar_events` table — there is no duplication, just two readers.

---

## 6. The REST APIs your team can call

> Base URL: `http://localhost/open_cms/apis/default/api`
> Auth: OAuth2 bearer token from `/oauth2/default/token` with scope `user/appointment.cruds`.

### 6.1 REST (OpenEMR-native) — defined in [`apis/routes/_rest_routes_standard.inc.php`](../apis/routes/_rest_routes_standard.inc.php)

| Method | Path | What it does | Line |
|---|---|---|---|
| `GET`    | `/api/appointment` | List all appointments (admin) | 4951 |
| `GET`    | `/api/appointment/:eid` | Get one appointment by `pc_eid` | 4987 |
| `GET`    | `/api/patient/:pid/appointment` | All appointments for one patient | 4816 |
| `GET`    | `/api/patient/:pid/appointment/:eid` | One appointment, restricted to patient | 5077 |
| `POST`   | `/api/patient/:pid/appointment` | Create a new appointment | 4923 |
| `DELETE` | `/api/patient/:pid/appointment/:eid` | Cancel/delete an appointment | 5032 |

### 6.2 FHIR R4 — defined in [`apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php`](../apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php)

| Method | Path | What it does |
|---|---|---|
| `GET` | `/fhir/Appointment` | Search appointments (FHIR R4) |
| `GET` | `/fhir/Appointment/{id}` | Read one by UUID |

The FHIR resource is built in [`src/Services/FHIR/FhirAppointmentService.php`](../src/Services/FHIR/FhirAppointmentService.php). This is what **third-party patient portals, hospital integrations, and HealthLake-style data lakes** consume.

### 6.3 Portal (patient-facing) — [`apis/routes/_rest_routes_portal.inc.php`](../apis/routes/_rest_routes_portal.inc.php)

Patients with portal access can `GET /api/portal/patient/appointment` to see their own bookings (scope `patient/appointment.read`).

### 6.4 Sample POST body

```json
POST /api/patient/4/appointment
Authorization: Bearer <token>
Content-Type: application/json

{
  "pc_catid": 9,                 // Established Patient
  "pc_title": "Diabetes follow-up",
  "pc_duration": 1800,           // seconds (= 30 min)
  "pc_hometext": "Reviewing HbA1c results",
  "pc_eventDate": "2026-05-30",
  "pc_startTime": "10:00",
  "pc_facility": 3,
  "pc_billing_location": 3,
  "pc_aid": 1                    // provider id
}
```

Response: `{ "id": 17 }` — the new `pc_eid`.

---

## 7. Permissions (ACL)

Configured in **Admin → ACL Administration**. The Calendar checks:

| ACL section → value | Required to… |
|---|---|
| `patients → appt` (`write`, `wsome`) | Create/edit any appointment |
| `patients → med` (`view`, `viewsome`) | See appointment details |
| `admin → super` | Create category, edit holidays |

Source: [`interface/main/calendar/add_edit_event.php:63`](../interface/main/calendar/add_edit_event.php#L63).

---

## 8. Events fired (for module developers)

The Calendar emits Symfony events that custom modules can listen to:

| Event class | When |
|---|---|
| `AppointmentSetEvent` | After successful insert/update of an appointment |
| `AppointmentRenderEvent` | When the dialog is being built — modules can inject extra fields |
| `AppointmentDialogCloseEvent` | After the dialog closes — used for SMS/email reminder dispatch |

Located in `src/Events/Appointments/`. Listen via `EventDispatcher::addListener()` in your custom module's `Bootstrap.php`.

---

## 9. End-to-end flow (the demo flow)

```
┌──────────────────────────────────────────────────────────────────────┐
│  USER ACTION                STATE CHANGE                  TABLE       │
├──────────────────────────────────────────────────────────────────────┤
│  1. Receptionist clicks   ──► dialog opens                            │
│     empty 10:00 slot         (add_edit_event.php)                     │
│                                                                       │
│  2. Picks patient Amit    ──► pc_pid=4, pc_aid=1                      │
│     + category "Estab."      pc_catid=9                               │
│                                                                       │
│  3. Clicks Save           ──► INSERT row                  openemr_    │
│                              + AppointmentSetEvent       postcalendar │
│                                fires                      _events     │
│                                                                       │
│  4. Patient arrives, FD   ──► UPDATE pc_apptstatus='^'                │
│     clicks "Arrived"          (Arrived)                               │
│                                                                       │
│  5. Auto-creates tracker  ──► INSERT row                 patient_     │
│                                                          tracker      │
│                                                                       │
│  6. Doctor clicks         ──► INSERT row                 form_        │
│     "Begin Encounter"        (encounter linked to        encounter    │
│                              pc_eid via the form)                     │
│                                                                       │
│  7. Doctor opens Fee      ──► INSERT CPT/ICD rows        billing      │
│     Sheet, adds 99214                                                 │
│                                                                       │
│  8. Front desk Checkout   ──► INSERT payment             ar_session,  │
│                                                          ar_activity  │
│                                                                       │
│  9. Billing Manager       ──► flagged for claim          claims       │
│     submits to insurer                                                │
└──────────────────────────────────────────────────────────────────────┘
```

The `pc_eid` (appointment ID) is the **golden thread** that ties together every downstream tab: Flow, Patient, Fees, Reports.

---

## 10. How Calendar connects to every other tab

| Other tab | The connection |
|---|---|
| **Finder** | Patient picker inside the appointment dialog (`find_patient_popup.php`) is the same widget Finder uses. |
| **Flow** | Every arrived appointment (`pc_apptstatus='^'`) becomes a row in `patient_tracker`, which is what Flow displays. |
| **Recalls** | Recall reminders create future `openemr_postcalendar_events` rows when the patient confirms. |
| **Messages** | "Send reminder" button on the dialog enqueues SMS/email via `notification_log`. |
| **Patient** | Patient summary page shows upcoming appointments by querying `openemr_postcalendar_events WHERE pc_pid = ?`. |
| **Fees** | Encounter row created from the calendar carries `pc_eid`, `pc_facility`, and `pc_billing_location` into the billing pipeline. |
| **Modules** | Custom modules subscribe to `AppointmentSetEvent` to push to external systems (e.g. WhatsApp reminders, Google Calendar sync). |
| **Procedures** | Procedure orders can be linked to the originating appointment via `pc_eid`. |
| **Admin** | Holidays, categories, default visit duration, "remind me X hours before" — all configured under **Admin → Globals → Calendar**. |

---

## 11. Configuration globals (Admin → Globals → Calendar)

| Global | Default | What it controls |
|---|---|---|
| `schedule_start` | 8 | Earliest hour shown on the grid |
| `schedule_end` | 17 | Latest hour shown |
| `calendar_interval` | 15 | Grid row size in minutes |
| `default_visit_category` | 5 (Office Visit) | Auto-selected category in the dialog |
| `event_color` | by category | Override colour scheme |
| `time_display_format` | 0 (24h) / 1 (12h) | AM/PM vs 24-hour |
| `appt_display_sets` | 1 | Group appointments by provider in week view |
| `auto_create_new_encounters` | 1 | If 1, "Begin Encounter" is automatic on Arrived |
| `recalls_enabled` | 1 | Enable recall reminders |

---

## 12. Talking points for the demo

1. **"One screen drives the whole clinic."** Booking, status, billing handoff — all from this view.
2. **Colour-coded by category.** A nurse can glance at the day and see Office Visits (yellow) vs Holidays (purple) vs New Patients (teal).
3. **Multi-provider, multi-facility.** Filter dropdown at the top — switch providers without changing screens.
4. **No double-bookings.** Slot-conflict check in `library/calendar.inc.php` blocks overlap before the row is written.
5. **Recurring slots in one click.** Daily, weekly, monthly recurrence using the `pc_recurrtype` + `pc_recurrspec` JSON rule.
6. **Auto-encounter on arrival.** When the front desk marks Arrived, the doctor's encounter is already waiting — no extra clicks.
7. **FHIR-native.** Any third party (insurance portal, lab, hospital HIS) can pull the schedule via FHIR `/Appointment`.
8. **Audit trail.** Every status change is logged in `patient_tracker_element` with timestamp and user.

---

## 13. Demo script (90 seconds)

> 1. *"Here's today's calendar — three providers, colour-coded by visit type."*
> 2. *Click empty 10:30 slot under Dr. Administrator.*
> 3. *"Pick the patient — let's grab Amit Patel — set category Established Patient. Save."*
> 4. *Block appears in green.*
> 5. *"Patient walks in — front desk clicks Arrived."*
> 6. *Block flips to bold border. **Behind the scenes** a `patient_tracker` row is created — that's what feeds the Flow board over there."*
> 7. *Switch to Patient tab → upcoming appointments list shows the new booking.*
> 8. *"And because this fires our `AppointmentSetEvent`, a custom WhatsApp module we built sends Amit a confirmation in 2 seconds."*

---

## 14. Where the data goes — visual summary

```
                          ┌─────────────┐
                          │  CALENDAR   │
                          │   (grid)    │
                          └──────┬──────┘
                                 │  user clicks slot
                                 ▼
              ┌──────────────────────────────────────┐
              │  openemr_postcalendar_events         │
              │  (single source of truth)            │
              └──┬──────┬───────┬───────┬───────┬────┘
                 │      │       │       │       │
                 ▼      ▼       ▼       ▼       ▼
            ┌──────┐ ┌────┐ ┌─────┐ ┌───────┐ ┌─────┐
            │Patient│ │Flow│ │Fees │ │Reports│ │ FHIR │
            │summary│ │board│ │(via │ │       │ │ API  │
            │       │ │     │ │ enc)│ │       │ │      │
            └──────┘ └────┘ └─────┘ └───────┘ └─────┘
```

---

## 15. Quick reference — files cheat sheet

```
URL entry         : /interface/main/calendar/index.php
Add/edit dialog   : /interface/main/calendar/add_edit_event.php
Read API (legacy) : /interface/main/calendar/modules/PostCalendar/pnuserapi.php
Write API (legacy): /interface/main/calendar/modules/PostCalendar/pnadminapi.php
PSR-4 service     : /src/Services/AppointmentService.php
REST controller   : /src/RestControllers/AppointmentRestController.php
FHIR controller   : /src/RestControllers/FHIR/FhirAppointmentRestController.php
REST routes       : /apis/routes/_rest_routes_standard.inc.php  (lines 4789-5077)
FHIR routes       : /apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php
DB table          : openemr_postcalendar_events
DB categories     : openemr_postcalendar_categories
Tracker bridge    : patient_tracker, patient_tracker_element
ACL keys          : patients → appt (write, wsome)
Smarty templates  : /interface/main/calendar/modules/PostCalendar/pntemplates/default/views/
Events            : OpenEMR\Events\Appointments\{AppointmentSetEvent, AppointmentRenderEvent, AppointmentDialogCloseEvent}
```

---

*End of Tab 01 — Calendar. Next file in the series will be `02_Finder_Tab.md`.*
