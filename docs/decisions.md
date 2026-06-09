# Architecture Decision Records

## ADR-001: Monolithic Laravel + Filament Multi-Panel

**Date:** 2026-06-09

**Context:**
Aplikasi E-Agenda Pengawas membutuhkan sistem dengan tiga role (Admin, Pengawas, Operator Sekolah) yang masing-masing memiliki akses fitur berbeda. PRD menentukan Laravel sebagai framework dengan Filament untuk admin panel.

**Decision:**
Menggunakan pendekatan monolithic dengan Laravel 12 dan Filament 5 multi-panel:
- AdminPanel untuk role Admin
- SupervisoryPanel untuk role Pengawas
- SchoolPanel untuk role Operator Sekolah

Tidak ada pemisahan backend API dan frontend SPA. Semua interaksi dilakukan melalui server-rendered Filament panel.

**Rationale:**
- Sesuai constraint PRD yang menyebutkan Laravel + Filament
- Multi-panel Filament memungkinkan pemisahan resource dan navigasi per role dalam satu codebase
- Menghindari kompleksitas tambahan dari arsitektur terpisah (SPA + API) yang tidak diperlukan di MVP
- Pengembangan lebih cepat karena seluruh logic di satu tempat

**Consequences:**
- Tidak ada API publik yang bisa dikonsumsi pihak ketiga
- Tampilan terbatas pada ekosistem Filament (tidak bisa menggunakan framework frontend terpisah seperti React/Vue)
- UI sepenuhnya server-rendered (Livewire)

---

## ADR-002: SQLite untuk Development, MySQL/PostgreSQL untuk Production

**Date:** 2026-06-09

**Context:**
PRD menyebutkan database SQLite untuk development dan MySQL atau PostgreSQL untuk production.

**Decision:**
Menggunakan SQLite sebagai database default untuk lingkungan development lokal, dan MySQL 8.0+ atau PostgreSQL 15+ untuk production. Konfigurasi database diatur melalui `.env`.

**Rationale:**
- SQLite memudahkan development tanpa setup server database
- Laravel Eloquent ORM memberikan abstraksi database sehingga migration dan query sama untuk semua driver
- MySQL/PostgreSQL diperlukan di production untuk konkurensi dan performa yang lebih baik

**Consequences:**
- Perlu memastikan migration kompatibel dengan ketiga driver database
- Tidak bisa menggunakan fitur spesifik database tertentu (harus portable)

---

## ADR-003: Local Storage untuk File Upload MVP

**Date:** 2026-06-09

**Context:**
PRD menyebutkan storage file menggunakan local storage pada tahap awal.

**Decision:**
Menyimpan semua file upload (gallery foto dan dokumen) di `storage/app/public/` dengan symlink ke `public/storage/`. Tidak menggunakan cloud storage (S3/GCS) pada MVP.

**Rationale:**
- Sederhana dan tidak memerlukan biaya tambahan untuk hosting
- Laravel Filesystem abstraction memudahkan migrasi ke S3/GCS di masa depan tanpa perubahan kode (cukup ubah konfigurasi `.env`)

**Consequences:**
- Backup file perlu dilakukan manual/terjadwal
- Storage terbatas pada kapasitas server
- Migrasi ke cloud storage di masa depan memerlukan copy file

---

## ADR-004: Manual RBAC via Middleware (tanpa Filament Shield)

**Date:** 2026-06-09

**Context:**
Tiga role (Admin, Pengawas, OperatorSekolah) membutuhkan pemisahan akses panel. Solusi umum adalah Filament Shield yang terintegrasi dengan Spatie Laravel Permission. Namun, Spatie Permission versi ^8.0 telah dirilis dan belum didukung oleh Shield (masih membutuhkan ^6.0|^7.0).

