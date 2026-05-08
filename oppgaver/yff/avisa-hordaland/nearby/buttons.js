// JAVASCRIPT FOR INTERAKSJON

// Like-funksjonalitet
document.querySelectorAll('.bi-heart').forEach(btn => {
    btn.addEventListener('click', function () {
        // Skift mellom tomt og fylt hjarta
        this.classList.toggle('bi-heart');
        this.classList.toggle('bi-heart-fill');

        // Visuelt feedback (raud farge ved like)
        if (this.classList.contains('bi-heart-fill')) {
            this.style.color = '#ff4d4d';
            this.style.transform = 'scale(1.2)';
            setTimeout(() => this.style.transform = 'scale(1)', 200);
        } else {
            this.style.color = 'white';
        }

        // ta opp og ned like tallet (for demonstrasjon, ingen backend)
        const likeCount = this.nextElementSibling;
        if (this.classList.contains('bi-heart-fill')) {
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
        } else {
            likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
        }

        console.log("Lika reel ID: " + this.getAttribute('data-reel-id'));
    });
});

// Delings-funksjonalitet
document.querySelectorAll('.bi-share').forEach(btn => {
    btn.addEventListener('click', function () {
        const reelId = this.getAttribute('data-reel-id');

        if (navigator.share) {
            navigator.share({
                title: 'Avisa Hordaland - Reels',
                text: 'Sjekk ut denne saka!',
                url: window.location.href
            }).catch(console.error);
        } else {
            alert("Delingslenka er kopiert til utklippstavla (Simulert for Reel " + reelId + ")");
        }

        // Ta opp delingstallet (for demonstrasjon, ingen backend)
        this.style.transform = 'scale(1.2)';
        setTimeout(() => this.style.transform = 'scale(1)', 200);

        // Simuler en økning i delingstallet (for demonstrasjon, ingen backend)
        const shareCount = this.nextElementSibling;
        shareCount.textContent = parseInt(shareCount.textContent) + 1;
    });
});

// Konfigurasjon for "kikkerten" (Observer)
// Terskelen 0.6 betyr at 60% av videoen må vere synleg før den startar
const observerOptions = {
    root: document.querySelector('main'),
    threshold: 0.6
};

let userDesiredMuted = true; // Global status for lyd

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        const video = entry.target.querySelector('video');
        if (!video) return;

        if (entry.isIntersecting) {
            // Videoen er i fokus
            video.muted = userDesiredMuted;
            video.play().catch(error => console.log("Auto-play blokkert:", error));

            // Oppdater volum-ikonet for denne reelen (viss du har det)
            updateMuteIcons();
        } else {
            // Videoen er rulla bort
            video.pause();
            video.currentTime = 0; // Startar på nytt neste gong
        }
    });
}, observerOptions);

// Start overvaking av alle reel-containere
document.querySelectorAll('.reel-container').forEach(section => {
    observer.observe(section);
});

// Funksjon for å skru lyd av/på manuelt (globale innstillingar)
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('mute-control')) {
        const currentVideo = e.target.closest('.reel-container').querySelector('video');

        userDesiredMuted = !userDesiredMuted; // Snu statusen

        // Oppdater alle videoar på sida til den nye statusen
        document.querySelectorAll('video').forEach(v => {
            v.muted = userDesiredMuted;
        });

        updateMuteIcons();
    }
});

function updateMuteIcons() {
    document.querySelectorAll('.mute-control').forEach(icon => {
        if (userDesiredMuted) {
            icon.classList.remove('bi-volume-up-fill');
            icon.classList.add('bi-volume-mute');
        } else {
            icon.classList.remove('bi-volume-mute');
            icon.classList.add('bi-volume-up-fill');
        }
    });
}