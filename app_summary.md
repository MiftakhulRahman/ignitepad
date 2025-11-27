# RANCANGAN SISTEM IGNITEPAD - CLEAN & SIMPLIFIED

## 🎯 KONSEP INTI

### **PRINSIP UTAMA:**

1. **Single URL untuk semua role** - URL bersih tanpa prefix role
2. **Adaptive Interface** - Konten berubah sesuai role yang login
3. **Policy-Based Authorization** - Backend handle akses, bukan routing
4. **Bahasa Indonesia** - Semua penamaan kecuali istilah teknis
5. **Full Featured Tables** - Search, filter, sort, bulk action, export, pagination

---

## 📊 STRUKTUR DATABASE

### **Tabel Utama (22 Tabel)**

#### **1. pengguna**

-   Identitas: id, nama, username, email, password
-   Profile: avatar, sampul, bio
-   Akademik: nim (mahasiswa), nidn (dosen), prodi_id
-   Sosial: github_url, linkedin_url, website_url
-   Status: email_terverifikasi_pada, registrasi_selesai, status_aktif
-   Timestamp: dibuat_pada, diperbarui_pada

#### **2. peran**

-   id, nama (superadmin/dosen/mahasiswa), slug, deskripsi

#### **3. pengguna_peran** (pivot)

-   pengguna_id, peran_id

#### **4. program_studi**

-   id, nama, fakultas, kode

#### **5. kategori_proyek**

-   id, nama, slug, deskripsi, ikon, warna, urutan, status_aktif

#### **6. teknologi**

-   id, nama, slug, ikon_url, warna, kategori_teknologi, status_aktif

#### **7. proyek**

-   Identitas: id, pengguna_id, kategori_id, judul, slug
-   Konten: deskripsi, konten_html, thumbnail, galeri_gambar (JSON)
-   Links: url_demo, url_repository, url_video
-   Meta: tag (JSON), status, visibilitas, boleh_komentar, unggulan
-   Counters: jumlah_lihat, jumlah_suka, jumlah_simpan, jumlah_komentar
-   Timestamp: terbit_pada, dibuat_pada, diperbarui_pada

#### **8. proyek_teknologi** (pivot)

-   proyek_id, teknologi_id

#### **9. kolaborator**

-   id, proyek_id, pengguna_id
-   peran_kolaborator (pembimbing/rekan/kontributor)
-   izin: bisa_edit, bisa_hapus
-   status_undangan (diundang/diterima/ditolak)
-   tracking: diundang_oleh, diundang_pada, direspon_pada

#### **10. challenge**

-   Identitas: id, pembuat_id, judul, slug
-   Konten: deskripsi, aturan_html, hadiah, banner
-   Settings: persyaratan (JSON), kategori_diizinkan (JSON), kriteria_penilaian (JSON)
-   Jadwal: tanggal_mulai, batas_waktu, tanggal_pengumuman
-   Status: status (draft/buka/tutup/selesai), maks_peserta
-   Counters: jumlah_peserta, jumlah_submisi
-   Winner: pemenang_1_id, pemenang_2_id, pemenang_3_id, pemenang_diumumkan_pada

#### **11. kriteria_penilaian**

-   id, challenge_id, nama_kriteria, bobot_persen, deskripsi, urutan

#### **12. submisi_challenge**

-   id, challenge_id, pengguna_id, proyek_id
-   status (bergabung/terkirim/dinilai)
-   catatan_peserta
-   nilai_total, grade (A/B/C/D/E), peringkat
-   umpan_balik_html
-   dikirim_pada, dinilai_pada

#### **13. nilai_kriteria**

-   id, submisi_id, kriteria_id, nilai (0-100)

#### **14. komentar**

-   id, proyek_id, pengguna_id, induk_id (untuk nested reply)
-   isi_html, jumlah_suka, jumlah_balasan
-   dibuat_pada, diperbarui_pada

#### **15. suka** (polymorphic)

-   id, pengguna_id
-   dapat_disuka_tipe (Proyek/Komentar)
-   dapat_disuka_id
-   dibuat_pada

#### **16. simpanan**

-   id, pengguna_id, proyek_id
-   folder (default/inspirasi/referensi/tugas)
-   catatan
-   dibuat_pada

#### **17. notifikasi**

-   id, pengguna_id
-   tipe (suka/komentar/kolaborator/challenge/submisi/pemenang)
-   judul, pesan, data_json
-   sudah_dibaca, dibaca_pada
-   dibuat_pada

#### **18. log_aktivitas**

-   id, pengguna_id
-   aksi (buat/ubah/hapus/login/logout)
-   subjek_tipe (Pengguna/Proyek/Challenge/dll)
-   subjek_id, deskripsi, perubahan_data (JSON)
-   ip_address, user_agent
-   dibuat_pada

#### **19. papan_peringkat**

-   id, pengguna_id
-   poin_total, poin_proyek, poin_suka, poin_komentar, poin_challenge
-   peringkat_global, peringkat_prodi
-   terakhir_dihitung_pada

#### **20. lencana** (badges)

-   id, nama, slug, deskripsi, ikon, kategori, syarat_json

#### **21. pengguna_lencana**

-   pengguna_id, lencana_id, diperoleh_pada

#### **22. pengaturan_sistem**

-   kunci, nilai, tipe_data, deskripsi

---

## 🗂️ STRUKTUR FOLDER (REVISED)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php          → Universal adaptive dashboard
│   │   ├── ProyekController.php             → Universal CRUD dengan policy
│   │   ├── ChallengeController.php          → Universal CRUD dengan policy
│   │   ├── SubmisiController.php            → Handle submission & grading
│   │   ├── KomentarController.php
│   │   ├── SukaController.php
│   │   ├── SimpanController.php
│   │   ├── KolaboratorController.php
│   │   ├── NotifikasiController.php
│   │   ├── ProfilController.php
│   │   ├── PencarianController.php
│   │   ├── PapanPeringkatController.php
│   │   │
│   │   └── Kelola/                          → Admin-only management
│   │       ├── PenggunaController.php
│   │       ├── KategoriController.php
│   │       ├── TeknologiController.php
│   │       ├── ProdiController.php
│   │       ├── LencanaController.php
│   │       ├── LogAktivitasController.php
│   │       └── PengaturanController.php
│   │
│   ├── Middleware/
│   │   ├── CekPeran.php
│   │   ├── RegistrasiSelesai.php
│   │   ├── CekKepemilikan.php
│   │   └── LogAktivitas.php
│   │
│   └── Requests/
│       ├── Proyek/
│       │   ├── SimpanProyekRequest.php
│       │   └── PerbaruiProyekRequest.php
│       ├── Challenge/
│       │   ├── SimpanChallengeRequest.php
│       │   └── PerbaruiChallengeRequest.php
│       └── Kelola/
│           ├── SimpanPenggunaRequest.php
│           └── PerbaruiPenggunaRequest.php
│
├── Models/
│   ├── Pengguna.php
│   ├── Peran.php
│   ├── ProgramStudi.php
│   ├── Proyek.php
│   ├── KategoriProyek.php
│   ├── Teknologi.php
│   ├── Kolaborator.php
│   ├── Challenge.php
│   ├── KriteriaPenilaian.php
│   ├── SubmisiChallenge.php
│   ├── NilaiKriteria.php
│   ├── Komentar.php
│   ├── Suka.php
│   ├── Simpanan.php
│   ├── Notifikasi.php
│   ├── LogAktivitas.php
│   ├── PapanPeringkat.php
│   ├── Lencana.php
│   └── PengaturanSistem.php
│
├── Services/
│   ├── ProyekService.php
│   ├── ChallengeService.php
│   ├── SubmisiService.php
│   ├── PenilaianService.php
│   ├── NotifikasiService.php
│   ├── FileService.php
│   ├── PoinService.php
│   └── LencanaService.php
│
├── Policies/
│   ├── ProyekPolicy.php
│   ├── ChallengePolicy.php
│   ├── SubmisiPolicy.php
│   └── KomentarPolicy.php
│
└── Traits/
    ├── MemilikiSuka.php
    ├── MemilikiKomentar.php
    └── CatatAktivitas.php

