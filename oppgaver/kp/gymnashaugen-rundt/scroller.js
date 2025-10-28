// Hent alle section-element
const sections = document.querySelectorAll("main section");
const navLinks = document.querySelectorAll(".navbar-nav .nav-link");

function updateActiveLink() {
    let scrollPos = window.scrollY + 100; // juster 100 etter header-høgda

    sections.forEach(section => {
        const top = section.offsetTop;
        const bottom = top + section.offsetHeight;

        const id = section.getAttribute("id");
        const link = document.querySelector(`.navbar-nav .nav-link[href="#${id}"]`);

        if (scrollPos >= top && scrollPos < bottom) {
            navLinks.forEach(l => l.classList.remove("active"));
            if (link) link.classList.add("active");
        }
    });
}

// Kall funksjonen ved scroll
window.addEventListener("scroll", updateActiveLink);

// Kall ein gong ved lasting
updateActiveLink();
