<?php
require_once 'includes/header.php';

// Redirect jika sudah login
if(isLoggedIn()) {
    redirect('index.php');
}

// Proses form registrasi
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validasi username
    if(empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif(strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username harus 3-20 karakter";
    }
    
    // Validasi email
    if(empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Validasi password
    if(empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif(strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    } elseif($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }
    
    // Cek username sudah ada atau belum
    $query = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $errors[] = "Username sudah digunakan";
    }
    
    // Cek email sudah ada atau belum
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email sudah terdaftar";
    }
    
    // Jika tidak ada error, simpan data user
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role
        
        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        if($stmt->execute()) {
            // Set session untuk user baru
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $role;
            
            redirect('index.php', 'Registrasi berhasil! Selamat datang di forum Mobile Legends', 'success');
        } else {
            $errors[] = "Terjadi kesalahan: " . $conn->error;
        }
    }
}
?>

<div class="auth-container">
    <h1>Daftar Akun Baru</h1>
    
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="register.php" method="POST" class="auth-form">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Daftar</button>
    </form>
    
    <div class="auth-links">
        <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>