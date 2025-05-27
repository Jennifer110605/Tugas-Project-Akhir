<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
// Cek apakah user adalah admin untuk izin menghapus thread
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_thread']) && isAdmin()) {
    $thread_id = (int)$_POST['thread_id'];

    if (deleteThread($thread_id)) {
        redirect('index.php', 'Thread berhasil dihapus.', 'success');
    } else {
        $error = "Gagal menghapus thread.";
    }
}

// Cek ID thread
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php', 'Thread tidak ditemukan', 'danger');
}

$thread_id = (int)$_GET['id'];

// Ambil data thread
$query = "SELECT t.*, u.username, c.name as category_name 
          FROM threads t 
          JOIN users u ON t.user_id = u.id 
          JOIN categories c ON t.category_id = c.id 
          WHERE t.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('index.php', 'Thread tidak ditemukan', 'danger');
}

$thread = $result->fetch_assoc();

// Tambah view count
$query = "UPDATE threads SET views = views + 1 WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $thread_id);
$stmt->execute();

// Proses komentar baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    if (isset($_POST['comment_content']) && !empty($_POST['comment_content'])) {
        $content = clean($_POST['comment_content']);
        $user_id = $_SESSION['user_id'];

        $query = "INSERT INTO comments (content, user_id, thread_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $content, $user_id, $thread_id);

        if ($stmt->execute()) {
            redirect('thread.php?id=' . $thread_id, 'Komentar berhasil ditambahkan', 'success');
        } else {
            $error = "Gagal menambahkan komentar: " . $conn->error;
        }
    } else {
        $error = "Komentar tidak boleh kosong";
    }
}

// Proses vote
if (isset($_POST['vote']) && isLoggedIn() && $thread['has_poll']) {
    $option_id = (int)$_POST['poll_option'];
    $user_id = $_SESSION['user_id'];

    // Ambil poll_id
    $query = "SELECT id FROM polls WHERE thread_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $poll_result = $stmt->get_result();

    if ($poll_result->num_rows > 0) {
        $poll = $poll_result->fetch_assoc();
        $poll_id = $poll['id'];

        // Cek apakah user sudah vote
        if (!hasUserVoted($poll_id, $user_id)) {
            $query = "INSERT INTO votes (poll_id, option_id, user_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iii", $poll_id, $option_id, $user_id);

            if ($stmt->execute()) {
                redirect('thread.php?id=' . $thread_id, 'Vote berhasil', 'success');
            } else {
                $error = "Gagal memproses vote: " . $conn->error;
            }
        } else {
            $error = "Anda sudah memberikan vote sebelumnya";
        }
    }
}

// Ambil polling (jika ada)
$poll = null;
$poll_options = [];

if ($thread['has_poll']) {
    $query = "SELECT * FROM polls WHERE thread_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $poll_result = $stmt->get_result();

    if ($poll_result->num_rows > 0) {
        $poll = $poll_result->fetch_assoc();

        // Ambil opsi polling
        $query = "SELECT * FROM poll_options WHERE poll_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $poll['id']);
        $stmt->execute();
        $poll_options_result = $stmt->get_result();

        while ($option = $poll_options_result->fetch_assoc()) {
            $poll_options[] = $option;
        }
    }
}

// Ambil komentar
$query = "SELECT c.*, u.username 
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.thread_id = ? 
          ORDER BY c.created_at";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$comments_result = $stmt->get_result();
?>

