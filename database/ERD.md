---
title: ERD Database Gardu Wisata
config:
  layout: elk
---
erDiagram
    USERS ||--o{ DUSUN : "membuat"
    USERS ||--o{ TOUR_PACKAGES : "membuat"
    USERS ||--o{ BOOKINGS : "membuat"
    USERS ||--o{ UMKM_PRODUCTS : "membuat"
    USERS ||--o{ BUDAYA : "membuat"
    USERS ||--o{ VILLAGE_PROFILE : "membuat"
    DUSUN ||--o{ DUSUN_GALLERIES : "memiliki"
    DUSUN ||--o{ DUSUN_KEUNGGULAN : "memiliki"
    TOUR_PACKAGES ||--o{ TOUR_PACKAGE_INCLUDES : "memiliki"
    TOUR_PACKAGES ||--o{ BOOKINGS : "dipesan"
    BUDAYA ||--o{ BUDAYA_SCHEDULES : "memiliki"

    USERS {
        string id PK "uuid"
        string username UK "nullable"
        string name
        string nama "nullable"
        string email UK
        enum role "superadmin | admin"
        timestamp email_verified_at "nullable"
        string password
        remember_token
        timestamp created_at
        timestamp updated_at
    }

    DUSUN {
        string id PK "uuid"
        string nama
        string rw
        int jumlah_rt
        int jumlah_penduduk
        string luas_wilayah
        text deskripsi
        text detail
        string hero_img
        string thumbnail
        bool is_active
        string created_by FK
        timestamp created_at
        timestamp updated_at
    }

    DUSUN_GALLERIES {
        string id PK "uuid"
        string dusun_id FK
        string image_url
        int urutan
        timestamp created_at
        timestamp updated_at
    }

    DUSUN_KEUNGGULAN {
        string id PK "uuid"
        string dusun_id FK
        string keunggulan
        int urutan
        timestamp created_at
        timestamp updated_at
    }

    TOUR_PACKAGES {
        string id PK "uuid"
        string nama
        text deskripsi
        decimal harga
        enum satuan "orang | grup"
        string tag "nullable"
        string durasi
        int min_participants "nullable"
        int max_participants "nullable"
        string gambar
        bool is_active
        string created_by FK
        timestamp created_at
        timestamp updated_at
    }

    TOUR_PACKAGE_INCLUDES {
        string id PK "uuid"
        string package_id FK
        string item
        int urutan
        timestamp created_at
        timestamp updated_at
    }

    BOOKINGS {
        string id PK "uuid"
        string kode_booking UK
        string nama_pemesan
        string no_wa_pemesan
        string email "nullable"
        string kota_asal
        text catatan "nullable"
        string package_id FK
        date tanggal
        string sesi
        int jumlah_peserta
        decimal total_harga
        enum status "pending | confirmed | cancelled"
        string bukti_bayar "nullable"
        text raw_wa_text "nullable"
        string created_by FK
        timestamp created_at
        timestamp updated_at
    }

    BOOKING_SESSIONS {
        string id PK "uuid"
        string nama
        time jam_mulai
        time jam_selesai
        bool is_active
        timestamp created_at
        timestamp updated_at
    }

    UMKM_PRODUCTS {
        string id PK "uuid"
        string nama
        enum kategori "Makanan | Kerajinan | Pertanian | Oleh-Oleh"
        decimal harga
        text deskripsi
        string gambar
        string no_wa_penjual
        bool is_active
        string created_by FK
        timestamp created_at
        timestamp updated_at
    }

    BUDAYA {
        string id PK "uuid"
        string judul
        string kategori
        text deskripsi
        string gambar
        int span_grid
        bool is_active
        string created_by FK
        timestamp created_at
        timestamp updated_at
    }

    BUDAYA_SCHEDULES {
        string id PK "uuid"
        string budaya_id FK
        string nama_acara
        string hari
        string jam
        text deskripsi
        bool is_active
        timestamp created_at
        timestamp updated_at
    }

    VILLAGE_PROFILE {
        string id PK "uuid"
        enum tipe "sejarah | visi | misi | pemerintahan"
        string judul
        longtext konten
        int urutan
        bool is_active
        string created_by FK
        timestamp created_at
        timestamp updated_at
    }

    VILLAGE_STATS {
        string id PK "uuid"
        string label
        string nilai
        string satuan "nullable"
        string icon "nullable"
        int urutan
        bool is_active
        timestamp created_at
        timestamp updated_at
    }

    SETTINGS {
        string id PK "uuid"
        string key UK
        text value
        string deskripsi "nullable"
        timestamp created_at
        timestamp updated_at
    }

    FONNTE_WEBHOOKS {
        string id PK "uuid"
        string phone "nullable"
        text message "nullable"
        text attachment "nullable"
        string event "nullable"
        string fonnte_type "nullable"
        json raw_payload "nullable"
        timestamp created_at
        timestamp updated_at
    }
