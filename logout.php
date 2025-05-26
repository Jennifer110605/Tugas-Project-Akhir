<?php
session_start();
require_once 'includes/functions.php';

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login
redirect('login.php', 'Anda telah berhasil logout', 'info');
