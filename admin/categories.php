<?php
// Mulai dari parent directory
$parent_dir = dirname(__DIR__);
require_once $parent_dir . '/includes/header.php';

// Redirect jika bukan admin
if(!isAdmin()) {
    redirect('../index.php', 'Anda tidak memiliki akses ke halaman ini', 'danger');
}

// Hapus kategori
if(isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    // Periksa apakah kategori masih memiliki thread
    $query = "SELECT COUNT(*) as count FROM threads WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $thread_count = $result->fetch_assoc()['count'];
    
    if($thread_count > 0) {
        $message = "Kategori ini masih memiliki $thread_count thread. Hapus atau pindahkan thread terlebih dahulu.";
        $message_type = "danger";
    } else {
        // Hapus kategori
        $query = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $category_id);
        
        if($stmt->execute()) {
            $message = "Kategori berhasil dihapus.";
            $message_type = "success";
        } else {
            $message = "Gagal menghapus kategori: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Form tambah/edit kategori
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    $errors = [];
    
    // Validasi
    if(empty($name)) {
        $errors[] = "Nama kategori harus diisi";
    }
    
    // Jika tidak ada error
    if(empty($errors)) {
        if($category_id > 0) {
            // Update kategori
            $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $name, $description, $category_id);
            
            if($stmt->execute()) {
                $message = "Kategori berhasil diupdate.";
                $message_type = "success";
            } else {
                $message = "Gagal mengupdate kategori: " . $conn->error;
                $message_type = "danger";
            }
        } else {
            // Tambah kategori baru
            $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $name, $description);
            
            if($stmt->execute()) {
                $message = "Kategori baru berhasil ditambahkan.";
                $message_type = "success";
            } else {
                $message = "Gagal menambahkan kategori: " . $conn->error;
                $message_type = "danger";
            }
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "danger";
    }
}

// Ambil kategori yang akan diedit
$edit_category = null;
if(isset($_GET['edit'])) {
    $category_id = (int)$_GET['edit'];
    
    $query = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $edit_category = $result->fetch_assoc();
    }
}

// Ambil semua kategori
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM threads WHERE category_id = c.id) as thread_count 
          FROM categories c 
          ORDER BY c.id";
$result = $conn->query($query);
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manajemen Kategori</h1>
    </div>
    
    <div class="admin-menu">
        <a href="index.php" class="admin-menu-item">Dashboard</a>
        <a href="users.php" class="admin-menu-item">Users</a>
        <a href="categories.php" class="admin-menu-item active">Kategori</a>
        <a href="threads.php" class="admin-menu-item">Thread</a>
    </div>
    
    <?php if(isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-content">
        <div class="admin-row">
            <div class="admin-col">
                <h2><?php echo $edit_category ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></h2>
                
                <form method="POST" action="categories.php" class="category-form">
                    <?php if($edit_category): ?>
                        <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Nama Kategori</label>
                        <input type="text" id="name" name="name" value="<?php echo isset($edit_category) ? $edit_category['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"><?php echo isset($edit_category) ? $edit_category['description'] : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_category ? 'Update Kategori' : 'Tambah Kategori'; ?>
                    </button>
                    
                    <?php if($edit_category): ?>
                        <a href="categories.php" class="btn">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="admin-col">
                <h2>Daftar Kategori</h2>
                
                <?php if($result->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Thread</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($category = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo $category['name']; ?></td>
                                    <td><?php echo $category['description']; ?></td>
                                    <td><?php echo $category['thread_count']; ?></td>
                                    <td class="action-buttons">
                                        <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm">Edit</a>
                                        
                                        <?php if($category['thread_count'] == 0): ?>
                                            <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada kategori yang dibuat.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once $parent_dir . '/includes/footer.php'; ?>