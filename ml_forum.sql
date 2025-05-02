-- Buat database
CREATE DATABASE IF NOT EXISTS ml_forum;
USE ml_forum;

-- Tabel Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('admin', 'user') DEFAULT 'user',
  join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Categories
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT
);

-- Tabel Threads
CREATE TABLE threads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  user_id INT NOT NULL,
  category_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  views INT DEFAULT 0,
  is_pinned BOOLEAN DEFAULT FALSE,
  has_poll BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Tabel Comments
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content TEXT NOT NULL,
  user_id INT NOT NULL,
  thread_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (thread_id) REFERENCES threads(id)
);

-- Tabel Polls
CREATE TABLE polls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id INT NOT NULL,
  question VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (thread_id) REFERENCES threads(id)
);

-- Tabel Poll Options
CREATE TABLE poll_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  option_text VARCHAR(255) NOT NULL,
  FOREIGN KEY (poll_id) REFERENCES polls(id)
);

-- Tabel Votes
CREATE TABLE votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  option_id INT NOT NULL,
  user_id INT NOT NULL,
  voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (poll_id) REFERENCES polls(id),
  FOREIGN KEY (option_id) REFERENCES poll_options(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  UNIQUE KEY unique_vote (poll_id, user_id)
);

-- Buat user admin default
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$xZtHr2hnj9uM7tYYjH3kMOfdG0iU3X34z42Qn5P6.CK1Fj42PeUOq', 'admin@mlforum.com', 'admin');
-- Password: admin123

-- Tambahkan kategori default
INSERT INTO categories (name, description) VALUES 
('Umum', 'Diskusi umum tentang Mobile Legends'),
('Hero Guide', 'Tips dan Guide tentang hero Mobile Legends'),
('Esports', 'Diskusi turnamen dan tim profesional'),
('Meta', 'Diskusi tentang meta game saat ini'),
('Patch Notes', 'Informasi update dan patch terbaru');

-- Tambahkan thread contoh
INSERT INTO threads (title, content, user_id, category_id) VALUES 
('Selamat Datang di Forum Mobile Legends!', 'Selamat datang di forum Mobile Legends! Mari berdiskusi tentang game kesayangan kita.', 1, 1),
('Guide Kimmy - Marksman Mematikan', 'Kimmy adalah salah satu marksman yang memiliki gaya permainan unik. Berikut adalah guide lengkap cara menggunakan Kimmy dengan efektif.', 1, 2),
('MPL Season 10 - Prediksi Juara', 'MPL Season 10 akan segera dimulai. Siapakah menurut kalian yang akan menjadi juara?', 1, 3);