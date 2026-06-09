# Architecture Document

## Project Overview

**E-Agenda Pengawas** adalah sistem pencatatan agenda kegiatan pengawas Dinas Pendidikan Kota Palangka Raya yang terpusat, terstandarisasi, dan berbasis web. Sistem ini memungkinkan pengawas mencatat agenda, mengunggah dokumentasi (foto & file), menandai peserta dan sekolah yang terlibat, serta menyediakan kanal tanya jawab antara operator sekolah dan pengawas. Admin bertugas mengelola data master sekolah, user, role, dan penugasan pengawas ke sekolah.

Arsitektur aplikasi ini menggunakan pendekatan **monolithic** dengan framework Laravel dan admin panel Filament dalam satu codebase. Tidak ada pemisahan backend API dan frontend SPA — seluruh interaksi dilakukan melalui panel Filament yang di-render di sisi server dengan Livewire.

---

## Technology Stack

### Frontend

| Teknologi | Versi | Keterangan |
|-----------|-------|------------|
| Filament | 5.x | Admin panel framework — menyediakan UI, form, table, widget, dan panel multi-role |
| Livewire | 4.x | Komponen reaktif sisi server untuk interaksi real-time di dalam panel |
| Blade | Laravel 12 | Template engine untuk layout dan view non-Filament (landing, PDF) |
| Alpine.js | 3.x | Interaktivitas ringan di sisi klien (bawaan Filament) |
| Tailwind CSS | 4.x | Utility CSS framework (bawaan Filament) |
| FullCalendar | integrasi via widget | Kalender dashboard |
| Chart.js | integrasi via widget | Grafik/diagram dashboard (opsional) |

**Pendekatan Frontend:**
- Tidak ada SPA terpisah (Vue/React) — seluruh UI di-render server-side via Filament.
- Tampilan **responsif** untuk desktop dan tablet.
- Multi-panel Filament: setiap role memiliki panel sendiri dengan resource dan navigasi yang sesuai.

### Backend

| Teknologi | Versi | Keterangan |
|-----------|-------|------------|
| Laravel | 12.x | Framework utama — routing, ORM, auth, queue, storage, dll. |
| PHP | 8.5 | Runtime |
| Laravel DomPDF | (barryvdh/laravel-dompdf) | Generate laporan PDF |
| Laravel Activitylog | (spatie/laravel-activitylog) | Activity logging untuk perubahan agenda |

### Database

| Lingkungan | Database | Keterangan |
|-----------|----------|------------|
| Development | SQLite | Ringan, tanpa setup server |
| Production | MySQL 8.0+ atau PostgreSQL 15+ | Sesuai infrastruktur Dinas Pendidikan |

**Driver Storage:**
- **Local** (`storage/app/public/`) untuk gallery foto dan file dokumen.
- Symlink `public/storage` → `storage/app/public/`.
- Di masa depan dapat diganti ke S3/GCS tanpa perubahan kode berkat Laravel Filesystem abstraction.

### Infrastructure

| Komponen | Spesifikasi (Production) |
|----------|--------------------------|
| Web Server | Nginx + PHP-FPM |
| Server | VPS minimal 2 CPU, 4 GB RAM |
| OS | Ubuntu 22.04 LTS |
| Queue Worker | Laravel Queue (database driver) untuk logging async (opsional) |
| Cron | Scheduler Laravel untuk job terjadwal (maintenance, backup) |

---

## Application Structure

### Struktur Direktori (Khusus Aplikasi)

```
/app
  /Filament
    /Resources            # CRUD resources admin (SekolahResource, UserResource, dll)
    /Widgets              # Dashboard widgets admin
    /Supervisory
      /Resources          # AgendaResource (Pengawas)
      /Widgets            # Kalender, agenda terbaru
    /School
      /Resources          # AgendaSekolahResource (read-only), PertanyaanResource
      /Widgets            # Kalender, daftar agenda sekolah
  /Http
    /Controllers
      /Auth               # Custom auth logic (jika perlu di luar Filament)
      /ReportController   # Endpoint generate download PDF
    /Middleware
      CheckRole.php       # Middleware untuk membatasi akses panel berdasarkan role
  /Livewire
    /Components            # Komponen Livewire kustom (kalender, filter)
  /Models
    User.php
    MasterSekolah.php
    PenugasanPengawas.php
    Agenda.php
    AgendaPeserta.php
    AgendaSekolah.php
    Gallery.php
    File.php
    LogAktivitas.php
    Pertanyaan.php
    Jawaban.php
  /Policies
    AgendaPolicy.php
    PertanyaanPolicy.php
    GalleryPolicy.php
    ...
  /Services
    AgendaService.php       # Business logic agenda
    ReportService.php       # Logic generate PDF
    FileUploadService.php   # Validasi & upload file
```

