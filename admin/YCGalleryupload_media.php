<?php
// upload_media.php - Handle media uploads for Gallery.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}

// Include database connection
require_once '../YCdb_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $artist_name = $_POST['artist_name'] ?? '';
    $file_type = $_POST['file_type'] ?? '';
    $category = $_POST['category'] ?? '';
    $music_category = $_POST['music_category'] ?? '';
    $youtube_url = isset($_POST['youtube_url']) ? trim($_POST['youtube_url']) : '';
    $hits = $_POST['hits'] ?? 0;

    // Convert hits to actual number (input is in thousands)
    if ($category === 'top' && $hits > 0) {
        // Store as raw integer (e.g., 250000 for 250K)
        $hits = (int)$hits * 1000;
    } else {
        $hits = 0;
    }

    // Set upload directories based on file type
    $audio_upload_dir = '../uploads/Gallery/YCaudio/';
    $video_upload_dir = '../uploads/Gallery/YCvideos/';
    
    // Set cover image directories based on content type
    $audio_cover_dir = '../uploads/Gallery/covers/YCaudio/';
    $video_cover_dir = '../uploads/Gallery/covers/YCvideos/';
    $post_cover_dir = '../uploads/Gallery/covers/YCposts/';
    
    // Create directories if they don't exist with proper permissions
    if (!is_dir($audio_upload_dir)) {
        if (!mkdir($audio_upload_dir, 0777, true)) {
            error_log("Failed to create audio upload directory: " . $audio_upload_dir);
        } else {
            chmod($audio_upload_dir, 0777);
        }
    }
    if (!is_dir($video_upload_dir)) {
        if (!mkdir($video_upload_dir, 0777, true)) {
            error_log("Failed to create video upload directory: " . $video_upload_dir);
        } else {
            chmod($video_upload_dir, 0777);
        }
    }
    if (!is_dir($audio_cover_dir)) {
        if (!mkdir($audio_cover_dir, 0777, true)) {
            error_log("Failed to create audio cover directory: " . $audio_cover_dir);
        } else {
            chmod($audio_cover_dir, 0777);
        }
    }
    if (!is_dir($video_cover_dir)) {
        if (!mkdir($video_cover_dir, 0777, true)) {
            error_log("Failed to create video cover directory: " . $video_cover_dir);
        } else {
            chmod($video_cover_dir, 0777);
        }
    }
    if (!is_dir($post_cover_dir)) {
        if (!mkdir($post_cover_dir, 0777, true)) {
            error_log("Failed to create post cover directory: " . $post_cover_dir);
        } else {
            chmod($post_cover_dir, 0777);
        }
    }

    $cover_image_path = '';
    $cover_image_db_path = '';
    $media_file_path = '';
    $upload_success = true;
    $error_message = '';

    // Handle cover image upload
    if (isset($_FILES['cover_image'])) {
        if ($_FILES['cover_image']['error'] == 0) {
            $file_extension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            if ($file_extension == 'jpg') {
                $file_extension = 'jpeg';
            }
            
            // Choose cover directory based on file type
            if ($file_type === 'audio') {
                $cover_upload_dir = $audio_cover_dir;
                $cover_db_path_prefix = 'uploads/Gallery/covers/YCaudio/';
                $cover_prefix = 'audio_cover_';
            } else {
                $cover_upload_dir = $video_cover_dir;
                $cover_db_path_prefix = 'uploads/Gallery/covers/YCvideos/';
                $cover_prefix = 'video_cover_';
            }
            
            $image_number = 1;
            $found_available = false;
            for ($i = 1; $i <= 100; $i++) {
                $test_filename = $cover_upload_dir . $cover_prefix . $i . ".{$file_extension}";
                if (!file_exists($test_filename)) {
                    $image_number = $i;
                    $found_available = true;
                    break;
                }
            }
            if (!$found_available) {
                $upload_success = false;
                $error_message = "Too many cover images. Please delete some existing images first.";
            } else {
                $cover_image_name = $cover_prefix . $image_number . ".{$file_extension}";
                $cover_image_path = $cover_upload_dir . $cover_image_name;
                $cover_image_db_path = $cover_db_path_prefix . $cover_image_name;
                $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $image_file_type = $_FILES['cover_image']['type'];
                if (!in_array($image_file_type, $allowed_image_types)) {
                    $upload_success = false;
                    $error_message = "Cover image must be JPG, JPEG, PNG, or GIF.";
                } elseif ($_FILES['cover_image']['size'] > 10 * 1024 * 1024) {
                    $upload_success = false;
                    $error_message = "Cover image size must be less than 10MB.";
                } else {
                    // Ensure the directory is writable
                    if (!is_writable($cover_upload_dir)) {
                        $upload_success = false;
                        $error_message = "Cover directory is not writable: " . $cover_upload_dir;
                        error_log("Directory not writable: " . $cover_upload_dir . " - permissions: " . decoct(fileperms($cover_upload_dir)));
                    } elseif (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image_path)) {
                        $upload_success = false;
                        $error_message = "Failed to upload cover image. Target: " . $cover_image_path . " Temp: " . $_FILES['cover_image']['tmp_name'];
                        error_log("Failed to move uploaded file from " . $_FILES['cover_image']['tmp_name'] . " to " . $cover_image_path);
                        error_log("Target directory writable: " . (is_writable($cover_upload_dir) ? 'YES' : 'NO'));
                        error_log("Target directory permissions: " . decoct(fileperms($cover_upload_dir)));
                    }
                }
            }
        } else {
            $upload_success = false;
            $error_message = "Cover image upload error.";
        }
    } else {
        $upload_success = false;
        $error_message = "Please select a cover image file.";
        $cover_image_db_path = '';
    }

    // Handle media input: for audio, expect a file. For video, use YouTube URL or fall back to file.
    if ($upload_success) {
        if ($file_type === 'audio') {
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
                $media_file_name = time() . '_media_' . $_FILES['media_file']['name'];
                $media_file_path = $audio_upload_dir . $media_file_name;
                $media_db_path = 'uploads/Gallery/YCaudio/' . $media_file_name;
                $allowed_media_types = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'];
                $media_file_type = $_FILES['media_file']['type'];
                if (!in_array($media_file_type, $allowed_media_types)) {
                    $upload_success = false;
                    $error_message = "Invalid audio file type.";
                } elseif ($_FILES['media_file']['size'] > 100 * 1024 * 1024) {
                    $upload_success = false;
                    $error_message = "Audio file size must be less than 100MB.";
                } else {
                    if (!move_uploaded_file($_FILES['media_file']['tmp_name'], $media_file_path)) {
                        $upload_success = false;
                        $error_message = "Failed to upload audio file.";
                        if (file_exists($cover_image_path)) { unlink($cover_image_path); }
                    }
                }
            } else {
                $upload_success = false;
                $error_message = "Please upload an audio file.";
            }
        } else { // video
            if ($youtube_url !== '') {
                // Basic sanity check for YouTube URL
                if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/+/i', $youtube_url)) {
                    $media_db_path = $youtube_url; // store URL directly
                } else {
                    $upload_success = false;
                    $error_message = "Please enter a valid YouTube URL.";
                }
            } elseif (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
                // Fallback: allow direct video upload if provided
                $media_file_name = time() . '_media_' . $_FILES['media_file']['name'];
                $media_file_path = $video_upload_dir . $media_file_name;
                $media_db_path = 'uploads/Gallery/YCvideos/' . $media_file_name;
                $allowed_media_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'];
                $media_file_type = $_FILES['media_file']['type'];
                if (!in_array($media_file_type, $allowed_media_types)) {
                    $upload_success = false;
                    $error_message = "Invalid video file type.";
                } elseif ($_FILES['media_file']['size'] > 200 * 1024 * 1024) {
                    $upload_success = false;
                    $error_message = "Video file size must be less than 200MB.";
                } else if (!move_uploaded_file($_FILES['media_file']['tmp_name'], $media_file_path)) {
                    $upload_success = false;
                    $error_message = "Failed to upload video file.";
                    if (file_exists($cover_image_path)) { unlink($cover_image_path); }
                }
            } else {
                $upload_success = false;
                $error_message = "Please provide a YouTube URL for video.";
            }
        }
    }

    // Save to database if upload successful
    if ($upload_success && $cover_image_db_path) {
        try {
            if ($file_type === 'audio') {
                // Insert into songs table, including cover_image
                $stmt = $pdo->prepare("INSERT INTO songs (title, artist_name, music_category, description, audio_path, cover_image, category, hits, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $title,
                    $artist_name,
                    $music_category,
                    $description,
                    $media_db_path, // Use the new organized path
                    $cover_image_db_path,
                    $category,
                    $hits
                ]);
                header("Location: YCGalleryadmin.php?page=video&upload=success");
                exit();
            } else {
                // Insert into video table, check if thumbnail_path column exists
                $columns = $pdo->query("SHOW COLUMNS FROM video")->fetchAll(PDO::FETCH_COLUMN);
                $fields = ['title', 'description'];
                $params = [$title, $description];
                if (in_array('thumbnail_path', $columns)) {
                    $fields[] = 'thumbnail_path';
                    $params[] = $cover_image_db_path;
                }
                if (in_array('cover_image', $columns)) {
                    $fields[] = 'cover_image';
                    $params[] = $cover_image_db_path;
                }
                if (in_array('video_type', $columns)) {
                    $fields[] = 'video_type';
                    $params[] = $category ? $category : 'music_video';
                }
                $fields[] = 'video_path';
                $params[] = $media_db_path;
                if (in_array('created_at', $columns)) {
                    $fields[] = 'created_at';
                    $params[] = date('Y-m-d H:i:s');
                }
                
                // Add additional fields if they exist in the video table
                if (in_array('Location', $columns)) {
                    $location_value = isset($_POST['location']) && $_POST['location'] !== '' ? $_POST['location'] : (isset($_POST['category']) ? $_POST['category'] : null);
                    $fields[] = 'Location';
                    $params[] = $location_value;
                }
                if (isset($_POST['music_category']) && in_array('music_category', $columns)) {
                    $fields[] = 'music_category';
                    $params[] = $_POST['music_category'];
                }
                if (isset($_POST['artist_name']) && in_array('artist_name', $columns)) {
                    $fields[] = 'artist_name';
                    $params[] = $_POST['artist_name'];
                }
                
                $sql = "INSERT INTO video (" . implode(", ", $fields) . ") VALUES (" . rtrim(str_repeat('?,', count($fields)), ',') . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                header("Location: YCGalleryadmin.php?page=video&upload=success");
                exit();
            }
        } catch (PDOException $e) {
            if (file_exists($cover_image_path)) {
                unlink($cover_image_path);
            }
            if (file_exists($media_file_path)) {
                unlink($media_file_path);
            }
            $error_message = "Database error: " . $e->getMessage();
        }
    }

    // Redirect with error message
    if ($file_type === 'audio') {
    header("Location: YCGalleryadmin.php?page=video&upload=error&message=" . urlencode($error_message));
    } else {
    header("Location: YCGalleryadmin.php?page=video&upload=error&message=" . urlencode($error_message));
    }
    exit();
}
?>
