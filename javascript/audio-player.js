/**
 * Bubble Audio Player
 * Core HTML5 audio player class with playback controls
 */

class BubbleAudioPlayer {
    constructor() {
        this.audio = new Audio();
        this.audio.preload = 'auto';
        this.currentSong = null;
        this.isPlaying = false;
        this.volume = 1.0;
        this.isMuted = false;
        this.previousVolume = 1.0;
        this.repeatMode = 'none'; // 'none', 'all', 'one'

        this.initAudioElement();
        this.loadSavedState();
    }

    /**
     * Initialize audio element and bind events
     */
    initAudioElement() {
        // Time update event - fires frequently during playback
        this.audio.addEventListener('timeupdate', () => {
            this.dispatchEvent('timeupdate', {
                currentTime: this.audio.currentTime,
                duration: this.audio.duration,
                progress: this.getProgress()
            });
        });

        // Loaded metadata - duration is available
        this.audio.addEventListener('loadedmetadata', () => {
            this.dispatchEvent('loadedmetadata', {
                duration: this.audio.duration
            });
        });

        // Can play - ready to start playback
        this.audio.addEventListener('canplay', () => {
            this.dispatchEvent('canplay', {});
        });

        // Play event
        this.audio.addEventListener('play', () => {
            this.isPlaying = true;
            this.dispatchEvent('play', { song: this.currentSong });
        });

        // Pause event
        this.audio.addEventListener('pause', () => {
            this.isPlaying = false;
            this.dispatchEvent('pause', { song: this.currentSong });
        });

        // Ended event - track finished
        this.audio.addEventListener('ended', () => {
            this.isPlaying = false;
            this.dispatchEvent('ended', { song: this.currentSong });
            this.handleSongEnd();
        });

        // Error handling
        this.audio.addEventListener('error', (e) => {
            const error = this.audio.error;
            let errorMessage = 'Unknown error';

            if (error) {
                switch (error.code) {
                    case error.MEDIA_ERR_ABORTED:
                        errorMessage = 'Playback aborted';
                        break;
                    case error.MEDIA_ERR_NETWORK:
                        errorMessage = 'Network error';
                        break;
                    case error.MEDIA_ERR_DECODE:
                        errorMessage = 'Decode error';
                        break;
                    case error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                        errorMessage = 'Format not supported';
                        break;
                }
            }

            console.error('Audio error:', errorMessage);
            this.dispatchEvent('error', { error: errorMessage, song: this.currentSong });
        });

        // Volume change
        this.audio.addEventListener('volumechange', () => {
            this.dispatchEvent('volumechange', {
                volume: this.audio.volume,
                muted: this.audio.muted
            });
        });
    }

    /**
     * Load a song into the player
     */
    loadSong(songData) {
        if (!songData || !songData.audioUrl) {
            console.error('Invalid song data');
            return false;
        }

        this.currentSong = {
            id: songData.id,
            title: songData.title || 'Unknown Title',
            artist: songData.artist || 'Unknown Artist',
            audioUrl: songData.audioUrl,
            coverUrl: songData.coverUrl || 'assets/images/default-cover.png',
            duration: songData.duration || 0
        };

        this.audio.src = this.currentSong.audioUrl;
        this.saveState();

        this.dispatchEvent('songchange', { song: this.currentSong });

        return true;
    }

    /**
     * Play the current song
     */
    async play() {
        if (!this.audio.src) {
            console.warn('No song loaded');
            return false;
        }

        try {
            await this.audio.play();
            this.recordPlay();
            return true;
        } catch (error) {
            console.error('Play error:', error);
            this.dispatchEvent('error', { error: error.message });
            return false;
        }
    }

    /**
     * Pause playback
     */
    pause() {
        this.audio.pause();
    }

