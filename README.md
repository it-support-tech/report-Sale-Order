# NTP Trading Petroleum — Sales Order System

Pure PHP + Bootstrap 5 + PostgreSQL. No framework, no build step.

## ແລ່ນທົດລອງໃນເຄື່ອງ (local, Docker)

```bash
cp .env.example .env      # ຄັ້ງທຳອິດເທົ່ານັ້ນ
docker compose up -d --build
```

- ເວັບ: http://localhost:8080
- PostgreSQL: `localhost:5432` (user/pass/db = `reportdn` — ປ່ຽນໄດ້ໃນ `.env`)
- ຖານຂໍ້ມູນຈະຖືກສ້າງ + seed ໂດຍອັດຕະໂນມັດຈາກ `database/schema.sql` ແລະ `database/seed.sql` ໃນຄັ້ງທຳອິດທີ່ volume `db_data` ຖືກສ້າງ

ຢຸດລະບົບ: `docker compose down` (ເພີ່ມ `-v` ຖ້າຢາກລຶບຂໍ້ມູນຖານຂໍ້ມູນທັງໝົດ ແລ້ວເລີ່ມໃໝ່)

## ຂຶ້ນ server ຈິງ

Server ຕ້ອງມີ PHP 8.1+ ພ້ອມ extension `pdo_pgsql`, ແລະ PostgreSQL (local ຫຼື managed).

1. Copy ໂຄງການທັງໝົດຂຶ້ນ server
2. ຕັ້ງ document root / vhost ໃຫ້ຊີ້ໄປທີ່ folder `public/`
3. ສ້າງ `.env` (ຫຼືຕັ້ງ env vars ດ້ວຍວິທີອື່ນຂອງ hosting) ດ້ວຍ `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` ຂອງ PostgreSQL ຈິງ
4. Run `database/schema.sql` ແລ້ວ `database/seed.sql` (ຖ້າຕ້ອງການຂໍ້ມູນຕົວຢ່າງ) ໃສ່ຖານຂໍ້ມູນ
5. ເປີດເວັບ, ຈັດການ "ຂໍ້ມູນທະນາຄານ" ແລະ "ຂໍ້ມູນລູກຄ້າ" ໃຫ້ຄົບກ່ອນ ຈຶ່ງເລີ່ມອອກບິນ Sales Order

## ໂຄງສ້າງ folder

- `assets/` — logo ຕົ້ນສະບັບ
- `database/` — DDL + seed SQL
- `src/` — ຟັງຊັນ PHP ລ້ວນໆ (models + helpers), ບໍ່ແມ່ນ web root
- `public/` — web root, ຊີ້ deploy path ມາທີ່ນີ້
- `docker-compose.yml`, `docker/` — ສຳລັບ local dev ເທົ່ານັ້ນ, ບໍ່ຈຳເປັນຕ້ອງໃຊ້ຕອນຂຶ້ນ server ຈິງ