**Decision:**
Menggunakan middleware `CheckRole` manual untuk membatasi akses per panel:
- Setiap Filament Panel (Admin, Supervisory, School) memiliki middleware `CheckRole` yang memverifikasi kolom `role` user.
- Tidak menggunakan Spatie Permission untuk RBAC — hanya untuk potensi penggunaan di masa depan (granular permissions).
- User diarahkan ke panel yang sesuai dengan role-nya setelah login.

**Rationale:**
- Filament Shield tidak kompatibel dengan Spatie Permission ^8.0 (rilis 2026-05-30)
- Downgrade Spatie Permission ke ^7.0 berisiko konflik dependensi lain
- Dengan hanya 3 role dan tanpa permission granular di MVP, solusi manual lebih sederhana dan ringan
- Kolom `role` sudah ada di tabel users (dari migration T-002)

**Consequences:**
- Tidak ada UI management role/permission — hanya bisa diubah via database atau seeder
- Jika di masa depan perlu granular permission, bisa mengaktifkan Spatie Permission tables + migrasi ke Shield
- Kode middleware perlu dirawat manual untuk setiap panel baru

---

## ADR-005: FilamentUser Interface untuk Panel Access

**Date:** 2026-06-09

**Context:**
Test HTTP ke panel Filament mendapat 403 Forbidden karena User model belum
mengimplementasikan `FilamentUser` interface. Filament v5 mewajibkan method
`canAccessPanel()` untuk mengizinkan akses di environment non-local.

**Decision:**
User model mengimplementasikan `Filament\Contracts\FilamentUser` dengan method
`canAccessPanel()` yang memetakan role ke panel:
- `admin` → `isAdmin()`
- `supervisory` → `isPengawas()`
- `school` → `isOperatorSekolah()`

**Rationale:**
- Diperlukan oleh Filament v5 untuk panel access authorization
- Menghindari `abort(403)` di environment testing
- Logic role-to-panel sudah matching dengan middleware `CheckRole`

**Consequences:**
- Panel access sekarang di-validasi di dua layer: FilamentUser + CheckRole middleware
- User model membutuhkan import `Filament\Panel`

---

## ADR-006: Filament v5 Action Classes di Namespace `Filament\Actions`

**Date:** 2026-06-09

**Context:**
Error `Class "Filament\Tables\Actions\EditAction" not found` saat mengakses
halaman MasterSekolah. Filament v5 memindahkan action classes dari
`Filament\Tables\Actions\*` ke `Filament\Actions\*`.

**Decision:**
Mengupdate `MasterSekolahResource.php` menggunakan import dari namespace baru:
- `Filament\Actions\EditAction`
- `Filament\Actions\DeleteAction`
- `Filament\Actions\DeleteBulkAction`
- `Filament\Actions\BulkActionGroup`

Import `TextColumn` tetap menggunakan `Filament\Tables\Columns\TextColumn`.

**Rationale:**
- Namespace baru sesuai struktur Filament v5.x yang terinstall
- Pola ini diterapkan pada semua resource ke depannya

**Consequences:**
- Semua resource baru harus menggunakan `Filament\Actions\*` bukan `Filament\Tables\Actions\*`
- Tidak perlu import blanket `use Filament\Tables;`

---

## ADR-007: CRUD User dengan Conditional sekolah_id

**Date:** 2026-06-09

**Context:**
T-007: Admin perlu mengelola user (CRUD) dengan field role dan sekolah_id
yang hanya muncul jika role = OperatorSekolah.

**Decision:**
Membuat `UserResource` di AdminPanel dengan:
- Field `role` menggunakan `Select` dengan opsi Admin, Pengawas, OperatorSekolah
- Field `sekolah_id` menggunakan `visible(fn ($get) => $get('role') === 'OperatorSekolah')`
- Field `role` menggunakan `live()` untuk trigger re-render form
- Password handling: required on create, optional on edit via `dehydrated()`

