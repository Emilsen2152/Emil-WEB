const canvas = document.getElementById("game");
const shotEl = document.getElementById("skutt");
const timeEl = document.getElementById("tid");
const timeRecordEl = document.getElementById("rekord");
const restartBtn = document.getElementById("restartGame");
const ctx = canvas.getContext("2d");

function resizeCanvas() {
    canvas.width = canvas.clientWidth;
    canvas.height = canvas.clientHeight;
}
window.addEventListener("resize", resizeCanvas);
resizeCanvas();

const balloonImg = new Image();
balloonImg.src = "gfx/BennBalloon.png";

const balloonImgEks = new Image();
balloonImgEks.src = "gfx/BennBalloonEks.png";

let state = "menu";

let shot = 0;
const maxShot = 10;

let timeUsed = 0;
let timeRecord = localStorage.getItem("timeRecord")
    ? parseFloat(localStorage.getItem("timeRecord"))
    : 0;

timeRecordEl.textContent = timeRecord.toFixed(2);

const balloons = [];

const treffLyder = [
    "afx/hit1.m4a",
    "afx/hit2.m4a",
    "afx/hit3.m4a",
    "afx/hit4.m4a"
];

const navn = [
    "Benn", "Leif", "Helge", "Øyvind", "Peter",
    "Maksym", "Filip", "Jakob", "Kyrylo", "Arsenii",
    "Jan", "Emma", "Emil"
];

function makeBalloon() {
    const baseScale = Math.min(canvas.width, canvas.height);
    const size = baseScale * (0.12 + Math.random() * 0.05);

    return {
        size,
        x: -size - Math.random() * 100,
        y: Math.random() * (canvas.height - size),
        speed: canvas.width * (0.005 + Math.random() * 0.005),
        isDead: false,
        navn: navn[Math.floor(Math.random() * navn.length)],
        health: Math.ceil(Math.random() * 3)
    };
}

function startGame() {
    shot = 0;
    timeUsed = 0;
    balloons.length = 0;

    for (let i = 0; i < 10; i++) {
        balloons.push(makeBalloon());
    }

    state = "playing";

    shotEl.textContent = `0 / ${maxShot}`;
}

restartBtn.addEventListener("click", startGame);

function drawGame() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.textAlign = "center";
    ctx.fillStyle = "black";
    ctx.font = "bold 14px Arial";

    for (const balloon of balloons) {
        ctx.drawImage(
            balloon.isDead ? balloonImgEks : balloonImg,
            balloon.x,
            balloon.y,
            balloon.size,
            balloon.size
        );

        const text = `${balloon.navn} (${balloon.health})`;
        const centerX = balloon.x + balloon.size / 2;

        const textY =
            (balloon.y - 10 < 20)
                ? balloon.y + balloon.size + 20
                : balloon.y - 10;

        ctx.fillText(text, centerX, textY);
    }
}

function updateBalloons() {
    balloons.forEach((balloon, index) => {
        balloon.x += balloon.speed;

        if (balloon.x > canvas.width && !balloon.isDead) {
            Object.assign(balloon, makeBalloon());
        }

        if (balloon.isDead) {
            balloon.y += 7;
            if (balloon.y > canvas.height) balloons.splice(index, 1);
        }
    });
}

function runGame() {
    updateBalloons();

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (state === "menu") {
        ctx.textAlign = "center";


        ctx.font = "28px Arial";
        ctx.fillText("The Cunt Hunt", canvas.width / 2, canvas.height / 2 - 40);

        ctx.font = "18px Arial";
        ctx.fillText("Trykk for å starte", canvas.width / 2, canvas.height / 2);

        restartBtn.textContent = "Start";
    }

    else if (state === "playing") {
        drawGame();

        timeUsed += 1 / 60;
        timeEl.textContent = timeUsed.toFixed(2);
        timeRecordEl.textContent = timeRecord.toFixed(2);

        restartBtn.textContent = "Restart";

        if (shot >= maxShot) {
            state = "ended";

            if (timeUsed < timeRecord || timeRecord === 0) {
                timeRecord = timeUsed;
                localStorage.setItem("timeRecord", timeRecord.toFixed(2));
            }
        }
    }

    else if (state === "ended") {
        ctx.textAlign = "center";


        ctx.font = "28px Arial";
        ctx.fillText("Gratulerer!", canvas.width / 2, canvas.height / 2 - 40);

        ctx.font = "18px Arial";
        ctx.fillText(`Tid: ${timeUsed.toFixed(2)}s`, canvas.width / 2, canvas.height / 2);

        ctx.fillText(`Rekord: ${timeRecord.toFixed(2)}s`, canvas.width / 2, canvas.height / 2 + 30);

        restartBtn.textContent = "Spill igjen";
    }

    requestAnimationFrame(runGame);
}

function handleInput(e) {
    if (e.type === "touchstart") e.preventDefault();

    const rect = canvas.getBoundingClientRect();

    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;

    const mousePos = {
        x: (clientX - rect.left) * (canvas.width / rect.width),
        y: (clientY - rect.top) * (canvas.height / rect.height)
    };

    shootingBalloons(mousePos);
}

function shootingBalloons(mousePos) {
    if (state === "menu") {
        startGame();
        return;
    }

    if (state !== "playing") return;

    new Audio("afx/shot.mp3").play();

    for (const balloon of balloons) {
        if (balloon.isDead) continue;

        if (
            mousePos.x >= balloon.x &&
            mousePos.x <= balloon.x + balloon.size &&
            mousePos.y >= balloon.y &&
            mousePos.y <= balloon.y + balloon.size
        ) {
            new Audio(
                treffLyder[Math.floor(Math.random() * treffLyder.length)]
            ).play();

            balloon.health--;

            if (balloon.health <= 0) {
                balloon.isDead = true;
                shot++;
                shotEl.textContent = `${shot} / ${maxShot}`;
            } else {
                balloon.y += 20;
            }
        }
    }
}

// Events
canvas.addEventListener("mousedown", handleInput);
canvas.addEventListener("touchstart", handleInput, { passive: false });

state = "menu";

document.getElementById("rekord-btn").addEventListener("click", () => {
    if (confirm("Vil du slette rekorden?")) {
        timeRecord = 0;
        localStorage.removeItem("timeRecord");
        timeRecordEl.textContent = timeRecord.toFixed(2);
    }
});

runGame();