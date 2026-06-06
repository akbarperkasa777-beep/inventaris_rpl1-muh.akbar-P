-- ============================================================
-- DATABASE: inventaris_rpl1
-- Asesmen Sumatif Akhir Semester Genap 2025-2026
-- Muh Faqihuddin Assholih, S.Kom | XI RPL 1
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventaris_rpl1;
USE inventaris_rpl1;

-- Tabel User
CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Barang
CREATE TABLE IF NOT EXISTS barang (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    jumlah INT NOT NULL DEFAULT 0,
    kondisi_barang ENUM('baik','rusak') DEFAULT 'baik',
    harga_sewa DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_barang INT NOT NULL,
    jumlah_pinjam INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE DEFAULT NULL,
    status ENUM('dipinjam','dikembalikan') DEFAULT 'dipinjam',
    FOREIGN KEY (id_user) REFERENCES user(id_user),
    FOREIGN KEY (id_barang) REFERENCES barang(id_barang)
);

-- ============================================================
-- STORED PROCEDURE: Peminjaman Barang
-- ============================================================
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS pinjam_barang(
    IN p_id_user INT,
    IN p_id_barang INT,
    IN p_jumlah INT
)
BEGIN
    DECLARE stok_tersedia INT;
    SELECT jumlah INTO stok_tersedia FROM barang WHERE id_barang = p_id_barang;
    
    IF stok_tersedia >= p_jumlah THEN
        INSERT INTO peminjaman(id_user, id_barang, jumlah_pinjam, tanggal_pinjam)
        VALUES(p_id_user, p_id_barang, p_jumlah, CURDATE());
        
        UPDATE barang SET jumlah = jumlah - p_jumlah WHERE id_barang = p_id_barang;
        SELECT 'SUKSES' AS hasil, 'Peminjaman berhasil dicatat' AS pesan;
    ELSE
        SELECT 'GAGAL' AS hasil, 'Stok tidak mencukupi' AS pesan;
    END IF;
END //
DELIMITER ;

-- ============================================================
-- STORED PROCEDURE: Pengembalian Barang
-- ============================================================
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS kembalikan_barang(
    IN p_id_pinjam INT
)
BEGIN
    DECLARE v_id_barang INT;
    DECLARE v_jumlah INT;
    
    SELECT id_barang, jumlah_pinjam INTO v_id_barang, v_jumlah
    FROM peminjaman WHERE id_pinjam = p_id_pinjam;
    
    UPDATE peminjaman 
    SET status = 'dikembalikan', tanggal_kembali = CURDATE()
    WHERE id_pinjam = p_id_pinjam;
    
    UPDATE barang SET jumlah = jumlah + v_jumlah WHERE id_barang = v_id_barang;
    
    SELECT 'SUKSES' AS hasil, 'Pengembalian berhasil' AS pesan;
END //
DELIMITER ;

-- ============================================================
-- FUNCTION: Status Barang
-- ============================================================
DELIMITER //
CREATE FUNCTION IF NOT EXISTS status_barang(jumlah INT)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE hasil VARCHAR(20);
    IF jumlah <= 0 THEN
        SET hasil = 'Habis';
    ELSEIF jumlah <= 5 THEN
        SET hasil = 'Hampir Habis';
    ELSE
        SET hasil = 'Tersedia';
    END IF;
    RETURN hasil;
END //
DELIMITER ;

-- ============================================================
-- DATA AWAL (SEED)
-- ============================================================
INSERT INTO user (nama, username, password, role) VALUES
('Administrator', 'admin', MD5('admin123'), 'admin'),
('Budi Santoso', 'budi', MD5('user123'), 'user'),
('Siti Rahayu', 'siti', MD5('user123'), 'user');

INSERT INTO barang (nama_barang, jumlah, kondisi_barang) VALUES
('Laptop Asus VivoBook', 10, 'baik'),
('Proyektor Epson', 5, 'baik'),
('Kamera Canon EOS', 3, 'baik'),
('Tripod Kamera', 8, 'baik'),
('Printer HP LaserJet', 4, 'baik'),
('Scanner Epson', 2, 'rusak'),
('Speaker Aktif', 6, 'baik'),
('Mikrofon Wireless', 4, 'baik');
