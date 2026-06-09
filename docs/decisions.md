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
