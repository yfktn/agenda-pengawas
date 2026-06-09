project:
  name: E-Agenda Pengawas — Sistem Pencatatan Agenda Pengawas Dinas Pendidikan Kota Palangka Raya
  short_description: Aplikasi web berbasis Laravel Filament untuk para pengawas Dinas Pendidikan Kota Palangka Raya melakukan pencatatan agenda kegiatan secara terpusat, meliputi pencatatan agenda, upload dokumentasi, tagging peserta dan sekolah, serta fitur tanya jawab antara operator sekolah dan pengawas.
  owner: Pengawas Dinas Pendidikan Kota Palangka Raya
  priority: Medium
  target_release: MVP dalam 3 bulan

problem:
  current_state: Pencatatan agenda kegiatan pengawas masih dilakukan secara campuran antara catatan manual (buku/fisik) dan spreadsheet yang tidak terpusat.
  pain_points:
    - Tidak ada sistem baku yang terstandarisasi untuk pencatatan agenda.
    - Data tersebar dan tidak real-time, menyulitkan monitoring oleh pimpinan.
    - Risiko kehilangan atau kerusakan data karena tidak tersimpan di sistem terpusat.
    - Komunikasi antara operator sekolah dan pengawas tidak terdokumentasi dengan baik.
    - Dokumentasi kegiatan (foto, file pendukung) tidak terkelola secara sistematis.
  business_impact: Kesulitan dalam memantau kinerja pengawas, keterlambatan pelaporan, dan hilangnya jejak dokumentasi kegiatan pengawas yang berdampak pada kualitas pengawasan Dinas Pendidikan.

goals:
  primary: Menyediakan sistem pencatatan agenda kegiatan pengawas yang terpusat, terstandarisasi, dan mudah diakses kapan saja melalui web.
  secondary:
    - Memudahkan monitoring agenda oleh pimpinan.
    - Mendokumentasikan hasil kegiatan secara digital (gallery & file).
    - Menyediakan kanal komunikasi dua arah antara operator sekolah dan pengawas.
    - Menghasilkan laporan agenda dalam format PDF.

non_goals:
  - Aplikasi mobile native (cukup web responsive).
  - Sistem presensi/absensi pengawas.
  - Integrasi dengan pihak ketiga di luar lingkup Dinas Pendidikan.

user_personas:
  - name: Admin/Operator Sistem
    role: Admin
    objectives: Mengelola data master sekolah, user, role, dan penugasan pengawas ke sekolah.
  - name: Pengawas Lapangan
    role: Pengawas
    objectives: Mencatat agenda kegiatan, mengupload dokumentasi (gallery & file), menandai peserta dan sekolah yang terlibat, serta menjawab pertanyaan dari operator sekolah binaan.
  - name: Operator Sekolah
    role: Operator Sekolah
    objectives: Melihat agenda yang menandai sekolahnya, mengajukan pertanyaan kepada pengawas yang ditugaskan di sekolahnya, dan melihat history tanya jawab.

user_stories:
  - id: US-01
    as_a: Admin
    i_want: dapat login ke sistem
    so_that: saya bisa mengakses fitur sesuai role saya.
  - id: US-02
    as_a: Admin
    i_want: dapat mengelola data master sekolah (NISN, nama, alamat)
    so_that: data sekolah tersedia untuk tagging agenda dan penugasan pengawas.
  - id: US-03
    as_a: Admin
    i_want: dapat mengelola data user dan role
    so_that: pengguna sistem memiliki hak akses yang sesuai.
  - id: US-04
    as_a: Admin
    i_want: dapat mengatur penugasan pengawas ke sekolah
    so_that: setiap sekolah memiliki pengawas binaan yang jelas.
  - id: US-05
    as_a: Pengawas
    i_want: dapat membuat agenda kegiatan dengan judul, deskripsi, tanggal & waktu mulai dan berakhir
    so_that: saya mencatat kegiatan pengawasan secara lengkap.
  - id: US-06
    as_a: Pengawas
    i_want: dapat menandai (tag) pengawas lain sebagai peserta agenda
    so_that: diketahui siapa saja pengawas yang terlibat dalam kegiatan tersebut.
  - id: US-07
    as_a: Pengawas
    i_want: dapat menandai (tag) sekolah yang terlibat dalam agenda
    so_that: sekolah yang mengikuti kegiatan tercatat dalam sistem.
  - id: US-08
    as_a: Pengawas
    i_want: dapat mengupload gallery foto kegiatan
    so_that: dokumentasi visual tersimpan rapi per agenda.
  - id: US-09
    as_a: Pengawas
    i_want: dapat mengupload file yang berkaitan dengan kegiatan
    so_that: dokumen pendukung (surat, laporan) terarsip secara digital.
  - id: US-10
    as_a: Pengawas
    i_want: dapat melihat riwayat perubahan pada agenda
    so_that: saya bisa memantau siapa yang mengubah data dan kapan.
  - id: US-11
    as_a: Pengawas
    i_want: dapat melihat pertanyaan dari operator sekolah binaan saya
    so_that: saya bisa memberikan jawaban atas pertanyaan tersebut.
  - id: US-12
    as_a: Operator Sekolah
    i_want: dapat melihat agenda yang menandai sekolah saya
    so_that: saya mengetahui kegiatan pengawasan di sekolah saya.
  - id: US-13
    as_a: Operator Sekolah
    i_want: dapat mengajukan pertanyaan kepada pengawas binaan sekolah saya
    so_that: saya bisa berkomunikasi dan berkonsultasi melalui sistem.
  - id: US-14
    as_a: Operator Sekolah
    i_want: dapat melihat history tanya jawab dengan pengawas
    so_that: saya bisa menelusuri kembali diskusi yang sudah dilakukan.
  - id: US-15
    as_a: Pengawas dan Operator Sekolah
    i_want: melihat dashboard kalender yang menampilkan agenda
    so_that: saya bisa melihat jadwal kegiatan secara visual.
  - id: US-16
    as_a: Pengawas dan Operator Sekolah
    i_want: dapat memfilter dashboard berdasarkan sekolah dan pengawas
    so_that: saya bisa melihat agenda yang relevan dengan cepat.

