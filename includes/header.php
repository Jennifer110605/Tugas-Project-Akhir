<?php
session_start();

// Deteksi apakah file ini dipanggil dari admin
$base_path = '';
if (strpos($_SERVER['SCRIPT_FILENAME'], 'admin') !== false) {
    $base_path = '../';
}

require_once $base_path . 'includes/db.php';
require_once $base_path . 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php">
                    <p style="color: crimson;">OMLOD</p>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo $base_path; ?>create-thread.php">Buat Thread</a></li>
                        <li><a href="<?php echo $base_path; ?>profile.php">Profil</a></li>
                        <li><a href="<?php echo $base_path; ?>logout.php">Logout</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo $base_path; ?>admin/index.php">Admin Panel</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>login.php">Login</a></li>
                        <li><a href="<?php echo $base_path; ?>register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <form action="search.php" method="GET" class="search-box">
                <input type="text" name="q" placeholder="Cari thread..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </header>
    <main class="container">
        <?php displayMessage(); ?>