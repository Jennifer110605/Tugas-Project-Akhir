<?php
require_once 'includes/header.php';

// Ambil semua kategori
$query = "SELECT * FROM categories ORDER BY id";
$result = $conn->query($query);
?>

<div class="forum-container">
    <h1>Selamat Datang di <b style="color:crimson;">Opini Masyarakat Land of Dawn</b></h1>
    <p>Diskusikan semua tentang Mobile Legends: Bang Bang - hero, strategi, meta, dan turnamen!</p>

    <div class="hero-details-link">
        <a href="hero-detail.php" class="btn btn-primary">
            <i class="fas fa-info"></i> Lihat Informasi Hero
        </a>
        <a href="hero-rank.php" class="btn btn-primary">
            <i class="fas fa-square-poll-vertical"></i> Lihat Peringkat Hero
        </a>
        <a href="developer.php" class="btn btn-primary">
            <i class="fas fa-shield"></i> Lihat Informasi Developer
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
        $query = "SELECT t.*, u.username, u.role as user_role, c.name as category_name 
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
                                <span>
                                    <i class="fas fa-user"></i> <?php echo $thread['username']; ?>
                                    <?php if ($thread['user_role'] == 'admin'): ?>
                                        <span class="user-badge admin-badge" data-tooltip="Admin">
                                            <i class="fas fa-shield-alt"></i>
                                        </span>
                                    <?php elseif ($thread['user_role'] == 'superadmin'): ?>
                                        <span class="user-badge superadmin-badge" data-tooltip="Super Admin">
                                            <i class="fas fa-crown"></i>
                                        </span>
                                    <?php endif; ?>
                                </span>
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

<style>
    .username {
        position: relative;
        display: inline-block;
    }

    .user-badge {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        font-size: 8px;
        top: -8px;
        margin-left: 2px;
        cursor: help;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .user-badge:hover {
        transform: translateY(-2px) scale(1.1);
    }

    /* Admin badge */
    .admin-badge {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    /* Super Admin badge */
    .superadmin-badge {
        background: linear-gradient(135deg, #9333ea, #7928ca);
        color: white;
    }

    /* Add subtle pulse effect to super admin badge */
    @keyframes gentle-pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .superadmin-badge i {
        animation: gentle-pulse 2s infinite;
    }

    /* Tooltip styling */
    [data-tooltip] {
        position: relative;
    }

    [data-tooltip]::before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 5px;
        padding: 5px 10px;
        border-radius: 3px;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        font-size: 12px;
        font-weight: normal;
        text-transform: none;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        pointer-events: none;
        z-index: 10;
    }

    [data-tooltip]::after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 4px;
        border-style: solid;
        border-color: rgba(0, 0, 0, 0.8) transparent transparent transparent;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        pointer-events: none;
        z-index: 10;
    }

    [data-tooltip]:hover::before,
    [data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
    }
</style>