functional_requirements:
  - id: FR-01
    description: Sistem memiliki fitur login dengan autentikasi berbasis email dan password.
    priority: High
  - id: FR-02
    description: Sistem memiliki role-based access control (Admin, Pengawas, Operator Sekolah).
    priority: High
  - id: FR-03
    description: Admin dapat melakukan CRUD data master sekolah (NISN, Nama Sekolah, Alamat).
    priority: High
  - id: FR-04
    description: Admin dapat melakukan CRUD data user dengan pemilihan role.
    priority: High
  - id: FR-05
    description: Admin dapat mengatur penugasan pengawas ke sekolah (relasi many-to-many).
    priority: High
  - id: FR-06
    description: Pengawas dapat membuat, membaca, mengubah, dan menghapus agenda kegiatan.
    priority: High
  - id: FR-07
    description: Setiap agenda memiliki field: judul, deskripsi hasil kegiatan, tanggal & waktu mulai, tanggal & waktu berakhir.
    priority: High
  - id: FR-08
    description: Pengawas dapat menandai (tag) pengawas lain sebagai peserta agenda (many-to-many).
    priority: High
  - id: FR-09
    description: Pengawas dapat menandai (tag) sekolah yang terlibat dalam agenda (many-to-many).
    priority: High
  - id: FR-10
    description: Pengawas dapat mengupload multiple foto ke gallery agenda.
    priority: High
  - id: FR-11
    description: Pengawas dapat mengupload multiple file dokumen pendukung agenda.
    priority: High
  - id: FR-12
    description: Sistem mencatat log aktivitas setiap perubahan pada agenda (created, updated, deleted).
    priority: Medium
  - id: FR-13
    description: Operator Sekolah dapat melihat daftar agenda yang menandai sekolahnya.
    priority: High
  - id: FR-14
    description: Operator Sekolah dapat mengajukan pertanyaan yang otomatis ditujukan ke pengawas binaan sekolahnya.
    priority: High
  - id: FR-15
    description: Pengawas dapat menjawab pertanyaan dari operator sekolah binaan.
    priority: High
  - id: FR-16
    description: Sistem menampilkan history tanya jawab dalam bentuk threaded conversation.
    priority: Medium
  - id: FR-17
    description: Dashboard menampilkan kalender bulanan yang menandai tanggal-tanggal yang memiliki agenda.
    priority: High
  - id: FR-18
    description: Dashboard memiliki filter berdasarkan Sekolah dan Pengawas yang memengaruhi tampilan kalender dan daftar agenda.
    priority: Medium
  - id: FR-19
    description: Sistem dapat menghasilkan laporan agenda dalam format PDF.
    priority: Medium

