# Frontend API Guide

Dokumentasi ini ditujukan untuk pengembangan frontend terhadap customer API di aplikasi ini.

Artefak siap pakai:

- OpenAPI: `docs/openapi.yaml`
- Postman collection: `docs/postman_collection.json`

## Ringkasan

- Base app URL lokal: `http://localhost:8000`
- Base API URL: `http://localhost:8000/api/v1`
- Auth model: Laravel Sanctum session-based authentication
- Format body utama: JSON
- Upload file: `multipart/form-data`
- Semua endpoint protected wajib mengirim cookie session

## Aturan Auth

API ini tidak memakai bearer token. Frontend harus memakai cookie-based session.

Langkah login yang benar:

1. Panggil `GET /sanctum/csrf-cookie`
2. Simpan cookie `XSRF-TOKEN` dan `laravel-session`
3. Panggil endpoint login dengan header `X-XSRF-TOKEN`
4. Untuk request berikutnya, selalu kirim cookie session

### Header Default

```http
Accept: application/json
Content-Type: application/json
```

Untuk request yang mengubah state (`POST`, `PUT`, `DELETE`), frontend juga harus menyertakan cookie dan header CSRF.

### Contoh Fetch Wrapper

```ts
const APP_URL = 'http://localhost:8000';
const API_URL = `${APP_URL}/api/v1`;

async function getCsrfCookie() {
  await fetch(`${APP_URL}/sanctum/csrf-cookie`, {
    method: 'GET',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
  });
}

function getCookie(name: string) {
  return document.cookie
    .split('; ')
    .find((row) => row.startsWith(`${name}=`))
    ?.split('=')[1];
}

async function apiFetch(path: string, init: RequestInit = {}) {
  const method = (init.method ?? 'GET').toUpperCase();
  const needsCsrf = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

  if (needsCsrf) {
    await getCsrfCookie();
  }

  const xsrfToken = getCookie('XSRF-TOKEN');

  return fetch(`${API_URL}${path}`, {
    ...init,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      ...(init.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
      ...(needsCsrf && xsrfToken ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken) } : {}),
      ...(init.headers ?? {}),
    },
  });
}
```

### Contoh Axios Setup

```ts
import axios from 'axios';

export const http = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
});

export async function ensureCsrfCookie() {
  await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
    withCredentials: true,
    headers: {
      Accept: 'application/json',
    },
  });
}
```

## Format Response Umum

### Success single resource

```json
{
  "data": {
    "id": 1
  }
}
```

### Success collection

```json
{
  "data": [
    {
      "id": 1
    }
  ]
}
```

### Success pagination

```json
{
  "data": [],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 0
  }
}
```

### Validation error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### Unauthenticated

```json
{
  "message": "Unauthenticated."
}
```

## Guest Endpoints

### POST `/auth/register`

Membuat akun customer baru dan langsung login ke session.

Body:

```json
{
  "name": "Budi",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response 201:

```json
{
  "data": {
    "id": 1,
    "name": "Budi",
    "email": "budi@example.com",
    "created_at": "2026-04-19T15:00:00.000000Z"
  }
}
```

### POST `/auth/login`

Login customer menggunakan email dan password.

Body:

```json
{
  "email": "customer@example.com",
  "password": "password123"
}
```

Response 200:

```json
{
  "data": {
    "id": 1,
    "name": "Customer User",
    "email": "customer@example.com",
    "created_at": "2026-04-19T15:00:00.000000Z"
  }
}
```

Catatan:

- Endpoint ini hanya menerima user dengan role `customer`
- Request tanpa CSRF bootstrap akan gagal
- Rate limit login: 5 percobaan gagal per menit per IP

## Protected Endpoints

Semua endpoint di bawah ini membutuhkan session login aktif.

### POST `/auth/logout`

Menghapus session login aktif.

Response 200:

```json
{
  "message": "Logged out successfully."
}
```

### GET `/profile`

Mengambil data profil customer yang sedang login.

Response 200:

```json
{
  "data": {
    "id": 1,
    "name": "Budi",
    "email": "budi@example.com",
    "created_at": "2026-04-19T15:00:00.000000Z"
  }
}
```

### PUT `/profile`

Update profil customer.

Body yang diterima:

```json
{
  "name": "Budi Baru",
  "email": "budi-baru@example.com"
}
```

Kedua field bersifat opsional, tetapi jika dikirim harus valid.

### PUT `/profile/password`

Ubah password user aktif.

Body:

```json
{
  "current_password": "password123",
  "password": "passwordBaru123",
  "password_confirmation": "passwordBaru123"
}
```

Response 200:

```json
{
  "message": "Password changed successfully."
}
```

## Master Data

Semua endpoint master data mengembalikan collection dengan format:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Nama Item"
    }
  ]
}
```

### GET `/master/programs`

Daftar program aktif.

### GET `/master/provinces`

Daftar provinsi yang bisa dipilih.

### GET `/master/regencies/{province}`

