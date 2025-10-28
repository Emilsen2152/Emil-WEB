// Finn elementa
const sections = document.querySelectorAll("main section");
const navLinks = document.querySelectorAll(".navbar-nav .nav-link");
const navbarCollapse = document.querySelector("#navbarNav");
const navbar = document.querySelector(".navbar");
const bsCollapse = new bootstrap.Collapse(navbarCollapse, { toggle: false });

// Funksjon: oppdater CSS-variabel med aktuell navbar-høgd
function updateNavHeightVar() {
    const navHeight = navbar.getBoundingClientRect().height || 80;
    document.documentElement.style.setProperty('--nav-height', `${Math.ceil(navHeight)}px`);
}
window.addEventListener('load', updateNavHeightVar);
window.addEventListener('resize', updateNavHeightVar);

let isAutoScrolling = false;

function updateActiveLink() {
    let scrollPos = window.scrollY + 100; // evt. juster
    sections.forEach(section => {
        const top = section.offsetTop;
        const bottom = top + section.offsetHeight;
        const id = section.getAttribute("id");
        const link = document.querySelector(`.navbar-nav .nav-link[href="#${id}"]`);
        if (scrollPos >= top && scrollPos < bottom) {
            navLinks.forEach(l => l.classList.remove("active"));
            if (link) link.classList.add("active");

            // Oppdater URL utan å laste sida på nytt
            if (isAutoScrolling === false) {
                history.replaceState(null, '', `#${id}`);
            }
        }
    });
}
window.addEventListener("scroll", updateActiveLink);
updateActiveLink();

function smoothScrollToElement(el) {
    isAutoScrolling = true;
    const navHeight = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-height')) || navbar.getBoundingClientRect().height;
    const y = el.getBoundingClientRect().top + window.scrollY - navHeight - 8;
    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
    setTimeout(() => { isAutoScrolling = false; }, 1000);
}

navLinks.forEach(link => {
    link.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (!targetId || !targetId.startsWith('#')) return; // ikkje anna link
        const targetEl = document.querySelector(targetId);
        if (!targetEl) return;

        const isMobile = window.innerWidth < 992;
        const isOpen = navbarCollapse.classList.contains('show');

        if (isMobile && isOpen) {
            e.preventDefault();
            const onHidden = function () {
                smoothScrollToElement(targetEl);
                navbarCollapse.removeEventListener('hidden.bs.collapse', onHidden);
            };

            navbarCollapse.addEventListener('hidden.bs.collapse', onHidden);
            bsCollapse.hide();
        } else {
            e.preventDefault();
            smoothScrollToElement(targetEl);

            history.pushState(null, '', targetId);
        }
    });
});