<div class="thread-container">
    <div class="thread-header">
        <h1><?php echo $thread['title']; ?></h1>
        <div class="thread-meta">
            <span class="username">
                <b>TS: </b>
                <?php echo $thread['username']; ?>
                <?php
                // Get user role from database
                $userQuery = "SELECT role FROM users WHERE id = ?";
                $userStmt = $conn->prepare($userQuery);
                $userStmt->bind_param("i", $thread['user_id']);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $userData = $userResult->fetch_assoc();

                if ($userData && $userData['role'] == 'admin'): ?>
                    <span class="user-badge admin-badge" data-tooltip="Admin">
                        <i class="fas fa-shield-alt"></i>
                    </span>
                <?php elseif ($userData && $userData['role'] == 'superadmin'): ?>
                    <span class="user-badge superadmin-badge" data-tooltip="Super Admin">
                        <i class="fas fa-crown"></i>
                    </span>
                <?php endif; ?>
            </span><span><i class="fas fa-folder"></i> <a href="category.php?id=<?php echo $thread['category_id']; ?>"><?php echo $thread['category_name']; ?></a></span>
            <span><i class="fas fa-clock"></i> <?php echo formatDate($thread['created_at']); ?></span>
            <span><i class="fas fa-eye"></i> <?php echo $thread['views']; ?> views</span>
        </div>
    </div>
    <div class="thread-content">
        <?php echo nl2br($thread['content']); ?>
    </div>

    <?php if ($poll): ?>
        <div class="poll-section">
            <h3><?php echo $poll['question']; ?></h3>

            <?php
            $user_voted = false;
            if (isLoggedIn()) {
                $user_voted = hasUserVoted($poll['id'], $_SESSION['user_id']);
            }
            ?>

            <?php if ($user_voted || !isLoggedIn()): ?>
                <!-- Tampilkan hasil polling -->
                <div class="poll-results">
                    <?php
                    $poll_results = getPollResults($poll['id']);
                    $total_votes = 0;
                    $results_data = [];

                    while ($result = $poll_results->fetch_assoc()) {
                        $results_data[] = $result;
                        $total_votes += $result['vote_count'];
                    }
                    ?>

                    <?php foreach ($results_data as $result): ?>
                        <?php
                        $percentage = $total_votes > 0 ? round(($result['vote_count'] / $total_votes) * 100) : 0;
                        ?>
                        <div class="poll-result-item">
                            <div class="poll-option-text">
                                <?php echo $result['option_text']; ?>
                                <span class="vote-count">(<?php echo $result['vote_count']; ?> votes)</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                    <?php echo $percentage; ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="total-votes">
                        Total votes: <?php echo $total_votes; ?>
                    </div>

                    <?php if (!isLoggedIn()): ?>
                        <div class="poll-login-message">
                            <p>Login untuk berpartisipasi dalam polling.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Tampilkan form polling -->
                <form action="thread.php?id=<?php echo $thread_id; ?>" method="POST" class="poll-form">
                    <?php foreach ($poll_options as $option): ?>
                        <div class="poll-option">
                            <input type="radio" id="option<?php echo $option['id']; ?>" name="poll_option" value="<?php echo $option['id']; ?>" required>
                            <label for="option<?php echo $option['id']; ?>"><?php echo $option['option_text']; ?></label>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" name="vote" class="btn btn-primary">Vote</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="comments-section">
        <h2>Komentar (<?php echo $comments_result->num_rows; ?>)</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($comments_result->num_rows > 0): ?>
            <div class="comments-list">
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment-item">
                        <div class="comment-author">
                            <span class="username">
                                <?php echo $comment['username']; ?>
                                <?php
                                // Get user role for comment author
                                $userQuery = "SELECT role FROM users WHERE id = ?";
                                $userStmt = $conn->prepare($userQuery);
                                $userStmt->bind_param("i", $comment['user_id']);
                                $userStmt->execute();
                                $userResult = $userStmt->get_result();
                                $userData = $userResult->fetch_assoc();

                                if ($userData && $userData['role'] == 'admin'): ?>
                                    <span class="user-badge admin-badge" data-tooltip="Admin">
                                        <i class="fas fa-shield-alt"></i>
                                    </span>
                                <?php elseif ($userData && $userData['role'] == 'superadmin'): ?>
                                    <span class="user-badge superadmin-badge" data-tooltip="Super Admin">
                                        <i class="fas fa-crown"></i>
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="date"><?php echo formatDate($comment['created_at']); ?></span>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br($comment['content']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <div class="comment-form">
                <h3>Tambahkan Komentar</h3>
                <form action="thread.php?id=<?php echo $thread_id; ?>" method="POST">
                    <div class="form-group">
                        <textarea name="comment_content" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                </form>
            </div>
        <?php else: ?>
            <div class="comment-login">
                <p><a href="login.php">Login</a> untuk menambahkan komentar.</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- Tombol Hapus Thread -->
    <?php if (isAdmin()): ?>
        <form action="thread.php?id=<?php echo $thread_id; ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus thread ini? Semua data terkait akan dihapus.')">
            <input type="hidden" name="delete_thread" value="1">
            <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
            <button type="submit" class="delete-thread-btn">
                <i class="fas fa-trash-alt"></i> Hapus Thread
            </button>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- Tambahkan style langsung di thread.php -->
<style>
    .delete-thread-btn {
        position: absolute;
        top: 50px;
        right: 20px;
        background-color: #dc3545;
        color: white;
        padding: 7px 7px;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .delete-thread-btn:hover {
        background-color: #c82333;
    }

    .delete-thread-btn i {
        margin-right: 5px;
    }

    .thread-container {
        position: relative;
    }

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