Daftar kabupaten/kota berdasarkan `province`.

### GET `/master/districts/{regency}`

Daftar kecamatan berdasarkan `regency`.

### GET `/master/villages/{district}`

Daftar desa/kelurahan berdasarkan `district`.

### GET `/master/complaint-types`

Daftar jenis pengaduan aktif.

## Nomor Sambungan

### POST `/customer-numbers/verify`

Memverifikasi apakah nomor sambungan ada di billing.

Body:

```json
{
  "no_sambungan": "1234567890"
}
```

Response 200:

```json
{
  "data": {
    "no_sambungan": "1234567890",
    "nama_pelanggan": "Budi",
    "alamat_pelanggan": "Jl. Mawar No. 1",
    "no_ktp": "3174****1234"
  }
}
```

Kemungkinan error:

- `404` jika nomor sambungan tidak ditemukan
- `503` jika layanan billing tidak tersedia

### POST `/customer-numbers/confirm`

Menghubungkan nomor sambungan ke akun customer setelah cocok dengan NIK.

Body:

```json
{
  "no_sambungan": "1234567890",
  "nik": "3174000011112222"
}
```

Response 200:

```json
{
  "data": {
    "id": 1,
    "no_sambungan": "1234567890",
    "verified_at": "2026-04-19T15:00:00.000000Z",
    "created_at": "2026-04-19T15:00:00.000000Z"
  }
}
```

Kemungkinan error:

- `404` jika nomor sambungan tidak ditemukan
- `409` jika nomor sambungan sudah pernah terhubung ke akun ini
- `422` jika NIK tidak cocok atau NIK billing belum tersedia
- `503` jika layanan billing tidak tersedia

### GET `/customer-numbers`

List nomor sambungan yang dimiliki user.

Response item:

```json
{
  "id": 1,
  "no_sambungan": "1234567890",
  "verified_at": "2026-04-19T15:00:00.000000Z",
  "created_at": "2026-04-19T15:00:00.000000Z"
}
```

### GET `/customer-numbers/{no}/billing`

Mengambil snapshot data billing dari nomor sambungan yang sudah terhubung.

Response 200:

```json
{
  "data": {
    "no_sambungan": "1234567890",
    "nama_pelanggan": "Budi",
    "alamat_pelanggan": "Jl. Mawar No. 1",
    "no_ktp": "3174****1234"
  }
}
```

### DELETE `/customer-numbers/{no}`

Menghapus nomor sambungan dari akun user.

Response 200:

```json
{
  "message": "Nomor sambungan berhasil dihapus dari akun Anda."
}
```

## Registrasi SR

### POST `/registrations`

Membuat registrasi SR baru. Field hanya yang benar-benar tersedia di form frontend yang perlu dikirim. Mayoritas field bersifat nullable.

Body minimal:

```json
{
  "nama_lengkap": "Budi",
  "program_id": 1,
  "no_ktp": "3174000011112222",
  "no_hp": "08123456789"
}
```

Field yang didukung:

- `nama_lengkap`
- `program_id`
- `no_ktp`
- `no_kk`
- `pekerjaan`
- `email`
- `no_telp`
- `no_hp`
- `alamat_ktp`
- `dusun_kampung_ktp`
- `rt_ktp`
- `rw_ktp`
- `province_id_ktp`
- `regency_id_ktp`
- `district_id_ktp`
- `village_id_ktp`
- `alamat_pasang`
- `dusun_kampung_pasang`
- `rt_pasang`
- `rw_pasang`
- `province_id_pasang`
- `regency_id_pasang`
- `district_id_pasang`
- `village_id_pasang`
- `jumlah_penghuni_tetap`
- `jumlah_penghuni_tidak_tetap`
- `jumlah_kran_air_minum`
- `jenis_rumah`
- `jumlah_kran`
- `daya_listrik`
- `upload_ktp`
- `upload_kk`
- `upload_tagihan_listrik`
- `upload_foto_rumah`
- `latitude`
- `longitude`

Nilai valid untuk `jenis_rumah`:

- `Permanen`
- `Semi Permanen`
- `Non Permanen`

Response 201 mengembalikan detail registrasi lengkap.

### GET `/registrations`

List registrasi user dengan pagination.

Query params:

- `per_page`, default `15`, maksimum `100`

Contoh item `data`:

```json
{
  "id": 1,
  "no_surat": "SRPB-0001",
  "nama_lengkap": "Budi",
  "program_id": 1,
  "no_ktp": "3174****1234",
  "source": "mobile",
  "tanggal": "2026-04-19T00:00:00+00:00",
  "has_survey": false,
  "created_at": "2026-04-19T15:00:00+00:00"
}
```

### GET `/registrations/{registration}`

Mengambil detail satu registrasi milik user.

Field response utama:

- semua field form registrasi
- `no_surat`
- `source`
- `tanggal`
- `created_at`
- `has_survey`
- `survey`, jika relasi survey sudah ada

