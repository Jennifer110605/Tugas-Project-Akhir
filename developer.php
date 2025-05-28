<?php
require_once 'includes/header.php';
?>

<div class="forum-container">
    <h1>Informasi Developer</h1>

    <div class="developer-section">
        <div class="developer-card">
            <div class="developer-header">
                <h2>About the Developers</h2>
            </div>
            <div class="developer-content">
                <div class="developers-grid">
                    <!-- Developer 1 -->
                    <div class="developer-profile">
                        <h3>Developer 1</h3>
                        <div>
                            <img src="img/raffi.jpg" alt="Raffi" class="developer-image">
                        </div>
                        <div class="info-item">
                            <strong>Nama:</strong> Raffi Ali Noer Golonda
                        </div>
                        <div class="info-item">
                            <strong>NIM:</strong> 230211060051
                        </div>
                        <a href="https://www.instagram.com/raffi_golonda/" class="social-link" target="_blank" rel="noopener noreferrer">
                            <img src="img/logo_ig.png" alt="">
                        </a>
                    </div>

                    <!-- Developer 2 -->
                    <div class="developer-profile">
                        <h3>Developer 2</h3>
                        <div>
                            <img src="img/jennifer.jpg" alt="Jennifer" class="developer-image">
                        </div>
                        <div class="info-item">
                            <strong>Nama:</strong> Jennifer Gloria Manoppo
                        </div>
                        <div class="info-item">
                            <strong>NIM:</strong> 230211060081
                        </div>
                        <a href="https://www.instagram.com/jennajahya_/" class="social-link" target="_blank" rel="noopener noreferrer">
                            <img src="img/logo_ig.png" alt="">
                        </a>
                    </div>

                    <!-- Developer 3 -->
                    <div class="developer-profile">
                        <h3>Developer 3</h3>
                        <div>
                            <img src="img/natanael.jpg" alt="Natanael" class="developer-image">
                        </div>
                        <div class="info-item">
                            <strong>Nama:</strong> Natanael Parulian Sitompul
                        </div>
                        <div class="info-item">
                            <strong>NIM:</strong> 230211060087
                        </div>
                        <a href="https://www.instagram.com/neanteaa/" class="social-link" target="_blank" rel="noopener noreferrer">
                            <img src="img/logo_ig.png" alt="">
                        </a>
                    </div>
                </div>

                <div class="project-info">
                    <h3>About This Project</h3>
                    <p>OMLOD (Opini Masyarakat Land of Dawn) adalah forum diskusi untuk para pemain Mobile Legends: Bang Bang.
                        Forum ini dibuat sebagai Tugas Project Akhir untuk matakuliah Pemrograman Web-TIK2032. Project ini dibuat oleh mahasiswa Program Studi Teknik Informatika, Universitas Sam Ratulangi, Manado, 2025.</p>

                    <h4>Features:</h4>
                    <ul>
                        <li>Forum diskusi dengan kategori berbeda</li>
                        <li>Sistem polling untuk mendapatkan opini pemain</li>
                        <li>Informasi hero dan statistik terkini</li>
                        <li>Peringkat hero berdasarkan win rate, pick rate, dan ban rate</li>
                        <li>Manajemen user dengan role berbeda (User, Admin, Super Admin)</li>
                        <li>Sistem pencarian thread</li>
                        <li>Badge untuk Admin dan Super Admin</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .developer-section {
        margin-top: 2rem;
    }

    .developer-card {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .developer-header {
        background-color: #1e1e2c;
        color: white;
        padding: 1.5rem;
    }

    .developer-header h2 {
        margin: 0;
    }

    .developer-content {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .developers-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }

    .developer-profile {
        background-color: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .developer-profile:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .developer-image {
        width: 200px;
        height: auto;
        border-radius: calc(infinity * 1px);
        margin-bottom: 1rem;
        justify-content: center;
    }

    .social-link img {
        width: 40px;
        height: auto;
        margin-top: 1rem;
    }

    .developer-profile h3 {
        color: #1e1e2c;
        margin-top: 0;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .info-item {
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .project-info {
        background-color: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        line-height: 1.6;
    }

    .project-info h3 {
        color: #1e1e2c;
        margin-top: 0;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .project-info h4 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .project-info ul {
        list-style-type: disc;
        padding-left: 1.5rem;
    }

    @media (max-width: 992px) {
        .developers-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .developers-grid {
            grid-template-columns: 1fr;
        }

        .developer-content {
            padding: 1.5rem;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>