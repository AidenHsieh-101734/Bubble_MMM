/**
 * Bubble Player UI
 * Handles all UI updates and user interactions for the music player
 */

class PlayerUI {
    constructor(player, queueManager) {
        this.player = player;
        this.queueManager = queueManager;
        this.elements = {};
        this.isDraggingProgress = false;
        this.isDraggingVolume = false;

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    /**
     * Initialize the UI
     */
    init() {
        this.cacheElements();
        this.bindUIEvents();
        this.bindPlayerEvents();
        this.bindSongItemClicks();
        this.updateUI();
    }

    /**
     * Cache DOM elements for performance
     */
    cacheElements() {
        // Player bar (bottom bar)
        this.elements.playerBar = document.querySelector('.afspelen');
        this.elements.playBtn = document.querySelector('.player-controls .play, .player-controls .pause-btn');
        this.elements.prevBtn = document.querySelector('.player-controls .lucide-skip-back')?.parentElement;
        this.elements.nextBtn = document.querySelector('.player-controls .lucide-skip-forward')?.parentElement;

        // Progress bar
        this.elements.progressContainer = document.querySelector('.progress-bar');
        this.elements.progress = document.querySelector('.progress');
        this.elements.currentTimeEl = document.querySelector('.player-progress span:first-child');
        this.elements.totalTimeEl = document.querySelector('.player-progress span:last-child');

        // Player info (in bottom bar)
        this.elements.playerCover = document.querySelector('.player-left img');
        this.elements.playerTitle = document.querySelector('.player-info h4');
        this.elements.playerArtist = document.querySelector('.player-info p');

        // Volume controls
        this.elements.volumeBtn = document.querySelector('.volume-btn, .lucide-volume-2')?.closest('button') ||
            document.querySelector('.lucide-volume-2')?.parentElement;
        this.elements.volumeSlider = document.querySelector('.volume-slider');

        // Repeat and shuffle buttons
        this.elements.repeatBtn = document.querySelector('.repeat-btn, .lucide-repeat')?.closest('button') ||
            document.querySelector('.lucide-repeat')?.parentElement;
        this.elements.shuffleBtn = document.querySelector('.shuffle-btn, .lucide-shuffle')?.closest('button') ||
            document.querySelector('.lucide-shuffle')?.parentElement;

        // Now playing sidebar
        this.elements.nowPlayingSection = document.querySelector('.nowplaying');
        this.elements.nowPlayingCover = document.querySelector('.nowplaying img');
        this.elements.nowPlayingTitle = document.querySelector('.nowplaying h3');
        this.elements.nowPlayingArtist = document.querySelector('.nowplaying p');

        // Next up section
        this.elements.nextUpContainer = document.querySelector('.nextup');
        this.elements.nextUpList = document.querySelector('.nextup-list, .next-songs');
    }

    /**
     * Bind UI interaction events
     */
    bindUIEvents() {
        // Play/Pause button
        if (this.elements.playBtn) {
            this.elements.playBtn.addEventListener('click', () => {
                this.player.togglePlay();
            });
        }

        // Previous button
        if (this.elements.prevBtn) {
            this.elements.prevBtn.addEventListener('click', () => {
                this.queueManager.playPrevious();
            });
        }

        // Next button
        if (this.elements.nextBtn) {
            this.elements.nextBtn.addEventListener('click', () => {
                this.queueManager.playNext();
            });
        }

        // Progress bar click/drag
        if (this.elements.progressContainer) {
            this.elements.progressContainer.addEventListener('click', (e) => {
                if (!this.isDraggingProgress) {
                    this.handleProgressClick(e);
                }
            });

            this.elements.progressContainer.addEventListener('mousedown', (e) => {
                this.isDraggingProgress = true;
                this.handleProgressClick(e);
            });

            document.addEventListener('mousemove', (e) => {
                if (this.isDraggingProgress) {
                    this.handleProgressDrag(e);
                }
            });

            document.addEventListener('mouseup', () => {
                this.isDraggingProgress = false;
            });
        }

        // Volume button (mute toggle)
        if (this.elements.volumeBtn) {
            this.elements.volumeBtn.addEventListener('click', () => {
                this.player.toggleMute();
            });
        }

        // Volume slider
        if (this.elements.volumeSlider) {
            this.elements.volumeSlider.addEventListener('input', (e) => {
                const volume = parseFloat(e.target.value) / 100;
                this.player.setVolume(volume);
            });
        }

        // Repeat button
        if (this.elements.repeatBtn) {
            this.elements.repeatBtn.addEventListener('click', () => {
                this.player.cycleRepeatMode();
            });
        }

        // Shuffle button
        if (this.elements.shuffleBtn) {
            this.elements.shuffleBtn.addEventListener('click', () => {
                this.queueManager.toggleShuffle();
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Only handle if not typing in an input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            switch (e.code) {
                case 'Space':
                    e.preventDefault();
                    this.player.togglePlay();
                    break;
                case 'ArrowLeft':
                    if (e.shiftKey) {
                        this.queueManager.playPrevious();
                    } else {
                        this.player.skipBackward(10);
                    }
                    break;
                case 'ArrowRight':
                    if (e.shiftKey) {
                        this.queueManager.playNext();
                    } else {
                        this.player.skipForward(10);
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.player.setVolume(this.player.volume + 0.1);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    this.player.setVolume(this.player.volume - 0.1);
                    break;
                case 'KeyM':
                    this.player.toggleMute();
                    break;
            }
        });
    }

    /**
     * Bind player events
     */
    bindPlayerEvents() {
        // Time update
        document.addEventListener('bubblePlayer:timeupdate', (e) => {
            this.updateProgress(e.detail.currentTime, e.detail.duration);
        });

        // Play state
        document.addEventListener('bubblePlayer:play', () => {
            this.updatePlayButton(true);
        });

        document.addEventListener('bubblePlayer:pause', () => {
            this.updatePlayButton(false);
        });

        // Song change
        document.addEventListener('bubblePlayer:songchange', (e) => {
            this.updateSongInfo(e.detail.song);
            this.updateNowPlaying(e.detail.song);
        });

        // Volume change
        document.addEventListener('bubblePlayer:volumechange', (e) => {
            this.updateVolumeUI(e.detail.volume, e.detail.muted);
        });

        // Repeat mode change
        document.addEventListener('bubblePlayer:repeatchange', (e) => {
            this.updateRepeatButton(e.detail.mode);
        });

        // Queue changes
        document.addEventListener('bubbleQueue:queuechange', (e) => {
            this.updateNextUpList(e.detail.queue, e.detail.currentIndex);
        });

        document.addEventListener('bubbleQueue:shufflechange', (e) => {
            this.updateShuffleButton(e.detail.shuffled);
        });

        document.addEventListener('bubbleQueue:indexchange', (e) => {
            this.updateNextUpList(this.queueManager.getQueue(), e.detail.index);
        });
    }

    /**
     * Bind click events to song items
     */
    bindSongItemClicks() {
        // Use event delegation for song items
        document.addEventListener('click', (e) => {
            const songItem = e.target.closest('.song-item, .favorite-song-item, .play-song');
            if (!songItem) return;

            // Don't trigger if clicking a specific button inside the song item
            if (e.target.closest('.song-actions, .favorite-btn, .menu-btn')) return;

            this.handleSongClick(songItem);
        });

        // Handle album/playlist play buttons
        document.addEventListener('click', (e) => {
            const playOverlay = e.target.closest('.album-play-overlay, .playlist-play-overlay');
            if (!playOverlay) return;

            e.stopPropagation();
            const container = playOverlay.closest('[data-album-id], [data-playlist-id]');
            if (container) {
                this.handleContainerPlay(container);
            }
        });
    }

    /**
     * Handle song item click
     */
    handleSongClick(songItem) {
        const songData = {
            id: songItem.dataset.songId,
            title: songItem.dataset.songTitle,
            artist: songItem.dataset.songArtist,
            audioUrl: songItem.dataset.songAudio,
            coverUrl: songItem.dataset.songCover,
            duration: parseInt(songItem.dataset.songDuration) || 0
        };

        if (!songData.audioUrl) {
            console.warn('No audio URL for song');
            return;
        }

        // Get all songs in the current context (same parent container)
        const container = songItem.closest('.songs-list, .favorite-songs-list, .playlist-songs, .album-songs, .song-list-container, .Lees') ||
            songItem.parentElement;
        const allSongItems = container.querySelectorAll('.song-item, .favorite-song-item, .play-song');

        // Build queue from all songs in container
        const songs = [];
        let clickedIndex = 0;

        allSongItems.forEach((item, index) => {
            if (item.dataset.songAudio) {
                songs.push({
                    id: item.dataset.songId,
                    title: item.dataset.songTitle,
                    artist: item.dataset.songArtist,
                    audioUrl: item.dataset.songAudio,
                    coverUrl: item.dataset.songCover,
                    duration: parseInt(item.dataset.songDuration) || 0
                });

                if (item === songItem) {
                    clickedIndex = songs.length - 1;
                }
            }
        });

        // Set queue and play
        if (songs.length > 0) {
            this.queueManager.setQueue(songs, clickedIndex);
            this.queueManager.playAtIndex(clickedIndex);
        }
    }

    /**
     * Handle album/playlist container play
     */
    handleContainerPlay(container) {
        const albumId = container.dataset.albumId;
        const playlistId = container.dataset.playlistId;

        if (albumId) {
            this.loadAndPlayAlbum(albumId);
        } else if (playlistId) {
            this.loadAndPlayPlaylist(playlistId);
        }
    }

    /**
     * Load and play an album
     */
    async loadAndPlayAlbum(albumId) {
        try {
            const response = await fetch(`logic/api/songs.php?action=album&album_id=${albumId}`);
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                this.queueManager.setQueue(data.data, 0);
                this.queueManager.playAtIndex(0);
            }
        } catch (error) {
            console.error('Error loading album:', error);
        }
    }

    /**
     * Load and play a playlist
     */
    async loadAndPlayPlaylist(playlistId) {
        try {
            const response = await fetch(`logic/api/playlists.php?action=get&id=${playlistId}`);
            const data = await response.json();

            if (data.success && data.data.songs && data.data.songs.length > 0) {
                this.queueManager.setQueue(data.data.songs, 0);
                this.queueManager.playAtIndex(0);
            }
        } catch (error) {
            console.error('Error loading playlist:', error);
        }
    }

    /**
     * Handle progress bar click
     */
    handleProgressClick(e) {
        const rect = this.elements.progressContainer.getBoundingClientRect();
        const percent = ((e.clientX - rect.left) / rect.width) * 100;
        this.player.seekToPercent(Math.max(0, Math.min(100, percent)));
    }

    /**
     * Handle progress bar drag
     */
    handleProgressDrag(e) {
        const rect = this.elements.progressContainer.getBoundingClientRect();
        const percent = ((e.clientX - rect.left) / rect.width) * 100;
        this.player.seekToPercent(Math.max(0, Math.min(100, percent)));
    }

    /**
     * Update play/pause button state
     */
    updatePlayButton(isPlaying) {
        if (!this.elements.playBtn) return;

        const playIcon = `<svg class="lucide lucide-play" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="6 3 20 12 6 21 6 3"/></svg>`;
        const pauseIcon = `<svg class="lucide lucide-pause" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="14" y="4" width="4" height="16" rx="1"/><rect x="6" y="4" width="4" height="16" rx="1"/></svg>`;

        this.elements.playBtn.innerHTML = isPlaying ? pauseIcon : playIcon;
        this.elements.playBtn.classList.toggle('playing', isPlaying);
    }

    /**
     * Update progress bar and time display
     */
    updateProgress(currentTime, duration) {
        if (this.isDraggingProgress) return;

        const percent = duration ? (currentTime / duration) * 100 : 0;

        if (this.elements.progress) {
            this.elements.progress.style.width = `${percent}%`;
        }

        if (this.elements.currentTimeEl) {
            this.elements.currentTimeEl.textContent = BubbleAudioPlayer.formatTime(currentTime);
        }

        if (this.elements.totalTimeEl) {
            this.elements.totalTimeEl.textContent = BubbleAudioPlayer.formatTime(duration);
        }
    }

    /**
     * Update song info in player bar
     */
    updateSongInfo(song) {
        if (!song) return;

        if (this.elements.playerCover) {
            this.elements.playerCover.src = song.coverUrl;
            this.elements.playerCover.alt = song.title;
        }

        if (this.elements.playerTitle) {
            this.elements.playerTitle.textContent = song.title;
        }

        if (this.elements.playerArtist) {
            this.elements.playerArtist.textContent = song.artist;
        }

        // Update page title
        document.title = `${song.title} - ${song.artist} | Bubble`;
    }

    /**
     * Update now playing sidebar
     */
    updateNowPlaying(song) {
        if (!song) return;

        if (this.elements.nowPlayingCover) {
            this.elements.nowPlayingCover.src = song.coverUrl;
            this.elements.nowPlayingCover.alt = song.title;
        }

        if (this.elements.nowPlayingTitle) {
            this.elements.nowPlayingTitle.textContent = song.title;
        }

        if (this.elements.nowPlayingArtist) {
            this.elements.nowPlayingArtist.textContent = song.artist;
        }
    }

    /**
     * Update next up list in sidebar
     */
    updateNextUpList(queue, currentIndex) {
        if (!this.elements.nextUpList) return;

        const upcoming = queue.slice(currentIndex + 1, currentIndex + 6);

        if (upcoming.length === 0) {
            this.elements.nextUpList.innerHTML = '<p class="no-upcoming">No upcoming songs</p>';
            return;
        }

        this.elements.nextUpList.innerHTML = upcoming.map((song, i) => `
            <div class="lirbrarySongs song-item" data-song-id="${song.id}" data-song-title="${song.title}" data-song-artist="${song.artist}" data-song-audio="${song.audioUrl}" data-song-cover="${song.coverUrl}" data-song-duration="${song.duration}" data-queue-index="${currentIndex + 1 + i}">
                <div class="songimg">
                    <img src="${song.coverUrl}" alt="${song.title}">
                    <div class="songdetails">
                        <h3>${song.title}</h3>
                        <p>${song.artist}</p>
                    </div>
                </div>
                <div class="song-actions-right">
                    <button class="favorite-btn" data-song-id="${song.id}" title="Favorite">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                        </svg>
                    </button>
                    <div class="songduration">${BubbleAudioPlayer.formatTime(song.duration)}</div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Update volume UI
     */
    updateVolumeUI(volume, muted) {
        if (this.elements.volumeSlider) {
            this.elements.volumeSlider.value = muted ? 0 : volume * 100;
        }

        if (this.elements.volumeBtn) {
            let icon;
            if (muted || volume === 0) {
                icon = `<svg class="lucide lucide-volume-x" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="22" x2="16" y1="9" y2="15"/><line x1="16" x2="22" y1="9" y2="15"/></svg>`;
            } else if (volume < 0.5) {
                icon = `<svg class="lucide lucide-volume-1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>`;
            } else {
                icon = `<svg class="lucide lucide-volume-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>`;
            }
            this.elements.volumeBtn.innerHTML = icon;
        }
    }

    /**
     * Update repeat button state
     */
    updateRepeatButton(mode) {
        if (!this.elements.repeatBtn) return;

        this.elements.repeatBtn.classList.remove('repeat-one', 'repeat-all', 'active');

        if (mode === 'one') {
            this.elements.repeatBtn.classList.add('repeat-one', 'active');
            this.elements.repeatBtn.innerHTML = `<svg class="lucide lucide-repeat-1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/><path d="M11 10h1v4"/></svg>`;
        } else if (mode === 'all') {
            this.elements.repeatBtn.classList.add('repeat-all', 'active');
            this.elements.repeatBtn.innerHTML = `<svg class="lucide lucide-repeat" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/></svg>`;
        } else {
            this.elements.repeatBtn.innerHTML = `<svg class="lucide lucide-repeat" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/></svg>`;
        }
    }

    /**
     * Update shuffle button state
     */
    updateShuffleButton(shuffled) {
        if (!this.elements.shuffleBtn) return;
        this.elements.shuffleBtn.classList.toggle('active', shuffled);
    }

    /**
     * Update entire UI state
     */
    updateUI() {
        const currentSong = this.queueManager.getCurrentSong();
        if (currentSong) {
            this.updateSongInfo(currentSong);
            this.updateNowPlaying(currentSong);
        }

        this.updatePlayButton(this.player.isPlaying);
        this.updateVolumeUI(this.player.volume, this.player.isMuted);
        this.updateRepeatButton(this.player.repeatMode);
        this.updateShuffleButton(this.queueManager.isShuffled);
        this.updateNextUpList(this.queueManager.getQueue(), this.queueManager.getCurrentIndex());
    }
}

// Create global player UI instance
window.playerUI = new PlayerUI(window.bubblePlayer, window.queueManager);
