<?php
// Mulai dari parent directory
$parent_dir = dirname(__DIR__);
require_once $parent_dir . '/includes/header.php';

// Redirect jika bukan admin
if(!isAdmin()) {
    redirect('../index.php', 'Anda tidak memiliki akses ke halaman ini', 'danger');
}

// Dashboard statistics
$stats = [];

// Total user
$query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($query);
$stats['users'] = $result->fetch_assoc()['count'];

// Total thread
$query = "SELECT COUNT(*) as count FROM threads";
$result = $conn->query($query);
$stats['threads'] = $result->fetch_assoc()['count'];

// Total comments
$query = "SELECT COUNT(*) as count FROM comments";
$result = $conn->query($query);
$stats['comments'] = $result->fetch_assoc()['count'];

// Total polls
$query = "SELECT COUNT(*) as count FROM polls";
$result = $conn->query($query);
$stats['polls'] = $result->fetch_assoc()['count'];

// Recent threads
$query = "SELECT t.*, u.username, c.name as category_name 
          FROM threads t 
          JOIN users u ON t.user_id = u.id 
          JOIN categories c ON t.category_id = c.id 
          ORDER BY t.created_at DESC LIMIT 5";
$recent_threads = $conn->query($query);

// Recent users
$query = "SELECT * FROM users ORDER BY join_date DESC LIMIT 5";
$recent_users = $conn->query($query);
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Selamat datang di panel admin Forum Mobile Legends.</p>
    </div>
    
    <div class="admin-menu">
        <a href="index.php" class="admin-menu-item active">Dashboard</a>
        <a href="users.php" class="admin-menu-item">Users</a>
        <a href="categories.php" class="admin-menu-item">Kategori</a>
        <a href="threads.php" class="admin-menu-item">Thread</a>
    </div>
    
    <div class="dashboard-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Users</div>
                <div class="stat-value"><?php echo $stats['users']; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Total Threads</div>
                <div class="stat-value"><?php echo $stats['threads']; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Total Komentar</div>
                <div class="stat-value"><?php echo $stats['comments']; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Total Polling</div>
                <div class="stat-value"><?php echo $stats['polls']; ?></div>
            </div>
        </div>
    </div>
    
    <div class="admin-content">
        <div class="admin-row">
            <div class="admin-col">
                <h2>Thread Terbaru</h2>
                
                <?php if($recent_threads->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Author</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($thread = $recent_threads->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $thread['title']; ?></td>
                                    <td><?php echo $thread['category_name']; ?></td>
                                    <td><?php echo $thread['username']; ?></td>
                                    <td><?php echo formatDate($thread['created_at']); ?></td>
                                    <td>
                                        <a href="../thread.php?id=<?php echo $thread['id']; ?>" class="btn btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada thread yang dibuat.</p>
                <?php endif; ?>
                
                <a href="threads.php" class="btn">Lihat Semua Thread</a>
            </div>
            
            <div class="admin-col">
                <h2>User Terbaru</h2>
                
                <?php if($recent_users->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td><?php echo formatDate($user['join_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada user yang terdaftar.</p>
                <?php endif; ?>
                
                <a href="users.php" class="btn">Lihat Semua User</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Admin Dashboard Styles */
.dashboard-stats {
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background-color: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
}

.stat-title {
    color: #666;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #1a73e8;
}

.admin-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

@media (max-width: 768px) {
    .admin-row {
        grid-template-columns: 1fr;
    }
}

.admin-col h2 {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f0f0f0;
}
</style>

<?php require_once $parent_dir . '/includes/footer.php'; ?>