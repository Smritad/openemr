# OpenEMR — Tab / Module Reference Guide

A walkthrough of every top-level menu in this OpenEMR build (Matrix Bricks
brand). For each tab you get **what it is**, **who uses it**, **the most
common workflow**, and **what to point at during a demo**. Use it as the
script for a guided walkthrough.

**Build details**
- Application: OpenEMR with Matrix Bricks branding
- URL: <http://localhost/open_cms/interface/login/login.php>
- Login: `admin` / `pass`
- Demo patients seeded (pids 2–6): Rajesh Kumar, Priya Sharma, Amit Patel,
  Neha Gupta, Vikram Singh — all wired with encounters, vitals,
  prescriptions, billing codes, and insurance.

---

## 1. Calendar

**What it is**
The scheduling grid. Day / Week / Month view of every appointment across
providers and rooms.

**Who uses it**
Receptionists book and reschedule. Doctors see the day's queue.

**Demo flow**
1. Open *Calendar* — see five upcoming appointments seeded for the demo
   patients (Lab review, BP recheck, HbA1c follow-up, Throat culture
   result, Physiotherapy referral).
2. Click any empty slot → an "Add Event" dialog opens to book a new
   appointment with category, status, room and recurrence.
3. Click an existing slot → quick actions (check-in, edit, delete).

**Talking point for boss**
> "This is the heartbeat — every front-desk action starts here."

---

## 2. Finder

**What it is**
A patient search grid with live filtering across name, DOB, phone, ID.

**Demo flow**
1. Type "kum" → Rajesh Kumar appears. Click → opens his chart.
2. Sort by column header; filter by status.

**Talking point**
> "Type two letters of any field and the patient list narrows in real-time."

---

## 3. Flow Board

**What it is**
Real-time visual board of patients currently in the clinic — checked-in,
with provider, in exam room, billed, checked-out. Like an airport
departures board for a clinic floor.

**Who uses it**
Nurses and receptionists running the day.

**Demo flow**
1. From Calendar, check a patient in → they appear on Flow Board.
2. Move status: *Waiting → In Exam → With Doctor → Checkout*.

**Talking point**
> "Anyone walking past the front desk knows who is waiting and how long."

---

## 4. Recalls

**What it is**
Reminders for patients who need to come back (annual physical, vaccine,
follow-up). Generates printable / mailable / SMS lists.

**Demo flow**
1. *Recalls* → filter by date range and recall type.
2. Print or export a CSV for outbound calls.

**Talking point**
> "This is how a clinic keeps revenue predictable — no one falls through
> the cracks."

---

## 5. Messages

**What it is**
The internal inbox. Patient phone messages, lab result notifications,
intra-staff notes, "while you were out" slips — all routed to the right
provider.

**Demo flow**
1. Compose → To: `admin` → Subject: *Test message* → Send.
2. Refresh — see the message in admin's inbox; reply / forward / archive.

**Talking point**
> "Replaces sticky notes on a monitor. Every message is auditable and
> linked to the patient."

---

## 6. Patient (Module)

The clinical core. Submenu items:

### 6.1 New / Search
Register a new patient or pull up an existing one. Demographics form
captures name, address, contact, insurance, race / ethnicity, language,
emergency contact, etc.

**Demo:** New / Search → search "Priya" → opens Priya Sharma's chart.

### 6.2 Dashboard (Patient File)
The patient's main hub: demographics, allergies, problem list, medications,
vitals, encounter history, lab results, documents.

**Demo:** Click any seeded patient → see allergies (Penicillin for Priya),
problems (Type 2 Diabetes for Amit), medications (Amlodipine, Metformin),
vitals from their last visit.

### 6.3 Visits
- **Calendar** — book a visit for this patient
- **Create Visit** — start a new encounter
- **Current** — what's open right now
- **Visit History** — every past encounter

**Demo:** Open patient → Visits → Visit History → see the seeded
encounters with reasons like "Annual physical", "Diabetes consultation".

### 6.4 Records
- **Patient Record Request** — print or export the patient's complete chart
  (PDF / CCDA / FHIR). Used for referrals, audits, or patient pickup.

### 6.5 Visit Forms
Adds clinical forms to the current encounter: SOAP note, Vitals,
Procedure Order, Lab Order, Eye Exam, custom forms.

**Talking point for the Patient module**
> "Everything a doctor needs in one chart — vitals, problems, allergies,
> meds, lab results, prescriptions, encounter notes. No swapping screens."

---

## 7. Groups

**What it is**
Group / class visits — diabetes education, prenatal class, antenatal
counselling. Track attendance and bill at the group level.