resources/js/
├── Components/
│   ├── Layout/
│   │   ├── AppLayout.vue                    → Universal layout
│   │   ├── GuestLayout.vue
│   │   ├── Header.vue                       → Logo, search, notif, user menu
│   │   ├── Sidebar.vue                      → Role-adaptive menu
│   │   ├── Footer.vue
│   │   └── Breadcrumb.vue
│   │
│   ├── UI/                                   → Reusable components
│   │   ├── Tabel/
│   │   │   ├── TabelData.vue               → Universal table wrapper
│   │   │   ├── KolomAksi.vue
│   │   │   └── FilterPanel.vue
│   │   ├── Form/
│   │   │   ├── InputTeks.vue
│   │   │   ├── InputFile.vue
│   │   │   ├── Dropdown.vue
│   │   │   ├── MultiSelect.vue
│   │   │   ├── DatePicker.vue
│   │   │   ├── RichTextEditor.vue
│   │   │   └── TagInput.vue
│   │   ├── Feedback/
│   │   │   ├── Toast.vue
│   │   │   ├── Dialog.vue
│   │   │   ├── KonfirmasiHapus.vue
│   │   │   └── LoadingState.vue
│   │   └── Display/
│   │       ├── Avatar.vue
│   │       ├── Badge.vue
│   │       ├── Tag.vue
│   │       ├── Card.vue
│   │       ├── EmptyState.vue
│   │       └── Skeleton.vue
│   │
│   ├── Proyek/
│   │   ├── ProyekCard.vue
│   │   ├── ProyekGrid.vue
│   │   ├── ProyekForm.vue                   → Multi-step form
│   │   ├── ProyekDetail.vue
│   │   ├── ProyekFilter.vue
│   │   ├── GaleriGambar.vue
│   │   └── TombolInteraksi.vue             → Like, save, share buttons
│   │
│   ├── Challenge/
│   │   ├── ChallengeCard.vue
│   │   ├── ChallengeForm.vue               → Multi-step form
│   │   ├── ChallengeDetail.vue
│   │   ├── KriteriaPenilaian.vue           → Drag & drop criteria
│   │   ├── FormSubmisi.vue
│   │   ├── FormPenilaian.vue               → Grading interface
│   │   ├── DaftarSubmisi.vue
│   │   └── PodiumPemenang.vue
│   │
│   ├── Komentar/
│   │   ├── KomentarList.vue
│   │   ├── KomentarItem.vue                → Support nested replies
│   │   └── FormKomentar.vue
│   │
│   ├── Dashboard/
│   │   ├── StatsCard.vue
│   │   ├── ChartPertumbuhan.vue
│   │   ├── AktivitasTerkini.vue
│   │   ├── ProyekTerbaru.vue
│   │   ├── ChallengeTerbuka.vue
│   │   └── WidgetLeaderboard.vue
│   │
│   └── Kelola/                              → Admin management components
│       ├── TabelPengguna.vue
│       ├── FormPengguna.vue
│       ├── TabelKategori.vue
│       ├── FormKategori.vue
│       ├── TabelTeknologi.vue
│       └── FormTeknologi.vue
│
├── Pages/
│   ├── Auth/
│   │   ├── Masuk.vue
│   │   ├── Daftar.vue
│   │   ├── LupaPassword.vue
│   │   └── ResetPassword.vue
│   │
│   ├── Onboarding/
│   │   └── LengkapiProfil.vue             → Multi-step adaptive
│   │
│   ├── Dashboard.vue                        → Role-adaptive content
│   │
│   ├── Proyek/
│   │   ├── Index.vue                        → Gallery + "Proyek Saya" tab (conditional)
│   │   ├── Buat.vue
│   │   ├── Edit.vue
│   │   └── Detail.vue
│   │
│   ├── Challenge/
│   │   ├── Index.vue                        → List + "Buat Challenge" button (Dosen only)
│   │   ├── Buat.vue
│   │   ├── Edit.vue
│   │   ├── Detail.vue
│   │   └── Submisi.vue                      → Grading interface (Dosen only)
│   │
│   ├── Kelola/                              → Admin-only pages (TIDAK ada prefix /kelola di URL)
│   │   ├── Pengguna.vue                    → URL: /pengguna (policy check di backend)
│   │   ├── Kategori.vue                    → URL: /kategori
│   │   ├── Teknologi.vue                   → URL: /teknologi
│   │   ├── ProgramStudi.vue                → URL: /program-studi
│   │   ├── Lencana.vue                     → URL: /lencana
│   │   ├── LogAktivitas.vue                → URL: /log-aktivitas
│   │   └── Pengaturan.vue                  → URL: /pengaturan
│   │
│   ├── Profil/
│   │   ├── Tampil.vue                       → Public profile view
│   │   └── Edit.vue                         → Own profile edit
│   │
│   ├── Tersimpan.vue
│   ├── Notifikasi.vue
│   ├── Pencarian.vue
│   ├── Leaderboard.vue
│   └── Beranda.vue                          → Landing page
│
├── Composables/
│   ├── useAuth.ts
│   ├── useToast.ts
│   ├── useDialog.ts
│   ├── useNotifikasi.ts
│   ├── usePencarian.ts
│   └── useDebounce.ts
│
└── Types/
    ├── index.d.ts
    ├── pengguna.ts
    ├── proyek.ts
    ├── challenge.ts
    └── notifikasi.ts
```

---

## 🛣️ ROUTING ARCHITECTURE (CLEAN URLS)

### **Prinsip Routing:**

1. **URL bersih tanpa prefix role** (misal: `/proyek` bukan `/mahasiswa/proyek`)
2. **Policy handle authorization** (backend check role & ownership)
3. **Bahasa Indonesia** untuk semua URL
4. **RESTful pattern** untuk CRUD

### **Route Mapping:**

```
PUBLIC ROUTES (Guest):
├── /                                   → Beranda (landing page)
├── /masuk                              → Login
├── /daftar                             → Register
├── /lupa-password                      → Forgot password
└── /reset-password/{token}             → Reset password

