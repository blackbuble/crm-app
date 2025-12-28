# 👔 Panduan Khusus: Super Admin
*Level: Global System Controller*

Selamat datang, **Super Admin**. Anda memegang kendali penuh atas ekosistem CRM ini. Berikut adalah alur kerja utama Anda:

## 1. 👥 Manajemen Pengguna & Keamanan
- **Users & Teams**: Kelola siapa saja yang bisa mengakses sistem. Pastikan setiap user memiliki **Manager** yang tepat untuk menjaga hierarki laporan.
- **Filament Shield**: Atur izin akses (permissions) secara granular. Anda bisa menentukan siapa yang boleh melihat budget customer atau siapa yang boleh mengekspor data ke Excel.
- **Audit Logs**: Pantau aktivitas sistem untuk memastikan keamanan data perusahaan.

## 2. ⚙️ Konfigurasi Global
- **Pricing Configuration**: Atur skema harga paket pernikahan dan add-on. Sistem menggunakan satu sumber kebenaran (single source of truth) untuk semua kalkulasi harga di aplikasi.
- **Branding & Logo**: Update logo perusahaan dan warna tema melalui menu Settings untuk menjaga identitas brand di semua dashboard.
- **Storage Settings**: Konfigurasikan penyimpanan file (Cloudflare R2/S3) untuk dokumen quotation dan material marketing.

## 3. 🌍 Ekspansi Internasional
- Atur kode negara (country codes) dan wilayah (areas) baru seiring dengan pertumbuhan bisnis ke luar negeri.
- Pantau performa antar negara melalui dashboard Country Manager.

---

# 🌏 Panduan Khusus: Country Manager
*Level: National Operations Lead*

Sebagai **Country Manager**, fokus utama Anda adalah memastikan operasional di negara Anda berjalan efisien dan mencapai target KPI.

## 1. 📈 Monitoring Performa Nasional
- **KPI Dashboard**: Pantau total leads, konversi, dan performa setiap area di negara Anda dalam satu tampilan.
- **Revenue Tracking**: Track total estimasi deal yang masuk dari pameran-pameran di wilayah Anda.

## 2. 🏛️ Manajemen Hierarki Area
- **Manager Oversight**: Pastikan setiap **Sales Manager** mengelola Sales Rep mereka dengan benar.
- **Data Oversight**: Anda memiliki akses untuk melihat semua customer di negara Anda, namun tidak memiliki akses ke pengaturan teknis sistem (Shield/Settings).

## 3. 🎯 Strategi Lokal
- Sesuaikan template pesan WhatsApp yang digunakan oleh tim di negara Anda agar relevan dengan budaya dan bahasa lokal.

---

# 🤝 Panduan Khusus: Sales Manager
*Level: Area Team Leader*

Tugas Anda adalah memimpin tim sales di lapangan (**Area**) dan memastikan setiap lead difollow-up dengan kualitas terbaik.

## 1. 📋 Manajemen Leads & Pipeline
- **Kanban Board**: Pantau pergerakan customer tim Anda dari 'Lead' ke 'Customer'. Pastikan tidak ada kartu yang tertahan terlalu lama di satu kolom.
- **Re-assignment**: Alokasikan leads dari pameran ke Sales Rep yang paling kompeten atau yang memiliki beban kerja paling sedikit.

## 2. ✍️ Approval & Review
- **Quotation Review**: Periksa setiap quotation yang dibuat oleh tim Anda sebelum dikirim ke klien untuk menjaga profesionalisme.
- **Follow-up Monitoring**: Pastikan setiap Sales Rep menjadwalkan "Next Commitment" setelah interaksi dengan customer.

---

# 🚀 Panduan Khusus: Sales Rep
*Level: Frontline Closer*

Anda adalah ujung tombak perusahaan. Gunakan alat-alat ini untuk menutup deal lebih cepat dan lebih banyak.

## 1. ⚡ Quick Lead Entry (Kiosk Mode)
Saat pameran, gunakan menu **Quick Lead Entry**:
- Input data pengunjung dalam < 30 detik.
- Berikan estimasi harga instan (Quick Price Estimator) untuk memukau pengunjung.
- **Kunci Lead**: Gunakan fitur "Promo Locked" untuk memberikan urgensi kepada pengunjung.

## 📱 Otomatisasi WhatsApp
- Jangan mengetik sapaan yang sama berulang kali. Gunakan **WA Templates** yang sudah Anda buat.
- Gunakan placeholder `{name}` agar pesan terasa personal dan profesional secara otomatis.

## 📅 Manajemen Harian
- Cek dashboard untuk melihat daftar "Today's Follow-up".
- Update status customer di **Kanban Board** segera setelah ada kemajuan prapanjual.
