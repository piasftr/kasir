
CREATE TABLE IF NOT EXISTS kategori_pengeluaran (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL
);

INSERT INTO kategori_pengeluaran (nama_kategori) VALUES
('Bahan Baku'),
('Gaji Karyawan'),
('Listrik & Air'),
('Gas'),
('Peralatan'),
('Sewa Tempat'),
('Transportasi'),
('Promosi'),
('Perbaikan'),
('Lain-lain');
