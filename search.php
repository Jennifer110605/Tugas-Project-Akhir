<?php
// filepath: c:\laragon\www\Tugas-Project-Akhir\search.php
require_once 'includes/header.php';

// Ambil parameter pencarian
$search_query = isset($_GET['q']) ? clean($_GET['q']) : '';

// Jika ada query pencarian
if (!empty($search_query)) {
    // Siapkan query untuk mencari di judul dan konten
    $query = "SELECT t.*, u.username, c.name as category_name,
              (SELECT COUNT(*) FROM comments WHERE thread_id = t.id) as comment_count 
              FROM threads t 
              JOIN users u ON t.user_id = u.id 
              JOIN categories c ON t.category_id = c.id 
              WHERE t.title LIKE ? OR t.content LIKE ? 
              ORDER BY t.is_pinned DESC, t.created_at DESC";

    $search_term = "%$search_query%";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jumlah hasil pencarian
    $result_count = $result->num_rows;
}
?>

<div class="forum-container">
    <h1>Hasil Pencarian</h1>

    <?php if (empty($search_query)): ?>
        <div class="search-info">
            <p>Silakan masukkan kata kunci untuk mencari thread.</p>
        </div>

        <!-- Form pencarian besar di halaman pencarian -->
        <div class="big-search-form">
            <form action="search.php" method="GET">
                <div class="form-group">
                    <input type="text" name="q" placeholder="Masukkan kata kunci pencarian..." class="search-input-large">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="search-info">
            <p>Menampilkan <strong><?php echo $result_count; ?></strong> hasil untuk pencarian: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></p>
        </div>

        <?php if ($result_count > 0): ?>
            <div class="thread-list">
                <?php while ($thread = $result->fetch_assoc()):
                    // Highlight kata pencarian di judul
                    $highlighted_title = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="search-highlight">$1</span>', htmlspecialchars($thread['title']));

                    // Potong konten untuk preview dan highlight kata pencarian
                    $content_preview = strip_tags($thread['content']);
                    $content_preview = substr($content_preview, 0, 150) . (strlen($content_preview) > 150 ? '...' : '');
                    $highlighted_preview = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="search-highlight">$1</span>', htmlspecialchars($content_preview));
                ?>
                    <div class="thread-item">
                        <?php if ($thread['is_pinned']): ?>
                            <div class="thread-pin"><i class="fas fa-thumbtack"></i></div>
                        <?php endif; ?>

                        <div class="thread-info">
                            <h3><a href="thread.php?id=<?php echo $thread['id']; ?>"><?php echo $highlighted_title; ?></a></h3>
                            <div class="thread-preview">
                                <?php echo $highlighted_preview; ?>
                            </div>
                            <div class="thread-meta">
                                <span><i class="fas fa-user"></i> <?php echo $thread['username']; ?></span>
                                <span><i class="fas fa-folder"></i> <?php echo $thread['category_name']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($thread['created_at']); ?></span>
                                <span><i class="fas fa-comments"></i> <?php echo $thread['comment_count']; ?> komentar</span>

                                <?php if ($thread['has_poll']): ?>
                                    <span class="poll-indicator"><i class="fas fa-chart-bar"></i> Polling</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>Tidak ditemukan thread yang sesuai dengan kata kunci pencarian.</p>
                <p>Saran:</p>
                <ul>
                    <li>Periksa ejaan kata kunci Anda</li>
                    <li>Coba dengan kata kunci yang berbeda</li>
                    <li>Coba kata kunci yang lebih umum</li>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .big-search-form {
        margin: 2rem 0;
    }

    .search-input-large {
        padding: 0.75rem !important;
        font-size: 1.1rem !important;
    }

    .thread-preview {
        margin: 0.5rem 0;
        color: #666;
        line-height: 1.5;
    }

    .no-results {
        background-color: #f9f9f9;
        padding: 1.5rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
    }

    .no-results ul {
        list-style: disc;
        margin-left: 1.5rem;
        margin-top: 0.5rem;
    }
</style>

<?php require_once 'includes/footer.php'; ?>