<?php
/**
 * Articles Content Logic
 * Functions for retrieving article data from the database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getArticlesDb() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get all published articles
 */
function getAllArticles($limit = 20, $offset = 0) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get article by ID
 */
function getArticleById($articleId) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.id = :id
    ");
    $stmt->bindValue(':id', (int)$articleId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get article by slug
 */
function getArticleBySlug($slug) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.slug = :slug
    ");
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get articles by category
 */
function getArticlesByCategory($category, $limit = 10) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.category = :category
        AND a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get featured/top articles
 */
function getFeaturedArticles($limit = 5) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.view_count DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get recent articles
 */
function getRecentArticles($limit = 5) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get related articles (same category, excluding current)
 */
function getRelatedArticles($articleId, $category, $limit = 3) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.category = :category
        AND a.id != :article_id
        AND a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':article_id', (int)$articleId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Increment article view count
 */
function incrementArticleViews($articleId) {
    $db = getArticlesDb();
    $stmt = $db->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = :id");
    $stmt->bindValue(':id', (int)$articleId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Get all unique categories
 */
function getAllArticleCategories() {
    $db = getArticlesDb();
    $stmt = $db->query("SELECT DISTINCT category FROM articles WHERE category IS NOT NULL ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get article count
 */
function getArticleCount() {
    $db = getArticlesDb();
    $stmt = $db->query("SELECT COUNT(*) as count FROM articles WHERE published_at IS NOT NULL");
    return $stmt->fetch()['count'];
}

/**
 * Search articles
 */
function searchArticles($query, $limit = 20) {
    $db = getArticlesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE (a.title LIKE :query OR a.content LIKE :query OR a.excerpt LIKE :query)
        AND a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
        LIMIT :limit
    ");
    $searchQuery = '%' . $query . '%';
    $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Format article date
 */
function formatArticleDate($dateString) {
    if (!$dateString) return '';
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);

    if ($diff->days == 0) {
        return 'Today';
    } elseif ($diff->days == 1) {
        return 'Yesterday';
    } elseif ($diff->days < 7) {
        return $diff->days . ' days ago';
    } else {
        return $date->format('M j, Y');
    }
}

/**
 * Render article card HTML
 */
function renderArticleCard($article) {
    $title = htmlspecialchars($article['title']);
    $excerpt = htmlspecialchars($article['excerpt'] ?? '');
    $banner = htmlspecialchars($article['banner_image'] ?? 'assets/images/default-article.png');
    $slug = htmlspecialchars($article['slug']);
    $category = htmlspecialchars($article['category'] ?? '');
    $readTime = (int)($article['read_time'] ?? 5);
    $date = formatArticleDate($article['published_at']);
    $articleId = (int)$article['id'];

    return <<<HTML
    <div class="article-card" data-article-id="{$articleId}">
        <a href="article_view.php?slug={$slug}">
            <div class="article-image">
                <img src="{$banner}" alt="{$title}">
                <span class="article-category">{$category}</span>
            </div>
            <div class="article-content">
                <h3 class="article-title">{$title}</h3>
                <p class="article-excerpt">{$excerpt}</p>
                <div class="article-meta">
                    <span class="article-date">{$date}</span>
                    <span class="article-read-time">{$readTime} min read</span>
                </div>
            </div>
        </a>
    </div>
HTML;
}

/**
 * Render featured article (larger card)
 */
function renderFeaturedArticle($article) {
    $title = htmlspecialchars($article['title']);
    $excerpt = htmlspecialchars($article['excerpt'] ?? '');
    $banner = htmlspecialchars($article['banner_image'] ?? 'assets/images/default-article.png');
    $slug = htmlspecialchars($article['slug']);
    $category = htmlspecialchars($article['category'] ?? '');
    $readTime = (int)($article['read_time'] ?? 5);
    $author = htmlspecialchars($article['author_name'] ?? 'Bubble Team');

    return <<<HTML
    <div class="featured-article">
        <a href="article_view.php?slug={$slug}">
            <div class="featured-article-image">
                <img src="{$banner}" alt="{$title}">
            </div>
            <div class="featured-article-content">
                <span class="article-category">{$category}</span>
                <h2 class="featured-article-title">{$title}</h2>
                <p class="featured-article-excerpt">{$excerpt}</p>
                <div class="article-meta">
                    <span class="article-author">By {$author}</span>
                    <span class="article-read-time">{$readTime} min read</span>
                </div>
            </div>
        </a>
    </div>
HTML;
}
