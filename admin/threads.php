<?php
// Mulai dari parent directory
$parent_dir = dirname(__DIR__);
require_once $parent_dir . '/includes/header.php';

// Redirect jika bukan admin
if (!isAdmin()) {
    redirect('../index.php', 'Anda tidak memiliki akses ke halaman ini', 'danger');
}

// Pin/unpin thread
if (isset($_GET['pin'])) {
    $thread_id = (int)$_GET['pin'];
    $is_pinned = (int)$_GET['status'] === 1 ? 0 : 1;

    $query = "UPDATE threads SET is_pinned = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $is_pinned, $thread_id);

    if ($stmt->execute()) {
        $message = $is_pinned ? "Thread berhasil dipin ke atas." : "Thread berhasil di-unpin.";
        $message_type = "success";
    } else {
        $message = "Gagal mengubah status pin thread: " . $conn->error;
        $message_type = "danger";
    }
}


// Hapus thread
if (isset($_GET['delete'])) {
    $thread_id = (int)$_GET['delete'];
    if (deleteThread($thread_id)) {
        $message = "Thread berhasil dihapus beserta semua datanya.";
        $message_type = "success";
    } else {
        $message = "Gagal menghapus thread.";
        $message_type = "danger";
    }
}

// Filter berdasarkan kategori
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Ambil semua kategori untuk filter
$query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($query);
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}

// Search thread
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Ambil semua thread
$params = [];
$types = '';
$where_clause = '';

if (!empty($search)) {
    $search_term = "%$search%";
    $where_clause .= "WHERE t.title LIKE ? OR t.content LIKE ?";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

if ($category_filter > 0) {
    if (empty($where_clause)) {
        $where_clause .= "WHERE t.category_id = ?";
    } else {
        $where_clause .= " AND t.category_id = ?";
    }
    $params[] = $category_filter;
    $types .= 'i';
}

$query = "SELECT t.*, u.username, c.name as category_name, 
         (SELECT COUNT(*) FROM comments WHERE thread_id = t.id) as comment_count
         FROM threads t 
         JOIN users u ON t.user_id = u.id 
         JOIN categories c ON t.category_id = c.id 
         $where_clause
         ORDER BY t.is_pinned DESC, t.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manajemen Thread</h1>
    </div>

    <div class="admin-menu">
        <a href="index.php" class="admin-menu-item">Dashboard</a>
        <a href="users.php" class="admin-menu-item">Users</a>
        <a href="categories.php" class="admin-menu-item">Kategori</a>
        <a href="threads.php" class="admin-menu-item active">Thread</a>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="admin-content">
        <div class="filter-section">
            <form action="threads.php" method="GET" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Cari judul atau konten..." value="<?php echo $search; ?>">

                    <select name="category">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn">Filter</button>

                    <?php if (!empty($search) || $category_filter > 0): ?>
                        <a href="threads.php" class="btn">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h2>Daftar Thread</h2>

        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Author</th>
                        <th>Tanggal</th>
                        <th>Views</th>
                        <th>Komentar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($thread = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $thread['id']; ?></td>
                            <td>
                                <a href="../thread.php?id=<?php echo $thread['id']; ?>" target="_blank">
                                    <?php echo $thread['title']; ?>
                                </a>
                                <?php if ($thread['has_poll']): ?>
                                    <span class="poll-badge">Poll</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $thread['category_name']; ?></td>
                            <td><?php echo $thread['username']; ?></td>
                            <td><?php echo formatDate($thread['created_at']); ?></td>
                            <td><?php echo $thread['views']; ?></td>
                            <td><?php echo $thread['comment_count']; ?></td>
                            <td>
                                <?php if ($thread['is_pinned']): ?>
                                    <a href="threads.php?pin=<?php echo $thread['id']; ?>&status=1" class="status-badge pinned" title="Klik untuk un-pin">Pinned</a>
                                <?php else: ?>
                                    <a href="threads.php?pin=<?php echo $thread['id']; ?>&status=0" class="status-badge unpinned" title="Klik untuk pin">Unpin</a>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <a href="threads.php?delete=<?php echo $thread['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('PERINGATAN: Semua data thread ini (komentar, polling) akan dihapus. Yakin ingin menghapus thread?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php if (!empty($search) || $category_filter > 0): ?>
                <p>Tidak ada thread yang ditemukan dengan filter yang dipilih.</p>
            <?php else: ?>
                <p>Belum ada thread yang dibuat.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .filter-section {
        margin-bottom: 1.5rem;
    }

    .filter-form .form-group {
        display: flex;
        gap: 0.5rem;
    }

    .filter-form input,
    .filter-form select {
        flex: 1;
    }

    .poll-badge {
        display: inline-block;
        background-color: #28a745;
        color: white;
        font-size: 0.75rem;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        margin-left: 0.5rem;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .status-badge.pinned {
        background-color: #dc3545;
        color: white;
    }

    .status-badge.unpinned {
        background-color: #6c757d;
        color: white;
    }
</style>

<?php require_once $parent_dir . '/includes/footer.php'; ?>