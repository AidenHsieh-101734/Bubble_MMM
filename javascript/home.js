(function () {
    const allNewsImgs = document.querySelectorAll(".news-content");
    let currentIndex = 0;

    if (allNewsImgs.length > 0) {
        allNewsImgs[0].classList.add('active');
    }

    // Expose functions to global scope if needed for inline onclick handlers
    window.switchSlide = function (index) {
        if (allNewsImgs.length === 0) return;

        allNewsImgs.forEach(img => img.classList.remove('active'));
        allNewsImgs[index].classList.add('active');

        currentIndex = index;
    }

    window.omhoog = function () {
        if (allNewsImgs.length === 0) return;
        let nextIndex = (currentIndex + 1) % allNewsImgs.length;
        switchSlide(nextIndex);
    }

    window.omlaag = function () {
        if (allNewsImgs.length === 0) return;
        let nextIndex = (currentIndex - 1 + allNewsImgs.length) % allNewsImgs.length;
        switchSlide(nextIndex);
    }
})();

// These functions seem to be used by onclicks, so they need to be global, but strictly defining them.
// Actually, `toggleRepeat`, `login`, `goToProfile` don't depend on the `const` above.
// But `omhoog` and `omlaag` DO depend on `allNewsImgs` and `currentIndex`.
// The fix above makes them global window functions closing over the private variables.

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
    if (window.location.pathname.includes('/view/')) {
        window.location.href = "login_view.php";
    } else {
        window.location.href = "view/login_view.php";
    }
}

function goToProfile() {
    if (window.location.pathname.includes('/view/')) {
        window.location.href = "profile_view.php";
    } else {
        window.location.href = "view/profile_view.php";
    }
}