AUTHENTICATED ROUTES:
├── /dashboard                          → Adaptive dashboard (ALL ROLES)
│
├── PROYEK (ALL ROLES - Adaptive):
│   ├── /proyek                         → Gallery + "Proyek Saya" tab
│   ├── /proyek/buat                    → Create (Mahasiswa/Dosen only via policy)
│   ├── /proyek/{slug}                  → Detail (public if published)
│   ├── /proyek/{slug}/edit             → Edit (owner/admin only via policy)
│   └── /proyek/{slug}                  → Delete (owner/admin only via policy)
│
├── CHALLENGE (ALL ROLES - Adaptive):
│   ├── /challenge                      → Browse + "Buat Challenge" (Dosen only)
│   ├── /challenge/buat                 → Create (Dosen only via policy)
│   ├── /challenge/{slug}               → Detail
│   ├── /challenge/{slug}/edit          → Edit (creator/admin only)
│   ├── /challenge/{slug}/submisi       → Submission list & grading (Dosen only)
│   ├── /challenge/{slug}/gabung        → Join (Mahasiswa only)
│   └── /challenge/{slug}/kirim         → Submit project (Mahasiswa only)
│
├── INTERACTIONS:
│   ├── /proyek/{slug}/suka             → Toggle like
│   ├── /proyek/{slug}/simpan           → Toggle save
│   ├── /komentar                       → Create/update/delete comments
│   └── /kolaborator                    → Invite/respond/remove collaborators
│
├── USER:
│   ├── /profil/{username}              → Public profile
│   ├── /profil                         → Edit own profile
│   ├── /tersimpan                      → Saved projects (Mahasiswa only)
│   ├── /notifikasi                     → Notifications
│   └── /leaderboard                    → Leaderboard
│
├── SEARCH:
│   └── /cari?q={query}                 → Global search
│
└── KELOLA (ADMIN ONLY - Policy-protected):
    ├── /pengguna                       → User management
    ├── /kategori                       → Category management
    ├── /teknologi                      → Technology management
    ├── /program-studi                  → Program management
    ├── /lencana                        → Badge management
    ├── /log-aktivitas                  → Activity logs
    └── /pengaturan                     → System settings
```

### **Cara Kerja Authorization:**

```
Request: GET /pengguna
    ↓
Middleware: auth (cek login)
    ↓
Middleware: registrasi.selesai (cek onboarding)
    ↓
Controller: Kelola\PenggunaController@index
    ↓
Policy: PenggunaPolicy@viewAny
    ↓
    - Check: User peran === 'superadmin' ?
    - TRUE  → Return view dengan data
    - FALSE → Abort 403 (Forbidden)
