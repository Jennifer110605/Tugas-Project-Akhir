<?php
require_once 'includes/header.php';

// Redirect jika belum login
if(!isLoggedIn()) {
    redirect('login.php', 'Anda harus login untuk melihat profil', 'warning');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Proses form update profil
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validasi email
    if(empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Cek apakah email sudah digunakan user lain
    if($email != $user['email']) {
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email sudah digunakan oleh user lain";
        }
    }
    
    // Update password jika diisi
    $update_password = false;
    if(!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Validasi password lama
        if(empty($current_password)) {
            $errors[] = "Password lama harus diisi";
        } elseif(!password_verify($current_password, $user['password'])) {
            $errors[] = "Password lama salah";
        }
        
        // Validasi password baru
        if(empty($new_password)) {
            $errors[] = "Password baru harus diisi";
        } elseif(strlen($new_password) < 6) {
            $errors[] = "Password baru minimal 6 karakter";
        } elseif($new_password != $confirm_password) {
            $errors[] = "Konfirmasi password baru tidak cocok";
        }
        
        $update_password = true;
    }
    
    // Jika tidak ada error, update profil
    if(empty($errors)) {
        if($update_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $email, $hashed_password, $user_id);
        } else {
            $query = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $email, $user_id);
        }
        
        if($stmt->execute()) {
            redirect('profile.php', 'Profil berhasil diupdate', 'success');
        } else {
            $errors[] = "Gagal mengupdate profil: " . $conn->error;
        }
    }
}

// Ambil thread yang dibuat user
$query = "SELECT t.*, c.name as category_name, 
         (SELECT COUNT(*) FROM comments WHERE thread_id = t.id) as comment_count
         FROM threads t 
         JOIN categories c ON t.category_id = c.id 
         WHERE t.user_id = ? 
         ORDER BY t.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$threads_result = $stmt->get_result();

// Ambil komentar yang dibuat user
$query = "SELECT c.*, t.title as thread_title 
          FROM comments c 
          JOIN threads t ON c.thread_id = t.id 
          WHERE c.user_id = ? 
          ORDER BY c.created_at DESC 
          LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$comments_result = $stmt->get_result();
?>

<div class="profile-container">
    <div class="profile-header">
        <h1>Profil Saya</h1>
        <div class="profile-info">
            <div class="profile-detail">
                <h3><?php echo $user['username']; ?></h3>
                <p><i class="fas fa-user-circle"></i> <?php echo $user['role']; ?></p>
                <p><i class="fas fa-clock"></i> Bergabung: <?php echo formatDate($user['join_date']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="profile-tabs">
            <ul class="nav-tabs">
                <li class="tab-item active" data-tab="account">Akun</li>
                <li class="tab-item" data-tab="threads">Thread Saya</li>
                <li class="tab-item" data-tab="comments">Komentar</li>
            </ul>
            
            <div class="tab-content">
                <!-- Tab Akun -->
                <div class="tab-pane active" id="account">
                    <h2>Edit Profil</h2>
                    
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="profile.php" method="POST" class="profile-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo $user['username']; ?>" readonly disabled>
                            <small>Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>
                        
                        <h3>Ubah Password</h3>
                        <div class="form-group">
                            <label for="current_password">Password Lama</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password">
                            <small>Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
                
                <!-- Tab Thread -->
                <div class="tab-pane" id="threads">
                    <h2>Thread Saya</h2>
                    
                    <?php if($threads_result->num_rows > 0): ?>
                        <div class="thread-list">
                            <?php while($thread = $threads_result->fetch_assoc()): ?>
                                <div class="thread-item">
                                    <div class="thread-info">
                                        <h3><a href="thread.php?id=<?php echo $thread['id']; ?>"><?php echo $thread['title']; ?></a></h3>
                                        <div class="thread-meta">
                                            <span><i class="fas fa-folder"></i> <?php echo $thread['category_name']; ?></span>
                                            <span><i class="fas fa-clock"></i> <?php echo formatDate($thread['created_at']); ?></span>
                                            <span><i class="fas fa-comments"></i> <?php echo $thread['comment_count']; ?> komentar</span>
                                            <span><i class="fas fa-eye"></i> <?php echo $thread['views']; ?> views</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>Anda belum membuat thread.</p>
                        <a href="create-thread.php" class="btn btn-primary">Buat Thread Baru</a>
                    <?php endif; ?>
                </div>
                
                <!-- Tab Komentar -->
                <div class="tab-pane" id="comments">
                    <h2>Komentar Saya</h2>
                    
                    <?php if($comments_result->num_rows > 0): ?>
                        <div class="comments-list">
                            <?php while($comment = $comments_result->fetch_assoc()): ?>
                                <div class="comment-item">
                                    <div class="comment-thread">
                                        <a href="thread.php?id=<?php echo $comment['thread_id']; ?>"><?php echo $comment['thread_title']; ?></a>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo nl2br($comment['content']); ?>
                                    </div>
                                    <div class="comment-meta">
                                        <span><i class="fas fa-clock"></i> <?php echo formatDate($comment['created_at']); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>Anda belum membuat komentar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabItems = document.querySelectorAll('.tab-item');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabItems.forEach(function(tab) {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panes
            tabItems.forEach(function(item) {
                item.classList.remove('active');
            });
            
            tabPanes.forEach(function(pane) {
                pane.classList.remove('active');
            });
            
            // Add active class to current tab and pane
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>