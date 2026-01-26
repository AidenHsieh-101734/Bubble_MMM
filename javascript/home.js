const allNewsImgs = document.querySelectorAll(".news-content");
let currentIndex = 0;

if (allNewsImgs.length > 0) {
    allNewsImgs[0].classList.add('active');
}

// -- HELPER FUNCTIONS -- \\
function switchSlide(index) {
    if (allNewsImgs.length === 0) return;

    allNewsImgs.forEach(img => img.classList.remove('active'));
    allNewsImgs[index].classList.add('active');

    currentIndex = index;
}

function omhoog() {
    if (allNewsImgs.length === 0) return;
    let nextIndex = (currentIndex + 1) % allNewsImgs.length;
    switchSlide(nextIndex);
}

function omlaag() {
    if (allNewsImgs.length === 0) return;
    let nextIndex = (currentIndex - 1 + allNewsImgs.length) % allNewsImgs.length;
    switchSlide(nextIndex);
}

function toggleRepeat() {
    const repeatNormal = document.getElementById('repeat-normal');
    const repeatOne = document.getElementById('repeat-one');

    if (repeatNormal.style.display === 'none') {
        repeatNormal.style.display = 'block';
        repeatOne.style.display = 'none';
    } else {
        repeatNormal.style.display = 'none';
        repeatOne.style.display = 'block';
    }
}

function login() {
    window.location.href = "../view/login.html";
}

