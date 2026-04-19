# Story 6.2: Admin Source Filter & Visibility

Status: done

## Story

As an admin,
I want to see mobile-submitted data in Filament and filter by source,
So that I can distinguish and process data from different channels.

## Acceptance Criteria

1. **Mobile registrations visible** — Given a mobile-submitted registration exists, When admin views registration list in Filament, Then the registration appears in the existing list without any workflow changes.
2. **Mobile complaints visible** — Given a mobile-submitted complaint exists, When admin views complaint list in Filament, Then the complaint appears in the existing list without any workflow changes.
3. **Filter registrations by source** — Given registrations from both sources exist, When admin uses source filter in Filament, Then can filter by `manual` or `mobile` source.
4. **Filter complaints by source** — Given complaints from both sources exist, When admin uses source filter in Filament, Then can filter by `manual` or `mobile` source.
5. **Same workflow** — Admin processes mobile-submitted data using exact same workflow as manual data.
6. **Only additive changes** — No existing Filament code logic is modified; only additive column, filter, and entry additions.

## Tasks / Subtasks

- [x] Task 1: Add `source` TextColumn to CustomerRegistrationsTable (AC: #1, #3)
  - [x] In `app/Filament/Resources/CustomerRegistrations/Tables/CustomerRegistrationsTable.php`
  - [x] Add `TextColumn::make('source')->label('Sumber')->badge()->sortable()` after `tanggal` column
  - [x] Add color mapping: `->color(fn (string $state): string => match ($state) { 'mobile' => 'info', default => 'gray' })`
  - [x] Position: after existing columns, before `created_at`

- [x] Task 2: Add `source` SelectFilter to CustomerRegistrationsTable (AC: #3)
  - [x] In the same file, add to the `filters()` array
  - [x] `SelectFilter::make('source')->label('Sumber')->options(['manual' => 'Manual', 'mobile' => 'Mobile'])`
  - [x] Follow existing filter pattern (program, jenis_rumah, kecamatan_pasang already exist)

- [x] Task 3: Add `source` TextEntry to CustomerRegistrationInfolist (AC: #1)
  - [x] In `app/Filament/Resources/CustomerRegistrations/Schemas/CustomerRegistrationInfolist.php`
  - [x] Add `TextEntry::make('source')->label('Sumber')->badge()` with same color mapping
  - [x] Position: in Data Surat section (near `tanggal`)

- [x] Task 4: Verify complaint `sumber` already satisfies AC #2 and #4 (NO CHANGES)
  - [x] Confirm `ComplaintsTable.php` already has `TextColumn::make('sumber')` with badge (line ~45-48)
  - [x] Confirm `ComplaintsTable.php` already has `SelectFilter::make('sumber')` with options including `mobile_apps` (line ~118-125)
  - [x] Confirm `ComplaintsInfolist.php` already has `TextEntry::make('sumber')` (line ~57-59)
  - [x] Document: Complaint side is ALREADY COMPLETE — no changes needed

- [x] Task 5: Create `tests/Feature/Filament/CustomerRegistrationSourceFilterTest.php` (AC: #1, #3, #6)
  - [x] Test 1: `test_source_column_renders_in_table` — create registrations with different sources, verify column renders
  - [x] Test 2: `test_can_filter_by_source_manual` — filter by manual, verify only manual shown
  - [x] Test 3: `test_can_filter_by_source_mobile` — filter by mobile, verify only mobile shown
  - [x] Test 4: `test_source_displays_in_infolist` — verify source entry visible on view page

## Dev Notes

### Critical Context: Asymmetric Column Names
- **customer_registrations** table uses `source` (English) — values: `manual` (default), `mobile`
- **complaints** table uses `sumber` (Indonesian) — values: `website`, `kantor`, `sosial_media`, `telepon`, `mobile_apps`
- This naming inconsistency is PRE-EXISTING. Do NOT rename or unify in this story.
- The API (Story 4.1) sets `$data['source'] = 'mobile'` in `RegistrationController::store()`
- The API (Story 5.1) sets `$data['sumber'] = 'mobile_apps'` in `ComplaintController::store()`

### What Already Exists (DO NOT RECREATE)
- `customer_registrations.source` column: migration `2026_04_11_175327_add_source_to_customer_registrations_table.php` — `string('source')->nullable()->default('manual')`
- `CustomerRegistration` model: `source` is in `$fillable` (line 17). No cast needed (plain string).
- Complaint `sumber` in Filament: **FULLY WIRED** — table column (badge), filter (5 options), form select, infolist entry. ZERO changes needed on complaint side.

### What Does NOT Exist (TO CREATE)
- CustomerRegistrationsTable: NO `source` column, NO `source` filter
- CustomerRegistrationInfolist: NO `source` entry
- No Filament test for source filtering

### Filament File Locations
```
app/Filament/Resources/CustomerRegistrations/
├── CustomerRegistrationResource.php          # Resource definition (DO NOT MODIFY)
├── Tables/CustomerRegistrationsTable.php     # ← ADD column + filter here
├── Schemas/
│   ├── CustomerRegistrationForm.php          # Form schema (DO NOT MODIFY)
│   └── CustomerRegistrationInfolist.php      # ← ADD entry here
└── Pages/                                    # (DO NOT MODIFY)
```

### Existing Filter Pattern (CustomerRegistrationsTable)
The table already has filters — follow the same pattern:
```php
// Existing filters (approximate pattern):
SelectFilter::make('program_id')
    ->label('Program')
    ->options([...]),
SelectFilter::make('jenis_rumah')
    ->label('Jenis Rumah')
    ->options([...]),
```

### Existing Complaint Source Pattern (Reference Only)
```php
// ComplaintsTable.php — column (line ~45-48):
TextColumn::make('sumber')->label('Sumber')->badge()->sortable()

// ComplaintsTable.php — filter (line ~118-125):
SelectFilter::make('sumber')
    ->label('Sumber')
    ->options([
        'website' => 'Website',
        'kantor' => 'Kantor',
        'sosial_media' => 'Sosial Media',
        'telepon' => 'Telepon',
        'mobile_apps' => 'Mobile Apps',
    ])
```

### Filament Testing Pattern
Use Livewire testing for Filament components:
```php
use Livewire\Livewire;
use App\Filament\Resources\CustomerRegistrations\Pages\ListCustomerRegistrations;

Livewire::test(ListCustomerRegistrations::class)
    ->assertCanSeeTableRecords($records)
    ->filterTable('source', 'mobile')
    ->assertCanSeeTableRecords($mobileRecords)
    ->assertCanNotSeeTableRecords($manualRecords);
```
- Use `RefreshDatabase` (standard Filament tests, NOT `RefreshDatabaseCompat` which is for API tests)
- Admin user: create with `super_admin` or appropriate admin role
- Check existing test files in `tests/Feature/` for Filament test patterns already in this project

### Architecture Compliance
- **FR42**: Mobile registrations visible in Filament → satisfied by existing data model (same DB)
- **FR43**: Mobile complaints visible in Filament → already satisfied (sumber column exists)
- **FR44**: Filter by source → adding SelectFilter for registrations; complaints already have it
- **FR45**: Same workflow → no workflow changes, only additive column/filter/entry
- **NFR17**: Immediate visibility → no cache layer between DB and Filament
- **NFR25**: No existing Filament code modified → only additive changes
- **Zero admin disruption** (architecture.md line 43): Changes are purely additive

### Previous Story Learnings (Story 6.1)
- Route `whereNumber()` constraint was missed — not applicable here (no API routes)
- Cache key proliferation — not applicable (no caching in Filament)
- Testing: 4 pre-existing failures to IGNORE: `CustomerRegistrationProcessTest` (2), `RepairReportTest`, `TeraMeterReportTest`
- Pint: run after changes to ensure clean formatting

### UI Language
Per project conventions: **Label UI wajib Bahasa Indonesia**. Use `->label('Sumber')` for source column/filter/entry (consistent with complaint's existing label).

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 6, Story 6.2 AC (lines 576-601)]
- [Source: _bmad-output/planning-artifacts/architecture.md — Zero admin disruption (line 43), Source tracking (line 49), AV-01..04 mapping (line 482)]
- [Source: _bmad-output/planning-artifacts/prd.md — FR42-FR45 (lines 498-503), NFR17 (line 531), NFR25 (line 545)]
- [Source: app/Filament/Resources/CustomerRegistrations/Tables/CustomerRegistrationsTable.php — no source column or filter]
- [Source: app/Filament/Resources/CustomerRegistrations/Schemas/CustomerRegistrationInfolist.php — no source entry]
- [Source: app/Filament/Resources/Complaints/Tables/ComplaintsTable.php — sumber column (line ~45), filter (line ~118)]
- [Source: app/Models/CustomerRegistration.php — source in $fillable (line 17)]
- [Source: database/migrations/2026_04_11_175327_add_source_to_customer_registrations_table.php — source column]
- [Source: _bmad-output/implementation-artifacts/6-1-master-data-endpoints.md — previous story learnings]

## Dev Agent Record

### Files Created
- `tests/Feature/Filament/CustomerRegistrationSourceFilterTest.php` — 4 Livewire tests (11 assertions)

### Files Modified
- `app/Filament/Resources/CustomerRegistrations/Tables/CustomerRegistrationsTable.php` — Added `source` TextColumn (badge, color) + `source` SelectFilter
- `app/Filament/Resources/CustomerRegistrations/Schemas/CustomerRegistrationInfolist.php` — Added `source` TextEntry (badge, color) in Data Surat section
- `database/factories/VillageFactory.php` — Fixed missing `district_id` FK (pre-existing bug blocking factory usage)

### Files Verified (No Changes Needed)
- `app/Filament/Resources/Complaints/Tables/ComplaintsTable.php` — `sumber` column + filter already complete
- `app/Filament/Resources/Complaints/Schemas/ComplaintsInfolist.php` — `sumber` entry already exists

### Review Findings
- [x] [Review][Patch] F1: Nullable `source` crashes `fn(string $state)` — TypeError on NULL [CustomerRegistrationsTable.php:61, CustomerRegistrationInfolist.php:28] — FIXED
- [x] [Review][Patch] F2: Factory missing `source` default — masks NULL edge case [CustomerRegistrationFactory.php] — FIXED
- [x] [Review][Defer] F3: Migration doesn't backfill existing NULL rows — deferred, pre-existing (migration from Story 4.1)

### Test Results
- Story tests: 4 passed (11 assertions)
- Full suite: 207 passed, 3 failed (pre-existing: CustomerRegistrationProcessTest, RepairReportTest, TeraMeterReportTest)
- Note: VillageFactory fix resolved 1 pre-existing failure (`registration_has_one_survey_relation`)
- Pint: 378 files clean
