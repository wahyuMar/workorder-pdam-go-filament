# AGENTS Guide for workorder-pdam-go-filament

This is the canonical AI instruction file for this repository and should be IDE-agnostic.
If instructions change, update this file first, then sync tool-specific files.

## Canonical Source and Mirrors
- Canonical source: `AGENTS.md`
- Copilot mirror: `.github/copilot-instructions.md`
- Team rule: keep both files aligned for cross-IDE consistency.

## Project Context
- This repository is a Laravel 12 + Filament 4 work order management system for PDAM workflows.
- Primary language is PHP. Frontend assets use Vite/Tailwind through Laravel defaults.
- Domain terms are mixed Indonesian/English. Preserve existing domain vocabulary and naming.

## Tech Stack
- PHP: 8.2+
- Laravel: 12.x
- Filament: 4.x
- Database: SQLite by default (keep code DB-agnostic unless asked otherwise)

## High-Confidence Conventions

### General
- Follow existing code style in nearby files; do not reformat unrelated code.
- Keep changes minimal and scoped to the request.
- Never edit `vendor/`, generated build artifacts, or unrelated files.
- Prefer readable, explicit code over clever shortcuts.

### Naming and Domain Language
- Keep database columns and persisted attributes in existing snake_case style (for example: `nama_lengkap`, `no_ktp`).
- Keep class names in PascalCase and method names in camelCase.
- Keep user-facing labels in Indonesian when surrounding UI uses Indonesian.
- Reuse existing enums/helpers/models before introducing new abstractions.

### Laravel Patterns
- For schema changes, create migrations. Do not modify old migrations unless explicitly requested.
- Use Eloquent relationships and eager loading to avoid N+1 queries.
- Add `$fillable`, `$casts`, and relationship methods consistently with existing models.
- Keep validation close to form/request boundaries (Filament form rules or FormRequest where appropriate).

### Filament v4 Patterns
- Follow current resource layout:
  - `app/Filament/Resources/<PluralResourceName>/`
  - `Schemas/` for form/infolist schema classes
  - `Tables/` for table config
  - `Pages/` for page classes
- For new resource features, prefer extending existing schema/table classes instead of putting everything in the Resource class.
- Use searchable selects and dependent selects consistently for regional hierarchy data (province/regency/district/village).
- Keep upload fields on `public` disk and directory naming aligned with existing patterns.

### Data and Safety
- Do not hardcode secrets or credentials.
- Use `.env` for runtime configuration values.
- Keep map/location logic compatible with existing `DEFAULT_LATITUDE` and `DEFAULT_LONGITUDE` env usage.

## Business Process Guide (Master Data to Budgeting)

### Scope
- This section documents only the flow from `Master Data` setup to `Customer Registration`, `Survey`, and `RAB/Budgeting`.
- Work order execution flow after budgeting is intentionally out of scope for now.

### Customer Registration Steps (End-to-End)
1. Admin prepares required master data in `Master Data` menu: `Program`, regional hierarchy (`Province -> Regency -> District -> Village`), `MaterialAndService`, and `KlasifikasiSr`.
2. Customer registration is submitted through `CustomerRegistrationResource` with identity, address, contact, house/utilities, document uploads, and location coordinates.
3. System saves registration and auto-generates `no_surat` (`SRPB-...`) plus `tanggal` during create flow.
4. Field staff opens the registration detail page (`ViewCustomerRegistration`) and runs `Create Survey` when no survey exists.
5. Field staff fills survey data (distribution pipe, SR location, rabatan, crossing, photos, classifications) and submits; system generates `no_survey` (`SRV-...`) and links it to the registration.
6. Field staff/reviewer opens survey detail (`ViewSurvey`) and runs `Create RAB` to create budgeting when no budget exists yet.
7. System pre-fills budgeting items from survey references (clamp saddle, crossing, klasifikasi SR, rabatan), allows manual adjustment, then saves `Budget` and `BudgetItem` records.
8. Final stage for this scope: budgeting is saved with generated `budgeting_number` (`BGT-...`) and aggregated `total_amount`.

