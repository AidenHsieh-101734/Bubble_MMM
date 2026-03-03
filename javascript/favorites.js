/**
 * Favorites Manager
 * Handles favoriting interactions for songs
 */

class FavoritesManager {
    constructor() {
        this.favoriteSongs = new Set();
        this.init();
    }

    async init() {
        await this.loadUserFavorites();
        this.bindEvents();
        this.updateUI();
    }

    getApiPath(action, params = '') {
        // Determine base path based on current location
        const isViewDir = window.location.pathname.includes('/view/');
        const base = isViewDir ? '../logic/api/favorites.php' : 'logic/api/favorites.php';
        return `${base}?action=${action}${params}`;
    }

    /**
     * Fetch user's favorite songs from API
     */
    async loadUserFavorites() {
        try {
            const url = this.getApiPath('list', '&type=song');
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && Array.isArray(data.data)) {
                this.favoriteSongs.clear();
                data.data.forEach(item => {
                    // The item format might depend on the API response structure
                    // Assuming item has favoritable_id for song type
                    this.favoriteSongs.add(parseInt(item.favoritable_id));
                });
            }
        } catch (error) {
            console.error('Error loading favorites:', error);
        }
    }

    /**
     * Bind click events using delegation
     */
    bindEvents() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.favorite-btn');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            const songId = parseInt(btn.dataset.songId);
            if (!songId) return;

            await this.toggleFavorite(songId, btn);
        });

        // Listen for song list updates (if any dynamic loading happens)
        // You might want to expose a method to re-run updateUI()
    }

    /**
     * Toggle favorite status
     */
    async toggleFavorite(songId, btn) {
        // Optimistic UI update
        const isCurrentlyFavorite = this.favoriteSongs.has(songId);

        if (isCurrentlyFavorite) {
            this.favoriteSongs.delete(songId);
        } else {
            this.favoriteSongs.add(songId);
        }

        this.updateButtonState(btn, !isCurrentlyFavorite);
        this.updateAllButtons(songId, !isCurrentlyFavorite);

        try {
            const url = this.getApiPath('toggle');
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'song',
                    item_id: songId
                })
            });

            const data = await response.json();

            if (!data.success) {
                // Revert on failure
                if (isCurrentlyFavorite) {
                    this.favoriteSongs.add(songId);
                } else {
                    this.favoriteSongs.delete(songId);
                }
                this.updateButtonState(btn, isCurrentlyFavorite);
                this.updateAllButtons(songId, isCurrentlyFavorite);
                console.error('Failed to toggle favorite:', data.error);
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
            // Revert
            if (isCurrentlyFavorite) {
                this.favoriteSongs.add(songId);
            } else {
                this.favoriteSongs.delete(songId);
            }
            this.updateButtonState(btn, isCurrentlyFavorite);
            this.updateAllButtons(songId, isCurrentlyFavorite);
        }
    }

    /**
     * Update all buttons for a specific song ID across the page
     */
    updateAllButtons(songId, isFavorite) {
        const buttons = document.querySelectorAll(`.favorite-btn[data-song-id="${songId}"]`);
        buttons.forEach(btn => this.updateButtonState(btn, isFavorite));
    }

    /**
     * Update visual state of a button
     */
    updateButtonState(btn, isFavorite) {
        const icon = btn.querySelector('.heart-icon');
        if (isFavorite) {
            btn.classList.add('active');
            if (icon) icon.setAttribute('fill', 'currentColor');
        } else {
            btn.classList.remove('active');
            if (icon) icon.setAttribute('fill', 'none');
        }

        // Add minimal animation pop
        btn.style.transform = 'scale(1.2)';
        setTimeout(() => btn.style.transform = 'scale(1)', 200);
    }

    /**
     * Update all buttons on the page based on loaded favorites
     */
    updateUI() {
        const buttons = document.querySelectorAll('.favorite-btn');
        buttons.forEach(btn => {
            const songId = parseInt(btn.dataset.songId);
            if (this.favoriteSongs.has(songId)) {
                this.updateButtonState(btn, true);
            } else {
                this.updateButtonState(btn, false);
            }
        });
    }
}

// Initialize
window.favoritesManager = new FavoritesManager();
