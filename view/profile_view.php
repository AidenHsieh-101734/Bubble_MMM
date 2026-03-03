<?php
require_once __DIR__ . '/../logic/profile.php';

// Ensure consistent view structure
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../assets/styling/style.css">
    <link rel="stylesheet" href="../assets/styling/home.css">
    <link rel="stylesheet" href="../assets/styling/profile.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>
    <div id="canvas-container"></div>
    <div class="container" id="container">
        <main>
            <!-- Sidebar (Consistent with Home) -->
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
            </header>

            <!-- Main Content Area -->
            <div class="hero">
                <div class="glass-panel-large" style="height: 100%; overflow-y: auto;">

                    <!-- Profile Header / Banner Area -->
                    <div class="profile-header-section">
                        <div class="avatar-large">
                            <?php if ($user['avatar_url']): ?>
                                <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar">
                            <?php else: ?>
                                <div class="default-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-meta">
                            <h1><?= htmlspecialchars($user['username']) ?></h1>
                            <p class="bio"><?= htmlspecialchars($user['bio'] ?? 'No bio yet.') ?></p>
                            <div class="meta-stats">
                                <span><strong><?= $user['playlist_count'] ?></strong> Playlists</span>
                                <span>&bull;</span>
                                <span><strong><?= $user['favorites_count'] ?></strong> Favorites</span>
                            </div>
                        </div>
                        <div class="profile-actions">
                            <button onclick="toggleEditMode()" class="btn-edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                </svg>
                                Edit Profile
                            </button>
                        </div>
                    </div>

                    <!-- Tabs/Navigation within Profile -->
                    <div class="profile-tabs">
                        <button class="tab-btn active" onclick="switchTab('overview')">Overview</button>
                        <button class="tab-btn" onclick="switchTab('playlists')">Playlists</button>
                        <button class="tab-btn" onclick="switchTab('settings')" id="settingsTabBtn"
                            style="display:none;">Settings</button>
                    </div>

                    <!-- Content Sections -->
                    <div id="overview-section" class="profile-content active">

                        <!-- Recent Favorites -->
                        <div class="content-block">
                            <h3>Recent Favorites</h3>
                            <div class="albums-grid">
                                <?php foreach ($userFavorites as $fav): ?>
                                    <div class="album-item">
                                        <img src="<?= htmlspecialchars($fav['cover_image'] ?? '../assets/images/default.jpg') ?>"
                                            alt="">
                                        <div class="album-play-overlay">
                                            <span>Play</span>
                                        </div>
                                        <h4><?= htmlspecialchars($fav['title']) ?></h4>
                                        <p><?= htmlspecialchars($fav['artist_name']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($userFavorites)): ?>
                                    <p class="empty-state">No favorites yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- My Playlists -->
                        <div class="content-block">
                            <h3>My Playlists</h3>
                            <div class="albums-grid">
                                <?php foreach ($userPlaylists as $list): ?>
                                    <div class="album-item playlist-card">
                                        <div class="playlist-cover-placeholder">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 18V5l12-2v13" />
                                                <circle cx="6" cy="18" r="3" />
                                                <circle cx="18" cy="16" r="3" />
                                            </svg>
                                        </div>
                                        <h4><?= htmlspecialchars($list['name']) ?></h4>
                                        <p><?= date('M d, Y', strtotime($list['created_at'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($userPlaylists)): ?>
                                    <p class="empty-state">No playlists created.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <div id="playlists-section" class="profile-content" style="display:none;">
                        <h3>All Playlists</h3>
                        <div class="albums-grid">
                            <!-- Duplicate grid specifically for playlists tab if needed, or AJAX load -->
                            <?php foreach ($userPlaylists as $list): ?>
                                <div class="album-item playlist-card">
                                    <div class="playlist-cover-placeholder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M9 18V5l12-2v13" />
                                            <circle cx="6" cy="18" r="3" />
                                            <circle cx="18" cy="16" r="3" />
                                        </svg>
                                    </div>
                                    <h4><?= htmlspecialchars($list['name']) ?></h4>
                                    <p><?= date('M d, Y', strtotime($list['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Edit Settings Form (Hidden by default) -->
                    <div id="settings-section" class="profile-content" style="display:none;">
                        <?php if ($updateSuccess): ?>
                            <div class="alert success-message">Profile updated successfully!</div>
                        <?php endif; ?>

                        <?php if (!empty($updateErrors['general'])): ?>
                            <div class="alert error-message"><?= htmlspecialchars($updateErrors['general']) ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="profile-grid">
                            <!-- Left Column: Avatar & Stats -->
                            <div class="profile-left">
                                <div class="avatar-upload-container">
                                    <div class="avatar-preview">
                                        <?php if ($user['avatar_url']): ?>
                                            <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar"
                                                id="avatarPreview">
                                        <?php else: ?>
                                            <div class="default-avatar" id="defaultAvatar">
                                                <?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                                            <img src="" alt="Avatar" id="avatarPreview" style="display:none;">
                                        <?php endif; ?>

                                        <label for="avatarInput" class="avatar-overlay">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="17 8 12 3 7 8" />
                                                <line x1="12" x2="12" y1="3" y2="15" />
                                            </svg>
                                            <span>Change Photo</span>
                                        </label>
                                        <input type="file" name="avatar" id="avatarInput"
                                            accept="image/png, image/jpeg, image/gif, image/webp" style="display: none;"
                                            onchange="previewImage(this)">
                                    </div>
                                    <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>

                            <!-- Right Column: Form Fields -->
                            <div class="profile-right">
                                <div class="form-section glass-panel">
                                    <h3>Personal Information</h3>
                                    <div class="form-group">
                                        <label for="full_name">Full Name</label>
                                        <input type="text" id="full_name" name="full_name"
                                            value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                            placeholder="Your full name">
                                    </div>

                                    <div class="form-group">
                                        <label for="bio-edit">Bio</label>
                                        <textarea id="bio-edit" name="bio" rows="5"
                                            placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-save">Save Changes</button>
                                    <a href="logout_view.php" class="btn-logout">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                            <polyline points="16 17 21 12 16 7" />
                                            <line x1="21" x2="9" y1="12" y2="12" />
                                        </svg>
                                        Log Out
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <!-- Right Sidebar (Songs) -->
            <div class="songs">
                <div class="profile">
                    <button class="nav-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg></button>
                    <button class="nav-btn active" onclick="window.location.href='profile_view.php'"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg></button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleEditMode() {
            document.getElementById('overview-section').style.display = 'none';
            document.getElementById('playlists-section').style.display = 'none';
            document.getElementById('settings-section').style.display = 'block';

            // Adjust tabs active state
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            // Could show a hidden settings tab if we want to visualize it
        }

        function switchTab(tabName) {
            document.querySelectorAll('.profile-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            if (tabName === 'overview') {
                document.getElementById('overview-section').style.display = 'block';
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
            } else if (tabName === 'playlists') {
                document.getElementById('playlists-section').style.display = 'block';
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
            } else if (tabName === 'settings') {
                document.getElementById('settings-section').style.display = 'block';
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('avatarPreview');
                    const defaultAvatar = document.getElementById('defaultAvatar');

                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (defaultAvatar) defaultAvatar.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script type="module">
        import { initSurrealBackground } from '../javascript/surreal-bg.js'
        initSurrealBackground('canvas-container')
    </script>
</body>

</html>