### 1) Master Data Preparation
- Maintain reference data under `Master Data` resources before transactional input.
- Core master data in this scope:
  - `Program` (`ProgramsResource`)
  - Regional hierarchy: `Province`, `Regency`, `District`, `Village`
  - `MaterialAndService` (used by Survey and RAB item pricing)
  - `KlasifikasiSr` (used by Survey and RAB prefill)
- Regional master data uses dependent hierarchy and selectability flags (`is_selectable`) to control available options.
- AI should preserve existing relationships and avoid bypassing master data with hardcoded values.

### 2) Customer Registration Process
- Entry point: `CustomerRegistrationResource` (`Customer Registrations` navigation group).
- Registration captures:
  - Identitas pelanggan (`nama_lengkap`, `no_ktp`, `no_kk`, contacts)
  - Program assignment (`program_id`)
  - KTP address and installation address (province -> regency -> district -> village)
  - House/utilities data and supporting uploads
  - Map coordinates (`latitude`, `longitude`)
- System-generated values on create:
  - `no_surat` generated by `CustomerRegistrationHelper::generateNoSurat()`
  - `tanggal` set during create flow
- One registration is expected to lead to at most one survey (`hasOne` survey relation in model).

### 3) Survey Process (from Customer Registration)
- Survey creation is triggered from `ViewCustomerRegistration` via `CreateSurveyAction`.
- Behavior:
  - If survey does not exist: show `Create Survey` action
  - If survey exists: show `Go To Survey` action
- Survey data includes distribution pipe, SR point, rabatan, crossing, photos, klasifikasi SR, and map coordinates.
- Survey number is generated by `SurveyHelper::generateNoSurvey()`.
- Survey persists linkage to registration via `customer_registration_id` and stores `created_by`.
- In this repository flow, survey creation is controlled from registration detail, not from generic survey list create.

### 4) Budgeting/RAB Process (from Survey)
- Budgeting is triggered from `ViewSurvey` via `CreateBudgetingAction`.
- Action is visible only when survey has no existing budgeting record.
- Budgeting header includes:
  - `budgeting_number` generated by `BudgetHelper::generateBudgetingNumber()`
  - `date`, `blueprint`, `survey_id`, `created_by`, `total_amount`
- Budget items are stored in related `BudgetItem` rows.
- The repeater is pre-filled from survey data (see sub-section below) and can be manually adjusted before saving.
- Total budgeting amount is aggregated from item totals (`item_amount`) before saving.

### 4a) Default Budget Items Auto-Generated from Survey Data

When the "Create RAB" action is triggered, up to **4 budget items** are pre-filled automatically based on the linked survey record. All items are generated inside `CreateBudgetingAction::fillForm()`.

#### Item 1 — Clamp Saddle
- **Condition:** generated only if `$survey->clampSaddle` relation is not null (i.e. survey has a selected clamp saddle material)
- **Source field:** `survey.material_clamp_saddle_id` → `MaterialAndService` record
- **Category:** `pekerjaan_pipa_dinas` (Pekerjaan Pipa Dinas)
- **Sub-category:** `material_pipa_dan_acc_dinas` (Material Pipa & Acc Dinas)
- **Name format:** `"Clamp Saddle {name} ({brand})"` — taken from the `MaterialAndService` record
- **Quantity:** fixed at `1`
- **Price / item_amount:** `MaterialAndService.price` (quantity × price = price)
- **Unit:** `MaterialAndService.unit`

#### Item 2 — Crossing
- **Condition:** generated only if `$survey->crossing` relation is not null (i.e. survey has a selected crossing material)
- **Source fields:** `survey.material_crossing_id` → `MaterialAndService`, `survey.panjang_crossing` (length in meters)
- **Category:** `pekerjaan_pipa_instalasi` (Pekerjaan Pipa Instalasi)
- **Sub-category:** `pekerjaan_tanah_instalasi` (Pekerjaan Tanah Instalasi)
- **Name format:** `"Crossing {name}"` — taken from the `MaterialAndService` record
- **Quantity:** `$survey->panjang_crossing` (integer, length of crossing in m)
- **Price:** `MaterialAndService.price` (per-unit price)
- **item_amount:** `panjang_crossing × price`
- **Unit:** `MaterialAndService.unit`

