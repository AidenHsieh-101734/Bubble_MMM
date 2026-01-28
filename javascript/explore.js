const animationdiv = document.getElementById("animationdiv");
const LeesContainer = document.getElementById("LeesContainer");
const LuisterSection = document.getElementById("LuisterSection");
const BekijkSection = document.getElementById("BekijkSection");
const exploreContent = document.getElementById("exploreContent");
const search = document.querySelector(" .search-container");


function hidealles(){
    LeesContainer.style.display = "none";
    LuisterSection.style.display = "none";
    BekijkSection.style.display = "none";
}

function lezen(){
    hidealles();
    LeesContainer.style.display = "block";
    animationdiv.style.left = "4%";
}
function luisteren(){
    hidealles();
    LuisterSection.style.display = "block";
    animationdiv.style.left = "37%";
}
function bekijken(){
    hidealles();
    BekijkSection.style.display = "block";
    animationdiv.style.left = "71%";
}

function aiden(){
    const aiden = document.getElementById("Aiden");
    exploreContent.style.display = "none";
    search.style.display = "none"
    aiden.style.display = "block"
}