non_functional_requirements:
  performance: Halaman dashboard dan daftar agenda harus dimuat dalam waktu kurang dari 3 detik. Kalender dashboard harus responsif saat menampilkan data dalam jumlah besar.
  security: Setiap akses ke fitur harus dicek otorisasi berdasarkan role (RBAC). Password disimpan dengan hashing. Upload file divalidasi tipe dan ukurannya.
  availability: Aplikasi dapat diakses 24/7 melalui web browser. Downtime terencana untuk maintenance di luar jam kerja.
  scalability: Arsitektur database dan query harus mendukung pertumbuhan data agenda, gallery, dan file dalam jumlah besar tanpa degradasi performa signifikan.
  accessibility: Tampilan web harus responsif dan dapat diakses melalui perangkat desktop maupun tablet.

data_requirements:
  entities:
    - User: id, name, email, password, role (Admin/Pengawas/Operator Sekolah), sekolah_id (untuk Operator Sekolah), timestamps
    - MasterSekolah: id, nisn, nama_sekolah, alamat, timestamps
    - PenugasanPengawas: id, user_id (Pengawas), master_sekolah_id, timestamps (pivot many-to-many)
    - Agenda: id, judul, deskripsi, deskripsi_hasil, tanggal_mulai, tanggal_berakhir, created_by (user_id), timestamps
    - AgendaPeserta: id, agenda_id, user_id (Pengawas), timestamps (pivot many-to-many)
    - AgendaSekolah: id, agenda_id, master_sekolah_id, timestamps (pivot many-to-many)
    - Gallery: id, agenda_id, file_path, original_name, timestamps
    - File: id, agenda_id, file_path, original_name, file_type, file_size, timestamps
    - LogAktivitas: id, user_id, agenda_id, action (created/updated/deleted), old_data (json), new_data (json), timestamps
    - Pertanyaan: id, user_id (Operator Sekolah), master_sekolah_id, judul, isi, timestamps
    - Jawaban: id, pertanyaan_id, user_id (Pengawas), isi, timestamps
  relationships:
    - User (Pengawas) many-to-many MasterSekolah melalui PenugasanPengawas
    - User (Operator Sekolah) belongsTo MasterSekolah (sekolah_id)
    - Agenda belongsTo User (creator)
    - Agenda many-to-many User (Pengawas) melalui AgendaPeserta
    - Agenda many-to-many MasterSekolah melalui AgendaSekolah
    - Agenda hasMany Gallery
    - Agenda hasMany File
    - Agenda hasMany LogAktivitas
    - Pertanyaan belongsTo User (penanya)
    - Pertanyaan belongsTo MasterSekolah
    - Pertanyaan hasMany Jawaban
    - Jawaban belongsTo User (penjawab)

integration_requirements:
  external_services: Tidak ada integrasi dengan layanan eksternal pada MVP.
  apis: Tidak ada API publik yang diexpose pada MVP.

constraints:
  technical: Aplikasi dibangun menggunakan framework Laravel dengan admin panel Filament. Database menggunakan SQLite untuk pengembangan dan MySQL atau PostgreSQL untuk production. Storage file menggunakan local storage pada tahap awal.
  business: Sistem harus mudah digunakan oleh pengguna non-teknis (operator sekolah dan pengawas lapangan).
  regulatory: Data sekolah (NISN, alamat) harus akurat sesuai dengan data Dinas Pendidikan.

success_metrics:
  - Semua pengawas aktif menggunakan sistem untuk mencatat agenda dalam 1 bulan setelah rilis.
  - Semua operator sekolah terdaftar dan dapat mengakses sistem.
  - Minimal 80% agenda tercatat melalui sistem dalam 3 bulan pertama.
  - Waktu pembuatan laporan PDF lebih cepat dibandingkan penyusunan manual.

acceptance_criteria:
  - Admin dapat login dan mengelola master sekolah, user, dan penugasan pengawas.
  - Pengawas dapat membuat agenda lengkap dengan tag peserta, tag sekolah, gallery, dan file.
  - Operator Sekolah dapat melihat agenda sekolahnya dan mengajukan pertanyaan.
  - Pengawas dapat menjawab pertanyaan dan history tanya jawab tercatat.
  - Dashboard kalender menampilkan agenda dan dapat difilter berdasarkan sekolah dan pengawas.
  - Laporan PDF dapat diunduh dan menampilkan data agenda dengan benar.

risks:
  - Adaptasi pengguna terhadap sistem baru membutuhkan waktu (mitigasi: pelatihan dan sosialisasi).
  - Koneksi internet di lapangan tidak stabil (mitigasi: aplikasi web ringan, optimasi loading).
  - Data sekolah tidak akurat (mitigasi: validasi data saat input master sekolah).

open_questions:
  - Apakah perlu ada fitur export Excel selain PDF?
  - Apakah perlu ada notifikasi (email/WhatsApp) untuk pertanyaan baru?
  - Apakah gallery dan file perlu diintegrasikan dengan cloud storage (S3, GCS) ke depannya?
