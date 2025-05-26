<?php

// Cek user login
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Cek user superadmin
function isSuperAdmin()
{
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'superadmin') {
        return true;
    } else {
        return false;
    }
}
// Cek user admin
function isAdmin()
{
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'superadmin') {
        return true;
    }
    return false;
}

// Redirect dengan pesan
function redirect($url, $message = '', $message_type = 'info')
{
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
    }
    header('Location: ' . $url);
    exit;
}

// Sanitasi input
function clean($input)
{
    global $conn;
    return htmlspecialchars(strip_tags(trim($input)));
}

// Tampilkan pesan
function displayMessage()
{
    if (isset($_SESSION['message'])) {
        $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        echo '<div class="alert alert-' . $message_type . '">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Format tanggal
function formatDate($date)
{
    return date('d F Y, H:i', strtotime($date));
}

// Ambil data user berdasarkan ID
function getUserById($id)
{
    global $conn;
    $id = (int)$id;
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Hitung jumlah thread dalam kategori
function countThreadsInCategory($category_id)
{
    global $conn;
    $category_id = (int)$category_id;
    $query = "SELECT COUNT(*) as count FROM threads WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Cek apakah user sudah vote di poll
function hasUserVoted($poll_id, $user_id)
{
    global $conn;
    $query = "SELECT id FROM votes WHERE poll_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $poll_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Hitung hasil voting
function getPollResults($poll_id)
{
    global $conn;
    $poll_id = (int)$poll_id;
    $query = "SELECT o.id, o.option_text, COUNT(v.id) as vote_count 
              FROM poll_options o 
              LEFT JOIN votes v ON o.id = v.option_id 
              WHERE o.poll_id = ? 
              GROUP BY o.id";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function untuk menghapus thread dan semua data terkait
function deleteThread($thread_id)
{
    if (!isAdmin()) {
        return false;
    }

    global $conn;
    $conn->begin_transaction();
    try {
        // Hapus polling terkait thread
        $query = "SELECT id FROM polls WHERE thread_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $thread_id);
        $stmt->execute();
        $polls_result = $stmt->get_result();

        while ($poll = $polls_result->fetch_assoc()) {
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

        // Akhirnya, hapus thread
        $query = "DELETE FROM threads WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $thread_id);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception) {
        $conn->rollback();
        return false;
    }
}
