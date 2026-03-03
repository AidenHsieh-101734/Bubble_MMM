<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../logic/utils/auth.php';
require_once __DIR__ . '/../logic/admin_dashboard.php';

// Enforce admin access with redirect instead of JSON error
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$stats = getAdminStats();
$recentUsers = getRecentUsers();
$recentContent = getContentOverview();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bubble - Admin Panel</title>
    <link rel="stylesheet" href="../assets/styling/style.css">
    <link rel="stylesheet" href="../assets/styling/home.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/styling/admin.css">
</head>

<body>
    <div id="canvas-container"></div>
    <div class="container" id="container">
        <main>

            <header>
                <div class="logo">
                    <h1>Bubble Admin</h1>
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
                        <a href="profile_view.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-user-icon">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Profile
                        </a>
                    </div>
                </div>
            </header>

            <div class="admin-dashboard">
                <div class="admin-header">
                    <h1>Dashboard</h1>
                    <p>Manage users, content, and system statistics.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="value">
                            <?= number_format($stats['users']) ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Active Sessions</h3>
                        <div class="value">
                            <?= number_format($stats['active_sessions']) ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Songs</h3>
                        <div class="value">
                            <?= number_format($stats['songs']) ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Articles</h3>
                        <div class="value">
                            <?= number_format($stats['articles']) ?>
                        </div>
                    </div>
                </div>

                <div class="admin-section">
                    <h2>Recent Users</h2>
                    <div class="admin-table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($user['username']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user['email']) ?>
                                        </td>
                                        <td>
                                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="status-badge">Active</span>
                                            <?php else: ?>
                                                <span class="status-badge inactive">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="action-btn"><i class="fas fa-edit"></i></button>
                                            <button class="action-btn"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-section">
                    <h2>Content Management</h2>
                    <div class="admin-table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Author/Artist</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentContent as $content): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($content['title']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($content['type']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($content['author'] ?? 'Unknown') ?>
                                        </td>
                                        <td>
                                            <?= date('M d, Y', strtotime($content['date'])) ?>
                                        </td>
                                        <td>
                                            <button class="action-btn"><i class="fas fa-edit"></i></button>
                                            <button class="action-btn"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="songs">
                <!-- Helper for layout matching, can be empty or hidden if we don't want right sidebar here, 
                     but keeping it maintains grid consistency if grid is strict. 
                     Based on profile.php, 'songs' stores the right sidebar. -->
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
                    <button onclick="window.location.href='profile_view.php'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user-icon lucide-user">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script src="../javascript/home.js"></script>
    <script type="module">
        import { initSurrealBackground } from '../javascript/surreal-bg.js'
        initSurrealBackground('canvas-container')
    </script>
</body>

</html>