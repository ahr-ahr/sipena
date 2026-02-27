# SIPENA — Sistem Informasi Pengaduan & Aspirasi Sekolah

SIPENA (Sistem Informasi Pengaduan & Aspirasi Sekolah) adalah platform berbasis web untuk mengelola pengaduan, laporan, dan aspirasi warga sekolah secara terstruktur, transparan, dan terdokumentasi.

Sistem ini dirancang dengan pendekatan scalable architecture dan real-time communication untuk mendukung kebutuhan sistem modern di lingkungan pendidikan.

---

## Features

- Manajemen pengaduan & aspirasi siswa
- Alur pelaporan berjenjang (multi-role)
- Tracking status laporan secara real-time
- Dashboard monitoring
- Sistem autentikasi & role-based access
- Dokumentasi laporan terstruktur
- Notifikasi real-time (Reverb)

---

## Tech Stack

> Detail lengkap ada di [`techstack.md`](./techstack.md)

**Core Stack:**
- Laravel 12 + Octane (RoadRunner)
- MySQL / MariaDB
- Redis (cache, session, queue)
- Laravel Reverb (real-time)

**Frontend:**
- Blade + Alpine.js
- Tailwind CSS

---

## Project Structure

```

.
├── app/
├── routes/
├── resources/
├── database/
├── docker/
├── nginx/
├── techstack.md
└── README.md

````

---

## Installation

```bash
git clone https://github.com/username/sipena.git
cd sipena
````

Copy environment:

```bash
cp .env.example .env
```

Install dependencies:

```bash
composer install
```

Generate key:

```bash
php artisan key:generate
```

Run with Docker:

```bash
docker-compose up -d
```

---

## Running (Development)

```bash
php artisan serve
```

Or using Octane:

```bash
php artisan octane:start
```

---

## Real-time System

SIPENA menggunakan Laravel Reverb untuk:

* Update status laporan secara langsung
* Notifikasi user tanpa refresh
* Event-driven communication

---

## Monitoring & Observability

* Health check endpoint untuk memastikan service berjalan
* Monitoring container dengan cAdvisor
* Metrics & visualization menggunakan Prometheus + Grafana
* Optional: Laravel Pulse untuk insight aplikasi

---

## Goals

* Digitalisasi sistem pengaduan sekolah
* Meningkatkan transparansi & akuntabilitas
* Menyediakan sistem yang scalable & maintainable
* Simulasi arsitektur backend modern

---

## Notes

* Project ini dikembangkan untuk tugas ujian praktek.
* Fokus pada clean architecture, scalability, dan maintainability.

---

## Author

Ahmad Haikal Rizal
GitHub: [https://github.com/ahr-ahr](https://github.com/ahr-ahr)

```