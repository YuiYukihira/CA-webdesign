// Set the width of the side navigation to 250px and add a margin on the body
function openNav() {
    document.getElementById("sidebar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
}

// Set the width of the side navigation to 0 and remove the margin on the content 
function closeNav() {
    document.getElementById("sidebar").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
} 
