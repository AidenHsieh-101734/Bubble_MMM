/**
 * Bubble Queue Manager
 * Manages the playback queue and playlist functionality
 */

class QueueManager {
    constructor(player) {
        this.player = player;
        this.queue = [];
        this.currentIndex = -1;
        this.originalQueue = []; // For shuffle restore
        this.isShuffled = false;

        this.bindEvents();
        this.loadSavedQueue();
    }

    /**
     * Bind to player events
     */
    bindEvents() {
        document.addEventListener('bubblePlayer:ended', () => {
            this.handleSongEnd();
        });
    }

    /**
     * Set the queue with an array of songs
     */
    setQueue(songs, startIndex = 0) {
        if (!Array.isArray(songs) || songs.length === 0) {
            console.warn('Invalid or empty songs array');
            return false;
        }

        this.queue = songs.map(song => this.normalizeSong(song));
        this.originalQueue = [...this.queue];
        this.currentIndex = startIndex;
        this.isShuffled = false;

        this.saveQueue();
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });

        return true;
    }

    /**
     * Normalize song data to consistent format
     */
    normalizeSong(song) {
        return {
            id: song.id || song.song_id,
            title: song.title || song.song_title || 'Unknown Title',
            artist: song.artist || song.artist_name || 'Unknown Artist',
            audioUrl: song.audioUrl || song.audio_file_path || song.audio_url,
            coverUrl: song.coverUrl || song.cover_image || song.cover_url || 'assets/images/default-cover.png',
            duration: song.duration || 0
        };
    }

    /**
     * Add a song to the end of the queue
     */
    addToQueue(song) {
        const normalizedSong = this.normalizeSong(song);
        this.queue.push(normalizedSong);
        this.originalQueue.push(normalizedSong);
        this.saveQueue();
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
    }

    /**
     * Add a song to play next (after current song)
     */
    addNextInQueue(song) {
        const normalizedSong = this.normalizeSong(song);
        const insertIndex = this.currentIndex + 1;
        this.queue.splice(insertIndex, 0, normalizedSong);
        this.originalQueue.splice(insertIndex, 0, normalizedSong);
        this.saveQueue();
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
    }

    /**
     * Remove a song from the queue by index
     */
    removeFromQueue(index) {
        if (index < 0 || index >= this.queue.length) return false;

        this.queue.splice(index, 1);

        // Adjust current index if needed
        if (index < this.currentIndex) {
            this.currentIndex--;
        } else if (index === this.currentIndex && this.currentIndex >= this.queue.length) {
            this.currentIndex = this.queue.length - 1;
        }

        this.saveQueue();
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
        return true;
    }

    /**
     * Clear the entire queue
     */
    clearQueue() {
        this.queue = [];
        this.originalQueue = [];
        this.currentIndex = -1;
        this.isShuffled = false;
        this.saveQueue();
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
    }

    /**
     * Play the next song in the queue
     */
    playNext() {
        if (!this.hasNext()) {
            // If repeat all is on, loop back to start
            if (this.player.repeatMode === 'all' && this.queue.length > 0) {
                return this.playAtIndex(0);
            }
            return false;
        }

        return this.playAtIndex(this.currentIndex + 1);
    }

    /**
     * Play the previous song in the queue
     */
    playPrevious() {
        // If more than 3 seconds into the song, restart it instead
        if (this.player.getCurrentTime() > 3) {
            this.player.seekTo(0);
            return true;
        }

        if (!this.hasPrevious()) {
            // If repeat all is on, loop to end
            if (this.player.repeatMode === 'all' && this.queue.length > 0) {
                return this.playAtIndex(this.queue.length - 1);
            }
            return false;
        }

        return this.playAtIndex(this.currentIndex - 1);
    }

    /**
     * Play song at specific index
     */
    playAtIndex(index) {
        if (index < 0 || index >= this.queue.length) {
            return false;
        }

        this.currentIndex = index;
        const song = this.queue[index];

        if (this.player.loadSong(song)) {
            this.player.play();
            this.saveQueue();
            this.dispatchEvent('indexchange', { index: this.currentIndex, song: song });
            return true;
        }

        return false;
    }

    /**
     * Handle song end - play next or stop
     */
    handleSongEnd() {
        // Repeat one is handled by the player itself
        if (this.player.repeatMode === 'one') {
            return;
        }

        this.playNext();
    }

    /**
     * Toggle shuffle mode
     */
    toggleShuffle() {
        if (this.isShuffled) {
            this.unshuffle();
        } else {
            this.shuffle();
        }
        return this.isShuffled;
    }

    /**
     * Shuffle the queue (keeping current song in place)
     */
    shuffle() {
        if (this.queue.length < 2) return;

        const currentSong = this.queue[this.currentIndex];

        // Remove current song, shuffle rest, put current at start
        const otherSongs = this.queue.filter((_, i) => i !== this.currentIndex);

        // Fisher-Yates shuffle
        for (let i = otherSongs.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [otherSongs[i], otherSongs[j]] = [otherSongs[j], otherSongs[i]];
        }

        this.queue = [currentSong, ...otherSongs];
        this.currentIndex = 0;
        this.isShuffled = true;

        this.saveQueue();
        this.dispatchEvent('shufflechange', { shuffled: true });
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
    }

    /**
     * Restore original queue order
     */
    unshuffle() {
        if (!this.isShuffled || this.originalQueue.length === 0) return;

        const currentSong = this.queue[this.currentIndex];
        this.queue = [...this.originalQueue];

        // Find current song in original queue
        this.currentIndex = this.queue.findIndex(s => s.id === currentSong.id);
        if (this.currentIndex === -1) this.currentIndex = 0;

        this.isShuffled = false;

        this.saveQueue();
        this.dispatchEvent('shufflechange', { shuffled: false });
        this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
    }

    /**
     * Get the current queue
     */
    getQueue() {
        return this.queue;
    }

    /**
     * Get the current song
     */
    getCurrentSong() {
        if (this.currentIndex < 0 || this.currentIndex >= this.queue.length) {
            return null;
        }
        return this.queue[this.currentIndex];
    }

    /**
     * Get the next song (without playing)
     */
    getNextSong() {
        const nextIndex = this.currentIndex + 1;
        if (nextIndex >= this.queue.length) {
            if (this.player.repeatMode === 'all' && this.queue.length > 0) {
                return this.queue[0];
            }
            return null;
        }
        return this.queue[nextIndex];
    }

    /**
     * Get upcoming songs (for "next up" display)
     */
    getUpcomingSongs(limit = 5) {
        const upcoming = [];
        for (let i = this.currentIndex + 1; i < this.queue.length && upcoming.length < limit; i++) {
            upcoming.push(this.queue[i]);
        }
        return upcoming;
    }

    /**
     * Check if there's a next song
     */
    hasNext() {
        return this.currentIndex < this.queue.length - 1;
    }

    /**
     * Check if there's a previous song
     */
    hasPrevious() {
        return this.currentIndex > 0;
    }

    /**
     * Get current index
     */
    getCurrentIndex() {
        return this.currentIndex;
    }

    /**
     * Get queue length
     */
    getQueueLength() {
        return this.queue.length;
    }

    /**
     * Save queue to localStorage
     */
    saveQueue() {
        const state = {
            queue: this.queue,
            originalQueue: this.originalQueue,
            currentIndex: this.currentIndex,
            isShuffled: this.isShuffled
        };
        localStorage.setItem('bubbleQueueState', JSON.stringify(state));
    }

    /**
     * Load saved queue from localStorage
     */
    loadSavedQueue() {
        try {
            const saved = localStorage.getItem('bubbleQueueState');
            if (saved) {
                const state = JSON.parse(saved);
                if (state.queue && state.queue.length > 0) {
                    this.queue = state.queue;
                    this.originalQueue = state.originalQueue || [...state.queue];
                    this.currentIndex = state.currentIndex || 0;
                    this.isShuffled = state.isShuffled || false;

                    // Load current song into player (without playing)
                    const currentSong = this.getCurrentSong();
                    if (currentSong) {
                        this.player.loadSong(currentSong);
                    }

                    this.dispatchEvent('queuechange', { queue: this.queue, currentIndex: this.currentIndex });
                }
            }
        } catch (e) {
            console.log('Could not load saved queue');
        }
    }

    /**
     * Dispatch custom event
     */
    dispatchEvent(eventName, data) {
        const event = new CustomEvent(`bubbleQueue:${eventName}`, {
            detail: data
        });
        document.dispatchEvent(event);
    }
}

// Create global queue manager instance
window.queueManager = new QueueManager(window.bubblePlayer);
