<?php
// YCupload_song.php - Handle song uploads from source folder to uploads folder
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}

// Debug toggle: set to true to show POST/FILES and errors on-page instead of redirecting
$DEBUG = true;

// Ensure PHP errors are visible
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../YCdb_connection.php';

// Function to get files from source directory
function getSourceFiles($directory) {
    $files = [];
    if (is_dir($directory)) {
        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..' && is_file($directory . $item)) {
                $files[] = $item;
            }
        }
    }
    return $files;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $artist_name = $_POST['artist_name'] ?? '';
    $file_type = 'audio'; // Only audio for songs
    $category = $_POST['category'] ?? '';
    $music_category = $_POST['music_category'] ?? '';
    $hits = $_POST['hits'] ?? 0;
    
    // Get selected source files (accept multiple possible field names)
    $selected_cover = $_REQUEST['selected_cover'] ?? $_REQUEST['cover'] ?? $_REQUEST['cover_image_select'] ?? $_REQUEST['selected_cover_audio'] ?? $_POST['selected_cover'] ?? '';
    $selected_media = $_REQUEST['selected_media'] ?? $_REQUEST['media'] ?? $_REQUEST['selected_audio'] ?? $_POST['selected_media'] ?? '';

    // Convert hits to actual number (input is in thousands)
    if ($category === 'top' && $hits > 0) {
        $hits = (int)$hits * 1000;
    } else {
        $hits = 0;
    }

    // Set source directories
    $source_audio_dir = '../source/Gallery/YCaudio/';
    $source_images_audio_dir = '../source/Gallery/images/audio/';
    
    // Set upload directories based on file type
    $audio_upload_dir = '../uploads/Gallery/YCaudio/';
    $audio_cover_dir = '../uploads/Gallery/covers/YCaudio/';
    
    // Create directories if they don't exist with proper permissions
    $directories = [$audio_upload_dir, $audio_cover_dir];
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                error_log("Failed to create directory: " . $dir);
            } else {
                chmod($dir, 0777);
            }
        }
    }

    $upload_success = true;
    $error_message = '';
    $cover_image_db_path = '';
    $media_db_path = '';

    // Require a cover image: either selected from source or uploaded
    $cover_provided = false;
    // Handle cover image uploaded via file input
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if ($file_extension == 'jpg') $file_extension = 'jpeg';
        $cover_upload_dir = $audio_cover_dir;
        $cover_db_path_prefix = 'uploads/Gallery/covers/YCaudio/';
        $unique_id = time() . '_' . bin2hex(random_bytes(4));
        $unique_filename = 'audio_cover_' . $unique_id . ".{$file_extension}";
        $destination_cover_path = $cover_upload_dir . $unique_filename;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination_cover_path)) {
            $cover_image_db_path = $cover_db_path_prefix . $unique_filename;
            $cover_provided = true;
        } else {
            $upload_success = false;
            $error_message = 'Failed to upload cover image.';
        }
    }

    // Handle cover image from source if not uploaded
    if (!$cover_provided && !empty($selected_cover)) {
        $source_cover_path = $source_images_audio_dir . $selected_cover;
        if (file_exists($source_cover_path)) {
            $cover_upload_dir = $audio_cover_dir;
            $cover_db_path_prefix = 'uploads/Gallery/covers/YCaudio/';
            $file_extension = strtolower(pathinfo($selected_cover, PATHINFO_EXTENSION));
            if ($file_extension == 'jpg') {
                $file_extension = 'jpeg';
            }
            // Generate a unique filename for each cover image
            $unique_id = time() . '_' . bin2hex(random_bytes(4));
            $unique_filename = 'audio_cover_' . $unique_id . ".{$file_extension}";
            $destination_cover_path = $cover_upload_dir . $unique_filename;
            if (copy($source_cover_path, $destination_cover_path)) {
                $cover_image_db_path = $cover_db_path_prefix . $unique_filename;
                $cover_provided = true;
            } else {
                $upload_success = false;
                $error_message = 'Failed to copy cover image.';
            }
        } else {
            $upload_success = false;
            $error_message = 'Selected cover image does not exist.';
        }
    }

    if (!$cover_provided) {
        $error_message = 'Please select a cover image file.';
        if ($DEBUG) {
            echo '<div style="background:#c00;color:#fff;padding:10px;">Error: ' . htmlspecialchars($error_message) . '</div>';
            echo '<pre style="background:#222;color:#fff;padding:10px;">REQUEST: ' . print_r($_REQUEST, true) . "\nPOST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . '</pre>';
        } else {
            header("Location: YCGalleryadmin.php?page=video&upload=error&message=" . urlencode($error_message));
            exit();
        }
    }

    // Continue: cover image has been handled
        $source_cover_path = $source_images_audio_dir . $selected_cover;
        if (file_exists($source_cover_path)) {
            $cover_upload_dir = $audio_cover_dir;
            $cover_db_path_prefix = 'uploads/Gallery/covers/YCaudio/';
            $cover_prefix = 'audio_cover_';
            $file_extension = strtolower(pathinfo($selected_cover, PATHINFO_EXTENSION));
            if ($file_extension == 'jpg') {
                $file_extension = 'jpeg';
            }
            // Generate a unique filename for each cover image
            $unique_id = time() . '_' . bin2hex(random_bytes(4));
            $unique_filename = $cover_prefix . $unique_id . ".{$file_extension}";
            $destination_cover_path = $cover_upload_dir . $unique_filename;
            if (copy($source_cover_path, $destination_cover_path)) {
                $cover_image_db_path = $cover_db_path_prefix . $unique_filename;
            } else {
                $upload_success = false;
                $error_message = 'Failed to copy cover image.';
            }
        } else {
            $upload_success = false;
            $error_message = 'Selected cover image does not exist.';
        }
    }

    // Handle media file from source
    if (!empty($selected_media)) {
        $source_media_path = $source_audio_dir . $selected_media;
        if (file_exists($source_media_path)) {
            $media_upload_dir = $audio_upload_dir;
            $media_db_path_prefix = 'uploads/Gallery/YCaudio/';
            $media_prefix = 'audio_';
            $file_extension = strtolower(pathinfo($selected_media, PATHINFO_EXTENSION));
            $media_number = 1;
            $found_available = false;
            for ($i = 1; $i <= 1000; $i++) {
                $test_filename = $media_upload_dir . $media_prefix . $i . ".{$file_extension}";
                if (!file_exists($test_filename)) {
                    $media_number = $i;
                    $found_available = true;
                    break;
                }
            }
            if (!$found_available) {
                $upload_success = false;
                $error_message = 'No available media slot.';
            } else {
                $destination_media_path = $media_upload_dir . $media_prefix . $media_number . ".{$file_extension}";
                if (copy($source_media_path, $destination_media_path)) {
                    $media_db_path = $media_db_path_prefix . $media_prefix . $media_number . ".{$file_extension}";
                } else {
                    $upload_success = false;
                    $error_message = 'Failed to copy media file.';
                }
            }
        } else {
            $upload_success = false;
            $error_message = 'Selected media file does not exist.';
        }
    }

    // Save to database if upload successful
    if ($upload_success && $media_db_path) {
        try {
            $sql = "INSERT INTO songs (title, description, artist_name, cover_image, audio_path, category, music_category, hits, upload_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $artist_name, $cover_image_db_path, $media_db_path, $category, $music_category, $hits]);
            header("Location: YCGalleryadmin.php?page=video&upload=success");
            exit();
        } catch (PDOException $e) {
            if (file_exists($destination_cover_path ?? $destination_media_path)) {
                unlink($destination_cover_path ?? $destination_media_path);
            }
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    header("Location: YCGalleryadmin.php?page=video&upload=error&message=" . urlencode($error_message));
    exit();
}

// ...existing code for displaying the upload form and listing source files...