```

**Tidak ada routing group berdasarkan role**, semua handle di policy!

---

## 📱 UI/UX FLOW

### **1. ALUR REGISTRASI & ONBOARDING**

#### **Step 1: Daftar Akun**

-   Halaman: `/daftar`
-   Form: Nama, Email, Password, Konfirmasi Password
-   Submit → Email verifikasi terkirim
-   Status: `registrasi_selesai = false`

#### **Step 2: Verifikasi Email**

-   User klik link di email
-   System set `email_terverifikasi_pada`
-   Redirect ke `/lengkapi-profil`

#### **Step 3: Lengkapi Profil (Multi-Step)**

**Sub-Step 1: Pilih Peran**

-   Radio button: Mahasiswa / Dosen
-   Next

**Sub-Step 2: Data Akademik**

-   **Jika Mahasiswa:**
    -   Input: NIM
    -   Dropdown: Program Studi, Angkatan, Semester
    -   Optional: GitHub URL
-   **Jika Dosen:**
    -   Input: NIDN
    -   Dropdown: Program Studi, Jabatan
    -   Textarea: Bidang Keahlian
    -   Optional: Google Scholar URL

**Sub-Step 3: Data Pribadi**

-   Textarea: Bio (opsional)
-   URL: LinkedIn, Website

**Sub-Step 4: Konfirmasi**

-   Preview semua data
-   Checkbox: "Data sudah benar"
-   Submit

#### **Step 4: Selesai**

-   System set `registrasi_selesai = true`
-   Redirect ke `/dashboard`
-   Toast: "Selamat datang!"

---

### **2. ALUR DASHBOARD (ADAPTIVE)**

#### **URL: `/dashboard`** (Semua role akses URL yang sama)

**Konten berdasarkan peran:**

#### **A. SUPERADMIN Dashboard**

-   **Stats Cards (4 cards):**

    -   Total Pengguna (dengan chart mini trend)
    -   Total Proyek Published
    -   Challenge Aktif
    -   Submission Pending

-   **Charts Section:**

    -   Line Chart: Pertumbuhan pengguna 6 bulan terakhir
    -   Pie Chart: Distribusi proyek per kategori
    -   Bar Chart: Top 10 teknologi paling populer

-   **Recent Activities Timeline:**

    -   User baru registrasi
    -   Proyek baru published
    -   Challenge baru dibuat
    -   Submission baru masuk

-   **Quick Actions:**
    -   Button: Kelola Pengguna → `/pengguna`
    -   Button: Kelola Kategori → `/kategori`
    -   Button: Kelola Teknologi → `/teknologi`
    -   Button: Lihat Logs → `/log-aktivitas`

#### **B. DOSEN Dashboard**

-   **Stats Cards:**

    -   Challenge Saya
    -   Submission Menunggu Penilaian
    -   Total Proyek (milik sendiri)
    -   Mahasiswa Bimbingan

-   **Challenge Aktif:**

    -   Card list challenge yang status = 'buka'
    -   Setiap card ada badge jumlah submission pending
    -   Click card → `/challenge/{slug}/submisi`

-   **Recent Submissions:**

    -   Table 10 submission terbaru yang belum dinilai
    -   Column: Mahasiswa, Proyek, Challenge, Tanggal Submit
    -   Action: Button "Nilai Sekarang"

-   **Performance Chart:**

    -   Bar chart: Rata-rata nilai submission per challenge

-   **Quick Actions:**
    -   Button: Buat Challenge → `/challenge/buat`
    -   Button: Lihat Semua Proyek → `/proyek`

#### **C. MAHASISWA Dashboard**

-   **Stats Cards:**

    -   Proyek Saya
    -   Total Suka
    -   Challenge Diikuti
    -   Peringkat Leaderboard

-   **Proyek Terbaru:**

    -   Grid 4-6 proyek terbaru milik sendiri
    -   Badge status: Draft/Published/Featured
    -   Button: Lihat Semua → `/proyek` (tab "Proyek Saya")

-   **Challenge Tersedia:**

    -   Card list challenge dengan status 'buka'
    -   Setiap card:
        -   Countdown deadline
        -   Jumlah peserta
        -   Hadiah
        -   Button: "Ikuti Challenge" atau "Lihat Detail"

-   **Leaderboard Widget:**

    -   Posisi sendiri di peringkat
    -   Top 3 dengan avatar
    -   Button: Lihat Leaderboard Lengkap

-   **Achievement Badges:**

    -   Display badge yang sudah diperoleh
    -   Badge yang belum: Grayscale + progress bar

-   **Quick Actions:**
    -   Button: Upload Proyek → `/proyek/buat`
    -   Button: Lihat Challenge → `/challenge`
    -   Button: Proyek Tersimpan → `/tersimpan`

---

### **3. ALUR UPLOAD PROYEK**

#### **URL: `/proyek/buat`** (Mahasiswa/Dosen only)

#### **Multi-Step Form:**

**Step 1: Informasi Dasar**

-   Input: Judul Proyek
-   Dropdown: Kategori (Web Dev, Mobile, AI/ML, dll)
-   Textarea: Deskripsi Singkat (max 200 karakter, counter)
-   Button: Lanjut

**Step 2: Konten Lengkap**

-   Rich Text Editor: Konten detail (support heading, list, code, link, image)
-   Upload Thumbnail: Drag & drop / browse (preview, max 2MB)
-   Upload Galeri: Multiple images (max 10, draggable reorder, preview grid)
-   Button: Kembali | Lanjut

**Step 3: Teknologi & Links**

-   Multi-select: Pilih Stack/Tools (autocomplete, max 10)
-   Input: URL Repository (GitHub/GitLab)
-   Input: URL Demo (link)
-   Input: URL Video Demo (YouTube/Vimeo embed)
-   Button: Kembali | Lanjut

**Step 4: Kolaborator (Opsional)**

-   Search User: Autocomplete (cari by nama/username/nim)
-   Selected List:
    -   Avatar + Nama + Role dropdown (Pembimbing/Rekan/Kontributor)
    -   Badge: Status pending
    -   Remove button
-   Button: Kembali | Lanjut

**Step 5: Publikasi**

-   Dropdown: Status (Draft/Terbit)
-   Dropdown: Visibilitas (Publik/Terbatas/Privat)
-   Checkbox: Bolehkan Komentar
-   Tag Input: Tambah tag (max 10, enter to add)
-   Preview Panel: Menampilkan summary semua input
-   Button: Kembali | Simpan

#### **Backend Process:**

1. **Validasi:** All fields sesuai rules
2. **ProyekService::buat():**
    - Insert ke tabel `proyek`
    - Upload thumbnail & galeri ke storage
    - Insert ke pivot `proyek_teknologi`
    - Insert ke tabel `kolaborator` (kirim notif undangan)
    - Log aktivitas
    - Jika status = terbit: Kirim notif ke followers
3. **Redirect:** `/proyek/{slug}` dengan toast sukses

---

### **4. ALUR CHALLENGE (END-TO-END)**

#### **A. DOSEN BUAT CHALLENGE**

**URL: `/challenge/buat`**

#### **Multi-Step Form:**

**Step 1: Informasi Challenge**

-   Input: Judul Challenge
-   Dropdown: Kategori Challenge
-   Rich Text: Deskripsi Challenge
-   Rich Text: Aturan & Persyaratan
-   Upload Banner: Drag & drop (16:9 ratio, preview)
-   Button: Lanjut

**Step 2: Jadwal & Batasan**

-   DatePicker: Tanggal Mulai
-   DatePicker: Deadline Submit
-   DatePicker: Tanggal Pengumuman (harus > deadline)
-   Input Number: Maksimal Peserta (opsional, 0 = unlimited)
-   Multi-select: Kategori Proyek Diizinkan
-   Button: Kembali | Lanjut

**Step 3: Kriteria Penilaian**

-   **Dynamic Form (Drag & Drop):**
    -   Add Kriteria Button:
        -   Input: Nama Kriteria
        -   Input: Bobot (%)
        -   Textarea: Deskripsi
    -   List Kriteria:
        -   Draggable untuk reorder
        -   Edit/Delete button
    -   Total Bobot: Auto calculate (harus = 100%)
    -   Validasi: Minimal 3 kriteria
-   Button: Kembali | Lanjut

**Step 4: Hadiah (Opsional)**

-   Rich Text: Hadiah Juara 1
-   Rich Text: Hadiah Juara 2 (opsional)
-   Rich Text: Hadiah Juara 3 (opsional)
-   Button: Kembali | Lanjut

**Step 5: Preview & Publikasi**

-   Preview Panel: Semua data dalam layout seperti detail page
-   Dropdown: Status (Draft/Buka)
-   Button: Kembali | Simpan

#### **Backend Process:**

1. **Validasi:** Form validation
2. **ChallengeService::buat():**
    - Insert ke tabel `challenge`
    - Insert ke tabel `kriteria_penilaian` (multiple records)
    - Upload banner
    - Log aktivitas
    - Jika status = buka: Kirim notif ke semua mahasiswa
3. **Redirect:** `/challenge/{slug}` dengan toast sukses

---

#### **B. MAHASISWA IKUTI CHALLENGE**

**Alur:**

1. **Browse Challenge:** `/challenge`

    - Filter: Status (Buka/Tutup/Selesai)
    - Sort: Deadline terdekat, Terbaru, Peserta terbanyak
    - Card display:
        - Banner, Judul, Kategori
        - Badge: Status, Deadline countdown
        - Info: Peserta, Hadiah
        - Button: "Lihat Detail"

2. **Detail Challenge:** `/challenge/{slug}`

    - Header: Banner, Judul, Pembuat, Tanggal
    - Tabs:
        - **Deskripsi:** Full description, rules, requirements
        - **Kriteria:** List kriteria dengan bobot
        - **Hadiah:** Prize info
        - **Peserta:** Avatar grid peserta (max 50 visible)
        - **Submission:** List submission yang sudah dinilai (jika challenge selesai)
    - Sidebar:
        - Countdown deadline
        - Progress bar: Peserta / Maks peserta
        - Button: "Ikuti Challenge" (jika belum join)
        - Button: "Submit Proyek" (jika sudah join tapi belum submit)

3. **Ikuti Challenge:** Click "Ikuti Challenge"

    - Confirm Dialog: "Yakin ikuti challenge ini?"
    - Backend:
        - Insert ke `submisi_challenge` dengan status = 'bergabung'
        - Increment `jumlah_peserta` di tabel challenge
        - Kirim notif ke pembuat challenge
    - UI Update: Button berubah jadi "Submit Proyek"
    - Toast: "Berhasil bergabung!"

4. **Submit Proyek:** Click "Submit Proyek"
    - Modal form:
        - Dropdown: Pilih Proyek (dari "Proyek Saya" yang statusnya published)
        - Preview: Thumbnail + Title + Description proyek terpilih
        - Textarea: Catatan untuk Dosen (opsional)
        - Button: Submit
    - Backend:
        - Update `submisi_challenge`:
            - Set `proyek_id`
            - Set `status = 'terkirim'`
            - Set `dikirim_pada = now()`
        - Increment `jumlah_submisi` di challenge
        - Kirim notif ke pembuat challenge
    - Toast: "Submission berhasil!"
    - Redirect back dengan badge "Sudah Submit"

---

#### **C. DOSEN REVIEW & PENILAIAN**

**URL: `/challenge/{slug}/submisi`** (Dosen only)

**Interface:**

**Left Panel: Submission List**

-   Filter: Semua/Belum Dinilai/Sudah Dinilai
-   Sort: Tanggal submit, Nama mahasiswa
-   List item:
    -   Avatar + Nama Mahasiswa + NIM
    -   Project title + thumbnail (small)
    -   Badge: Status (Terkirim/Dinilai)
    -   Tanggal submit
    -   Click → Load detail ke right panel

**Right Panel: Grading Interface**

**Jika status = 'terkirim' (Belum dinilai):**

**Section 1: Project Preview**

-   Thumbnail besar
-   Judul proyek
-   Deskripsi singkat
-   Link: Demo, Repository, Video
-   Button: "Lihat Proyek Lengkap" (open detail)
-   Catatan dari mahasiswa (jika ada)

**Section 2: Form Penilaian**

-   **Per Kriteria:**
    -   Nama Kriteria + Bobot
    -   Slider: 0-100 (default 0)
    -   Indikator warna:
        -   0-50: Red
        -   51-70: Yellow
        -   71-85: Blue
        -   86-100: Green
-   **Total Nilai (Auto Calculate):**
    -   Formula: (nilai1 × bobot1 + nilai2 × bobot2 + ...) / 100
    -   Display besar dengan animasi
-   **Grade (Auto):**
    -   A: 86-100
    -   B: 71-85
    -   C: 56-70
    -   D: 41-55
    -   E: 0-40
-   **Umpan Balik:**
    -   Rich Text Editor
    -   Saran perbaikan, kelebihan, kekurangan

**Button:**

-   Simpan & Lanjut → Auto load submission berikutnya
-   Simpan → Tetap di submission ini
-   Batal

**Backend Process:**

1. **PenilaianService::nilai():**
    - Insert ke `nilai_kriteria` (multiple records per kriteria)
    - Calculate total weighted score
    - Determine grade
    - Update `submisi_challenge`:
        - Set `nilai_total`, `grade`, `umpan_balik_html`
        - Set `status = 'dinilai'`
        - Set `dinilai_pada = now()`
    - Recalculate ranking (jika semua submission challenge ini sudah dinilai)
    - Kirim notif ke mahasiswa
    - Log aktivitas

**Jika status = 'dinilai' (Sudah dinilai):**

-   Display mode: Read-only
-   Show: Nilai per kriteria, Total, Grade, Feedback
-   Button: Edit Nilai (reload form)

**Progress Indicator:**

-   Top bar: X/Y submission sudah dinilai (progress bar)

---

#### **D. PENGUMUMAN PEMENANG**

**Setelah semua submission dinilai:**

**URL: `/challenge/{slug}/submisi`** (Dosen)

**New Button Muncul:** "Umumkan Pemenang"

**Click → Modal:**

-   **Juara 1:**
    -   Dropdown auto-filled dengan submission ranking #1
    -   Editable (bisa pilih manual)
-   **Juara 2:**
    -   Dropdown auto-filled dengan submission ranking #2
    -   Optional (bisa kosong)
-   **Juara 3:**
    -   Dropdown auto-filled dengan submission ranking #3
    -   Optional
-   Checkbox: "Kirim notifikasi ke semua peserta"
-   Button: Konfirmasi

**Backend Process:**

1. **ChallengeService::umumkanPemenang():**
    - Update tabel `challenge`:
        - Set `pemenang_1_id`, `pemenang_2_id`, `pemenang_3_id`
        - Set `status = 'selesai'`
        - Set `pemenang_diumumkan_pada = now()`
    - Update `submisi_challenge` pemenang:
        - Set `peringkat = 1/2/3`
    - Kirim notif:
        - Ke pemenang: "Selamat! Anda Juara X"
        - Ke semua peserta: "Pemenang sudah diumumkan"
    - Beri lencana ke pemenang
    - Update leaderboard points
    - Log aktivitas

**Public Display:**

-   Di `/challenge/{slug}`:
    -   Tab baru muncul: "Pemenang"
    -   Podium display:
        -   Tengah (paling tinggi): Juara 1 + avatar + nama + proyek
        -   Kiri: Juara 2
        -   Kanan: Juara 3
    -   Full submission list dengan ranking

---

### **5. ALUR INTERAKSI SOSIAL**

#### **A. LIKE PROYEK**

**UI:** Tombol ♡ di project card & detail page

**Flow:**

1. User click tombol
2. **AJAX POST** `/proyek/{slug}/suka`
3. **Backend Check:**
    - Cek di tabel `suka` → Sudah like?
    - **Jika sudah:** DELETE record, decrement `jumlah_suka`
    - **Jika belum:** INSERT record, increment `jumlah_suka`
    - **Milestone check:** Jika `jumlah_suka % 10 == 0` → Kirim notif ke owner
4. **Response:** `{ sudah_suka: true/false, jumlah_suka: 234 }`
5. **UI Update (tanpa reload):**
    - Button icon: ♡ → ♥ (filled) atau sebaliknya
    - Counter update dengan animasi count-up

**Dimana saja ada tombol like:**

-   Project card di gallery
-   Project detail page (sticky pada scroll)
-   Submission list di challenge

---

#### **B. KOMENTAR**

**UI:** Comment section di bawah project detail

**Flow Create:**

1. User ketik di textarea
2. Click "Kirim"
3. **POST** `/komentar`
    - Body: `{ proyek_id, isi_html, induk_id (null jika top-level) }`
4. **Backend:**
    - Insert ke tabel `komentar`
    - Increment `jumlah_komentar` di proyek
    - Kirim notif:
        - Ke owner proyek (jika bukan owner yang komen)
        - Ke parent comment owner (jika reply)
        - Ke user yang di-mention (jika ada @username)
5. **Response:** Comment data + user info
6. **UI Update:**
    - Append comment ke list (dengan animasi slide-in)
    - Clear textarea
    - Auto-scroll ke comment baru

**Flow Reply:**

-   Click "Balas" pada comment
-   Textarea expand di bawah comment tersebut
-   Submit → Set `induk_id` ke parent comment ID
-   Display: Nested layout dengan indent

**Flow Edit/Delete:**

-   Three-dot menu pada own comment
-   Edit: Textarea replace comment body
-   Delete: Confirm dialog → Soft delete (set `deleted_at`)

**Features:**

-   Mention: Type @ → Autocomplete user
-   Like comment: Mini ♡ button per comment
-   Sort: Terbaru / Terlama / Populer

---

#### **C. SAVE PROYEK**

**UI:** Tombol bookmark ⏷ di project card & detail

**Flow:**

1. Click bookmark
2. **AJAX POST** `/proyek/{slug}/simpan`
3. **Backend:**
    - Cek di tabel `simpanan` → Sudah save?
    - **Jika sudah:** DELETE
    - **Jika belum:** INSERT, increment `jumlah_simpan`
4. **Response:** `{ sudah_disimpan: true/false }`
5. **UI:** Button icon ⏷ → ✓ (filled)

**Access Saved:**

-   URL: `/tersimpan` (Mahasiswa only)
-   Display: Grid proyek yang disimpan
-   Feature:
    -   Filter by folder (Default/Inspirasi/Referensi/Tugas)
    -   Add note per saved project
    -   Remove from saved

---

#### **D. KOLABORATOR**

**Invite Collaborator:**

-   Di project edit page
-   Search user → Select
-   Set role: Pembimbing/Rekan/Kontributor
-   Set permission: Bisa edit? Bisa hapus?
-   Submit → Kirim notif undangan

**Respond Invitation:**

-   Notifikasi muncul di dropdown
-   Click → Modal:
    -   Info: Project, Role, Permissions
    -   Button: Terima / Tolak
-   Backend update status undangan
-   Kirim notif ke inviter

**Collaborator View:**

-   Di project detail: Avatar group collaborators
-   Hover: Tooltip nama + role
-   Click: Lihat profil

---

### **6. ALUR PENCARIAN & FILTER**

#### **A. GLOBAL SEARCH (TOPBAR)**

**UI:** Search bar di header (selalu visible)

**Flow:**

1. User ketik query (debounce 300ms)
2. **AJAX GET** `/cari?q={query}&tipe=autocomplete`
3. **Backend:** Search di multiple tables:
    - Proyek: title, deskripsi, tag
    - User: nama, username, nim, nidn
    - Challenge: title, deskripsi
4. **Response:**
    ```json
    {
      proyek: [{ id, judul, thumbnail, kategori }], // max 3
      pengguna: [{ id, nama, avatar, peran }],      // max 2
      challenge: [{ id, judul, banner, deadline }]  // max 2
    }
    ```
5. **UI:** Dropdown autocomplete muncul di bawah search bar
    - Group by tipe (Proyek, Pengguna, Challenge)
    - Click item → Redirect ke detail
    - Button "Lihat Semua" → `/cari?q={query}`

**Full Search Page:** `/cari?q={query}`

-   Tabs: Semua / Proyek / Pengguna / Challenge
-   Filter sidebar (kategori, teknologi, dll)
-   Sort: Relevansi / Terbaru / Populer
-   Grid display dengan pagination

---

#### **B. FILTER PROYEK**

**URL:** `/proyek` dengan query params

**UI:** Sidebar filter (desktop) / Bottom sheet (mobile)

**Filter Options:**

1. **Kategori:** Checkbox multiple (Web Dev, Mobile, AI/ML, dll)
2. **Teknologi:** Checkbox multiple dengan search
3. **Pemilik:** Radio (Semua / Mahasiswa / Dosen)
4. **Periode:** Radio (Hari ini / Minggu ini / Bulan ini / Tahun ini)
5. **Status:** Checkbox (Featured only)

**Flow:**

1. User ubah filter → Update URL query params
2. **AJAX GET** `/proyek?kategori=1,2&teknologi=3,4&periode=bulan_ini`
3. **Backend:** Build query dengan where clauses
4. **Response:** Filtered project data
5. **UI:**
    - Fade out grid lama
    - Show skeleton loader
    - Fade in grid baru
    - Update active filter chips (removable)

**Sort Options:**

-   Dropdown: Terbaru / Terpopuler / Paling Banyak Suka / Paling Banyak Dilihat

---

### **7. ALUR NOTIFIKASI**

#### **Real-time Notification System**

**Trigger Events:**

-   Like proyek
-   Komentar baru
-   Reply komentar
-   Undangan kolaborator
-   Respons undangan
-   Challenge baru (ke semua mahasiswa)
-   Submission baru (ke dosen)
-   Nilai keluar (ke mahasiswa)
-   Pemenang diumumkan
-   Deadline reminder (H-3, H-1)

**Backend Process:**

1. **Event terpicu** (misal: User A like proyek User B)
2. **NotifikasiService::buat():**
    ```php
    - Insert ke tabel `notifikasi`:
      - pengguna_id: User B
      - tipe: 'suka'
      - judul: 'Proyek Disukai'
      - pesan: 'User A menyukai proyek Anda: {project title}'
      - data_json: { proyek_id, pengguna_id }
    ```

**Frontend Polling:**

-   Every 30s: **GET** `/notifikasi/unread-count`
-   Response: `{ jumlah_belum_dibaca: 5 }`
-   Update badge di bell icon

**Notification Dropdown:**

-   Click bell icon → Load unread notifications
-   Display:
    -   Avatar sender
    -   Message
    -   Time ago
    -   Icon sesuai tipe
    -   Unread: Background highlight
-   Click notification:
    -   Mark as read
    -   Navigate to related page
-   Button: "Tandai Semua Dibaca"

**Notification Page:** `/notifikasi`

-   Full list dengan pagination
-   Filter: Semua / Belum Dibaca / Sudah Dibaca
-   Filter by tipe
-   Clear all button

---

### **8. ALUR LEADERBOARD**

**URL:** `/leaderboard`

**Update Scoring (CRON JOB - Daily):**

1. **Run at midnight**
2. **Loop semua user:**
    - **Calculate base points:**
        - Proyek published: +10 poin
        - Like received: +1 poin per like
        - Comment received: +2 poin per comment
        - Challenge won: +50/30/20 poin (Juara 1/2/3)
        - Project featured: +20 poin bonus
        - Submission submitted: +5 poin
    - **Apply multipliers:**
        - Verified user: ×1.2
        - Active streak (login 7 hari berturut): ×1.1
    - **Update tabel `papan_peringkat`:**
        - Set poin_total
        - Calculate ranking (order by poin_total DESC)
3. **Cache hasil** (30 menit)

**Display:**

**Podium Section:**

-   Top 3 dengan avatar besar
-   Posisi 1 di tengah (paling tinggi)
-   Posisi 2 & 3 di samping
-   Show: Nama, Poin, Badge

**Table Section:**

-   Rank 4-100
-   Column: Rank, Avatar, Nama, Prodi, Poin, Trend (↑ naik X / ↓ turun X)
-   Highlight own row

**Filter:**

-   Dropdown: Global / Per Prodi
-   Period: All Time / Bulan Ini / Minggu Ini

**Own Stats Widget (Sidebar):**

-   Current rank
-   Points
-   Next milestone (berapa poin lagi naik 1 rank)

---

### **9. TABEL CRUD (ADMIN PAGES)**

**Semua tabel management memiliki fitur sama:**

#### **Fitur Wajib:**

1. **Search Global** (search across multiple columns)
2. **Filter per Column** (input/dropdown di header column)
3. **Sort Multi-Column** (click header, shift+click untuk multi)
4. **Pagination** (dengan rows per page options: 10/25/50)
5. **Bulk Selection** (checkbox di setiap row + select all)
6. **Bulk Actions** (delete multiple)
7. **Export** (CSV/Excel button)
8. **Responsive** (mobile: card layout, desktop: table)
9. **Loading State** (skeleton loader)
10. **Empty State** (ilustrasi + text jika no data)

#### **Contoh: `/pengguna` (Admin)**

**Layout:**

**Header:**

-   Title: "Kelola Pengguna"
-   Button: "+ Tambah Pengguna" (open modal/slide-over)

**Table Toolbar:**

-   Search input (icon + placeholder "Cari nama, email, NIM...")
-   Filter dropdown: Peran (Semua/Admin/Dosen/Mahasiswa)
-   Filter dropdown: Status (Semua/Aktif/Nonaktif)
-   Action button: Hapus Terpilih (disabled jika tidak ada yang dipilih)
-   Export button: Download CSV

**Table:**

-   Columns:
    -   ☐ Checkbox (select all di header)
    -   Avatar (with fallback to initials)
    -   Nama (sortable, filterable)
    -   Email (sortable, filterable)
    -   Peran (tag badge, sortable, filterable)
    -   Status (tag badge, sortable, filterable)
    -   Terdaftar Pada (sortable)
    -   Aksi (edit icon, delete icon, toggle status switch)

**Footer:**

-   Pagination: "Menampilkan 1-10 dari 234"
-   Page numbers with prev/next
-   Rows per page dropdown

**Actions:**

-   **Edit:** Click edit icon → Open slide-over dengan form
-   **Delete:** Click delete icon → Confirm dialog → Toast feedback
-   **Toggle Status:** Switch aktif/nonaktif → Confirm → Toast
-   **Bulk Delete:** Select multiple → Click "Hapus Terpilih" → Confirm → Toast

**Toast Messages:**

-   Success: "Pengguna berhasil ditambahkan"
-   Error: "Gagal menghapus pengguna"
-   Info: "5 pengguna berhasil dihapus"

---

### **10. AUTHORIZATION FLOW**

**Contoh: User A (Mahasiswa) coba akses `/pengguna`**

```
1. Request: GET /pengguna
   ↓
