-- Seed data for local dev

INSERT INTO warehouses (code, name) VALUES
    ('SVK-F', 'Savannakhet'),
    ('VTE-F', 'Vientiane'),
    ('SYB-F', 'Xayaburi'),
    ('LNT-F', 'Luang Namtha'),
    ('BLX-F', 'Bolikhamxay'),
    ('KHM-F', 'Khammouane');

INSERT INTO products (code, name, uom, default_unit_price) VALUES
    ('DB0-T-0001', 'ນ້ຳມັນດີເຊວ (Diesel)', 'Liter', 0),
    ('GS0-T-0001', 'ນ້ຳມັນເບັນຊິນ (Gasoline)', 'Liter', 0);

INSERT INTO company_banks (bank_name, account_lak, account_thb, account_usd, swift_code) VALUES
    ('BANQUE POUR LE COMMERCE EXTERIEUR LAO PUBLIC',
     '030110001337263001',
     '030110201337263001',
     '030110101337263001',
     'COEBLALA');

INSERT INTO customers (code, company_name, village, district, province, contact_person, phone, fax, tax_id, payment_term, ship_to_address) VALUES
    ('CF00077', 'ບໍລິສັດ ຫຸ້ນສ່ວນກຳມະຕາອັດໄຟປະແທດຈິນ ຈຳກັດຜູ້ດຽວ', 'ບ້ານ ສະພານເຫງັກໄຕ້', 'ເມືອງ ສີສັດຕະນາກ', 'ນະຄອນຫຼວງວຽງຈັນ', 'Wang Yujin', 'Wechat', '', '368118215000', '60 Days', 'ບ້ານ ໂນນຮຸ້ງພູ ເມືອງ ວີລະບູລີ ແຂວງ ສະຫວັນນະເຂດ');
