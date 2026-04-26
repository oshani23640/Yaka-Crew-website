<?php
// edit_song.php - Edit audio (song) entry
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}
require_once '../YCdb_connection.php';

// Helper: format large hit counts as K/M (e.g. 250000 -> 250K, 1200000 -> 1.2M)
if (!function_exists('formatHitsCount')) {
    function formatHitsCount($hits) {
        $hits = (int)$hits;
        if ($hits >= 1000000) return round($hits / 1000000, 1) . 'M';
        if ($hits >= 1000) return round($hits / 1000, 1) . 'K';
        return (string)$hits;
    }
}

$error_message = '';
$success_message = '';
$debug_info = ''; // store debug output for later

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid song ID.');
}

// Fetch song data
$stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->execute([$id]);
$song = $stmt->fetch();
if (!$song) {
    die('Song not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store debug info (don't echo yet)


    $title = $_POST['title'] ?? '';
    $artist_name = $_POST['artist_name'] ?? ($song['artist_name'] ?? '');
    $music_category = $_POST['music_category'] ?? ($song['music_category'] ?? '');
    $category = $_POST['category'] ?? ($song['category'] ?? '');
    $description = $_POST['description'] ?? ($song['description'] ?? '');

    // Only set hits if category is 'top'
    $include_hits = false;
    if ($category === 'top' && isset($_POST['hits']) && $_POST['hits'] !== '') {
        $hits = (int)$_POST['hits'] * 1000;
        $include_hits = true;
    }

    // Handle file uploads
    $cover_image = $song['cover_image'] ?? null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $new_cover = 'uploads/Gallery/covers/YCaudio/' . uniqid('audio_cover_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], '../' . $new_cover)) {
            $cover_image = $new_cover;
        }
    }
    $audio_path = $song['audio_path'] ?? null;
    if (isset($_FILES['audio_path']) && $_FILES['audio_path']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['audio_path']['name'], PATHINFO_EXTENSION);
        $new_audio = 'uploads/Gallery/YCaudio/' . uniqid('audio_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['audio_path']['tmp_name'], '../' . $new_audio)) {
            $audio_path = $new_audio;
        }
    } else {
        $audio_path = $song['audio_path'] ?? null;
    }

    // Only update columns that exist in the table
    $columns = $pdo->query("SHOW COLUMNS FROM songs")->fetchAll(PDO::FETCH_COLUMN);
    $fields = [];
    $params = [];
    $fields[] = 'title=?'; $params[] = $title;
    if (in_array('artist_name', $columns)) { $fields[] = 'artist_name=?'; $params[] = $artist_name; }
    if (in_array('music_category', $columns)) { $fields[] = 'music_category=?'; $params[] = $music_category; }
    if (in_array('category', $columns)) { $fields[] = 'category=?'; $params[] = $category; }
    if (in_array('description', $columns)) { $fields[] = 'description=?'; $params[] = $description; }
    if ($include_hits && in_array('hits', $columns)) { $fields[] = 'hits=?'; $params[] = $hits; }
    if (in_array('cover_image', $columns)) { $fields[] = 'cover_image=?'; $params[] = $cover_image; }
    if (in_array('audio_path', $columns)) { $fields[] = 'audio_path=?'; $params[] = $audio_path; }
    $sql = "UPDATE songs SET " . implode(", ", $fields) . " WHERE id=?";
    $params[] = $id;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        // Show success message on the same page
        $_SESSION['edit_song_success'] = 'Changes saved successfully!';
        // No redirect, just reload the page to show the message
    header("Location: YCGalleryedit_song.php?id=$id");
        exit();
    } catch (PDOException $e) {
        $_SESSION['edit_song_error'] = 'Error updating song: ' . htmlspecialchars($e->getMessage()) . '<br>SQL: ' . htmlspecialchars($sql) . '<br>Params: ' . htmlspecialchars(json_encode($params));
    header("Location: YCGalleryedit_song.php?id=$id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Audio - Yaka Crew Admin</title>
    <link rel="stylesheet" href="../css/YCGalleryadmin-style.css">
    <style>
        .edit-form-container { max-width: 500px; margin: 40px auto; background: #111; padding: 0 30px 30px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); position: relative;}
        .edit-form-top {
            position: sticky;
            top: 0;
            background: #111;
            z-index: 2;
            padding-top: 30px;
        }
        .edit-form-container label { color: #fff; font-weight: 600; margin-top: 10px; display: block;}
        .edit-form-container input[type="text"], .edit-form-container input[type="file"], .edit-form-container input[type="number"], .edit-form-container select, .edit-form-container textarea {
            width: 100%; min-width: 100%; max-width: 100%; box-sizing: border-box; padding: 10px; margin-top: 5px; margin-bottom: 15px;
            border-radius: 4px; border: 1px solid #654922; background: #222; color: #fff; font-size: 16px;
        }
        .edit-form-container button { background: #654922; color: #fff; border: none; padding: 12px 30px; border-radius: 4px; font-size: 16px; cursor: pointer;}
        .edit-form-container button:hover { background: #956E2F; }
        .edit-form-container img { display: block; margin-bottom: 10px; max-width: 180px; height: 120px; object-fit: cover; border-radius: 4px;}
        .edit-form-container h2 { color: #fff; margin-bottom: 20px; }
        body { background-color: #000000; margin: 0; padding: 0; }
    </style>
</head>
<body>
<div class="edit-form-container">
    <div class="edit-form-top">
        <?php if (!empty($debug_info)) echo $debug_info; ?>
        <?php if (!empty($_SESSION['edit_song_success'])): ?>
            <div id="edit-song-success-message" style="background: #654922; color: #FFFFFF; padding: 12px; border-radius: 5px; margin-bottom: 18px; border: 1px solid #654922; text-align:center;">
                <?= $_SESSION['edit_song_success']; unset($_SESSION['edit_song_success']); ?>
            </div>
            <script>
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(function(){
                    var msg = document.getElementById('edit-song-success-message');
                    if(msg){ msg.style.display = 'none'; }
                }, 1200);
            });
            </script>
        <?php endif; ?>
        <?php if (!empty($_SESSION['edit_song_error'])): ?>
            <div style="background: #654922; color: #FFFFFF; padding: 12px; border-radius: 5px; margin-bottom: 18px; border: 1px solid #654922; text-align:center;">
                <?= $_SESSION['edit_song_error']; unset($_SESSION['edit_song_error']); ?>
            </div>
        <?php endif; ?>
    <h2>Edit Audio</h2>
        
    </div>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($song['title']) ?>" required>

        <label for="artist_name">Artist Name:</label>
        <input type="text" name="artist_name" id="artist_name" value="<?= htmlspecialchars($song['artist_name']) ?>" required>

        <label for="music_category">Music Category:</label>
        <select name="music_category" id="music_category" required>
            <option value="hip_hop" <?= ($song['music_category'] === 'hip_hop') ? 'selected' : '' ?>>Hip hop</option>
            <option value="traditional" <?= ($song['music_category'] === 'traditional') ? 'selected' : '' ?>>Traditional</option>
            <option value="pop" <?= ($song['music_category'] === 'pop') ? 'selected' : '' ?>>Pop</option>
            <option value="rock" <?= ($song['music_category'] === 'rock') ? 'selected' : '' ?>>Rock</option>
            <option value="jazz" <?= ($song['music_category'] === 'jazz') ? 'selected' : '' ?>>Jazz</option>
            <option value="reggae" <?= ($song['music_category'] === 'reggae') ? 'selected' : '' ?>>Reggae</option>
            <option value="classical" <?= ($song['music_category'] === 'classical') ? 'selected' : '' ?>>Classical</option>
            <option value="electronic" <?= ($song['music_category'] === 'electronic') ? 'selected' : '' ?>>Electronic</option>
            <option value="folk" <?= ($song['music_category'] === 'folk') ? 'selected' : '' ?>>Folk</option>
            <option value="country" <?= ($song['music_category'] === 'country') ? 'selected' : '' ?>>Country</option>
            <option value="blues" <?= ($song['music_category'] === 'blues') ? 'selected' : '' ?>>Blues</option>
            <option value="r&b" <?= ($song['music_category'] === 'r&b') ? 'selected' : '' ?>>R&amp;B</option>
        </select>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="latest" <?= $song['category'] === 'latest' ? 'selected' : '' ?>>Latest Album</option>
            <option value="top" <?= $song['category'] === 'top' ? 'selected' : '' ?>>Top Album</option>
            <option value="video" <?= $song['category'] === 'video' ? 'selected' : '' ?>>Video</option>
        </select>

        <div id="hits-field" style="display: <?= $song['category'] === 'top' ? 'block' : 'none' ?>;">
            <label for="hits">Hits Amount: <span id="hits-suffix" style="font-weight:600; color:#ccc;">K / M</span></label>
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="number" name="hits" id="hits" min="1" max="9999" value="<?= ($song['category'] === 'top' && isset($song['hits'])) ? round($song['hits']/1000) : '' ?>" <?= $song['category'] === 'top' ? 'required' : '' ?> >
                <div id="hits-preview" style="color:#bbb; font-size:13px;"><?php if (isset($song['hits']) && $song['hits'] > 0) { echo htmlspecialchars(formatHitsCount($song['hits'])); } else { echo '—'; } ?></div>
            </div>
            <small style="color: #aaa;">Enter the number of hits in thousands (e.g., 250 for 250K, 1400 for 1.4M)</small>
        </div>

        <label for="description">Description:</label>
        <textarea name="description" id="description"><?= htmlspecialchars($song['description']) ?></textarea>

        <label>Current Cover Image:</label>
        <?php 
        $cover = $song['cover_image'] ?? '';
        if (!empty($cover)) {
            // Extract filename from path
            $filename = basename($cover);
            $final_image_path = '';
            
            // Check source directory first
            if (file_exists('../source/Gallery/covers/YCaudio/' . $filename)) {
                $final_image_path = '../source/Gallery/covers/YCaudio/' . $filename;
            } elseif (file_exists('../uploads/Gallery/covers/YCaudio/' . $filename)) {
                $final_image_path = '../uploads/Gallery/covers/YCaudio/' . $filename;
            } elseif (file_exists('../' . $cover)) {
                // Fallback to original path
                $final_image_path = '../' . $cover;
            }
            
            if (!empty($final_image_path)) {
                echo '<img src="' . htmlspecialchars($final_image_path) . '" alt="Cover Image">';
            } else {
                echo '<div style="color: #aaa; margin-bottom: 10px;">No cover image available.<br>(' . htmlspecialchars($cover) . ')</div>';
            }
        } else {
            echo '<div style="color: #aaa; margin-bottom: 10px;">No cover image available.</div>';
        }
        ?>
        <label for="cover_image">Change Cover Image:</label>
        <input type="file" name="cover_image" id="cover_image" accept="image/*">

        <label>Current Audio File:</label>
        <?php
        $audio_path = $song['audio_path'] ?? '';
        $audio_file_exists = false;
        $audio_file_url = '';
        if ($audio_path) {
            $clean_path = ltrim($audio_path, '/');
            while (strpos($clean_path, '../') === 0) {
                $clean_path = substr($clean_path, 3);
            }
            if (file_exists('../' . $clean_path)) {
                $audio_file_exists = true;
                $audio_file_url = '../' . $clean_path;
            } else {
                // Check for audio file in source/Gallery/YCaudio first, then uploads/Gallery/YCaudio
                $filename = basename($clean_path);
                $source_audio = '../source/Gallery/YCaudio/' . $filename;
                $uploads_audio = '../uploads/Gallery/YCaudio/' . $filename;
                
                if (file_exists($source_audio)) {
                  $normalized_path = 'source/Gallery/YCaudio/' . $filename;
                  $audio_file_exists = true;
                  $audio_file_url = '../' . $normalized_path;
                } elseif (file_exists($uploads_audio)) {
                  $normalized_path = 'uploads/Gallery/YCaudio/' . $filename;
                  $audio_file_exists = true;
                  $audio_file_url = '../' . $normalized_path;
                }
            }
        }
        if ($audio_file_exists): ?>
            <audio class="audio-preview" controls src="<?= htmlspecialchars($audio_file_url) ?>"></audio>
        <?php else: ?>
            <div style="color: #aaa; margin-bottom: 10px;">No audio file available.</div>
            <div style="color: #f88; font-size: 12px;">Debug: audio_path = '<?= htmlspecialchars($audio_path) ?>'<br>
            Checked: '../<?= htmlspecialchars($clean_path) ?>' (<?= file_exists('../' . $clean_path) ? 'exists' : 'not found' ?>), normalized path: '<?= htmlspecialchars($normalized_path ?? 'none') ?>' (<?= isset($normalized_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . $normalized_path) ? 'exists' : 'not found' ?>)</div>
        <?php endif; ?>
        <label for="audio_path">Change Audio File:</label>
        <input type="file" name="audio_path" id="audio_path" accept="audio/*">

        <button type="submit" id="save-btn">Save Changes</button>
    <a href="YCGalleryadmin.php?page=video" style="margin-left: 20px; color: #aaa;">Cancel</a>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const hitsField = document.getElementById('hits-field');
    const hitsInput = document.getElementById('hits');
    if (categorySelect && hitsField) {
        categorySelect.addEventListener('change', function() {
            if (this.value === 'top') {
                hitsField.style.display = 'block';
                if (hitsInput) hitsInput.required = true;
            } else {
                hitsField.style.display = 'none';
                if (hitsInput) hitsInput.value = '';
                if (hitsInput) hitsInput.required = false;
            }
        });
    }
    // Live preview/suffix for hits in edit form
    function updateEditHitsPreview() {
        const input = document.getElementById('hits');
        const preview = document.getElementById('hits-preview');
        const suffix = document.getElementById('hits-suffix');
        if (!input || !preview) return;
        const val = parseInt(input.value, 10);
        if (!val || val <= 0) {
            preview.textContent = '—';
            if (suffix) suffix.textContent = 'K';
            return;
        }
        const raw = val * 1000;
        if (raw >= 1000000) {
            preview.textContent = (Math.round((raw / 1000000) * 10) / 10) + 'M';
            if (suffix) suffix.textContent = 'M';
        } else if (raw >= 1000) {
            preview.textContent = (Math.round((raw / 1000) * 10) / 10) + 'K';
            if (suffix) suffix.textContent = 'K';
        } else {
            preview.textContent = String(raw);
            if (suffix) suffix.textContent = '';
        }
    }
    if (hitsInput) hitsInput.addEventListener('input', updateEditHitsPreview);
    // initialize preview on load
    updateEditHitsPreview();
    // Always enable the Save Changes button
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) saveBtn.removeAttribute('disabled');
});
</script>
</body>
</html>
