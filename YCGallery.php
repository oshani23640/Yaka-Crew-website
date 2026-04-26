<?php
require_once 'YCdb_connection.php';

// Get gallery images from database

// Fetch latest songs from songs table
$latest_songs = [];
try {
  $stmt = $pdo->prepare("SELECT * FROM songs WHERE category != 'top' ORDER BY id DESC LIMIT 20");
  $stmt->execute();
  $latest_songs = $stmt->fetchAll();
} catch (PDOException $e) {
  $latest_songs = [];
}

// Fetch top albums from songs table
$top_albums = [];
try {
  // Order top albums by hit count descending (highest to lowest). Use COALESCE to treat NULL as 0.
  $stmt = $pdo->prepare("SELECT * FROM songs WHERE category = 'top' ORDER BY COALESCE(hits,0) DESC LIMIT 20");
  $stmt->execute();
  $top_albums = $stmt->fetchAll();
} catch (PDOException $e) {
  $top_albums = [];
}

// Fetch latest videos from video table
$latest_videos = [];
try {
  $stmt = $pdo->prepare("SELECT * FROM video ORDER BY id DESC LIMIT 20");
  $stmt->execute();
  $latest_videos = $stmt->fetchAll();
} catch (PDOException $e) {
  $latest_videos = [];
}

// Disable all videos from being shown
// $latest_videos = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Font Awesome for search icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yaka Crew - Music/Video Gallery</title>
  <link rel="stylesheet" href="css/YCGallerystyle.css">
  <!-- Swiper CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"
  />
  <style>
    /* Ensure a single play button is visible in Latest Videos */
    .latest-videos .video-play-overlay::before,
    .latest-videos .video-play-overlay::after {
      content: none !important;
    }
    /* Force-disable any pseudo overlay play icon on video cards */
    .latest-videos .video-card::after,
    .latest-videos .video-card:hover::after {
      content: none !important;
      display: none !important;
      opacity: 0 !important;
    }
  /* Modal/video sizing: force a consistent player box for all videos */
  .video-player-modal .video-player-container { max-width: 900px; width: 95%; }
  .video-player-content { display:flex; justify-content:center; align-items:center; }
  .video-player-content video { width: 100% !important; height: 480px !important; object-fit: contain !important; background: #000; }
  #youtubeContainer, #youtubePlayer { width:100% !important; height:480px !important; }
  #youtubePlayer iframe { width:100% !important; height:100% !important; }
  </style>
</head>
<body>
  <!-- Swiper JS -->
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

  <!-- Responsive Navigation Bar -->
  <nav class="navbar">
    <div class="logo">
      <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo">
    </div>
  
    <ul class="nav-links" id="navLinks">
      <li><a href="YCHome.php">Home</a></li>
      <li class="gallery-dropdown">
        Gallery <span class="arrow">&#9662;</span>
        <ul class="dropdown">
          <li><a href="YCPosts.php">Music</a></li>
          <li><a href="YCGallery.php">Video</a></li>
        </ul>
      </li>
      <li><a href="YCBlogs-index.php">Blogs</a></li>
      <li><a href="YCBooking-index.php">Bookings</a></li>
      <li><a href="YCEvents.php">Events</a></li>
      <li><a href="YCMerch-merch1.php">Merchandise Store</a></li>
    </ul>
      <button class="menu-btn" id="menuBtn" aria-label="Open navigation menu">
      <span class="menu-icon"></span>
    </button>
   
  </nav>
  <script>
    // Responsive menu toggle (safe: checks elements exist)
    document.addEventListener('DOMContentLoaded', function() {
      const menuBtn = document.getElementById('menuBtn');
      const navLinks = document.getElementById('navLinks');
      if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', function() {
          navLinks.classList.toggle('nav-open');
          menuBtn.classList.toggle('open');
        });
      }
    });
  </script>

  <!-- Cover Wallpaper -->
<div class="cover">
  <img src="assets/images/image6.JPG" alt="Cover Wallpaper">