2. Middleware: auth
   - Cek: User sudah login?
   - Tidak → Redirect ke /masuk
   - Ya → Lanjut
   ↓
3. Middleware: registrasi.selesai
   - Cek: registrasi_selesai = true?
   - Tidak → Redirect ke /lengkapi-profil
   - Ya → Lanjut
   ↓
4. Controller: Kelola\PenggunaController@index
   ↓
5. Policy: PenggunaPolicy@viewAny
   - Cek: User A peran === 'superadmin'?
   - Tidak → Abort 403 (Forbidden Page)
   - Ya → Lanjut
   ↓
6. Query: Get all pengguna dengan pagination
   ↓
7. Return: Inertia render 'Kelola/Pengguna' dengan data
```

**403 Forbidden Page:**

-   Ilustrasi
-   Text: "Anda tidak memiliki akses ke halaman ini"
-   Button: Kembali ke Dashboard

---

### **11. BREADCRUMB PATTERN**

**Semua halaman (kecuali dashboard & beranda) memiliki breadcrumb:**

**Contoh:**

-   `/dashboard`: (tidak ada breadcrumb)
-   `/proyek`: Dashboard > Proyek
-   `/proyek/buat`: Dashboard > Proyek > Buat Proyek
-   `/proyek/{slug}`: Dashboard > Proyek > {judul proyek}
-   `/proyek/{slug}/edit`: Dashboard > Proyek > {judul proyek} > Edit
-   `/challenge/{slug}/submisi`: Dashboard > Challenge > {judul challenge} > Penilaian Submisi
-   `/pengguna`: Dashboard > Kelola Pengguna

**UI:**

-   Link: All items kecuali yang terakhir
-   Separator: > atau / atau icon chevron
-   Mobile: Show only last 2 items

---

## 📦 TECH STACK SUMMARY

### **Backend:**

-   Laravel 12 (PHP 8.3)
-   MySQL 8.0
-   Spatie Laravel Permission (role & permission)
-   Intervention Image (image manipulation)
-   Laravel Sluggable (auto slug generation)

### **Frontend:**

-   Vue.js 3 (Composition API)
-   TypeScript
-   Inertia.js (SPA without API)
-   PrimeVue (UI component library)
-   TailwindCSS (utility-first CSS)
-   Vite (build tool)

### **Additional Libraries:**

-   TipTap (rich text editor)
-   Chart.js (charts & analytics)
-   date-fns (date manipulation)
-   VueUse (composable utilities)

### **Development:**

-   Pinia (state management - jika diperlukan)
-   Axios (HTTP client - untuk AJAX)
-   Laravel Telescope (debugging)
-   Laravel Pint (code formatting)

---

## 🎨 DESIGN PRINCIPLES

### **1. Adaptive Interface**

-   Satu URL, berbeda konten berdasarkan role
-   Policy handle authorization, bukan routing
-   Component conditional rendering based on user role

### **2. User Experience**

-   No full page reload (Inertia.js SPA)
-   Instant feedback (toast notifications)
-   Loading states everywhere (skeleton loaders)
-   Empty states with actionable message
-   Breadcrumb navigation
-   Keyboard shortcuts untuk power users

### **3. Performance**

-   Lazy loading images
-   Pagination untuk semua list
-   Query optimization (eager loading)
-   Cache leaderboard & stats
-   CDN untuk static assets

### **4. Mobile First**

-   Responsive layout
-   Touch-friendly (button size min 44x44px)
-   Bottom sheet untuk mobile filter/action
-   Swipe gestures

### **5. Accessibility**

-   Semantic HTML
-   ARIA labels
-   Keyboard navigation
-   High contrast mode support
-   Alt text untuk semua gambar

---

## 🔒 SECURITY MEASURES

### **1. Authentication**

-   Password hashing (bcrypt)
-   Email verification
-   Remember me token
-   Session management

### **2. Authorization**

-   Role-based access control
-   Policy-based ownership check
-   CSRF protection (Laravel default)
-   XSS protection (escape output)

### **3. Input Validation**

-   Form Request validation (backend)
-   Client-side validation (optional UX)
-   File upload validation (size, type, mime)
-   SQL injection protection (Eloquent ORM)

### **4. Data Privacy**

-   Soft delete untuk user data
-   GDPR compliance (data export/delete)
-   Activity logging
-   Sensitive data encryption

---

## 📊 MONITORING & LOGGING

### **Activity Log:**

**Capture:**

-   User login/logout
-   CRUD operations (create, update, delete)
-   Permission changes
-   File uploads
-   Export data

**Display:** `/log-aktivitas` (Admin only)

-   Filter by: User, Action, Date range
-   Search: By description
-   Export: CSV

### **System Health:**

-   Database connection
-   Storage usage
-   Cache hit rate
-   Average response time

---

Ini adalah rancangan lengkap dan sistematis untuk **Ignitepad** dengan konsep **clean URLs, adaptive interface, dan policy-based authorization**. Semua fitur lengkap, alur jelas, dan mudah di-maintain karena tidak ada duplikasi routing berdasarkan role.

# Alur Sistem Unuha Showcase

Berdasarkan dokumen konsep sistem, berikut adalah penjelasan alur kerja sistem secara lengkap:

---

## 🔐 **1. ALUR REGISTRASI & ONBOARDING**

### **Step 1: Registrasi Awal**
1. User mengakses `/register`
2. Mengisi form: Nama, Email, Password, Confirm Password
3. Sistem membuat akun dengan `registration_completed = false`
4. Sistem mengirim email verifikasi

### **Step 2: Verifikasi Email**
1. User membuka email dan klik link verifikasi
2. Sistem set `email_verified_at` timestamp
3. Redirect ke halaman lengkapi profil

### **Step 3: Multi-Step Complete Profile**
**Sub-Step 1:** Pilih Role
- User memilih: Mahasiswa atau Dosen

**Sub-Step 2:** Data Diri
- **Jika Mahasiswa:** Input NIM, Pilih Prodi, Angkatan, Semester, GitHub URL
- **Jika Dosen:** Input NIDN, Pilih Prodi, Jabatan, Bidang Keahlian, Scholar URL

**Sub-Step 3:** Konfirmasi
- Review semua data
- Checklist "Data sudah benar"
- Submit

### **Step 4: Selesai**
1. Sistem set `registration_completed = true`
2. Redirect ke `/dashboard`

---

## 📊 **2. ALUR DASHBOARD (ADAPTIVE)**

### **User Login → Redirect `/dashboard`**

Dashboard menampilkan konten berbeda berdasarkan role:

### **🔴 SUPERADMIN Dashboard:**
- **Stats Cards:** Total Users, Total Projects, Pending Reviews, System Health
- **Content:** Recent Activities Timeline, User Growth Chart, Quick Manage Links
- **Widgets:** System Logs, Performance Metrics

### **🔵 DOSEN Dashboard:**
- **Stats Cards:** My Challenges, Submissions to Grade, Total Projects, Engagement Rate
- **Content:** Active Challenges List, Recent Submissions, Performance Chart
- **Widgets:** Upcoming Deadlines, Announcements

### **🟢 MAHASISWA Dashboard:**
- **Stats Cards:** My Projects, Total Likes, Challenges Joined, Leaderboard Position
- **Content:** My Projects List (5 terbaru), Available Challenges, Achievement Badges
- **Widgets:** Leaderboard Widget, Quick Tips

---

## 📁 **3. ALUR UPLOAD PROJECT (MAHASISWA/DOSEN)**

### **Step 1: Navigate**
1. Login → Dashboard
2. Klik "Project Saya" di sidebar
3. Klik tombol "+ Upload Project"

### **Step 2: Form Upload (Multi-Section)**

**Section 1 - Informasi Dasar:**
- Judul Project
- Pilih Kategori (Skripsi, PKM, Tugas Kuliah, dll)
- Deskripsi Singkat (max 200 char)

**Section 2 - Konten:**
- Konten Lengkap (Rich Text Editor)
- Upload Thumbnail (max 2MB)
- Upload Gallery Images (max 10, draggable reorder)

**Section 3 - Teknologi & Links:**
- Pilih Tools/Stack (multi-select tags)
- Repository URL (GitHub/GitLab)
- Demo URL
- Video Demo URL (YouTube)

**Section 4 - Kolaborator (Optional):**
- Search dan invite user lain
- Invited list dengan status pending

**Section 5 - Publikasi:**
- Pilih: Save as Draft atau Publish Now
- Submit

### **Step 3: Backend Process**
1. **Validasi:** Title, kategori, description, thumbnail
2. **ProjectService::createProject():**
   - Save project ke database
   - Upload images ke storage
   - Sync tools (many-to-many)
   - Log activity
   - Jika publish: Notify followers
3. **Redirect:** Ke `/projects/{id}` (Detail Page)

---

## 🏆 **4. ALUR CHALLENGE (KOMPETISI)**

### **A. Dosen Buat Challenge**

**Step 1: Navigate**
- Login sebagai Dosen
- Klik "Manajemen Challenge"
- Klik "+ Buat Challenge"

**Step 2: Form Create (Multi-Step)**

**Step 1 - Informasi:**
- Judul Challenge
- Kategori
- Deskripsi (Rich Text)
- Persyaratan Peserta
- Banner Image

**Step 2 - Jadwal:**
- Tanggal Mulai
- Deadline Submit
- Tanggal Pengumuman
- Max Peserta (optional)

**Step 3 - Kriteria Penilaian:**
- Tambah kriteria (nama, bobot %, deskripsi)
- Total bobot harus = 100%
- Draggable untuk urutan

**Step 4 - Hadiah:**
- Hadiah Juara 1, 2, 3 (optional)
- Pilih status: Draft atau Open
- Preview & Submit

**Step 3: Backend Process**
1. **ChallengeService::createChallenge():**
   - Save challenge
   - Save criteria
   - Jika open: Notify All Users
2. **Redirect:** Ke `/challenges/{id}`

---

### **B. Mahasiswa Submit ke Challenge**

**Step 1: Browse Challenge**
- Login sebagai Mahasiswa
- Klik "Ikuti Challenge"
- Filter challenge dengan status "Open"
- Lihat detail challenge (deadline, kriteria, hadiah)

**Step 2: Submit**
1. Klik tombol "Submit Project"
2. Modal muncul: Pilih project dari "My Projects" atau upload baru
3. Tambah notes (optional)
4. Submit

**Step 3: Backend Process**
1. **ChallengeService::submitToChallenge():**
   - Validasi: Deadline belum lewat, belum submit sebelumnya
   - Save submission dengan status "pending"
   - Notify challenge creator (dosen)
2. **Success Toast:** "Submission berhasil!"

---

### **C. Dosen Review & Grading**

**Step 1: Navigate**
- Login sebagai Dosen
- Klik "Penilaian Challenge"
- Pilih challenge
- Klik "Lihat Submissions"

**Step 2: Review Submission**
1. List submissions dengan status "pending"
2. Klik "Review" pada submission

**Step 3: Grading Form**
- **Preview Panel:** Thumbnail, title, link repo/demo, notes peserta
- **Grading Panel:**
  - Input score per kriteria (0-100)
  - Total score (auto calculated weighted)
  - Grade otomatis (A/B/C/D/E)
  - Feedback textarea
- Submit Grade

**Step 4: Backend Process**
1. **ChallengeService::gradeSubmission():**
   - Calculate total score (weighted)
   - Save score & feedback
   - Update submission status ke "reviewed"
   - Notify mahasiswa
2. **Next:** Auto navigate ke submission berikutnya

---

### **D. Pengumuman Pemenang**

**Step 1: Setelah Semua Dinilai**
- Dosen kembali ke detail challenge
- Klik tombol "Umumkan Pemenang"

**Step 2: Select Winners**
- Modal: Dropdown untuk pilih Juara 1, 2, 3
- Confirm

**Step 3: Backend Process**
1. **ChallengeService::announceWinners():**
   - Update submission.ranking (1, 2, 3)
   - Update submission.status = "winner"
   - Update challenge.status = "completed"
   - Notify all participants
   - Create activity log
2. **Public Display:** Winner podium muncul di challenge detail page

---

## 💬 **5. ALUR INTERAKSI SOSIAL**

### **A. Like Project**
1. User view detail project
2. Klik ♡ Like button
3. **AJAX POST** `/projects/{id}/like`
4. **Backend Check:**
   - Jika sudah like: Remove (DELETE), decrement counter
   - Jika belum: Create (INSERT), increment counter
   - Jika like_count % 10 == 0: Notify owner (milestone)
5. **Response:** `{liked: true/false, like_count: 234}`
6. **UI Update:** Button jadi ♥ (filled), counter update (tanpa reload)

### **B. Comment**
1. User scroll ke comment section
2. Ketik komentar di textarea
3. Klik "Send"
4. **POST** `/comments`
5. **CommentService::createComment():**
   - Save comment
   - Increment project.comment_count
   - Notify project owner
   - Check @mention → Notify user yang di-mention
   - Jika reply: Notify parent comment owner
6. **Response:** Comment data dengan user info
7. **UI:** Append comment ke list (no reload)

### **C. Save Project**
1. User klik ⏷ Save button
2. **AJAX POST** `/projects/{id}/save`
3. **Backend:**
   - Jika sudah save: Remove
   - Jika belum: Create, increment save_count
4. **UI:** Button jadi ✓ Saved
5. **Access Saved:** Menu "Saved Projects"

---

## 🔍 **6. ALUR SEARCH & FILTER**

### **A. Global Search (Topbar)**
1. User klik search bar
2. Ketik query (debounce 300ms)
3. **AJAX:** Autocomplete muncul
   - Projects (max 3)
   - Users (max 2)
   - Challenges (max 2)
4. Klik "View All" → Redirect ke `/search?q={query}`

### **B. Filter Projects**
1. User di `/projects` (gallery)
2. Klik "Filter" button (mobile: bottom sheet, desktop: sidebar)
3. **Set Filters:**
   - Kategori (checkbox multiple)
   - Teknologi (checkbox multiple)
   - Author Type (radio)
   - Periode (radio)
   - Featured only (checkbox)
4. Klik "Apply"
5. **AJAX:** Update URL query params
6. **Backend:** Filter query, return results
7. **UI:** Fade out old, fade in new (no full reload)

---

## 🔔 **7. ALUR NOTIFIKASI**

### **Real-Time Notification**
1. **Trigger Event:** Comment, like, collaboration invite, dll
2. **Backend:** NotificationService create record
3. **Polling (every 30s):** Frontend check unread count
4. **UI Update:**
   - Bell icon badge count
   - Dropdown list update
5. **Click Notification:**
   - Mark as read
   - Navigate ke related page

---

## 🔐 **8. ALUR AUTHORIZATION**

### **Route Access Control**
```
Request → Middleware auth → Check registration_completed
         ↓
    Check Role (superadmin/dosen/mahasiswa)
         ↓
    Policy Check (can view/update/delete?)
         ↓
    Controller Action
