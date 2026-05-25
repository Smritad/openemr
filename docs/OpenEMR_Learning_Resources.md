# OpenEMR — Learning Resources

Curated videos, official docs, and search-strings to help you understand
each tab and practice adding data manually. All links below were verified
from a live web search; the search dates are noted where the source page
showed them.

> **Tip for practice:** keep `http://localhost/open_cms` open in one
> browser tab, the video in another. Watch a step, pause, do the same
> step in your local install with the demo patients (Rajesh, Priya,
> Amit, Neha, Vikram) until it feels natural.

---

## ⭐ Start here — overview videos

These cover the whole product. Watch one of them first so the rest of the
tabs make sense in context.

| Video | What it covers | Notes |
|---|---|---|
| [OpenEMR Patient Complete Workflow Step-by-Step](https://www.youtube.com/watch?v=waTJ_h-5-G0) | Reception → encounter → billing → checkout, end-to-end | Recent (May 2025) — closest to the version you're running |
| [OpenEMR Tutorial for Beginners](https://www.youtube.com/watch?v=VlKZ5oAft4E) | Install + first-look UI walkthrough | Recent (Jul 2025) |
| [MI2 OpenEMR 5 Intro 1 Walkthrough](https://www.youtube.com/watch?v=TRb3jBBlPgQ) | The tabs layout — top menu by top menu | Older (2017) but the menu structure is essentially the same |
| [OpenEMR Tutorial — Getting started with OpenEMR](https://www.youtube.com/watch?v=NTMMKo2_lnc) | Login, navigation basics | 2016, still relevant |

**Official channel and playlist** (browse for more):
- [OpenEMR — official YouTube channel](https://www.youtube.com/@openemr3787)
- [How-to Use OpenEMR playlist](https://www.youtube.com/playlist?list=PLFiWG_dDadgRTp66VeN86nSmMPPJP03AG)

---

## 📖 Official documentation (always the source of truth)

- [OpenEMR 7.0.4 Users Guide](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.4_Users_Guide) — current
- [OpenEMR 7.0.1 Users Guide](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.1_Users_Guide) — slightly older, often easier-to-read screenshots
- [HOWTO: Create a New Patient Record (v7)](https://www.open-emr.org/wiki/index.php/HOWTO:_Create_a_New_Patient_Record_-_OpenEMR_v7) — the cleanest "add a patient" walkthrough

---

## 🗂 Map: tab → video → practice exercise

### 1. Calendar  &nbsp;·&nbsp; 2. Finder  &nbsp;·&nbsp; 3. Flow Board

| Resource | Use |
|---|---|
| [Setting up your clinic or medical practice (1 of 2)](https://www.youtube.com/watch?v=lRSLpxpccEI) | Facility, providers, calendar categories |
| [OpenEMR 7.0.4 Users Guide](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.4_Users_Guide) → *Calendar* section | Click-by-click reference |

**Practice in your local install:**
1. **Calendar** → click any empty cell tomorrow → book a 30-minute appointment for *Neha Gupta* with reason "Follow-up".
2. **Finder** → search `kum` → confirm Rajesh appears at the top.
3. **Flow** → check Rajesh in for his next visit → move him through Waiting → In Exam → Checkout.

---

### 5. Messages

| Resource | Use |
|---|---|
| [OpenEMR Users Guide — Messages chapter](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.4_Users_Guide) | Inbox, sending, recalls |

**Practice:**
1. Compose a new message → To `admin` → Subject "Test from learning session".
2. From Rajesh's chart, click the **Notes** widget → send a note linked to him.

---

### 6. Patient (Dashboard, Demographics, Visit Forms)

| Resource | Use |
|---|---|
| [HOWTO: Create a New Patient Record (v7)](https://www.open-emr.org/wiki/index.php/HOWTO:_Create_a_New_Patient_Record_-_OpenEMR_v7) | Step-by-step new patient |
| [OpenEMR Tutorial — Documenting a Patient's Visit Encounter](https://www.youtube.com/watch?v=tGh0sO4PqKg) | Encounters and SOAP notes |
| [VistACan Walkthrough — Upload Patient Documents on OpenEMR](https://www.youtube.com/watch?v=tMA_81ShLJU) | Documents tab inside the chart |

**Practice — add a new patient manually:**
1. **Patient → New/Search → Add New Patient**.
2. Fill: First "Anita", Last "Verma", DOB "1988-03-12", phone "9000011111", language "Hindi", sex "Female".
3. Save → open her chart → confirm she appears in Finder.
4. From her chart, click **Add Allergy** → "Aspirin" → severity *moderate* → save.
5. Click **Add Problem** → "Migraine" → ICD-10 *G43.909* → save.

---

### 8. Fees (Billing) &nbsp;·&nbsp; 14. Reports (Financial)

| Resource | Use |
|---|---|
| [Openemr Tutorial New Encounter Patient Visit & Coding](https://www.youtube.com/watch?v=_sczvBKFbmM) | Fee sheet + CPT/ICD-10 coding |
| [The Complete OpenEMR Billing, RCM & Reporting Guide (Capminds)](https://www.capminds.com/blog/the-complete-openemr-billing-rcm-reporting-guide/) | Long-form written billing guide |

**Practice — add a charge manually:**
1. Open Rajesh's encounter (the seeded one, encounter 101).
2. **Fee Sheet** → add CPT code `99213` (Office visit, established) → save.
3. Add ICD-10 `I10` (essential hypertension) → save.
4. **Checkout** → take a ₹500 cash payment → save → print receipt.
5. Go to **Reports → Financial → Cash Rec** → confirm the ₹500 shows up under today.

---

### 10. Inventory &nbsp;·&nbsp; 12. Ensora eRx (Prescriptions)

| Resource | Use |
|---|---|
| [OpenEMR Users Guide — Pharmacy / Prescriptions chapter](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.4_Users_Guide) | Drugs, prescription writing |

**Practice:**
1. **Inventory → Management → Add Drug** → "Cetirizine 10mg" → NDC code `NDC-00006-0006` → active = yes.
2. Open Priya's chart → **Visit Forms → Prescription** → write "Cetirizine 10mg, 1 tab at night, 10 days".

---

### 13. Admin (Users, Globals, Lists, Backup)

| Resource | Use |
|---|---|
| [Setting up your clinic or medical practice — part 1 of 2](https://www.youtube.com/watch?v=lRSLpxpccEI) | Users, providers, facility |
| [Getting started with OpenEMR](https://www.youtube.com/watch?v=NTMMKo2_lnc) | First-login admin tasks |

**Practice:**
1. **Admin → Users → Add New User** → username "reception1", role "Front Office".
2. **Admin → Globals → Appearance → CSS Style Sheet** → confirm "Matrix Bricks" is selected (your branded theme).
3. **Admin → Lists** → pick "Marital Status" → add a new option "Cohabiting".

---

### Self-host / install reference

| Resource | Use |
|---|---|
| [How to Install OpenEMR Using XAMPP](https://www.youtube.com/watch?v=NOFP-wD1og0) | Your exact stack (XAMPP on Windows) |
| [OpenEMR Easy Docker Development](https://www.youtube.com/watch?v=D4tXP5G9-sY) | If you ever switch to Docker |

---

## 🔎 Search strings to find more (paste into YouTube)

When you want a video for a tab not listed above, paste one of these:

- `OpenEMR <feature name> tutorial`  — e.g. *OpenEMR fee sheet tutorial*
- `OpenEMR <feature name> walkthrough`
- `OpenEMR <feature name> demo 2024`
- `OpenEMR 7 <feature name>` — pins results to the modern UI

**Tabs / features worth searching for:**
- *OpenEMR Recalls tutorial*
- *OpenEMR Patient Flow Board*
- *OpenEMR Groups module*
- *OpenEMR Procedures lab orders*
- *OpenEMR Reports clinical*
- *OpenEMR FHIR API*
- *OpenEMR portal patient*
- *OpenEMR forms registry*

---

## 📌 Suggested learning order (1 week, ~30 min/day)

| Day | Watch | Practice |
|---|---|---|
| 1 | Getting started (MI2 Intro) | Log in, click every top menu, no changes |
| 2 | Patient Complete Workflow | Add new patient "Anita Verma" |
| 3 | Documenting an Encounter | Create encounter for Anita, add SOAP note |
| 4 | Coding / Fee Sheet video | Add CPT + ICD-10, take a payment |
| 5 | Setting up your clinic 1/2 | Practise Admin → Users, Lists, Globals |
| 6 | Upload Patient Documents | Upload a sample PDF into a chart |
| 7 | Free practice | Try a complete day: book → check-in → encounter → bill → report |

---

## When you get stuck

- **Community forum:** <https://community.open-emr.org> — searchable, replies usually within a day.
- **OpenEMR project wiki:** <https://www.open-emr.org/wiki/> — every feature has a HOWTO page.
- **GitHub issues:** <https://github.com/openemr/openemr/issues> — search for the error message; many already have answers.

---

*Document version 1.0 · 22 May 2026*