### Alur Multi-Panel

```
User Login
  ↓
Authenticate (email + password)
  ↓
Cek Role:
  ├── Admin           → redirect ke /admin
  ├── Pengawas        → redirect ke /supervisory
  └── Operator Sekolah → redirect ke /school
```

Setiap panel memiliki:
- **Dashboard** sendiri — widget kalender, statistik, daftar agenda terbaru.
- **Resource** sendiri — sesuai fitur yang diizinkan oleh role.
- **Navigasi** sendiri — hanya menampilkan menu yang relevan.

### Alur Pembuatan Agenda

```
Pengawas → Buka Panel → Buat Agenda Baru
  1. Isi judul, deskripsi, tanggal mulai & berakhir
  2. Tag peserta (pengawas lain) — select multiple
  3. Tag sekolah — select multiple
  4. Upload foto gallery (multiple)
  5. Upload file dokumen (multiple)
  6. Simpan → Agenda tersimpan, log aktivitas tercatat
```

### Alur Tanya Jawab

```
Operator Sekolah:
  → Buka Panel → Buat Pertanyaan Baru
  → Pilih judul & isi pertanyaan
  → Otomatis terarah ke pengawas binaan sekolahnya

Pengawas:
  → Buka Panel → Lihat daftar pertanyaan masuk
  → Klik pertanyaan → Lihat detail & history
  → Tulis jawaban → Submit
  → Jawaban tersimpan, tampil sebagai threaded conversation
```

---

## Data Model Overview

### Entity Relationship Summary

```
User (Pengawas) *---* MasterSekolah        (via PenugasanPengawas)
User (Operator Sekolah) *---1 MasterSekolah (sekolah_id)

Agenda *---1 User     (created_by)
Agenda *---* User     (via AgendaPeserta — tag pengawas)
Agenda *---* MasterSekolah (via AgendaSekolah — tag sekolah)
Agenda 1---* Gallery
Agenda 1---* File
Agenda 1---* LogAktivitas

Pertanyaan *---1 User        (penanya — Operator Sekolah)
Pertanyaan *---1 MasterSekolah
Pertanyaan 1---* Jawaban
Jawaban *---1 User           (penjawab — Pengawas)
```

### Entity Detail

| Entity | Fields | Catatan |
|--------|--------|---------|
| **User** | id, name, email, password, role (enum: Admin/Pengawas/OperatorSekolah), sekolah_id (nullable), timestamps | sekolah_id diisi jika role = OperatorSekolah |
| **MasterSekolah** | id, nisn (unique), nama_sekolah, alamat, timestamps | Data master sekolah dari Dinas Pendidikan |
| **PenugasanPengawas** | id, user_id, master_sekolah_id, timestamps | pivot many-to-many |
| **Agenda** | id, judul, deskripsi, deskripsi_hasil, tanggal_mulai (datetime), tanggal_berakhir (datetime), created_by (FK user_id), timestamps | |
| **AgendaPeserta** | id, agenda_id, user_id, timestamps | pivot many-to-many agenda ↔ pengawas |
| **AgendaSekolah** | id, agenda_id, master_sekolah_id, timestamps | pivot many-to-many agenda ↔ sekolah |
| **Gallery** | id, agenda_id, file_path, original_name, timestamps | Foto kegiatan |
| **File** | id, agenda_id, file_path, original_name, file_type, file_size, timestamps | Dokumen pendukung |
| **LogAktivitas** | id, user_id, agenda_id, action (enum: created/updated/deleted), old_data (json), new_data (json), timestamps | Log perubahan agenda |
| **Pertanyaan** | id, user_id, master_sekolah_id, judul, isi, timestamps | Pertanyaan dari operator sekolah |
| **Jawaban** | id, pertanyaan_id, user_id, isi, timestamps | Jawaban dari pengawas |

---

## API Design Overview