```

### **Contoh: Edit Project**
1. User klik "Edit" pada project
2. **Middleware:** Check logged in
3. **Policy:** `ProjectPolicy@update`
   - Cek: User = owner OR admin?
   - Jika tidak: Return 403
4. Jika pass: Show edit form
5. Submit → Validate → Update

---

## 📊 **9. ALUR LEADERBOARD**

### **Update Scoring (Daily Cron)**
1. **Midnight:** Cron job run
2. **Loop All Users:**
   - Hitung base points (projects, views, likes, dll)
   - Apply multipliers (featured, verified)
   - Update user.total_score
3. **Generate Leaderboard:**
   - Sort by score DESC
   - Cache hasil (30 menit)
4. **Display:** Podium top 3, table top 100

---

## 🎯 **10. ALUR LENGKAP: Mahasiswa Upload Project → Challenge → Menang**

```
1. Mahasiswa upload project (Draft)
2. Edit & polish project
3. Publish project
4. Browse challenge yang open
5. Submit project ke challenge
6. Tunggu deadline
7. Dosen review & grading
8. Dosen umumkan pemenang
9. Mahasiswa dapat notif "Selamat! Anda Juara 1"
10. Project tampil di winner podium
11. Badge "Challenge Winner" unlock
12. Leaderboard rank naik
```

---

Semua alur ini saling terhubung dengan konsep **Single Dashboard URL** yang adaptif, **Policy-Based Authorization**, dan **Service Layer** untuk business logic yang bersih dan terstruktur.