Contoh `survey`:

```json
{
  "no_survey": "SRV-0001",
  "tanggal_survey": "2026-04-19T15:00:00+00:00"
}
```

## Pengaduan

### POST `/complaints`

Membuat pengaduan baru.

Body:

```json
{
  "no_sambungan": "1234567890",
  "complaint_type_id": 1,
  "judul_pengaduan": "Air kecil",
  "isi_pengaduan": "Debit air sangat kecil sejak pagi.",
  "foto": [
    "bukti-1.jpg",
    "bukti-2.jpg"
  ],
  "latitude": -6.2,
  "longitude": 106.8
}
```

Catatan:

- `foto` adalah array nama file hasil endpoint upload
- maksimum 5 file
- `no_sambungan` harus sudah diverifikasi dan dimiliki user

Response 201:

```json
{
  "data": {
    "id": 1,
    "no_pengaduan": "PGD-0001",
    "complaint_type": {
      "id": 1,
      "name": "Kebocoran"
    },
    "no_sambungan": "1234567890",
    "nama": "Budi",
    "alamat": "Jl. Mawar No. 1",
    "latitude": -6.2,
    "longitude": 106.8,
    "email": null,
    "no_hp": null,
    "no_ktp": null,
    "sumber": "mobile_apps",
    "judul_pengaduan": "Air kecil",
    "isi_pengaduan": "Debit air sangat kecil sejak pagi.",
    "foto": [
      "bukti-1.jpg",
      "bukti-2.jpg"
    ],
    "tanggal": "2026-04-19T15:00:00+00:00",
    "status": "open",
    "priority": "normal",
    "created_at": "2026-04-19T15:00:00+00:00"
  }
}
```

### GET `/complaints`

List pengaduan milik user dengan pagination.

Query params:

- `per_page`, default `15`, maksimum `100`

Contoh item `data`:

```json
{
  "id": 1,
  "no_pengaduan": "PGD-0001",
  "complaint_type": {
    "id": 1,
    "name": "Kebocoran"
  },
  "no_sambungan": "1234567890",
  "judul_pengaduan": "Air kecil",
  "status": "open",
  "priority": "normal",
  "tanggal": "2026-04-19T15:00:00+00:00",
  "created_at": "2026-04-19T15:00:00+00:00"
}
```

### GET `/complaints/{complaint}`

Mengambil detail pengaduan.

### GET `/complaints/{complaint}/timeline`

Mengambil follow up pengaduan.

Contoh item `data`:

```json
{
  "id": 1,
  "work_order": "survey",
  "notes": "Petugas akan datang besok pagi.",
  "photos": [
    "foto-1.jpg"
  ],
  "carbon_copies": [],
  "follow_up_at": "2026-04-19T15:00:00+00:00",
  "created_at": "2026-04-19T15:00:00+00:00"
}
```

## Upload File

### POST `/uploads`

Upload file lampiran sebelum dipakai di form registrasi atau pengaduan.

Format request:

- `Content-Type: multipart/form-data`
- field file: `file`

Validasi file:

- ekstensi: `jpg`, `jpeg`, `png`, `pdf`
- mime type: `image/jpeg`, `image/png`, `application/pdf`
- maksimum: `2048 KB`

Contoh menggunakan `FormData`:

```ts
const formData = new FormData();
formData.append('file', file);

await apiFetch('/uploads', {
  method: 'POST',
  body: formData,
});
```

Response 201:

```json
{
  "data": {
    "filename": "abc123.jpg",
    "original_name": "ktp.jpg",
    "size": 120045,
    "mime_type": "image/jpeg"
  }
}
```

Gunakan nilai `filename` ini untuk field seperti `upload_ktp`, `upload_kk`, `upload_tagihan_listrik`, `upload_foto_rumah`, atau elemen array `foto` pada pengaduan.

## Error Handling Yang Perlu Ditangani Frontend

- `401 Unauthenticated`: session belum ada atau sudah expired
- `403 Forbidden`: user sudah login lalu mencoba login lagi, atau akses ditolak oleh policy tertentu
- `404 Not Found`: resource milik user tidak ditemukan
- `409 Conflict`: data sudah pernah ditautkan, misalnya nomor sambungan sudah ada
- `422 Validation Error`: tampilkan pesan per field dari `errors`
- `429 Too Many Requests`: retry dengan backoff, terutama pada login
- `503 Service Unavailable`: layanan billing eksternal sedang down

## Saran Implementasi Frontend

- Selalu gunakan `credentials: 'include'` pada `fetch` atau `withCredentials: true` pada Axios
- Buat interceptor untuk redirect ke halaman login saat menerima `401`
- Pisahkan helper upload file dari helper submit form JSON
- Simpan hanya data user di state frontend, jangan mencoba membaca cookie session secara manual selain token CSRF bila benar-benar diperlukan
- Untuk form registrasi dan pengaduan, upload file lebih dulu lalu kirim `filename` hasil upload ke endpoint utama