<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../logic/utils/auth.php';
require_once __DIR__ . '/../logic/content/songs.php';
require_once __DIR__ . '/../logic/content/articles.php';
require_once __DIR__ . '/../logic/content/playlists.php';
require_once __DIR__ . '/../logic/content/videos.php';

// Fetch dynamic content
$genres = getAllGenres();
$featuredPlaylists = getPublicPlaylists(1);
$featuredPlaylist = !empty($featuredPlaylists) ? $featuredPlaylists[0] : null;
$featuredPlaylistSongs = $featuredPlaylist ? getPlaylistWithSongs($featuredPlaylist['id'])['songs'] : [];
$recentArticles = getRecentArticles(5);
$recentVideos = getRecentVideos(10);
$genreSections = ['Hip-Hop', 'Pop', 'R&B', 'Rock', 'Electronic']; // Default genres to show
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bubble - Explore</title>
    <link rel="stylesheet" href="../assets/styling/style.css">
    <link rel="stylesheet" href="../assets/styling/home.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/styling/explore.css">
</head>

<body>
    <div id="canvas-container"></div>
    <div class="container" id="container" style="z-index: 99;">
        <main>
            <header>
                <div class="logo">
                    <!-- <img src="../images/logo.png" alt=""> -->
                    <h1>Bubble</h1>
                </div>

                <div class="navbar">
                    <div class="nav">
                        <a href="../index.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-house-icon lucide-house">
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                                <path
                                    d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            </svg>
                            Home
                        </a>
                        <a href="explore_view.php" class="active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-compass-icon lucide-compass">
                                <path
                                    d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                            Explore
                        </a>
                        <a href="radio_view.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-radio-icon lucide-radio">
                                <path d="M16.247 7.761a6 6 0 0 1 0 8.478" />
                                <path d="M19.075 4.933a10 10 0 0 1 0 14.134" />
                                <path d="M4.925 19.067a10 10 0 0 1 0-14.134" />
                                <path d="M7.753 16.239a6 6 0 0 1 0-8.478" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>
                            Radio
                        </a>
                        <a href="library_view.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-library-icon lucide-library">
                                <path d="m16 6 4 14" />
                                <path d="M12 6v14" />
                                <path d="M8 8v12" />
                                <path d="M4 4v16" />
                            </svg>
                            Library
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="admin_view.php">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-shield-check">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>
                                Admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>

                <div class="navbar Library">
                    <h3>YOUR PLAYLIST</h3>
                    <div class="nav">
                        <a href="favoriete_view.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart">
                                <path
                                    d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5" />
                            </svg>
                            Favorite
                        </a>
                    </div>
                </div>
            </header>

            <div class="hero">
                <div class="search-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-search-icon lucide-search">
                        <path d="m21 21-4.34-4.34" />
                        <circle cx="11" cy="11" r="8" />
                    </svg>
                    <input type="text" placeholder="Search explores, genres, articles..." id="zoekbalk">
                </div>

                <div class="exploreSectionBTN">
                    <div id="animationdiv"></div>
                    <button onclick="lezen()" id="btn-read" class="active">Read</button>
                    <button onclick="luisteren()" id="btn-listen">Listen</button>
                    <button onclick="bekijken()" id="btn-watch">Watch</button>
                </div>

                <div id="LeesContainer" class="section-container glass-panel-large active-section">
                    <div class="TopAlbums" style="margin-left: auto; margin-right: auto;">
                        <div class="albums-header">
                            <h1>Recent Articles</h1>
                        </div>
                        <div class="albums-grid">
                            <?php foreach ($recentArticles as $article): ?>
                                <?php
                                // ensure path is correct relative to view/
                                $banner = htmlspecialchars($article['banner_image'] ?? '../assets/images/default-article.png');
                                // if path doesn't start with http or ../, assume it needs ../
                                if (!preg_match('/^(http|\.\.\/)/', $banner)) {
                                    $banner = '../' . $banner;
                                }
                                ?>
                                <div class="album-item" style="aspect-ratio: 16/9;"
                                    onclick="window.location.href='article_view.php?slug=<?= htmlspecialchars($article['slug']) ?>'">
                                    <img src="<?= $banner ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                                    <div class="album-play-overlay">
                                        <span style="font-weight:bold; text-transform:uppercase;">Read</span>
                                    </div>
                                    <h4 class="album-title" style="margin-top: 10px;">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div id="LuisterSection" style="display: none;">
                    <div class="TopAlbums" style="margin-left: auto; margin-right: auto;">
                        <div class="albums-header">
                            <h1>Browse Genres</h1>
                        </div>
                        <div class="albums-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
                            <?php foreach ($genres as $genre): ?>
                                <?php
                                $hash = md5($genre);
                                $color1 = '#' . substr($hash, 0, 6);
                                $color2 = '#' . substr($hash, 6, 6);
                                $gradient = "linear-gradient(135deg, $color1 0%, $color2 100%)";
                                ?>
                                <div class="album-item genre-card" style="background: <?= $gradient ?>; border:none;"
                                    onclick="window.location.href='#genre-<?= htmlspecialchars($genre) ?>'">
                                    <h3 style="z-index:2; position:relative;"><?= htmlspecialchars($genre) ?></h3>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($featuredPlaylist): ?>
                        <div class="TopAlbums" style="margin-left: auto; margin-right: auto;">
                            <div class="herosection" style="padding:0;">
                                <div class="news-content"
                                    style="opacity: 1; position: relative; transform: none; background: linear-gradient(90deg, rgba(144, 85, 248, 0.2), rgba(0,0,0,0.3)); border-radius: 20px; overflow: hidden; padding: 40px; border: 1px solid rgba(255,255,255,0.1); width: 100%;">
                                    <div style="display: flex; align-items: center; gap: 30px;">
                                        <?php
                                        // Fix playlist image path
                                        $plInfo = $featuredPlaylist['cover_image'] ?? '../assets/images/default-playlist.png';
                                        if (!preg_match('/^(http|\.\.\/)/', $plInfo)) {
                                            $plInfo = '../' . $plInfo;
                                        }
                                        ?>
                                        <img src="<?= htmlspecialchars($plInfo) ?>" alt="Playlist Cover"
                                            style="width: 200px; height: 200px; border-radius: 15px; object-fit: cover; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                                        <div>
                                            <p
                                                style="text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; margin-bottom: 10px; color: var(--accent);">
                                                Featured Playlist</p>
                                            <h1 style="font-size: 3rem; margin-bottom: 10px;">
                                                <?= htmlspecialchars($featuredPlaylist['name']) ?>
                                            </h1>
                                            <button class="play-mix" onclick="playPlaylist(<?= $featuredPlaylist['id'] ?>)"
                                                style="margin-top: 20px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-play-icon lucide-play">
                                                    <path
                                                        d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z" />
                                                </svg>
                                                PLAY NOW
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($genreSections as $genreName):
                        $genreSongs = getSongsByGenre($genreName, 5);
                        if (empty($genreSongs))
                            continue;
                        ?>
                        <div class="TopAlbums" id="genre-<?= htmlspecialchars($genreName) ?>"
                            style="margin-left: auto; margin-right: auto;">
                            <div class="albums-header">
                                <h1><?= htmlspecialchars($genreName) ?></h1>
                            </div>
                            <div class="albums-grid">
                                <?php foreach ($genreSongs as $song): ?>
                                    <?php
                                    $cover = htmlspecialchars($song['cover_image'] ?? '../assets/images/default-cover.png');
                                    if (!preg_match('/^(http|\.\.\/)/', $cover)) {
                                        $cover = '../' . $cover;
                                    }
                                    ?>
                                    <div class="album-item play-song" data-song-id="<?= $song['id'] ?>"
                                        data-song-title="<?= htmlspecialchars($song['title']) ?>"
                                        data-song-artist="<?= htmlspecialchars($song['artist_name'] ?? 'Unknown') ?>"
                                        data-audio-url="<?= htmlspecialchars($song['audio_file_path']) ?>"
                                        data-cover-url="<?= $cover ?>">
                                        <img src="<?= $cover ?>" alt="<?= htmlspecialchars($song['title']) ?>">
                                        <div class="album-play-overlay">
                                            <svg class="lucide lucide-play" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" viewBox="0 0 24 24" fill="white" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="6 3 20 12 6 21 6 3" />
                                            </svg>
                                        </div>
                                        <h4 class="album-title"><?= htmlspecialchars($song['title']) ?></h4>
                                        <p class="album-artist"><?= htmlspecialchars($song['artist_name'] ?? 'Unknown') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="BekijkSection" style="display: none;">
                    <div class="TopAlbums" style="margin-left: auto; margin-right: auto;">
                        <div class="albums-header">
                            <h1>Recent Videos</h1>
                        </div>
                        <div class="albums-grid">
                            <?php foreach ($recentVideos as $video): ?>
                                <?php
                                $thumb = htmlspecialchars($video['thumbnail_url'] ?? '../assets/images/default-video.png');
                                if (!preg_match('/^(http|\.\.\/)/', $thumb)) {
                                    $thumb = '../' . $thumb;
                                }
                                ?>
                                <div class="album-item" style="aspect-ratio: 16/9;"
                                    onclick="openVideo('<?= htmlspecialchars($video['video_url']) ?>', '<?= htmlspecialchars($video['title']) ?>')">
                                    <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                                    <div class="album-play-overlay">
                                        <svg class="lucide lucide-play" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="white" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="6 3 20 12 6 21 6 3" />
                                        </svg>
                                    </div>
                                    <h4 class="album-title" style="margin-top: 10px;">
                                        <?= htmlspecialchars($video['title']) ?>
                                    </h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="songs">
                <div class="profile">
                    <button>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-sun-moon-icon lucide-sun-moon">
                            <path d="M12 2v2" />
                            <path
                                d="M14.837 16.385a6 6 0 1 1-7.223-7.222c.624-.147.97.66.715 1.248a4 4 0 0 0 5.26 5.259c.589-.255 1.396.09 1.248.715" />
                            <path d="M16 12a4 4 0 0 0-4-4" />
                            <path d="m19 5-1.256 1.256" />
                            <path d="M20 12h2" />
                        </svg>
                    </button>
                    <button>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-bell-icon lucide-bell">
                            <path d="M10.268 21a2 2 0 0 0 3.464 0" />
                            <path
                                d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
                        </svg>
                    </button>
                    <?php if (isAuthenticated()): ?>
                        <button onclick="goToProfile()" title="Profile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-user-icon lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </button>
                    <?php else: ?>
                        <button onclick="login()" class="sign-in-btn" title="Sign in">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-user-icon lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span class="sign-in-text">Sign in</span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="playing">
                    <h3>Currently playing</h3>

                    <div class="nowplaying">
                        <img src="../assets/images/albumcover1.png" alt="Now Playing">
                        <h3>Select a song to play</h3>
                        <p>Artist</p>
                        <div class="songInfo"></div>
                    </div>
                </div>

                <div class="nextsongs">
                    <h3>Next song</h3>

                    <div class="nextup next-songs">
                        <p class="no-upcoming">No songs in queue</p>
                    </div>
                </div>

                <div class="lyrics">
                    <h2>Lyrics</h2>
                    <p style="color: rgba(255,255,255,0.5); font-size: 0.9rem; margin-top: 1rem;">No lyrics available
                    </p>
                </div>
            </div>
        </main>
    </div>

    <!-- Same footer player as Index -->
    <div class="afspelen">
        <div class="player">
            <div class="player-main">
                <div class="player-left">
                    <img src="../assets/images/albumcover1.png" alt="Song cover">
                    <div class="player-info">
                        <h4>Song Name</h4>
                        <p>Artist Name</p>
                    </div>
                </div>

                <div class="player-controls">
                    <button><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-skip-back-icon lucide-skip-back">
                            <path
                                d="M17.971 4.285A2 2 0 0 1 21 6v12a2 2 0 0 1-3.029 1.715l-9.997-5.998a2 2 0 0 1-.003-3.432z" />
                            <path d="M3 20V4" />
                        </svg></button>
                    <button class="play"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.25"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play-icon lucide-play">
                            <path
                                d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z" />
                        </svg></button>
                    <button><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-skip-forward-icon lucide-skip-forward">
                            <path d="M21 4v16" />
                            <path
                                d="M6.029 4.285A2 2 0 0 0 3 6v12a2 2 0 0 0 3.029 1.715l9.997-5.998a2 2 0 0 0 .003-3.432z" />
                        </svg></button>
                </div>
            </div>

            <div class="player-expanded">
                <div class="player-progress">
                    <span>1:12</span>
                    <div class="progress-bar">
                        <div class="progress"></div>
                    </div>
                    <span>3:45</span>
                </div>

                <div class="player-extra">
                    <button class="volume-btn"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                            viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-volume2-icon lucide-volume-2">
                            <path
                                d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z" />
                            <path d="M16 9a5 5 0 0 1 0 6" />
                            <path d="M19.364 18.364a9 9 0 0 0 0-12.728" />
                        </svg>
                    </button>
                    <button class="repeat-btn" onclick="toggleRepeat()"><svg xmlns="http://www.w3.org/2000/svg"
                            width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.25"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-repeat-icon lucide-repeat" id="repeat-normal">
                            <path d="m17 2 4 4-4 4" />
                            <path d="M3 11v-1a4 4 0 0 1 4-4h14" />
                            <path d="m7 22-4-4 4-4" />
                            <path d="M21 13v1a4 4 0 0 1-4 4H3" />
                        </svg><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="#ffffff" stroke-width="2.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-repeat1-icon lucide-repeat-1" id="repeat-one"
                            style="display:none;">
                            <path d="m17 2 4 4-4 4" />
                            <path d="M3 11v-1a4 4 0 0 1 4-4h14" />
                            <path d="m7 22-4-4 4-4" />
                            <path d="M21 13v1a4 4 0 0 1-4 4H3" />
                            <path d="M11 10h1v4" />
                        </svg></button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="../assets/styling/favorites.css">
    <script src="../javascript/favorites.js"></script>
    <script src="../javascript/home.js"></script>
    <script src="../javascript/audio-player.js"></script>
    <script src="../javascript/queue-manager.js"></script>
    <script src="../javascript/player-ui.js"></script>
    <script src="../javascript/explore.js"></script>
    <script>
        function openVideo(url, title) {
            // Video overlay logic would go here, reusing existing modal or creating one
            console.log("Open video:", url);
            // This could likely use a similar modal to the article view if we wanted
        }
    </script>
    <script type="module">
        import { initSurrealBackground } from '../javascript/surreal-bg.js'
        initSurrealBackground('canvas-container')
    </script>
</body>

</html>