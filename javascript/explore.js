const animationdiv = document.getElementById("animationdiv");
const LeesContainer = document.getElementById("LeesContainer");
const LuisterSection = document.getElementById("LuisterSection");
const BekijkSection = document.getElementById("BekijkSection");

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