**Sub-items:** Groups, New, Group Details, Visits (Create / Current /
History).

**Demo flow**
1. New → "Diabetes Education Q3" → add Amit Patel and another patient.
2. Create Visit → mark both as attended.

**Talking point**
> "Single visit, multiple patients, single chart entry — saves enormous
> time for clinics running group programmes."

---

## 8. Fees (Billing)

The revenue side of the application. Submenu:

| Sub-tab | What it does |
|---|---|
| **Fee Sheet** | Add CPT / ICD-10 codes to the current encounter |
| **Charges** | Set or override a fee per code |
| **Payment** | Record a patient payment at point of service |
| **Checkout** | Combined payment + receipt at end of visit |
| **Billing Manager** | Pending claims; submit to insurance / clearinghouse |
| **Batch Payments** | Apply a single insurer payment across many claims |
| **Posting Payments** | EOB / ERA matching |
| **EDI History** | Audit of submitted X12 claim files |
| **Claim File Tracker** | Status of each outbound claim |

**Demo flow**
1. Open Amit Patel's most recent encounter → Fee Sheet → see CPT 99214
   ("Office visit, moderate") and ICD-10 E11.9 ("Type 2 diabetes") already
   loaded from seed data.
2. Click *Checkout* → take a partial payment → printable receipt.
3. *Billing Manager* → pending claims list shows all five demo patients.

**Talking point**
> "From the doctor's pen to the insurer's wire transfer — this is the
> whole revenue cycle."

---

## 9. Modules

**What it is**
Pluggable add-ons. The standard build ships:

- **Manage Modules** — install / enable / disable extras (FHIR, OAuth2,
  CCDA, Direct Mail, e-prescribing, etc.)

**Talking point**
> "OpenEMR is a platform — most regulatory and interoperability features
> live as modules so we can ship them on the customer's schedule."

---

## 10. Inventory

Pharmacy / supplies stock.

| Sub-tab | What it does |
|---|---|
| **Management** | Add / edit drugs and stock counts |
| **Destroyed** | Log expired / damaged stock for compliance |

**Demo:** Management → see seeded drugs (Paracetamol, Amoxicillin,
Metformin, Amlodipine, Atorvastatin) with NDC numbers and dispensable flag.

---

## 11. Procedures

Lab / imaging / referrals lifecycle.

| Sub-tab | What it does |
|---|---|
| **Providers** | The labs / radiology centres you send orders to |
| **Configuration** | Compendium of orderable tests per provider |
| **Load Compendium** | Bulk-import test catalogue |
| **Pending Review** | Results waiting on a doctor's eyes |
| **Patient Results** | Per-patient lab history |
| **Lab Overview** | Day-level review queue |
| **Batch Results** | Match HL7 inbound results to orders |
| **Electronic Reports** | PDF lab reports |
| **Lab Documents** | Scanned paper results |

**Talking point**
> "Order, send, receive, sign — closed-loop lab workflow without leaving
> the chart."

---

## 12. Ensora eRx (Electronic Prescribing)

Direct connect to an e-prescribing network (e.g. NewCrop / DrFirst).
Sends prescriptions electronically to the patient's pharmacy.

**Demo flow**
1. Open patient → Visit Forms → Prescription → fill drug / dose / refills.
2. Sign & Send — routes to the eRx gateway.

**Talking point**
> "No more handwritten scripts. The prescription lands at the pharmacy
> before the patient walks out."

---

## 13. Admin

System configuration. Sub-items (selection):

- **Users** — add / disable / role-assign staff
- **Practice** — facilities, providers, taxonomy codes
- **Address Book** — referring providers, organisations
- **Codes** — manage ICD-10, CPT, HCPCS, SNOMED, LOINC dictionaries
- **Patient Reminders** — schedule recall rules
- **Forms** — register or hide visit forms
- **Practice Settings** — language, encounter title, defaults
- **Lists** — drop-down values used across the app
- **Files** — uploaded reference docs (policies, fee schedules)
- **Backup** — DB + filesystem export
- **System Logs** — audit trail of every login and data change
- **Globals** — every tunable setting (Appearance, Calendar, Locale, etc.)

**Demo flow**
1. Globals → Appearance → CSS Style Sheet → confirm *Matrix Bricks* theme
   is active. (Live preview of the brand colours: red, yellow, black.)
2. Users → Add User → assign role *Physician* or *Front Office*.
3. System Logs → see every login (auditable for HIPAA).

**Talking point**
> "Every configuration choice is auditable. Nothing about how the system
> behaves is hard-coded — operations can tune it without us shipping
> code."

---

## 14. Reports

Read-only analytics and exports. Top categories:

- **Clinical** — diagnoses, procedures, medications, immunisations, lab
  results trends
- **Visits** — appointment volumes, no-show rate, by-provider load
- **Financial** —
  - *Cash Rec* — cash drawer reconciliation
  - *Front Rec* — receipts at front desk
  - *Pmt Method* — split by cash / card / cheque / wallet
  - *Collections and Aging* — outstanding AR buckets (0–30, 31–60, 61–90, 90+)
  - *Pat Ledger* — per-patient running balance
  - *Financial Summary by Service Code* — revenue per CPT
  - *Payment Processing* — gateway settlement log
- **Inventory** — list, activity, transactions
- **Procedures** — pending results, pending F/U, statistics
- **Insurance** — distribution by payer, indigent care log, unique
  subscribers
- **Statistics** — clinic-wide KPIs (IPPF, GCAC, MA, CYP, daily record)
- **Blank Forms / Services / Background Services / Direct Message Log /
  IP Tracker** — operational reports

**Demo flow**
1. Reports → Financial → Cash Rec → choose today → see no payments yet
   (or after running Checkout for a demo patient, see the entry).
2. Reports → Clinical → Diagnoses → choose date range → see ICD-10
   distribution across the demo patients (I10, E11.9, M54.5, J02.9).
3. Reports → Visits → see appointment count.

**Talking point**
> "Boss asks 'how was last month' → one click, dashboard answer."

---

## 15. Miscellaneous

Tools that don't fit a clinical workflow:

| Sub-tab | What it does |
|---|---|
| **Portal Dashboard** | Patient-portal admin: messages, signups, intake |
| **Dicom Viewer** | Radiology image viewer (CT / MRI / X-ray) |
| **Patient Education** | Send leaflets / care instructions to a patient |
| **Authorizations** | Approve pending demographic / chart changes |
| **Fax / Scan** | Inbound fax queue, document scanning workflow |
| **Chart Tracker** | (Paper-chart practices) check-out / check-in |
| **Office Notes** | Practice-wide bulletin board |
| **Batch Communication Tool** | Mass email / SMS to a patient cohort |
| **Configure Tracks** | Customise the Flow Board statuses |
| **New Documents** | Quick upload to a patient's chart |
| **Blank Forms** | Print blank Demographics / Superbill / Referral |

**Talking point**
> "Catch-all for the practical things every clinic needs but no two
> clinics use the same way."

---

## 16. Popups

Quick-action pop-out windows for the current patient — always one click
away from the chart:

- **Issues** — open the problem / allergy / medication list popup
- **Export** — quick patient export (CCDA / PDF)
- **Import** — drop a CCDA into the chart
- **Appointments** — book without leaving the chart
- **Superbill** — printable encounter charge sheet
- **Payment / Checkout** — quick capture
- **Letter** — referral / sick note / school excuse from a template
- **Chart Label / Barcode Label / Address Label** — physical printing

**Demo flow**
1. Open Vikram Singh → Popups → Letter → pick a template → print preview.
2. Popups → Barcode Label → print to a label printer.

**Talking point**
> "All the small, frequent paperwork tasks — designed to take seconds."

---

## Cross-cutting features (worth name-dropping)

- **Role-based access (ACL)** — every menu item is gated by a role; the
  receptionist literally can't see the Reports tab if you don't want
  them to.
- **Audit log** — every read and write of a record is logged (HIPAA / DPDP
  ready).
- **API** — REST + FHIR + standard CCDA / HL7 — integrates with labs,
  HIEs, government registries, third-party analytics.
- **Themes** — fully re-skinnable (this build runs the Matrix Bricks
  brand SCSS in `interface/themes/colors/style_matrix_bricks.scss`).
- **Multi-language** — the UI labels live in a translation table; we can
  ship in English + any regional language without touching code.

---

## Suggested demo path (15 minutes)

1. **Login** — show the branded login (black background, white card, red
   headings, yellow CTA).
2. **Calendar** — point at the seeded appointments.
3. **Finder** → open Amit Patel.
4. **Patient Dashboard** — show allergies (Sulfa), problem (Diabetes),
   medication (Metformin), vitals from his last visit (BP 136/88, BMI 29).
5. **Visit History** → open his Diabetes consultation encounter.
6. **Fee Sheet** — show CPT 99214 + ICD-10 E11.9 already billed.
7. **Checkout** — take a sample payment.
8. **Messages** — send a message to admin.
9. **Reports → Financial → Cash Rec** — show today's intake.
10. **Admin → Globals → Appearance** — show the theme picker.
11. Close with **Modules** — to plant the "we can extend this" idea.
