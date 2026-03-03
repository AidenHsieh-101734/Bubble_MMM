document.getElementById('video-overlay').style.display = 'none';

function openVideo() {
    document.getElementById('video-overlay').style.display = 'flex';
}

function closeVideo() {
    document.getElementById('video-overlay').style.display = 'none';
    const iframe = document.querySelector('#video-overlay iframe');
    const src = iframe.src;
    iframe.src = src;
}

document.getElementById('video-overlay').addEventListener('click', function (e) {
    if (e.target === this) {
        closeVideo();
    }
});