</div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="section-header">
      <h2>Latest Songs</h2>
      <div class="search-container">
        <input type="text" id="songSearch" placeholder="Search songs..." class="search-input">
  <button class="search-btn" aria-label="Search"><i class="fas fa-search"></i></button>
        <div class="search-dropdown" id="searchDropdown"></div>
      </div>
    </div>

    <!-- Image Slider -->
    <div class="song-slider">
      <div class="slider-container">
        <button class="slider-nav-btn slider-nav-left" id="prevBtn">&#8249;</button>
        <div class="slider-track">
          <?php 
          // Only display uploaded data from the database
          if (!empty($latest_songs)) {
            foreach ($latest_songs as $song) {
              $artist_name = '';
              if (!empty($song['description'])) {
                if (preg_match('/Artist: (.*?)(\n|$)/', $song['description'], $matches)) {
                  $artist_name = trim($matches[1]);
                }
              }
              if (!$artist_name && !empty($song['artist_name'])) {
                $artist_name = trim($song['artist_name']);
              }
              echo '<div class="slider-item">';
              $cover_path = isset($song['image_path']) ? $song['image_path'] : (isset($song['cover_image']) ? $song['cover_image'] : '');
              
              // Check for cover image in source/Gallery/images/audio first, then uploads/Gallery/covers/YCaudio
              $cover_exists = false;
              $final_cover_path = '';
              if ($cover_path) {
                $filename = basename($cover_path);
                $source_cover = 'source/Gallery/images/audio/' . $filename;
                $uploads_cover = 'uploads/Gallery/covers/YCaudio/' . $filename;
                
                if (file_exists($source_cover)) {
                  $final_cover_path = $source_cover;
                  $cover_exists = true;
                } elseif (file_exists($uploads_cover)) {
                  $final_cover_path = $uploads_cover;
                  $cover_exists = true;
                }
              }
              
              if ($cover_exists) {
                echo '<img src="' . htmlspecialchars($final_cover_path) . '" alt="' . htmlspecialchars($song['title']) . '" class="slider-image">';
              } else {
                echo '<img src="assets/images/image3.jpeg" alt="' . htmlspecialchars($song['title']) . '" class="slider-image">';
              }
              echo '<div class="song-info">';
              echo '<h4 class="song-title">' . htmlspecialchars($song['title']) . '</h4>';
              if ($artist_name) {
                echo '<p class="artist-name">' . htmlspecialchars($artist_name) . '</p>';
              }
              $audio_file = $song['audio_path'] ?? $song['media_file'] ?? $song['song_file'] ?? '';
              
              // Resolve audio path with multiple fallbacks and project-relative paths
              $audio_file_src = '';
              if ($audio_file) {
                $filename = basename($audio_file);
                $candidates = [];
                // If DB already has a relative path and it exists, use it first
                if (file_exists($audio_file)) { $candidates[] = $audio_file; }
                // Preferred locations
                $candidates[] = 'source/Gallery/YCaudio/' . $filename;
                $candidates[] = 'uploads/Gallery/YCaudio/' . $filename;
                // Common fallback locations observed in this project
                $candidates[] = 'uploads/gallery/' . $filename; // lowercase folder fallback
                $candidates[] = 'assets/audios/' . $filename;    // assets fallback
                foreach ($candidates as $cand) {
                  if ($cand && file_exists($cand)) { $audio_file_src = $cand; break; }
                }
                // If nothing exists on disk, still provide a best-guess project-relative path
                if (!$audio_file_src && $filename) {
                  $audio_file_src = 'uploads/gallery/' . $filename;
                }
              }
              
              echo '<button class="slider-play-btn" data-song="song' . $song['id'] . '" data-audio-src="' . htmlspecialchars($audio_file_src) . '">▶</button>';
              echo '</div>';
              echo '</div>';
            }
          }
          ?>
        </div>
        <button class="slider-nav-btn slider-nav-right" id="nextBtn">&#8250;</button>
      </div>
    </div>

    <div class="content-row">
      <div class="left-section">
        <h2>Top Albums</h2>
        <table class="albums-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Album</th>
              <th>Genre</th>
              <th>Hits</th>
            </tr>
          </thead>
          <tbody id="top-albums">
            <?php 
            if (!empty($top_albums)) {
              $counter = 1;
              foreach ($top_albums as $album) {
                $artist_name = '';
                $music_category = isset($album['music_category']) ? $album['music_category'] : '';
                $hits = isset($album['hits']) ? (int)$album['hits'] : 0;
                if (!empty($album['artist_name'])) {
                  $artist_name = trim($album['artist_name']);
                }
                $cover_path = isset($album['image_path']) ? $album['image_path'] : (isset($album['cover_image']) ? $album['cover_image'] : '');
                
                // Check for cover image in source/Gallery/images/audio first, then uploads/Gallery/covers/YCaudio
                $cover_exists = false;
                $final_cover_path = '';
                if ($cover_path) {
                  $filename = basename($cover_path);
                  $source_cover = 'source/Gallery/images/audio/' . $filename;
                  $uploads_cover = 'uploads/Gallery/covers/YCaudio/' . $filename;
                  
                  if (file_exists($source_cover)) {
                    $final_cover_path = $source_cover;
                    $cover_exists = true;
                  } elseif (file_exists($uploads_cover)) {
                    $final_cover_path = $uploads_cover;
                    $cover_exists = true;
                  }
                }
                
                $cover_for_player = $cover_exists ? $final_cover_path : 'assets/images/image3.jpeg';
                // Format hits using helper if available, else fallback to inline logic
                if (!function_exists('formatHitsCount')) {
                  function formatHitsCount($hits) {
                      $hits = (int)$hits;
                      if ($hits >= 1000000) return round($hits / 1000000, 1) . 'M';
                      if ($hits >= 1000) return round($hits / 1000, 1) . 'K';
                      return (string)$hits;
                  }
                }
                $hits_display = formatHitsCount($hits > 0 ? $hits : 0);
                $album_audio_file = $album['audio_path'] ?? $album['media_file'] ?? $album['song_file'] ?? '';
                
                // Resolve album audio with same fallbacks
                $album_audio_src = '';
                if ($album_audio_file) {
                  $filename = basename($album_audio_file);
                  $candidates = [];
                  if (file_exists($album_audio_file)) { $candidates[] = $album_audio_file; }
                  $candidates[] = 'source/Gallery/YCaudio/' . $filename;
                  $candidates[] = 'uploads/Gallery/YCaudio/' . $filename;
                  $candidates[] = 'uploads/gallery/' . $filename;
                  $candidates[] = 'assets/audios/' . $filename;
                  foreach ($candidates as $cand) {
                    if ($cand && file_exists($cand)) { $album_audio_src = $cand; break; }
                  }
                  if (!$album_audio_src && $filename) {
                    $album_audio_src = 'uploads/gallery/' . $filename;
                  }
                }
                
                echo '<tr data-album-id="' . $album['id'] . '" data-album-title="' . htmlspecialchars($album['title']) . '" data-artist-name="' . htmlspecialchars($artist_name) . '" data-album-image="' . htmlspecialchars($cover_for_player) . '" data-audio-src="' . htmlspecialchars($album_audio_src) . '">';
                echo '<td>' . str_pad($counter, 2, '0', STR_PAD_LEFT) . '</td>';
                echo '<td>';
                echo '<div class="album-info">';
                echo '<div class="play-icon album-play-btn" data-album="album' . $album['id'] . '">▶</div>';
                echo '<span>' . htmlspecialchars($album['title']) . '</span>';
                if ($artist_name) {
                  echo '<div class="artist-name">' . htmlspecialchars($artist_name) . '</div>';
                }
                echo '</div>';
                echo '</td>';
                // Capitalize first letter of genre (music_category)
                $genre_display = $music_category ? ucfirst(strtolower($music_category)) : '';
                echo '<td>' . htmlspecialchars($genre_display) . '</td>';
                echo '<td>' . htmlspecialchars($hits_display) . '</td>';
                echo '</tr>';
                $counter++;
                if ($counter > 10) break;
              }
            }
            ?>
          </tbody>
        </table>
      </div>

      <div class="right-section">
        <h2>Latest Videos</h2>
        <div class="latest-videos" id="latest-videos">
          <?php 
          // Only show items with category 'video' in Latest Videos section
          if (!empty($latest_videos)) {
            foreach ($latest_videos as $video) {
              // Always use artist_name from database if available, otherwise parse from description
              $artist_name = $video['artist_name'] ?? '';
              $description_text = '';
              if ($video['description']) {
                if (strpos($video['description'], 'Artist: ') === 0) {
                  $lines = explode("\n\n", $video['description']);
                  foreach ($lines as $line) {
                    if (strpos($line, 'Artist: ') === 0 && !$artist_name) {
                      $artist_name = str_replace('Artist: ', '', $line);
                    } elseif (strpos($line, 'Description: ') === 0) {
                      $description_text = str_replace('Description: ', '', $line);
                    }
                  }
                } else {
                  $description_text = $video['description'];
                }
              }
              $video_file = $video['video_path'] ?? $video['media_file'] ?? $video['video_file'] ?? '';
              
              // Determine if this is a YouTube URL or a local file
              $video_type = 'file';
              $video_file_src = '';
              if ($video_file) {
                if (preg_match('/^https?:\/\//i', $video_file) && (stripos($video_file, 'youtube.com') !== false || stripos($video_file, 'youtu.be') !== false)) {
                  $video_type = 'youtube';
                  $video_file_src = $video_file;
                } else {
                  // Check for video file in source/Gallery/YCvideos first, then uploads/Gallery/YCvideos
                  $filename = basename($video_file);
                  $source_video = 'source/Gallery/YCvideos/' . $filename;
                  $uploads_video = 'uploads/Gallery/YCvideos/' . $filename;
                  if (file_exists($source_video)) {
                    $video_file_src = $source_video;
                  } elseif (file_exists($uploads_video)) {
                    $video_file_src = $uploads_video;
                  }
                }
              }
              
              echo '<div class="video-card" data-song-name="' . htmlspecialchars($video['title']) . '" data-song-desc="' . htmlspecialchars($description_text) . '" data-video-src="' . htmlspecialchars($video_file_src) . '" data-video-type="' . htmlspecialchars($video_type) . '">';
              echo '<div class="video-thumbnail">';
              $cover = isset($video['image_path']) ? $video['image_path'] : (isset($video['cover_image']) ? $video['cover_image'] : '');
              
              // Check for cover image in source/Gallery/images/video first, then uploads/Gallery/covers/YCvideos
              $cover_exists = false;
              $final_cover_path = '';
              if ($cover) {
                $filename = basename($cover);
                $source_cover = 'source/Gallery/images/video/' . $filename;
                $uploads_cover = 'uploads/Gallery/covers/YCvideos/' . $filename;
                
                if (file_exists($source_cover)) {
                  $final_cover_path = $source_cover;
                  $cover_exists = true;
                } elseif (file_exists($uploads_cover)) {
                  $final_cover_path = $uploads_cover;
                  $cover_exists = true;
                }
              }
              
              if ($cover_exists) {
                echo '<img src="' . htmlspecialchars($final_cover_path) . '" alt="' . htmlspecialchars($video['title']) . '">';
              } else {
                echo '<img src="assets/images/image3.jpeg" alt="' . htmlspecialchars($video['title']) . '">';
              }
              echo '<div class="video-play-overlay">';
              echo '<button class="video-play-btn" data-video-title="' . htmlspecialchars($video['title']) . '" data-video-src="' . htmlspecialchars($video_file_src) . '" data-video-type="' . htmlspecialchars($video_type) . '">▶</button>';
              echo '</div>';
              echo '</div>';
              echo '<div class="video-info">';
              echo '<h3 class="video-title">' . htmlspecialchars($video['title']) . '</h3>';
              if ($artist_name) {
                echo '<p class="video-artist">' . htmlspecialchars($artist_name) . '</p>';
              }
              echo '</div>';
              echo '<div class="video-tooltip">';
              echo '<h4>' . htmlspecialchars($video['title']) . '</h4>';
              if ($artist_name) {
                echo '<p><strong>' . htmlspecialchars($artist_name) . '</strong></p>';
              }
              if ($description_text) {
                echo '<p>' . htmlspecialchars($description_text) . '</p>';
              }
              echo '</div>';
              echo '</div>';
            }
          }
          ?>
        </div>
      </div>
    </div>

  <!-- Audio Player -->
  <div class="player" id="player" style="display:none;">
      <img id="player-pic" src="https://via.placeholder.com/60" alt="Song Pic">
      <span id="player-title">Select a song to play</span>
      <audio id="audio" controls>
        <source id="audio-source" src="" type="audio/mpeg">
        Your browser does not support the audio element.
      </audio>
      <div id="audio-error" style="color:red;display:none;margin-top:5px;"></div>
    </div>

    <!-- Video Player Modal -->
    <div class="video-player-modal" id="videoPlayerModal">
      <div class="video-player-container">
        <div class="video-player-header">
          <h3 id="video-player-title">Video Title</h3>
          <button class="video-close-btn" id="videoCloseBtn">×</button>
        </div>
        <div class="video-player-content">
          <video id="videoPlayer" preload="metadata" controls>
            <source id="videoSource" src="" type="video/mp4">
            <source id="videoSourceWebm" src="" type="video/webm">
            <source id="videoSourceOgg" src="" type="video/ogg">
            Your browser does not support the video tag.
          </video>
          <div id="youtubeContainer" style="display:none; width:100%; background:#000;">
            <div id="youtubePlayer" style="width:100%; height:480px;"></div>
          </div>
          <div id="youtubeFallback" style="display:none; margin-top:10px; text-align:center;">
            <a id="openOnYouTubeBtn" href="#" target="_blank" rel="noopener" style="display:inline-block; padding:10px 14px; background:#c00; color:#fff; border-radius:4px; text-decoration:none; font-weight:600;">Watch on YouTube</a>
          </div>
          <div id="youtubeBlockedMsg" style="display:none; margin-top:8px; padding:10px; text-align:center; background:#1b1b1b; color:#fff; border:1px solid #333; border-radius:4px;">
            This video can’t be embedded by the owner.
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
// Gallery.php JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Deduplicate any accidental multiple play buttons in video thumbnails
    document.querySelectorAll('.latest-videos .video-thumbnail').forEach(thumb => {
      const btns = thumb.querySelectorAll('.video-play-btn');
      if (btns.length > 1) {
        for (let i = 1; i < btns.length; i++) btns[i].remove();
      }
    });
    
    // Slider functionality
  const slider = document.querySelector('.slider-track');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const sliderItems = document.querySelectorAll('.slider-item');
  if (slider && sliderItems.length > 0) {
    let autoLoopInterval;
    function getItemWidth() {
      return slider.querySelector('.slider-item').offsetWidth + 20;
    }
    function moveToNext() {
      slider.style.transition = 'transform 0.3s ease-in-out';
      const itemWidth = getItemWidth();
      slider.style.transform = `translateX(-${itemWidth}px)`;
      setTimeout(() => {
        slider.style.transition = 'none';
        slider.appendChild(slider.firstElementChild);
        slider.style.transform = 'translateX(0)';
      }, 300);
    }
    function moveToPrev() {
      slider.style.transition = 'none';
      slider.insertBefore(slider.lastElementChild, slider.firstElementChild);
      const itemWidth = getItemWidth();
      slider.style.transform = `translateX(-${itemWidth}px)`;
      setTimeout(() => {
        slider.style.transition = 'transform 0.3s ease-in-out';
        slider.style.transform = 'translateX(0)';
      }, 10);
    }
    function startAutoLoop() {
      autoLoopInterval = setInterval(moveToNext, 3000);
    }
    function stopAutoLoop() {
      if (autoLoopInterval) clearInterval(autoLoopInterval);
    }
    startAutoLoop();
    slider.addEventListener('mouseenter', stopAutoLoop);
    slider.addEventListener('mouseleave', startAutoLoop);
    if (nextBtn) {
      nextBtn.addEventListener('click', function() {
        stopAutoLoop();
        moveToNext();
        setTimeout(startAutoLoop, 5000);
      });
    }
    if (prevBtn) {
      prevBtn.addEventListener('click', function() {
        stopAutoLoop();
        moveToPrev();
        setTimeout(startAutoLoop, 5000);
      });
    }
    // Touch/swipe support for mobile
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    slider.addEventListener('touchstart', function(e) {
      stopAutoLoop();
      startX = e.touches[0].clientX;
      isDragging = true;
      slider.style.transition = 'none';
    });
    slider.addEventListener('touchmove', function(e) {
      if (!isDragging) return;
      currentX = e.touches[0].clientX;
      const diffX = startX - currentX;
      slider.style.transform = `translateX(${diffX}px)`;
    });
    slider.addEventListener('touchend', function(e) {
      if (!isDragging) return;
      isDragging = false;
      slider.style.transition = 'transform 0.3s ease-in-out';
      const diffX = startX - currentX;
      if (Math.abs(diffX) > 50) {
        if (diffX > 0) {
          moveToNext();
        } else {
          moveToPrev();
        }
      } else {
        slider.style.transform = 'translateX(0)';
      }
      setTimeout(startAutoLoop, 3000);
    });
  }
    
    // Play button functionality
    const playButtons = document.querySelectorAll('.slider-play-btn');
    const player = document.getElementById('player');
    const playerPic = document.getElementById('player-pic');
    const playerTitle = document.getElementById('player-title');
    const audio = document.getElementById('audio');
    const showPlayer = () => {
      if (player) {
        player.style.display = 'flex';
        player.offsetHeight;
        player.classList.add('active');
      }
    };
    const hidePlayer = () => {
      if (player) {
        player.classList.remove('active');
        player.style.display = 'none';
      }
    };
    if (audio) {
      audio.addEventListener('ended', () => hidePlayer());
    }
    
    playButtons.forEach(button => {

    button.addEventListener('click', function() {
      const songData = button.getAttribute('data-song');
      let audioSrc = button.getAttribute('data-audio-src');
      const songItem = button.closest('.slider-item');
      const songTitle = songItem.querySelector('.song-title').textContent;
      const songImage = songItem.querySelector('.slider-image').src;
      const artistName = songItem.querySelector('.artist-name')?.textContent || '';

      // Prevent multiple rapid clicks
      if (button.disabled) return;
      button.disabled = true;
      setTimeout(() => button.disabled = false, 500);

      // Update player UI first (before showing)
      if (playerPic) playerPic.src = songImage;
      if (playerTitle) {
        playerTitle.textContent = songTitle + (artistName ? ' - ' + artistName : '');
      }

  // Load and play audio
  const audioError = document.getElementById('audio-error');
  const audioSource = document.getElementById('audio-source');
      if (audioError) audioError.style.display = 'none';
      if (audio && audioSrc && audioSource) {
        // Normalize to a project-relative URL without forcing leading slash
        const normalizeMediaSrc = (src) => {
          if (!src) return '';
          // Trim existing leading slash if present (keeps it relative to current host directory)
          let out = src.replace(/^\/+/, '');
          // If it's absolute URL, leave as-is
          if (/^https?:\/\//i.test(src)) return src;
          // Encode spaces
          out = out.replace(/ /g, '%20');
          return out;
        };
        audioSrc = normalizeMediaSrc(audioSrc);
        // Show the player and log debug info
        showPlayer();
        console.log('DEBUG: audioSrc:', audioSrc);
        console.log('DEBUG: window.location.href:', window.location.href);
  // Clear <source> child and set audio.src directly to avoid conflicts
  if (audioSource) audioSource.src = '';
  audio.removeAttribute('src');
  audio.src = audioSrc;
  audio.load();
        audio.currentTime = 0;
        // Add canplay and error handlers
        audio.oncanplay = function() {
          if (audioError) audioError.style.display = 'none';
          console.log('DEBUG: canplay event fired for', audioSrc);
          // Play only after canplay
          audio.play().then(() => {
            if (audioError) audioError.style.display = 'none';
            console.log('DEBUG: audio.play() succeeded for', audioSrc);
          }).catch(e => {
            if (audioError) {
              audioError.textContent = 'Audio play failed: ' + e;
              audioError.style.display = 'block';
            }
            console.log('Audio play failed:', e);
            hidePlayer();
          });
        };
        audio.onerror = function() {
          if (audioError) {
            audioError.textContent = 'Audio failed to load: ' + audioSrc;
            audioError.style.display = 'block';
          }
          console.error('Audio failed to load:', audioSrc);
          hidePlayer();
        };
      } else {
        // Keep player hidden if no valid audio source
        console.log('No audioSrc found for this song; player remains hidden.');
      }

            // Scroll to player after a short delay
            setTimeout(() => {
                if (player) {
                    player.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            }, 200);
        });
    });
    
    // Search functionality with dropdown
    const searchInput = document.getElementById('songSearch');
    const searchDropdown = document.getElementById('searchDropdown');
    
    if (searchInput && searchDropdown) {
        // Collect all content data for search (songs, albums, videos)
        const allContent = [];
        
        // Get songs from slider (Latest Songs)
        const sliderItems = document.querySelectorAll('.slider-item');
    sliderItems.forEach((item) => {
      const title = item.querySelector('.song-title')?.textContent || '';
      const artist = item.querySelector('.artist-name')?.textContent || '';
      const image = item.querySelector('.slider-image')?.src || '';
      const playBtn = item.querySelector('.slider-play-btn');
      const audioSrc = playBtn?.getAttribute('data-audio-src') || '';
      allContent.push({
        title: title,
        artist: artist,
        image: image,
        audioSrc: audioSrc,
        videoSrc: '',
        type: 'latest-song',
        category: 'Latest Songs',
        element: item,
        playType: 'audio'
      });
    });
        
        // Get songs from top albums
        const albumRows = document.querySelectorAll('.albums-table tbody tr');
        albumRows.forEach(row => {
            const title = row.getAttribute('data-album-title') || '';
            const artist = row.getAttribute('data-artist-name') || '';
            const image = row.getAttribute('data-album-image') || '';
            const audioSrc = row.getAttribute('data-audio-src') || '';
            
            if (title) {
                allContent.push({
                    title: title,
                    artist: artist,
                    image: image,
                    audioSrc: audioSrc,
                    videoSrc: '',
                    type: 'top-album',
                    category: 'Top Albums',
                    element: row,
                    playType: 'audio'
                });
            }
        });
        
        // Get videos from latest videos section
        const videoCards = document.querySelectorAll('.video-card');
        videoCards.forEach(card => {
            const title = card.querySelector('.video-title')?.textContent || 
                          card.getAttribute('data-song-name') || '';
            const artist = card.querySelector('.video-artist')?.textContent || '';
            const image = card.querySelector('.video-thumbnail img')?.src || '';
            const videoBtn = card.querySelector('.video-play-btn');
            const videoSrc = videoBtn?.getAttribute('data-video-src') || '';
            const description = card.getAttribute('data-song-desc') || '';
            
            if (title) {
                allContent.push({
                    title: title,
                    artist: artist,
                    image: image,
                    audioSrc: '',
                    videoSrc: videoSrc,
                    description: description,
                    type: 'video',
                    category: 'Latest Videos',
                    element: card,
                    playType: 'video'
                });
            }
        });
        
        // Search input event listener
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                searchDropdown.classList.remove('show');
                return;
            }
            
            // Filter content based on search term
            const filteredContent = allContent.filter(content => 
                content.title.toLowerCase().includes(searchTerm) ||
                content.artist.toLowerCase().includes(searchTerm) ||
                (content.description && content.description.toLowerCase().includes(searchTerm))
            );
            
            // Clear previous results
            searchDropdown.innerHTML = '';
            
            if (filteredContent.length > 0) {
                // Group content by category for better organization
                const groupedContent = {};
                filteredContent.forEach(content => {
                    if (!groupedContent[content.category]) {
                        groupedContent[content.category] = [];
                    }
                    groupedContent[content.category].push(content);
                });
                
                // Display grouped results
                Object.keys(groupedContent).forEach(category => {
                    // Add category header
                    const categoryHeader = document.createElement('div');
                    categoryHeader.className = 'dropdown-category-header';
                    categoryHeader.textContent = category;
                    searchDropdown.appendChild(categoryHeader);
                    
                    // Add items in this category
                    groupedContent[category].slice(0, 5).forEach(content => { // Limit per category
                        const dropdownItem = document.createElement('div');
                        dropdownItem.className = 'dropdown-item';
                        
                        // Different icons for different content types
                        const playIcon = content.playType === 'video' ? '🎬' : '▶';
                        
                        dropdownItem.innerHTML = `
                            <img src="${content.image || 'https://via.placeholder.com/40'}" alt="${content.title}">
                            <div class="dropdown-item-info">
                                <div class="dropdown-item-title">${content.title}</div>
                                <div class="dropdown-item-artist">${content.artist || 'Unknown Artist'}</div>
                                <div class="dropdown-item-type">${content.category}</div>
                            </div>
                            <button class="dropdown-play-btn" 
                                data-content-title="${content.title}" 
                                data-content-artist="${content.artist}" 
                                data-content-image="${content.image}" 
                                data-audio-src="${content.audioSrc}" 
                                data-video-src="${content.videoSrc}"
                                data-play-type="${content.playType}">${playIcon}</button>
                        `;
                        
                        // Add click event for the dropdown item
                        dropdownItem.addEventListener('click', function(e) {
                            if (!e.target.classList.contains('dropdown-play-btn')) {
                                // Scroll to the original element
                                if (content.element) {
                                    content.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    
                                    // Add highlight effect
                                    content.element.style.background = 'rgba(255, 221, 68, 0.2)';
                                    setTimeout(() => {
                                        content.element.style.background = '';
                                    }, 2000);
                                }
                                
                                // Hide dropdown
                                searchDropdown.classList.remove('show');
                                searchInput.value = '';
                            }
                        });
                        
                        // Add play button event
                        const playBtn = dropdownItem.querySelector('.dropdown-play-btn');
                        playBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            
                            const playType = this.getAttribute('data-play-type');
                            
                            if (playType === 'video') {
                                // Handle video playback
                                const videoTitle = this.getAttribute('data-content-title');
                                const videoSrc = this.getAttribute('data-video-src');
                                
                                if (videoSrc && videoSrc.trim() !== '') {
                                    // Use existing video player modal functionality
                                    const videoPlayerModal = document.getElementById('videoPlayerModal');
                                    const videoPlayer = document.getElementById('videoPlayer');
                                    const videoSource = document.getElementById('videoSource');
                                    const videoPlayerTitle = document.getElementById('video-player-title');
                                    
                                    // Clear previous sources
                                    videoSource.src = '';
                                    
                                    // Set video source
                                    videoSource.src = videoSrc;
                                    videoSource.type = 'video/mp4';
                                    
                                    // Set video title
                                    videoPlayerTitle.textContent = videoTitle || 'Video Player';
                                    
                                    // Reset and load video
                                    videoPlayer.currentTime = 0;
                                    videoPlayer.load();
                                    
                                    // Show modal
                                    videoPlayerModal.classList.add('show');
                                    
                                    // Auto-play
                                    videoPlayer.play().catch(e => {
                                        console.log('Video autoplay failed:', e);
                                    });
                                } else {
                                    alert('Video file not available.');
                                }
                            } else {
                                // Handle audio playback
                                const audioSrc = this.getAttribute('data-audio-src');
                                const contentTitle = this.getAttribute('data-content-title');
                                const contentArtist = this.getAttribute('data-content-artist');
                                const contentImage = this.getAttribute('data-content-image');
                                
                                // Update player
                                if (playerPic && contentImage) {
                                    playerPic.src = contentImage;
                                }
                                if (playerTitle) {
                                    playerTitle.textContent = contentTitle + (contentArtist ? ' - ' + contentArtist : '');
                                }
                                
                                // Play audio
        if (audio && audioSrc) {
                  const norm = (src) => {
                    if (!src) return '';
                    if (/^https?:\/\//i.test(src)) return src;
                    return src.replace(/^\/+/, '').replace(/ /g, '%20');
                  };
                  const finalSrc = norm(audioSrc);
                                    setTimeout(() => {
          // Clear child <source> and set audio.src
          const child = document.getElementById('audio-source');
          if (child) child.src = '';
          audio.removeAttribute('src');
          audio.src = finalSrc;
                                        audio.load();
                    showPlayer();
                    audio.play().catch(e => {
                      console.log('Audio play failed:', e);
                      hidePlayer();
                    });
                                    }, 100);
                                }
                                
                                // Scroll to player
                                setTimeout(() => {
                                    if (player) {
                                        player.scrollIntoView({ behavior: 'smooth', block: 'end' });
                                    }
                                }, 200);
                            }
                            
                            // Hide dropdown
                            searchDropdown.classList.remove('show');
                            searchInput.value = '';
                        });
                        
                        searchDropdown.appendChild(dropdownItem);
                    });
                });
                
                searchDropdown.classList.add('show');
            } else {
                // Show "No results found" message
                const noResults = document.createElement('div');
                noResults.className = 'dropdown-item no-results';
                noResults.innerHTML = `
                    <div class="dropdown-item-info">
                        <div class="dropdown-item-title">No content found</div>
                        <div class="dropdown-item-artist">Try a different search term</div>
                    </div>
                `;
                searchDropdown.appendChild(noResults);
                searchDropdown.classList.add('show');
            }
        });
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.remove('show');
            }
        });
        
        // Hide dropdown when pressing Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchDropdown.classList.remove('show');
                searchInput.blur();
            }
        });
    }
    
    // Video card hover effects
    const videoCards = document.querySelectorAll('.video-card');
    videoCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const tooltip = this.querySelector('.video-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.video-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(10px)';
            }
        });
    });

    // Video player functionality
    const videoPlayButtons = document.querySelectorAll('.video-play-btn');
    const videoPlayerModal = document.getElementById('videoPlayerModal');
    const videoPlayer = document.getElementById('videoPlayer');
    const videoSource = document.getElementById('videoSource');
    const videoSourceWebm = document.getElementById('videoSourceWebm');
    const videoSourceOgg = document.getElementById('videoSourceOgg');
    const videoPlayerTitle = document.getElementById('video-player-title');
  const videoCloseBtn = document.getElementById('videoCloseBtn');
    const youtubeContainer = document.getElementById('youtubeContainer');
    const youtubePlayer = document.getElementById('youtubePlayer');
  const youtubeFallback = document.getElementById('youtubeFallback');
  const openOnYouTubeBtn = document.getElementById('openOnYouTubeBtn');
  const youtubeBlockedMsg = document.getElementById('youtubeBlockedMsg');

    // YouTube IFrame API integration
    let ytApiReady = false;
    let ytPlayer = null;
    let pendingYouTube = null; // { id, start, url }
    function loadYouTubeAPI() {
      if (!window.YT || !window.YT.Player) {
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
      }
    }
    function ensureYTPlayer() {
      if (ytApiReady && !ytPlayer && youtubePlayer) {
        ytPlayer = new YT.Player('youtubePlayer', {
          playerVars: { autoplay: 1, rel: 0, modestbranding: 1 },
          events: {
            onReady: function() {
              if (pendingYouTube) {
                ytPlayer.loadVideoById({ videoId: pendingYouTube.id, startSeconds: pendingYouTube.start || 0 });
              }
            },
            onError: function(e) {
              // 101 or 150 = embedding disabled
              if (e && (e.data === 101 || e.data === 150)) {
                if (youtubeBlockedMsg) youtubeBlockedMsg.style.display = 'block';
                if (youtubeFallback) youtubeFallback.style.display = 'block';
              }
            }
          }
        });
      }
    }
    window.onYouTubeIframeAPIReady = function() {
      ytApiReady = true;
      ensureYTPlayer();
      if (pendingYouTube && ytPlayer) {
        ytPlayer.loadVideoById({ videoId: pendingYouTube.id, startSeconds: pendingYouTube.start || 0 });
      }
    };

    const parseYouTube = (url) => {
      if (!url) return { id: '', start: 0 };
      try {
        const u = new URL(url, window.location.href);
        let id = '';
        if (u.hostname.includes('youtu.be')) {
          id = u.pathname.replace('/', '');
        } else if (u.searchParams.has('v')) {
          id = u.searchParams.get('v');
        } else if (u.pathname.includes('/embed/')) {
          id = u.pathname.split('/embed/')[1];
        }
        let start = 0;
        if (u.searchParams.has('t')) {
          const t = u.searchParams.get('t');
          const m = /^([0-9]+)m([0-9]+)s$/.exec(t);
          if (m) start = parseInt(m[1],10)*60 + parseInt(m[2],10);
          else if (/^[0-9]+s$/.test(t)) start = parseInt(t,10);
          else if (/^[0-9]+$/.test(t)) start = parseInt(t,10);
        } else if (u.searchParams.has('start')) {
          start = parseInt(u.searchParams.get('start'), 10) || 0;
        }
        return { id, start };
      } catch { return { id: '', start: 0 }; }
    };

  const toYouTubeEmbed = (url) => {
    if (!url) return '';
    try {
      const u = new URL(url, window.location.href);
      let id = '';
      if (u.hostname.includes('youtu.be')) {
        id = u.pathname.replace('/', '');
      } else if (u.searchParams.has('v')) {
        id = u.searchParams.get('v');
      } else if (u.pathname.includes('/embed/')) {
        id = u.pathname.split('/embed/')[1];
      }
      if (!id) return '';
      let start = 0;
      if (u.searchParams.has('t')) {
        const t = u.searchParams.get('t');
        const m = /^([0-9]+)m([0-9]+)s$/.exec(t);
        if (m) start = parseInt(m[1],10)*60 + parseInt(m[2],10);
        else if (/^[0-9]+s$/.test(t)) start = parseInt(t,10);
        else if (/^[0-9]+$/.test(t)) start = parseInt(t,10);
      } else if (u.searchParams.has('start')) {
        start = parseInt(u.searchParams.get('start'), 10) || 0;
      }
      const params = new URLSearchParams({ autoplay: '1', rel: '0', modestbranding: '1' });
      if (start > 0) params.set('start', String(start));
      return `https://www.youtube.com/embed/${id}?${params.toString()}`;
    } catch { return ''; }
  };

    videoPlayButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const videoTitle = this.getAttribute('data-video-title');
            const videoSrc = this.getAttribute('data-video-src');
            const videoType = this.getAttribute('data-video-type') || '';
            
            // Prevent multiple rapid clicks
            if (button.disabled) return;
            button.disabled = true;
            setTimeout(() => button.disabled = false, 500);
            
            console.log('Video play clicked:', videoTitle, 'Source:', videoSrc);
            
      if (videoSrc && videoSrc.trim() !== '') {
                // Clear previous sources
                videoSource.src = '';
                videoSourceWebm.src = '';
                videoSourceOgg.src = '';
        // Reset and hide YouTube by default
                if (ytPlayer && ytApiReady) { try { ytPlayer.stopVideo(); } catch(_){} }
                if (youtubeContainer) youtubeContainer.style.display = 'none';
                if (youtubeFallback) youtubeFallback.style.display = 'none';
                if (youtubeBlockedMsg) youtubeBlockedMsg.style.display = 'none';
                
                // Set video title
                videoPlayerTitle.textContent = videoTitle || 'Video Player';
                
        // If YouTube, embed in iframe
                if (videoType.toLowerCase() === 'youtube' || (/^https?:\/\//i.test(videoSrc) && (videoSrc.includes('youtube.com') || videoSrc.includes('youtu.be')))) {
                    const parsed = parseYouTube(videoSrc);
                    if (!parsed.id) {
            alert('Invalid YouTube URL.');
            return;
          }
                    if (videoPlayer) videoPlayer.style.display = 'none';
                    if (youtubeContainer) youtubeContainer.style.display = 'block';
                    if (openOnYouTubeBtn) openOnYouTubeBtn.href = videoSrc;
                    if (youtubeFallback) youtubeFallback.style.display = 'none';
                    if (youtubeBlockedMsg) youtubeBlockedMsg.style.display = 'none';
                    // Load via IFrame API and auto-open on error 101/150
                    loadYouTubeAPI();
                    pendingYouTube = { id: parsed.id, start: parsed.start, url: videoSrc };
                    if (ytApiReady) {
                      ensureYTPlayer();
                      if (ytPlayer) {
                        ytPlayer.loadVideoById({ videoId: parsed.id, startSeconds: parsed.start || 0 });
                      }
                    }
          videoPlayerModal.classList.add('show');
        } else {
          // Local file: detect extension and set appropriate source
          const fileExtension = videoSrc.toLowerCase().split('.').pop();
          if (fileExtension === 'mp4' || fileExtension === 'm4v') {
            videoSource.src = videoSrc;
            videoSource.type = 'video/mp4';
          } else if (fileExtension === 'webm') {
            videoSourceWebm.src = videoSrc;
          } else if (fileExtension === 'ogg' || fileExtension === 'ogv') {
            videoSourceOgg.src = videoSrc;
          } else {
            // Default to mp4
            videoSource.src = videoSrc;
            videoSource.type = 'video/mp4';
          }
          if (videoPlayer) videoPlayer.style.display = 'block';
                    // Hide YouTube fallback button for local files
                    if (youtubeFallback) youtubeFallback.style.display = 'none';
                    // Reset video player
          videoPlayer.currentTime = 0;
          // Load the video
          videoPlayer.load();
          // Show modal
          videoPlayerModal.classList.add('show');
          // Add load event listener
          videoPlayer.addEventListener('loadeddata', function() {
            console.log('Video loaded successfully');
            // Auto-play the video
            videoPlayer.play().catch(e => {
              console.log('Video autoplay failed:', e);
              alert('Click the play button in the video player to start playing.');
            });
          }, { once: true });
          // Add error event listener
          videoPlayer.addEventListener('error', function(e) {
            console.error('Video error:', e);
            alert('Error loading video. Please check if the file exists and is in a supported format (MP4, WebM, OGG).');
          }, { once: true });
        }
                
            } else {
                // Show message for videos without source
                alert('Video file not available. Please upload a video file for this content.');
            }
        });
    });

    // Close video player modal
    if (videoCloseBtn) {
    videoCloseBtn.addEventListener('click', function() {
      videoPlayerModal.classList.remove('show');
            if (videoPlayer) {
    if (document.pictureInPictureElement !== videoPlayer) {
      videoPlayer.pause();
      videoPlayer.currentTime = 0;
      videoSource.src = '';
      if (videoSourceWebm) videoSourceWebm.src = '';
      if (videoSourceOgg) videoSourceOgg.src = '';
      videoPlayer.load();
    }
      }
            if (ytPlayer && ytApiReady) { try { ytPlayer.stopVideo(); } catch(_){} }
      if (youtubeContainer) youtubeContainer.style.display = 'none';
            if (youtubeFallback) youtubeFallback.style.display = 'none';
            if (youtubeBlockedMsg) youtubeBlockedMsg.style.display = 'none';
      if (videoPlayer) videoPlayer.style.display = '';
    });
    }

    // Close modal when clicking outside the player
    if (videoPlayerModal) {
        videoPlayerModal.addEventListener('click', function(e) {
      if (e.target === videoPlayerModal) {
        videoPlayerModal.classList.remove('show');
                if (videoPlayer) {
          videoPlayer.pause();
          videoPlayer.currentTime = 0;
          videoSource.src = '';
          if (videoSourceWebm) videoSourceWebm.src = '';
          if (videoSourceOgg) videoSourceOgg.src = '';
          videoPlayer.load();
        }
                if (ytPlayer && ytApiReady) { try { ytPlayer.stopVideo(); } catch(_){} }
        if (youtubeContainer) youtubeContainer.style.display = 'none';
                if (youtubeFallback) youtubeFallback.style.display = 'none';
                if (youtubeBlockedMsg) youtubeBlockedMsg.style.display = 'none';
        if (videoPlayer) videoPlayer.style.display = '';
      }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && videoPlayerModal.classList.contains('show')) {
      videoPlayerModal.classList.remove('show');
            if (videoPlayer) {
        videoPlayer.pause();
        videoPlayer.currentTime = 0;
        videoSource.src = '';
        if (videoSourceWebm) videoSourceWebm.src = '';
        if (videoSourceOgg) videoSourceOgg.src = '';
        videoPlayer.load();
      }
            if (ytPlayer && ytApiReady) { try { ytPlayer.stopVideo(); } catch(_){} }
      if (youtubeContainer) youtubeContainer.style.display = 'none';
            if (youtubeFallback) youtubeFallback.style.display = 'none';
            if (youtubeBlockedMsg) youtubeBlockedMsg.style.display = 'none';
      if (videoPlayer) videoPlayer.style.display = '';
    }
    });
    
    // Album play button functionality
    const albumPlayButtons = document.querySelectorAll('.album-play-btn');
    albumPlayButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent row click
            const albumRow = this.closest('tr');
            const albumTitle = albumRow.getAttribute('data-album-title');
            // Always get artist name from data-artist-name attribute
            const artistName = albumRow.getAttribute('data-artist-name') || '';
            const albumImage = albumRow.getAttribute('data-album-image');
            const audioSrc = albumRow.getAttribute('data-audio-src');
            const albumId = albumRow.getAttribute('data-album-id');
            // Prevent multiple rapid clicks
            if (button.disabled) return;
            button.disabled = true;
            setTimeout(() => button.disabled = false, 500);
            // Update player UI first
            if (playerPic && albumImage) {
                playerPic.src = albumImage;
            }
            if (playerTitle) {
                playerTitle.textContent = albumTitle + (artistName ? ' - ' + artistName : '');
            }
      // Load and play audio after UI is updated
      if (audio && audioSrc) {
        const norm = (src) => {
          if (!src) return '';
          if (/^https?:\/\//i.test(src)) return src;
          return src.replace(/^\/+/, '').replace(/ /g, '%20');
        };
        const finalSrc = norm(audioSrc);
                setTimeout(() => {
        // Clear child <source> and set audio.src for Top Albums
        const child = document.getElementById('audio-source');
        if (child) child.src = '';
        audio.removeAttribute('src');
        audio.src = finalSrc;
                    audio.load();
          showPlayer();
          audio.play().catch(e => {
            console.log('Audio play failed:', e);
            hidePlayer();
          });
                }, 100);
            }
            // Scroll to player after a short delay
            setTimeout(() => {
                if (player) {
                    player.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            }, 200);
            // Add visual feedback
            this.style.transform = 'scale(1.2)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
            console.log('Playing album:', albumTitle, 'Artist:', artistName);
        });
    });
    
    // Album table row hover effects (without play functionality)
    const albumRows = document.querySelectorAll('.albums-table tbody tr');
    albumRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(255, 221, 68, 0.1)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Auto-resize slider on window resize
    window.addEventListener('resize', function() {
        if (slider && sliderItems.length > 0) {
            updateSliderPosition();
        }
    });
});
  </script>

<?php include_once 'footer.php'; ?>
</body>
</html>

