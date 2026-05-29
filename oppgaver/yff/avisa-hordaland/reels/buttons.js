// Get url parameters
const urlParams = new URLSearchParams(window.location.search);
const reelId = urlParams.get('reelId');

// Scroll til den spesifikke reelen hvis reelId er i URL-en
if (reelId) {
    const targetReel = document.querySelector(`.reel-container[data-index="${reelId}"]`);
    if (targetReel) {
        targetReel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Like-funksjonalitet
document.querySelectorAll('.bi-heart').forEach(btn => {
    btn.addEventListener('click', function () {
        this.classList.toggle('bi-heart');
        this.classList.toggle('bi-heart-fill');

        if (this.classList.contains('bi-heart-fill')) {
            this.style.color = '#ff4d4d';
            this.style.transform = 'scale(1.2)';
            setTimeout(() => this.style.transform = 'scale(1)', 200);
        } else {
            this.style.color = 'white';
        }

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
                url: window.location.origin + window.location.pathname + '?reelId=' + reelId
            }).catch(console.error);
        } else {
            alert("Delingslenka er kopiert til utklippstavla (Simulert for Reel " + reelId + ")");
        }

        this.style.transform = 'scale(1.2)';
        setTimeout(() => this.style.transform = 'scale(1)', 200);

        const shareCount = this.nextElementSibling;
        shareCount.textContent = parseInt(shareCount.textContent) + 1;
    });
});

// --- VALIDERT VIDEO-LOGIKK FOR REELS ---

const observerOptions = {
    root: null, // Bruk null (viewport) for å fange opp mobilskjermen helt nøyaktig
    threshold: 0.25 // Enkel og lav terskel. Har du sett 25% av videoen, skal den spille.
};

let userDesiredMuted = true;
let activeVideo = null;

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        const video = entry.target.querySelector('video');
        if (!video) return; // Hopper over tekst-reels

        if (entry.isIntersecting && entry.intersectionRatio >= 0.25) {
            // Hvis vi registrerer en ny video på skjermen, pause den gamle først
            if (activeVideo && activeVideo !== video) {
                activeVideo.pause();
            }
            activeVideo = video;
            playCurrentVideo();
        } else {
            // Hvis videoen faller under 25% synlighet, skru den av
            if (activeVideo === video) {
                activeVideo = null;
            }
            video.pause();
        }
    });
}, observerOptions);

function playCurrentVideo() {
    if (!activeVideo) return;
    
    // Sjekk om vi faktisk TRENGER å endre mute-status for å unngå unødvendig API-kall til videospilleren
    if (activeVideo.muted !== userDesiredMuted) {
        activeVideo.muted = userDesiredMuted;
    }
    
    if (activeVideo.paused) {
        activeVideo.play().catch(error => {
            console.log("Nettleseren forhindret avspilling:", error);
        });
    }
}

// Start overvåking av alle beholdere
document.querySelectorAll('.reel-container').forEach(section => {
    observer.observe(section);
    
    const video = section.querySelector('video');
    if (video) {
        // Sikkerhetsnett: Hvis nettleseren/mobilen pauser videoen uventet (f.eks. pga. buffering),
        // men videoen fortsatt er den som er aktiv på skjermen, tvinger vi den i gang igjen.
        video.addEventListener('pause', () => {
            if (video === activeVideo && !video.ended) {
                // Bruk requestAnimationFrame for å la nettleseren fullføre interne oppgaver før vi kjører play()
                requestAnimationFrame(() => {
                    if (video === activeVideo && video.paused) {
                        video.play().catch(() => {});
                    }
                });
            }
        });
    }
});

// Global lydstyring (Mute/Unmute)
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('mute-control')) {
        userDesiredMuted = !userDesiredMuted;

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