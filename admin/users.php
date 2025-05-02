<?php
// Mulai dari parent directory
$parent_dir = dirname(__DIR__);
require_once $parent_dir . '/includes/header.php';

// Redirect jika bukan admin
if(!isAdmin()) {
    redirect('../index.php', 'Anda tidak memiliki akses ke halaman ini', 'danger');
}

// Ubah role user
if(isset($_GET['change_role'])) {
    $user_id = (int)$_GET['change_role'];
    $new_role = $_GET['role'] === 'admin' ? 'admin' : 'user';
    
    // Pastikan bukan mengubah diri sendiri
    if($user_id == $_SESSION['user_id']) {
        $message = "Anda tidak dapat mengubah role diri sendiri.";
        $message_type = "danger";
    } else {
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_role, $user_id);
        
        if($stmt->execute()) {
            $message = "Role user berhasil diubah.";
            $message_type = "success";
        } else {
            $message = "Gagal mengubah role user: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Hapus user
if(isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Pastikan bukan menghapus diri sendiri
    if($user_id == $_SESSION['user_id']) {
        $message = "Anda tidak dapat menghapus akun diri sendiri.";
        $message_type = "danger";
    } else {
        // Hapus semua data terkait user
        $conn->begin_transaction();
        
        try {
            // Hapus vote user
            $query = "DELETE FROM votes WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Hapus komentar user
            $query = "DELETE FROM comments WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Hapus thread user
            $query = "SELECT id FROM threads WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while($thread = $result->fetch_assoc()) {
                $thread_id = $thread['id'];
                
                // Hapus polling terkait thread
                $query = "SELECT id FROM polls WHERE thread_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $thread_id);
                $stmt->execute();
                $polls_result = $stmt->get_result();
                
                while($poll = $polls_result->fetch_assoc()) {
                    $poll_id = $poll['id'];
                    
                    // Hapus vote pada poll
                    $query = "DELETE FROM votes WHERE poll_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $poll_id);
                    $stmt->execute();
                    
                    // Hapus opsi polling
                    $query = "DELETE FROM poll_options WHERE poll_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $poll_id);
                    $stmt->execute();
                }
                
                // Hapus semua poll untuk thread
                $query = "DELETE FROM polls WHERE thread_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $thread_id);
                $stmt->execute();
                
                // Hapus komentar pada thread
                $query = "DELETE FROM comments WHERE thread_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $thread_id);
                $stmt->execute();
            }
            
            // Hapus thread user
            $query = "DELETE FROM threads WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Akhirnya, hapus user
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $conn->commit();
            
            $message = "User berhasil dihapus beserta semua datanya.";
            $message_type = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menghapus user: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Search user
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Ambil semua user
if(!empty($search)) {
    $search_term = "%$search%";
    $query = "SELECT *, 
             (SELECT COUNT(*) FROM threads WHERE user_id = users.id) as thread_count,
             (SELECT COUNT(*) FROM comments WHERE user_id = users.id) as comment_count
             FROM users 
             WHERE username LIKE ? OR email LIKE ?
             ORDER BY id";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT *, 
             (SELECT COUNT(*) FROM threads WHERE user_id = users.id) as thread_count,
             (SELECT COUNT(*) FROM comments WHERE user_id = users.id) as comment_count
             FROM users 
             ORDER BY id";
    $result = $conn->query($query);
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manajemen User</h1>
    </div>
    
    <div class="admin-menu">
        <a href="index.php" class="admin-menu-item">Dashboard</a>
        <a href="users.php" class="admin-menu-item active">Users</a>
        <a href="categories.php" class="admin-menu-item">Kategori</a>
        <a href="threads.php" class="admin-menu-item">Thread</a>
    </div>
    
    <?php if(isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-content">
        <div class="search-section">
            <form action="users.php" method="GET" class="search-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Cari username atau email..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn">Cari</button>
                    <?php if(!empty($search)): ?>
                        <a href="users.php" class="btn">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <h2>Daftar User</h2>
        
        <?php if($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Thread</th>
                        <th>Komentar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <?php if($user['role'] == 'admin'): ?>
                                        <a href="users.php?change_role=<?php echo $user['id']; ?>&role=user" class="role-badge admin" onclick="return confirm('Ubah role <?php echo $user['username']; ?> menjadi user?')">Admin</a>
                                    <?php else: ?>
                                        <a href="users.php?change_role=<?php echo $user['id']; ?>&role=admin" class="role-badge user" onclick="return confirm('Ubah role <?php echo $user['username']; ?> menjadi admin?')">User</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="role-badge <?php echo $user['role']; ?>"><?php echo $user['role']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($user['join_date']); ?></td>
                            <td><?php echo $user['thread_count']; ?></td>
                            <td><?php echo $user['comment_count']; ?></td>
                            <td class="action-buttons">
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('PERINGATAN: Semua data user ini (thread, komentar, voting) akan dihapus. Yakin ingin menghapus user <?php echo $user['username']; ?>?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php if(!empty($search)): ?>
                <p>Tidak ada user yang ditemukan dengan kata kunci "<?php echo $search; ?>".</p>
            <?php else: ?>
                <p>Belum ada user yang terdaftar.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.search-section {
    margin-bottom: 1.5rem;
}

.search-form .form-group {
    display: flex;
    gap: 0.5rem;
}

.search-form input {
    flex: 1;
}

.role-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.role-badge.admin {
    background-color: #dc3545;
    color: white;
}

.role-badge.user {
    background-color: #28a745;
    color: white;
}
</style>

<?php require_once $parent_dir . '/includes/footer.php'; ?>