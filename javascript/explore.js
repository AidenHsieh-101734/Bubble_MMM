// DOM Elements
const animationdiv = document.getElementById("animationdiv");
const LeesContainer = document.getElementById("LeesContainer");
const LuisterSection = document.getElementById("LuisterSection");
const BekijkSection = document.getElementById("BekijkSection");
const search = document.querySelector(".search-container");
const buttons = document.querySelectorAll(".exploreSectionBTN button");

// Navigation Functions
function setActiveButton(index) {
    buttons.forEach((btn, i) => {
        if (i === index) btn.classList.add('active');
        else btn.classList.remove('active');
    });
}

function switchSection(targetSection, pillLeftPos, btnIndex) {
    // Hide all sections with fade out effect
    const sections = [LeesContainer, LuisterSection, BekijkSection];

    sections.forEach(sec => {
        if (!sec) return;
        if (sec !== targetSection) {
            sec.style.opacity = '0';
            sec.classList.remove('active-section');
            setTimeout(() => {
                if (sec.style.opacity === '0') sec.style.display = 'none';
            }, 300);
        }
    });

    // Move Pill
    if (animationdiv) {
        animationdiv.style.left = pillLeftPos;
    }
    setActiveButton(btnIndex);

    // Show Target Section
    if (targetSection) {
        targetSection.style.display = 'block';
        // Trigger reflow
        void targetSection.offsetWidth;
        targetSection.style.opacity = '1';
        targetSection.classList.add('active-section');
    }
}

function lezen() {
    switchSection(LeesContainer, "5px", 0);
}

function luisteren() {
    // Roughly center (33% + approx adjustment for padding)
    switchSection(LuisterSection, "calc(33.33% + 2px)", 1);
}

function bekijken() {
    // Roughly end (66% + approx adjustment)
    switchSection(BekijkSection, "calc(66.66% - 2px)", 2);
}

// Initial State
document.addEventListener('DOMContentLoaded', () => {
    // Ensure initial button state
    setActiveButton(0); // Default to Read
});