    /**
     * Toggle play/pause
     */
    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }

    /**
     * Stop playback and reset position
     */
    stop() {
        this.pause();
        this.audio.currentTime = 0;
    }

    /**
     * Seek to a specific time (in seconds)
     */
    seekTo(time) {
        if (isNaN(time) || time < 0) return;
        this.audio.currentTime = Math.min(time, this.audio.duration || 0);
    }

    /**
     * Seek to a percentage of the track
     */
    seekToPercent(percent) {
        if (isNaN(percent) || !this.audio.duration) return;
        const time = (percent / 100) * this.audio.duration;
        this.seekTo(time);
    }

    /**
     * Skip forward by seconds
     */
    skipForward(seconds = 10) {
        this.seekTo(this.audio.currentTime + seconds);
    }

    /**
     * Skip backward by seconds
     */
    skipBackward(seconds = 10) {
        this.seekTo(this.audio.currentTime - seconds);
    }

    /**
     * Set volume (0-1)
     */
    setVolume(level) {
        level = Math.max(0, Math.min(1, level));
        this.volume = level;
        this.audio.volume = level;

        if (level > 0 && this.isMuted) {
            this.isMuted = false;
            this.audio.muted = false;
        }

        this.saveState();
    }

    /**
     * Mute audio
     */
    mute() {
        if (!this.isMuted) {
            this.previousVolume = this.volume;
            this.isMuted = true;
            this.audio.muted = true;
        }
    }

    /**
     * Unmute audio
     */
    unmute() {
        if (this.isMuted) {
            this.isMuted = false;
            this.audio.muted = false;
            this.audio.volume = this.previousVolume;
        }
    }

    /**
     * Toggle mute
     */
    toggleMute() {
        if (this.isMuted) {
            this.unmute();
        } else {
            this.mute();
        }
    }

    /**
     * Get current playback time
     */
    getCurrentTime() {
        return this.audio.currentTime;
    }

    /**
     * Get total duration
     */
    getDuration() {
        return this.audio.duration || 0;
    }

    /**
     * Get progress as percentage (0-100)
     */
    getProgress() {
        if (!this.audio.duration) return 0;
        return (this.audio.currentTime / this.audio.duration) * 100;
    }

    /**
     * Set repeat mode
     */
    setRepeatMode(mode) {
        const validModes = ['none', 'all', 'one'];
        if (validModes.includes(mode)) {
            this.repeatMode = mode;
            this.saveState();
            this.dispatchEvent('repeatchange', { mode: this.repeatMode });
        }
    }

    /**
     * Cycle through repeat modes
     */
    cycleRepeatMode() {
        const modes = ['none', 'all', 'one'];
        const currentIndex = modes.indexOf(this.repeatMode);
        const nextIndex = (currentIndex + 1) % modes.length;
        this.setRepeatMode(modes[nextIndex]);
        return this.repeatMode;
    }

    /**
     * Handle song end based on repeat mode
     */
    handleSongEnd() {
        if (this.repeatMode === 'one') {
            this.audio.currentTime = 0;
            this.play();
        }
        // 'all' and 'none' modes are handled by QueueManager
    }

    /**
     * Record play to server
     */
    recordPlay() {
        if (!this.currentSong || !this.currentSong.id) return;

        fetch('logic/api/play_history.php?action=record', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ song_id: this.currentSong.id })
        }).catch(err => console.log('Could not record play:', err));
    }

    /**
     * Save player state to localStorage
     */
    saveState() {
        const state = {
            volume: this.volume,
            repeatMode: this.repeatMode,
            currentSong: this.currentSong
        };
        localStorage.setItem('bubblePlayerState', JSON.stringify(state));
    }

    /**
     * Load saved state from localStorage
     */
    loadSavedState() {
        try {
            const saved = localStorage.getItem('bubblePlayerState');
            if (saved) {
                const state = JSON.parse(saved);
                if (state.volume !== undefined) {
                    this.setVolume(state.volume);
                }
                if (state.repeatMode) {
                    this.repeatMode = state.repeatMode;
                }
            }
        } catch (e) {
            console.log('Could not load saved player state');
        }
    }

    /**
     * Dispatch custom event
     */
    dispatchEvent(eventName, data) {
        const event = new CustomEvent(`bubblePlayer:${eventName}`, {
            detail: data
        });
        document.dispatchEvent(event);
    }

    /**
     * Format time in seconds to MM:SS
     */
    static formatTime(seconds) {
        if (isNaN(seconds) || seconds < 0) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// Create global player instance
window.bubblePlayer = new BubbleAudioPlayer();