#### Item 3 — Klasifikasi SR
- **Condition:** generated only if `$survey->klasifikasiSr` relation is not null
- **Source field:** `survey.klasifikasi_sr_id` → `KlasifikasiSr` record
- **Category:** `pekerjaan_pipa_instalasi` (Pekerjaan Pipa Instalasi)
- **Sub-category:** `lain_lain_instalasi` (Lain-lain Instalasi)
- **Name format:** `"Klasifikasi SR {name}"` — taken from the `KlasifikasiSr` record
- **Quantity:** fixed at `1`
- **Price / item_amount:** `KlasifikasiSr.price`
- **Unit:** hardcoded as `"-"` (no unit for this type)

#### Item 4 — Rabatan
- **Condition:** generated only if `$survey->panjang_rabatan > 0`
- **Source:** `MaterialAndService::find(5)` — **hardcoded ID 5** is the rabatan entry in `material_and_services` table. Do not delete or reorder this record.
- **Category:** `pekerjaan_pipa_instalasi` (Pekerjaan Pipa Instalasi)
- **Sub-category:** `pekerjaan_tanah_instalasi` (Pekerjaan Tanah Instalasi)
- **Name:** taken directly from `MaterialAndService.name` (id=5)
- **Quantity:** `$survey->panjang_rabatan` (length in meters)
- **Price:** `MaterialAndService.price` (id=5)
- **item_amount:** `panjang_rabatan × price`
- **Unit:** `MaterialAndService.unit` (id=5)

#### Guardrails for AI — Budget Item Prefill
- Do **not** change the hardcoded `MaterialAndService::find(5)` for rabatan without also updating the seeder/migration that guarantees that record.
- All 4 items are **conditional** — if the survey field is null/zero, no item is generated.
- The repeater allows the user to add, edit, or remove items after prefill — prefill is a starting point, not a locked set.
- Category and sub-category values must come from `BudgetItemCategory` and `BudgetItemSubCategory` enums respectively; do not use raw strings.

### Process Guardrails for AI Changes
- Preserve chain integrity: `CustomerRegistration -> Survey -> Budget -> BudgetItem`.
- Do not change generated numbering format (`SRPB-`, `SRV-`, `BGT-`) unless explicitly requested.
- Keep Survey and Budget creation actions contextual from parent records.
- Keep upload disk as `public` and follow existing folder patterns (`customer-registrations/`, `surveys`, `budgets`).
- When adding fields in this flow, update model fillable/casts, Filament schemas/infolists/tables, and relation loading consistently.

## Verification
Run relevant commands for touched areas:

```bash
composer test
# or
php artisan test

./vendor/bin/pint

php artisan migrate --pretend
```

If frontend assets are changed:

```bash
npm run build
```

## Change Strategy for AI
1. Read related model/resource/schema/table files first.
2. Mirror existing naming and structure.
3. Implement the smallest complete change.
4. Add or update tests for behavior changes when feasible.
5. If the task introduces or clarifies conventions, workflow steps, or guardrails, update `AGENTS.md` first, then sync `.github/copilot-instructions.md`.
6. Summarize changed files and any manual follow-up steps.

## Instruction Maintenance
- After completing a task, always check whether new project knowledge was introduced (patterns, constraints, process updates).
- If yes, update `AGENTS.md` and sync `.github/copilot-instructions.md` in the same task.
- Keep updates concise and scoped; do not rewrite unrelated sections.

## Avoid
- Introducing new architectural patterns when existing local patterns already solve the problem.
- Renaming domain fields just to make them English-only.
- Large cross-module refactors unless explicitly requested.
