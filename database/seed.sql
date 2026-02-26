
USE orgfinance;

INSERT INTO periods (name, start_date, end_date, is_active)
VALUES ('2025/2026', '2025-01-01', '2026-12-31', 1);

INSERT INTO users (name, username, password_hash, role) VALUES
('Admin Keuangan', 'keuangan', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'KEUANGAN'),
('Viewer User', 'viewer', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'VIEWER');

INSERT INTO categories (type,name) VALUES
('IN','Iuran'),('IN','Sponsor'),
('OUT','Konsumsi'),('OUT','Transport');

INSERT INTO payment_methods (name) VALUES ('Cash'),('Transfer'),('E-Wallet');

INSERT INTO members (period_id,name,division) VALUES
(1,'Ahmad','Ketua'),
(1,'Budi','Sekretaris'),
(1,'Citra','Bendahara');

INSERT INTO programs (period_id,name,pic,status) VALUES
(1,'Makrab','Ahmad','ongoing');

INSERT INTO transactions (period_id,trx_no,date,type,amount,description)
VALUES (1,'TRX-202501-0001','2025-01-10','IN',1000000,'Dana Awal');
