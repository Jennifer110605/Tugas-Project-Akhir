<?php
require_once 'includes/header.php';

// Ambil semua kategori
$query = "SELECT * FROM categories ORDER BY id";
$result = $conn->query($query);
?>

<div class="forum-container">
    <h1>Selamat Datang di Forum Mobile Legends</h1>
    <p>Diskusikan semua tentang Mobile Legends: Bang Bang - hero, strategi, meta, dan turnamen!</p>

    <div class="hero-details-link">
        <a href="hero-detail.php" class="btn btn-primary">
            <i class="fas fa-info"></i> Lihat Informasi Hero
        </a>
        <a href="hero-rank.php" class="btn btn-primary">
            <i class="fas fa-square-poll-vertical"></i> Lihat Peringkat Hero
        </a>
    </div>


    <div class="categories-section">
        <h2>Kategori</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="category-list">
                <?php while ($category = $result->fetch_assoc()): ?>
                    <div class="category-item">
                        <div class="category-info">
                            <h3><a href="category.php?id=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a></h3>
                            <p><?php echo $category['description']; ?></p>
                        </div>
                        <div class="category-stats">
                            <span><?php echo countThreadsInCategory($category['id']); ?> thread</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Tidak ada kategori yang tersedia.</p>
        <?php endif; ?>
    </div>

    <div class="recent-threads">
        <h2>Thread Terbaru</h2>

        <?php
        // Ambil thread terbaru
        $query = "SELECT t.*, u.username, c.name as category_name 
                  FROM threads t 
                  JOIN users u ON t.user_id = u.id 
                  JOIN categories c ON t.category_id = c.id 
                  ORDER BY t.created_at DESC LIMIT 10";
        $recent_result = $conn->query($query);
        ?>

        <?php if ($recent_result->num_rows > 0): ?>
            <div class="thread-list">
                <?php while ($thread = $recent_result->fetch_assoc()): ?>
                    <div class="thread-item">
                        <?php if ($thread['is_pinned']): ?>
                            <div class="thread-pin"><i class="fas fa-thumbtack"></i></div>
                        <?php endif; ?>

                        <div class="thread-info">
                            <h3><a href="thread.php?id=<?php echo $thread['id']; ?>"><?php echo $thread['title']; ?></a></h3>
                            <div class="thread-meta">
                                <span><i class="fas fa-user"></i> <?php echo $thread['username']; ?></span>
                                <span><i class="fas fa-folder"></i> <?php echo $thread['category_name']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($thread['created_at']); ?></span>

                                <?php if ($thread['has_poll']): ?>
                                    <span class="poll-indicator"><i class="fas fa-chart-bar"></i> Polling</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Belum ada thread yang dibuat.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>