**Rationale:**
- `live()` pada Select memungkinkan form re-render tanpa submit
- `visible()` conditional menghindari JS/CSS hack
- `dehydrated()` mencegah overwrite password kosong saat edit

**Consequences:**
- Field sekolah_id tersembunyi ketika role != OperatorSekolah
- Password tidak perlu diisi ulang saat edit kecuali ingin diganti
- Resource otomatis terdaftar via `discoverResources` di AdminPanelProvider

---

## ADR-008: Multi-Select Sekolah untuk Role Pengawas

**Date:** 2026-06-09

**Context:**
Awalnya UserResource hanya punya `sekolah_id` (single) untuk role OperatorSekolah.
Pengawas membutuhkan banyak sekolah (many-to-many via penugasan_pengawas) karena
satu pengawas bisa membina beberapa sekolah.

**Decision:**
Menambahkan `Select::make('penugasanSekolah')->multiple()` pada form yang hanya
visible ketika role = Pengawas. Relasi many-to-many `penugasanSekolah()` sudah
terdefinisi di User model.

**Rationale:**
- `multiple()` pada Select menghasilkan array ID yang di-sync otomatis oleh
  Filament ke tabel pivot `penugasan_pengawas`
- Conditional visible memastikan field hanya muncul untuk role yang tepat
- Tidak perlu model PenugasanPengawas — cukup gunakan `attach()`/`sync()` via
  relationship

**Consequences:**
- Tabel user sekarang punya dua field sekolah: `sekolah_id` (Operator) dan
  `penugasanSekolah` (Pengawas) — masing-masing mutually exclusive via visibility
- Relasi many-to-many tersimpan di tabel pivot `penugasan_pengawas`

---

## ADR-009: Filter Role & Sekolah di UserResource

**Date:** 2026-06-09

**Context:**
Halaman daftar users menampilkan semua role tercampur. Admin perlu memfilter
berdasarkan role dan juga berdasarkan sekolah (baik untuk Operator via sekolah_id
maupun Pengawas via penugasanSekolah).

**Decision:**
Menambahkan dua filter di tabel UserResource:
1. `SelectFilter::make('role')` — filter dropdown langsung berdasarkan kolom `role`
2. `Filter::make('sekolah')` — filter custom dengan query yang mencakup:
   - `sekolah_id` (untuk OperatorSekolah)
   - `penugasanSekolah` (untuk Pengawas via tabel pivot)

**Rationale:**
- SelectFilter sudah built-in untuk enum/simple column filter
- Filter custom dengan query builder memungkinkan OR condition antara dua
  kolom berbeda (sekolah_id dan penugasan_pengawas)
- Admin bisa mencari semua user yang terkait dengan satu sekolah tertentu

**Consequences:**
- Filter sekolah menggunakan OR condition, bisa memengaruhi performa jika
  dataset sangat besar (tapi untuk MVP dengan skala kecil tidak masalah)

---

## ADR-010: Unique Constraint pada Assign Sekolah

**Date:** 2026-06-09

**Context:**
Aturan bisnis: 1 sekolah hanya boleh memiliki 1 operator dan 1 pengawas.
Sebelumnya tidak ada unique constraint yang meng enforce aturan ini.

**Decision:**
Menambahkan migration dengan dua unique constraint:
1. `users.sekolah_id` — memastikan 1 sekolah hanya 1 operator
2. `penugasan_pengawas.master_sekolah_id` — memastikan 1 sekolah hanya 1 pengawas

**Rationale:**
- Constraint di level database lebih kuat dibanding validasi di aplikasi
- SQLite (testing) dan MySQL/PostgreSQL (production) semua mendukung unique index
- Tidak perlu mengubah kode form/controller karena exception akan di-handle oleh
  Filament sebagai error validasi

**Consequences:**
- Insert duplikat operator/pengawas ke sekolah yang sama akan throw QueryException
- Perlu di-drop unique jika aturan bisnis berubah (sekolah boleh memiliki >1 pengawas)
