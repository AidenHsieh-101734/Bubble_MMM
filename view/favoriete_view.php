<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../logic/utils/auth.php';
require_once __DIR__ . '/../logic/content/favorites.php';
require_once __DIR__ . '/../logic/content/songs.php';

$isLoggedIn = isAuthenticated();
$currentUser = $isLoggedIn ? getCurrentUser() : null;

// Fetch favorites
if ($isLoggedIn) {
    $favoriteSongs = getFavoriteSongs($currentUser['id']);
    $totalDuration = getFavoriteSongsTotalDuration($currentUser['id']);
    $formattedDuration = formatFavoritesDuration($totalDuration);
    $songCount = count($favoriteSongs);
} else {
    // Show top songs for non-logged in users
    $favoriteSongs = getTopSongs(10);
    $totalDuration = 0;
    foreach ($favoriteSongs as $song) {
        $totalDuration += $song['duration'] ?? 0;
    }
    $formattedDuration = formatFavoritesDuration($totalDuration);
    $songCount = count($favoriteSongs);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bubble - Favorites</title>
    <link rel="stylesheet" href="../assets/styling/style.css">
    <link rel="stylesheet" href="../assets/styling/home.css">
    <link rel="stylesheet" href="../assets/styling/favorites.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body>
    <div id="canvas-container"></div>
    <div class="container" id="container">
        <main>

            <header>
                <div class="logo">
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
                        <a href="explore_view.php">
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
                    </div>
                </div>

                <hr>

                <div class="navbar Library">
                    <h3>YOUR PLAYLIST</h3>
                    <div class="nav">
                        <a href="favoriete_view.php" class="active">
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

            <div class="hero" id="favorieteHero">
                <div class="search-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-search-icon lucide-search">
                        <path d="m21 21-4.34-4.34" />
                        <circle cx="11" cy="11" r="8" />
                    </svg>
                    <input type="text" placeholder="Search favorites..." id="zoekbalk">
                </div>

                <div class="favorites-content">
                    <div class="favorite-header">
                        <div class="favorite-cover">
                            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24"
                                fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-heart">
                                <path
                                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                            </svg>
                        </div>
                        <div class="favorite-info">
                            <p>Playlist</p>
                            <h1><?= $isLoggedIn ? 'Favorite Songs' : 'Top Songs' ?></h1>
                            <div class="favorite-meta">
                                <p><?= $isLoggedIn ? htmlspecialchars($currentUser['username'] ?? 'You') : 'Bubble' ?>
                                </p>
                                <span>•</span>
                                <p><?= $songCount ?> songs</p>
                                <span>•</span>
                                <p><?= $formattedDuration ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="favorite-controls">
                        <button class="favorite-play-btn" id="playAllFavorites">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-play-icon lucide-play">
                                <path
                                    d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z" />
                            </svg>
                        </button>
                        <button class="favorite-heart-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24"
                                fill="var(--accent)" stroke="var(--accent)" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-heart">
                                <path
                                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                            </svg>
                        </button>
                        <button class="favorite-menu-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-more-vertical">
                                <circle cx="12" cy="12" r="1" />
                                <circle cx="12" cy="5" r="1" />
                                <circle cx="12" cy="19" r="1" />
                            </svg>
                        </button>
                    </div>

                    <div class="favorite-songs-list song-list-container">
                        <?php if (count($favoriteSongs) === 0): ?>
                            <div class="no-favorites">
                                <p><?= $isLoggedIn ? 'No favorite songs yet. Start adding songs to your favorites!' : 'Sign in to save your favorite songs.' ?>
                                </p>
                                <?php if (!$isLoggedIn): ?>
                                    <a href="login_view.php" class="btn-login">Sign In</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($favoriteSongs as $index => $song): ?>
                                <div class="favorite-song-item song-item" data-song-id="<?= $song['id'] ?>"
                                    data-song-title="<?= htmlspecialchars($song['title']) ?>"
                                    data-song-artist="<?= htmlspecialchars($song['artist_name'] ?? 'Unknown Artist') ?>"
                                    data-song-audio="<?= htmlspecialchars($song['audio_file_path']) ?>"
                                    data-song-cover="<?= htmlspecialchars($song['cover_image'] ?? '../assets/images/albumcover1.png') ?>"
                                    data-song-duration="<?= $song['duration'] ?>">
                                    <div class="favorite-song-left">
                                        <span class="favorite-song-number"><?= $index + 1 ?></span>
                                        <img src="<?= htmlspecialchars($song['cover_image'] ?? '../assets/images/albumcover1.png') ?>"
                                            alt="<?= htmlspecialchars($song['title']) ?>" class="favorite-song-img">
                                        <div class="favorite-song-info">
                                            <h3><?= htmlspecialchars($song['title']) ?></h3>
                                            <p><?= htmlspecialchars($song['artist_name'] ?? 'Unknown Artist') ?></p>
                                        </div>
                                    </div>
                                    <div class="favorite-song-right">
                                        <p class="favorite-song-album">
                                            <?= htmlspecialchars($song['album_title'] ?? $song['title']) ?>
                                        </p>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                            fill="var(--accent)" stroke="var(--accent)" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-heart favorite-song-heart">
                                            <path
                                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        </svg>
                                        <p class="favorite-song-duration"><?= formatDuration($song['duration'] ?? 0) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                    <?php if ($isLoggedIn): ?>
                        <button onclick="window.location.href='profile_view.php'" title="Profile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-user-icon lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </button>
                    <?php else: ?>
                        <button onclick="window.location.href='login_view.php'" class="sign-in-btn" title="Sign in">
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
                        <h3>Select a song</h3>
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
                    <span>0:00</span>
                    <div class="progress-bar">
                        <div class="progress"></div>
                    </div>
                    <span>0:00</span>
                </div>

                <div class="player-extra">
                    <button class="volume-btn"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                            viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-volume2-icon lucide-volume-2">
                            <path
                                d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z" />
                            <path d="M16 9a5 5 0 0 1 0 6" />
                            <path d="M19.364 18.364a9 9 0 0 0 0-12.728" />
                        </svg></button>
                    <button class="repeat-btn"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                            viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-repeat-icon lucide-repeat">
                            <path d="m17 2 4 4-4 4" />
                            <path d="M3 11v-1a4 4 0 0 1 4-4h14" />
                            <path d="m7 22-4-4 4-4" />
                            <path d="M21 13v1a4 4 0 0 1-4 4H3" />
                        </svg></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Player Scripts -->
    <script src="../javascript/audio-player.js"></script>
    <script src="../javascript/queue-manager.js"></script>
    <script src="../javascript/player-ui.js"></script>
    <script src="../javascript/home.js"></script>
    <script>
        // Play all favorites button
        document.getElementById('playAllFavorites')?.addEventListener('click', function () {
            const songItems = document.querySelectorAll('.favorite-song-item');
            if (songItems.length > 0) {
                songItems[0].click();
            }
        });
    </script>
    <script type="module">
        import { initSurrealBackground } from '../javascript/surreal-bg.js'
        initSurrealBackground('canvas-container')
    </script>
</body>

</html>