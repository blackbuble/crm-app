# Customer Import Template Guide

## Overview
Template Excel ini dibuat untuk memudahkan import data customer ke dalam sistem CRM.

## Cara Menggunakan Template

### 1. Download Template
- Buka halaman **Customers** di aplikasi CRM
- Klik tombol **"Download Template"** (berwarna biru/info)
- File `customers-import-template.xlsx` akan terdownload

### 2. Isi Data di Template

Template memiliki struktur sebagai berikut:

| Column | Deskripsi | Required | Contoh |
|--------|-----------|----------|--------|
| **type** | Tipe customer: `company` atau `personal` | Ya | company |
| **name** | Nama lengkap (bisa kosong jika menggunakan first_name/last_name) | Tidak* | PT Contoh Indonesia |
| **email** | Email customer | Tidak | contact@contoh.co.id |
| **phone** | Nomor telepon | Tidak | +6221123456 |
| **address** | Alamat lengkap | Tidak | Jl. Sudirman No. 123, Jakarta |
| **company_name** | Nama perusahaan (required jika type=company) | Ya* | PT Contoh Indonesia |
| **tax_id** | NPWP/Tax ID (untuk company) | Tidak | 01.234.567.8-901.000 |
| **first_name** | Nama depan (required jika type=personal) | Ya* | John |
| **last_name** | Nama belakang (required jika type=personal) | Ya* | Doe |
| **notes** | Catatan tambahan | Tidak | Customer potensial |
| **status** | Status: `lead`, `prospect`, `customer`, atau `inactive` | Tidak | lead |

**Catatan:**
- Jika `type = company`: `company_name` wajib diisi
- Jika `type = personal`: `first_name` dan `last_name` wajib diisi
- Kolom `name` bisa dikosongkan, sistem akan otomatis generate dari company_name atau first_name + last_name

### 3. Contoh Data

Template sudah dilengkapi dengan 2 baris contoh:

**Contoh 1 - Company:**
```
type: company
name: PT Contoh Indonesia
email: contact@contoh.co.id
phone: +6221123456
address: Jl. Sudirman No. 123, Jakarta
company_name: PT Contoh Indonesia
tax_id: 01.234.567.8-901.000
status: lead
```

**Contoh 2 - Personal:**
```
type: personal
email: john.doe@email.com
phone: +628123456789
address: Jl. Gatot Subroto No. 45, Bandung
first_name: John
last_name: Doe
status: prospect
```

### 4. Import Data
1. Hapus baris contoh (baris 3 dan 4) setelah Anda memahami formatnya
2. Isi data customer Anda mulai dari baris 3
3. Simpan file Excel
4. Kembali ke halaman **Customers**
5. Klik tombol **"Import Excel"**
6. Upload file yang sudah diisi
7. Sistem akan memproses dan menampilkan notifikasi sukses/gagal

## Validasi Data

Sistem akan melakukan validasi:
- ✅ Email harus format yang valid
- ✅ Email harus unik (tidak boleh duplikat)
- ✅ Type harus `company` atau `personal`
- ✅ Status harus salah satu dari: `lead`, `prospect`, `customer`, `inactive`
- ✅ Jika type=company, company_name wajib diisi
- ✅ Jika type=personal, first_name dan last_name wajib diisi

## Tips
1. **Jangan hapus baris header** (baris 1)
2. **Hapus baris instruksi** (baris 2) dan contoh (baris 3-4) sebelum import
3. Gunakan format yang konsisten untuk nomor telepon
4. Pastikan email tidak ada yang duplikat
5. Jika import gagal, periksa pesan error untuk mengetahui baris mana yang bermasalah

## Troubleshooting

### Import Gagal
- Periksa apakah semua kolom required sudah diisi
- Pastikan email dalam format yang benar
- Pastikan tidak ada email yang duplikat
- Periksa apakah type diisi dengan benar (company/personal)

### Data Tidak Muncul
- Pastikan file yang diupload adalah file Excel (.xlsx atau .xls)
- Jangan mengubah nama kolom di header
- Pastikan data dimulai dari baris 3 (setelah header dan instruksi)

## Fitur Template

✨ **Header berwarna** - Memudahkan identifikasi kolom
📝 **Instruksi inline** - Panduan langsung di setiap kolom
📊 **Contoh data** - 2 contoh untuk company dan personal
🎨 **Styling otomatis** - Template sudah diformat dengan baik
📏 **Lebar kolom optimal** - Kolom sudah disesuaikan untuk kemudahan input

---

**Dibuat oleh:** CRM System
**Versi:** 1.0
**Terakhir diupdate:** 2025-12-12
