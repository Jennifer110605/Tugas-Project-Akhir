<?php
require_once 'includes/header.php';

// Redirect jika belum login
if(!isLoggedIn()) {
    redirect('login.php', 'Anda harus login untuk membuat thread', 'warning');
}

// Pre-selected category
$selected_category = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Ambil semua kategori
$query = "SELECT * FROM categories ORDER BY id";
$result = $conn->query($query);
$categories = [];
while($category = $result->fetch_assoc()) {
    $categories[] = $category;
}

// Proses form pembuatan thread
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $category_id = (int)$_POST['category_id'];
    $user_id = $_SESSION['user_id'];
    $has_poll = isset($_POST['create_poll']) ? 1 : 0;
    
    $errors = [];
    
    // Validasi
    if(empty($title)) {
        $errors[] = "Judul thread harus diisi";
    } elseif(strlen($title) < 5) {
        $errors[] = "Judul thread minimal 5 karakter";
    }
    
    if(empty($content)) {
        $errors[] = "Konten thread harus diisi";
    }
    
    // Validasi kategori
    $category_valid = false;
    foreach($categories as $category) {
        if($category['id'] == $category_id) {
            $category_valid = true;
            break;
        }
    }
    
    if(!$category_valid) {
        $errors[] = "Kategori tidak valid";
    }
    
    // Jika tidak ada error, simpan thread
    if(empty($errors)) {
        $query = "INSERT INTO threads (title, content, user_id, category_id, has_poll) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiis", $title, $content, $user_id, $category_id, $has_poll);
        
        if($stmt->execute()) {
            $thread_id = $stmt->insert_id;
            
            // Jika thread memiliki poll, proses data poll
            if($has_poll && isset($_POST['poll_question']) && !empty($_POST['poll_question'])) {
                $poll_question = clean($_POST['poll_question']);
                
                // Simpan poll
                $query = "INSERT INTO polls (thread_id, question) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $thread_id, $poll_question);
                
                if($stmt->execute()) {
                    $poll_id = $stmt->insert_id;
                    
                    // Simpan opsi poll
                    if(isset($_POST['poll_options']) && is_array($_POST['poll_options'])) {
                        $poll_options = $_POST['poll_options'];
                        
                        foreach($poll_options as $option_text) {
                            if(!empty($option_text)) {
                                $option_text = clean($option_text);
                                
                                $query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("is", $poll_id, $option_text);
                                $stmt->execute();
                            }
                        }
                    }
                }
            }
            
            redirect('thread.php?id=' . $thread_id, 'Thread berhasil dibuat', 'success');
        } else {
            $errors[] = "Gagal membuat thread: " . $conn->error;
        }
    }
}
?>

<div class="create-thread-container">
    <h1>Buat Thread Baru</h1>
    
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="create-thread.php" method="POST" class="thread-form">
        <div class="form-group">
            <label for="title">Judul Thread</label>
            <input type="text" id="title" name="title" value="<?php echo isset($title) ? $title : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">Pilih Kategori</option>
                <?php foreach($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($selected_category == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="content">Konten</label>
            <textarea id="content" name="content" rows="10" required><?php echo isset($content) ? $content : ''; ?></textarea>
        </div>
        
        <div class="poll-section">
            <div class="form-check">
                <input type="checkbox" id="create_poll" name="create_poll" <?php echo isset($has_poll) && $has_poll ? 'checked' : ''; ?>>
                <label for="create_poll">Tambahkan polling</label>
            </div>
            
            <div id="poll-fields" class="poll-fields" style="display: none;">
                <div class="form-group">
                    <label for="poll_question">Pertanyaan Polling</label>
                    <input type="text" id="poll_question" name="poll_question" value="<?php echo isset($poll_question) ? $poll_question : ''; ?>">
                </div>
                
                <div class="poll-options">
                    <label>Opsi Polling</label>
                    <div class="option-inputs">
                        <div class="form-group">
                            <input type="text" name="poll_options[]" placeholder="Opsi 1">
                        </div>
                        <div class="form-group">
                            <input type="text" name="poll_options[]" placeholder="Opsi 2">
                        </div>
                    </div>
                    <button type="button" id="add-option" class="btn btn-sm">+ Tambah Opsi</button>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Buat Thread</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle poll fields
    const createPollCheckbox = document.getElementById('create_poll');
    const pollFields = document.getElementById('poll-fields');
    
    function togglePollFields() {
        if(createPollCheckbox.checked) {
            pollFields.style.display = 'block';
        } else {
            pollFields.style.display = 'none';
        }
    }
    
    createPollCheckbox.addEventListener('change', togglePollFields);
    togglePollFields(); // Initial state
    
    // Add new poll option
    const addOptionBtn = document.getElementById('add-option');
    const optionInputs = document.querySelector('.option-inputs');
    
    addOptionBtn.addEventListener('click', function() {
        const newOption = document.createElement('div');
        newOption.className = 'form-group';
        
        const optionCount = optionInputs.querySelectorAll('input').length + 1;
        
        newOption.innerHTML = `
            <input type="text" name="poll_options[]" placeholder="Opsi ${optionCount}">
            <button type="button" class="remove-option btn btn-sm btn-danger">x</button>
        `;
        
        optionInputs.appendChild(newOption);
        
        // Add remove functionality
        const removeBtn = newOption.querySelector('.remove-option');
        removeBtn.addEventListener('click', function() {
            optionInputs.removeChild(newOption);
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>