<?php
/**
 * Videos Content Logic
 * Functions for retrieving video data from the database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getVideosDb() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get all videos
 */
function getAllVideos($limit = 20) {
    $db = getVideosDb();
    $stmt = $db->prepare("
        SELECT * FROM videos
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get video by ID
 */
function getVideoById($videoId) {
    $db = getVideosDb();
    $stmt = $db->prepare("SELECT * FROM videos WHERE id = :id");
    $stmt->bindValue(':id', (int)$videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get videos by category
 */
function getVideosByCategory($category, $limit = 10) {
    $db = getVideosDb();
    $stmt = $db->prepare("
        SELECT * FROM videos
        WHERE category = :category
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get featured/top videos by view count
 */
function getFeaturedVideos($limit = 5) {
    $db = getVideosDb();
    $stmt = $db->prepare("
        SELECT * FROM videos
        ORDER BY view_count DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get recent videos
 */
function getRecentVideos($limit = 5) {
    $db = getVideosDb();
    $stmt = $db->prepare("
        SELECT * FROM videos
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Increment video view count
 */
function incrementVideoViews($videoId) {
    $db = getVideosDb();
    $stmt = $db->prepare("UPDATE videos SET view_count = view_count + 1 WHERE id = :id");
    $stmt->bindValue(':id', (int)$videoId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Get all unique video categories
 */
function getAllVideoCategories() {
    $db = getVideosDb();
    $stmt = $db->query("SELECT DISTINCT category FROM videos WHERE category IS NOT NULL ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get video count
 */
function getVideoCount() {
    $db = getVideosDb();
    $stmt = $db->query("SELECT COUNT(*) as count FROM videos");
    return $stmt->fetch()['count'];
}

/**
 * Format video duration
 */
function formatVideoDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%d:%02d', $minutes, $secs);
}

/**
 * Format view count
 */
function formatViewCount($count) {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    return number_format($count);
}

/**
 * Render video card HTML
 */
function renderVideoCard($video) {
    $title = htmlspecialchars($video['title']);
    $description = htmlspecialchars($video['description'] ?? '');
    $thumbnail = htmlspecialchars($video['thumbnail_url'] ?? 'assets/images/default-video.png');
    $videoUrl = htmlspecialchars($video['video_url']);
    $category = htmlspecialchars($video['category'] ?? '');
    $duration = formatVideoDuration($video['duration'] ?? 0);
    $views = formatViewCount($video['view_count'] ?? 0);
    $videoId = (int)$video['id'];

    return <<<HTML
    <div class="video-card" data-video-id="{$videoId}" data-video-url="{$videoUrl}">
        <div class="video-thumbnail">
            <img src="{$thumbnail}" alt="{$title}">
            <span class="video-duration">{$duration}</span>
            <div class="video-play-overlay">
                <svg class="lucide lucide-play" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="white" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="6 3 20 12 6 21 6 3"/>
                </svg>
            </div>
        </div>
        <div class="video-info">
            <h4 class="video-title">{$title}</h4>
            <p class="video-description">{$description}</p>
            <div class="video-meta">
                <span class="video-category">{$category}</span>
                <span class="video-views">{$views} views</span>
            </div>
        </div>
    </div>
HTML;
}

/**
 * Render video embed for overlay/modal
 */
function renderVideoEmbed($video) {
    $title = htmlspecialchars($video['title']);
    $videoUrl = htmlspecialchars($video['video_url']);

    return <<<HTML
    <div class="video-embed-container">
        <iframe
            src="{$videoUrl}"
            title="{$title}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>
    </div>
HTML;
}
