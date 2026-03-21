// Reference elements
var sidebar = document.getElementById("sidebar");
var overlay = document.getElementById("overlay");
var closeBtn = document.getElementById("closeSidebar");

// When the sidebar is shown, display the overlay and close button
sidebar.addEventListener("shown.bs.collapse", function () {
    overlay.classList.add("show");
    closeBtn.style.display = "flex";
});

// When the sidebar is hidden, remove the overlay and close button
sidebar.addEventListener("hidden.bs.collapse", function () {
    overlay.classList.remove("show");
    closeBtn.style.display = "none";
});

// Clicking on the overlay hides the sidebar
overlay.addEventListener("click", function () {
    var bsCollapse = bootstrap.Collapse.getInstance(sidebar);
    if (bsCollapse) {
        bsCollapse.hide();
    }
});

// Clicking the close button also hides the sidebar
closeBtn.addEventListener("click", function () {
    var bsCollapse = bootstrap.Collapse.getInstance(sidebar);
    if (bsCollapse) {
        bsCollapse.hide();
    }
});
