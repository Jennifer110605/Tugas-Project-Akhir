<?php
require_once 'includes/header.php';

// Cek ID kategori
if(!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php', 'Kategori tidak ditemukan', 'danger');
}

$category_id = (int)$_GET['id'];

// Ambil data kategori
$query = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    redirect('index.php', 'Kategori tidak ditemukan', 'danger');
}

$category = $result->fetch_assoc();

// Ambil thread dalam kategori
$query = "SELECT t.*, u.username, 
         (SELECT COUNT(*) FROM comments WHERE thread_id = t.id) as comment_count
         FROM threads t 
         JOIN users u ON t.user_id = u.id 
         WHERE t.category_id = ? 
         ORDER BY t.is_pinned DESC, t.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$threads_result = $stmt->get_result();
?>

<div class="category-container">
    <div class="category-header">
        <h1><?php echo $category['name']; ?></h1>
        <p><?php echo $category['description']; ?></p>
        
        <?php if(isLoggedIn()): ?>
            <a href="create-thread.php?category_id=<?php echo $category_id; ?>" class="btn btn-primary">Buat Thread Baru</a>
        <?php endif; ?>
    </div>
    
    <div class="threads-section">
        <h2>Thread</h2>
        
        <?php if($threads_result->num_rows > 0): ?>
            <div class="thread-list">
                <?php while($thread = $threads_result->fetch_assoc()): ?>
                    <div class="thread-item">
                        <?php if($thread['is_pinned']): ?>
                            <div class="thread-pin"><i class="fas fa-thumbtack"></i></div>
                        <?php endif; ?>
                        
                        <div class="thread-info">
                            <h3><a href="thread.php?id=<?php echo $thread['id']; ?>"><?php echo $thread['title']; ?></a></h3>
                            <div class="thread-meta">
                                <span><i class="fas fa-user"></i> <?php echo $thread['username']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($thread['created_at']); ?></span>
                                <span><i class="fas fa-comments"></i> <?php echo $thread['comment_count']; ?> komentar</span>
                                
                                <?php if($thread['has_poll']): ?>
                                    <span class="poll-indicator"><i class="fas fa-chart-bar"></i> Polling</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Belum ada thread yang dibuat di kategori ini.</p>
            <?php if(isLoggedIn()): ?>
                <p>Jadilah yang pertama membuat thread!</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>