Pada MVP, **tidak ada API publik yang di-expose**. Seluruh interaksi dilakukan melalui internal Filament routing:

| Metode | URL (relatif) | Deskripsi |
|--------|--------------|-----------|
| GET | `/admin/*` | Panel Admin — CRUD master data |
| GET | `/supervisory/*` | Panel Pengawas — agenda, upload, jawab |
| GET | `/school/*` | Panel Operator Sekolah — lihat agenda, tanya |
| GET | `/report/agenda/{id}/pdf` | Download laporan PDF agenda |

**Internal Data Flow:**
- Resource Filament → Form/Tabel → Action → Service Layer → Model → Database
- Upload file → FileUploadService → validasi → simpan ke storage → simpan record ke DB
- Activity log → Spatie Activitylog → tercatat otomatis via model event `created`/`updated`/`deleted`

---

## Security Considerations

| Aspek | Implementasi |
|-------|-------------|
| **Autentikasi** | Laravel default auth (email + password, bcrypt) via Filament |
| **Otorisasi** | Middleware CheckRole — setiap panel dibatasi berdasarkan kolom `role` di User |
| **RBAC** | 3 role: Admin, Pengawas, OperatorSekolah — diatur via kolom `role` (string) di tabel users |
| **File Upload** | Validasi tipe file (gambar: jpg/png/webp, dokumen: pdf/doc/xls), batas ukuran (max 10MB per file) |
| **CSRF** | Laravel CSRF Protection aktif |
| **XSS** | Blade escaping otomatis, Filament sanitasi input |
| **SQL Injection** | Eloquent ORM menggunakan parameter binding |
| **Rate Limiting** | Laravel throttle middleware pada route login |
| **Logging** | Semua perubahan agenda tercatat di LogAktivitas (audit trail) |

---

## Deployment Strategy

### Lingkungan

| Lingkungan | Database | Storage | Domain |
|-----------|----------|---------|--------|
| Development Lokal | SQLite | Local | `localhost` |
| Staging | MySQL/PostgreSQL | Local | `staging.example.com` |
| Production | MySQL/PostgreSQL | Local (dapat migrasi ke S3) | `app.example.com` |

### Deployment Steps

1. Clone repository ke server
2. Setup Nginx + PHP-FPM + database (MySQL/PostgreSQL)
3. Copy `.env` dengan konfigurasi production
4. `composer install --optimize-autoloader --no-dev`
5. `php artisan key:generate`
6. `php artisan migrate --force`
7. `php artisan storage:link`
8. `php artisan optimize` (cache config, route, event)
9. Setup cron: `* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`
10. Setup queue worker: `php artisan queue:work database --daemon` (jika queue digunakan)
11. Setup Nginx virtual host pointing ke `public/` directory

### Backup Strategy

- Backup database harian via cron + Laravel backup package (spatie/laravel-backup)
- Backup folder `storage/app/public/` untuk file upload

---

## Assumptions

1. Semua pengguna (pengawas, operator sekolah, admin) memiliki akses internet yang stabil untuk mengakses aplikasi web.
2. Tidak diperlukan integrasi dengan layanan eksternal (cloud storage, API pihak ketiga) pada tahap MVP.
3. Tidak diperlukan notifikasi real-time (email, WhatsApp) untuk pertanyaan baru pada tahap MVP — pengguna diharapkan login secara periodik.
4. File upload (gallery & dokumen) disimpan di local storage — belum menggunakan S3/GCS.
5. Aplikasi hanya diakses melalui web browser (desktop & tablet) — tidak ada aplikasi mobile native.
6. Data master sekolah (NISN, nama, alamat) sudah tersedia dan akan diinput oleh Admin.
7. Role pengguna bersifat tetap (tidak ada perubahan role yang sering terjadi).

---

## Open Questions

1. Apakah perlu fitur **export Excel** untuk data agenda selain PDF?
2. Apakah perlu **notifikasi** (email/WhatsApp) ketika ada pertanyaan baru dari operator sekolah?
3. Apakah gallery foto dan file dokumen perlu diintegrasikan dengan **cloud storage** (S3, GCS) di masa depan?
4. Apakah perlu **backup otomatis** database dan file ke cloud storage?
5. Apakah perlu **fitur cetak/export** laporan dalam periode tertentu (bulanan/triwulan)?
