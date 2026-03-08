# Copilot Instructions — workorder-pdam-go-filament

## 1. Project Foundation & Tech Stack
- **Core**: Laravel 12 (Streamlined Structure), Filament 4, Livewire 3, Tailwind 4.
- **Environment**: PHP 8.3+, Vite, SQLite (default).
- **Standards**: PSR-12, Laravel Pint.
- **Boost Tools**: Gunakan `search-docs` untuk verifikasi sintaks L12/F4 terbaru. Gunakan `tinker` untuk debug Eloquent models secara langsung.
- **Naming**: `snake_case` (DB columns), `PascalCase` (Classes), `camelCase` (Methods).
- **Language**: Domain campuran (Indo/English). **Label UI wajib Bahasa Indonesia** jika komponen sekitarnya menggunakan Bahasa Indonesia.

## 2. Coding Rules & Architecture
### General Guidelines
- **Mirroring**: Selalu ikuti pola kode, struktur, dan naming di file sekitar. Lakukan minimal edit; hindari reformat masif pada kode yang tidak terkait.
- **Reuse**: Cek folder `Enums/`, `Helpers/`, `Actions/`, dan `Models/` sebelum membuat komponen baru.
- **Logic Placement**: Logika bisnis berat harus berada di **Action classes** atau **Helpers**. Jangan menulis logika bisnis yang kompleks secara inline di dalam Filament Resource.

### Laravel & Database
- **Migrations**: Dilarang mengedit file migration yang sudah ada. Selalu buat file migration baru untuk perubahan skema.
- **Eloquent**: Gunakan relasi resmi Laravel (`hasOne`, `belongsTo`, dll.), hindari penggunaan `DB::` raw query. Cegah masalah N+1 dengan *eager loading*.
- **Models**: Saat menambah field baru, pastikan memperbarui `$fillable`, method `casts()`, dan relasi terkait di model.
- **L12 Structure**: Konfigurasi middleware dan exception dilakukan di `bootstrap/app.php`. Service Provider di `bootstrap/providers.php`. (File `app/Http/Kernel.php` tidak digunakan).

### Filament & UI
- **Structure**: Patuhi organisasi folder: `Schemas/Components/`, `Tables/Columns/`, `Tables/Filters/`, dan `Actions/`.
- **Fields**: Gunakan method `->relationship()` untuk Select, Checkbox, atau Repeater options.
- **Storage**: Upload file (foto survey/dokumen) harus diarahkan ke **public disk**.

## 3. Business Process & Domain Logic (End-to-End)
Alur data utama (**Chain Integrity**) tidak boleh terputus:
**Master Data → Registration → Survey → Budgeting (RAB) → Budget Item**

### 3.1. Master Data (Prerequisites)
- Tabel referensi: `Program`, `Province`, `Regency`, `District`, `Village`, `MaterialAndService`, `KlasifikasiSr`.
- Gunakan flag `is_selectable` pada data regional untuk mengontrol opsi di UI. Jangan melakukan hardcode nilai master data di dalam kode.

### 3.2. Customer Registration (`SRPB-*`)
- Resource: `CustomerRegistrationResource` (Grup navigasi: `Customer Registrations`).
- Mencakup: Identitas pelanggan, alamat instalasi, program, dokumen pendukung, dan koordinat map.
- Auto-generate `no_surat` (via `CustomerRegistrationHelper::generateNoSurat()`) dan `tanggal` saat proses pembuatan data.
- **Constraint**: Satu registrasi maksimal hanya boleh memiliki satu survey (`hasOne` relationship).

### 3.3. Survey (`SRV-*`)
- Trigger: Action `Create Survey` di halaman `ViewCustomerRegistration`.
- Mencakup: Data pipa distribusi, lokasi titik SR, panjang rabatan, panjang crossing, foto lapangan, dan koordinat.
- Relasi: Wajib terhubung secara eksplisit melalui `customer_registration_id`.
- Auto-generate `no_survey` via `SurveyHelper::generateNoSurvey()`.

### 3.4. Budgeting / RAB (`BGT-*`)
- Trigger: Action `Create RAB` (atau `CreateBudgetingAction`) di halaman `ViewSurvey`.
- Header: `budgeting_number`, `date`, `survey_id`, `created_by`, `total_amount`.
- **Auto-Prefill Items (Kritikal)**:
    - **Clamp Saddle**: Generate jika `survey.material_clamp_saddle_id` terisi.
    - **Crossing**: Generate jika `survey.material_crossing_id` terisi (Qty = `survey.panjang_crossing`).
    - **Klasifikasi SR**: Generate berdasarkan `survey.klasifikasi_sr_id` (Qty = 1).
    - **Rabatan**: Generate jika `survey.panjang_rabatan > 0`. **WAJIB menggunakan `MaterialAndService::find(5)`** (ID 5 adalah referensi tetap untuk Rabatan).
- **Data Persistence**: Disimpan di tabel `budgets` dan `budget_items`.
- **Enums**: Kategori dan Sub-kategori item harus menggunakan `BudgetItemCategory` dan `BudgetItemSubCategory`.

## 4. Guardrails & Verification
- **Prefixes**: Jangan mengubah format nomor otomatis: `SRPB-` (Reg), `SRV-` (Survey), `BGT-` (Budget).
- **Rabatan Reference**: ID 5 di tabel `material_and_services` adalah referensi sakral; jangan dihapus atau diubah urutannya.
- **Security**: Selalu gunakan `config()` untuk mengakses variabel `.env`. Jangan melakukan hardcode kredensial.
- **Testing & Style**: Jalankan `php artisan test` dan `./vendor/bin/pint` secara rutin.
- **Frontend**: Jalankan `npm run build` jika ada perubahan pada file CSS (Tailwind 4) atau aset Vite lainnya.

## 5. Change Strategy for AI
1. **Read & Analyze**: Baca file model, resource, schema, dan action yang berkaitan sebelum mulai menulis kode.
2. **Smallest Complete Change**: Implementasikan perubahan terkecil yang tetap fungsional dan lengkap.
3. **Continuous Knowledge Update**: Jika tugas baru memperkenalkan konvensi atau workflow baru, update `AGENTS.md` terlebih dahulu, baru kemudian sinkronkan ke file instruksi ini.
4. **Avoid**: Jangan memperkenalkan pola arsitektur baru jika pola lokal yang ada sudah mencukupi. Jangan mengubah nama field domain ke Bahasa Inggris murni tanpa instruksi eksplisit.

## 6. Verification Commands
```bash
composer test
# atau
php artisan test

./vendor/bin/pint

php artisan migrate --pretend

# Jika ada perubahan UI/Tailwind:
npm run build
