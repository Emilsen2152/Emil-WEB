const canvas = document.getElementById("game");
const shotEl = document.getElementById("skutt");
const timeEl = document.getElementById("tid");
const timeRecordEl = document.getElementById("rekord");
const restartBtn = document.getElementById("restartGame");
const ctx = canvas.getContext("2d");
const balloonImg = new Image();
balloonImg.src = "gfx/BennBalloon.png";

const balloonImgEks = new Image();
balloonImgEks.src = "gfx/BennBalloonEks.png";

let shot = 0;
let gameOver = true;
const maxShot = 10;

let timeUsed = 0;

let timeRecord = localStorage.getItem("timeRecord") ? parseFloat(localStorage.getItem("timeRecord")) : 0;
timeRecordEl.textContent = timeRecord.toFixed(2);

let gameHasHappened = false;

const balloons = [];

const treffLyder = [
    "afx/hit1.m4a",
    "afx/hit2.m4a",
    "afx/hit3.m4a",
    "afx/hit4.m4a"
];

const navn = [
    "Benn",
    "Leif",
    "Helge",
    "Øyvind",
    "Peter",
    "Maksym",
    "Filip",
    "Jakob",
    "Kyrylo",
    "Arsenii",
    "Jan",
    "Emma",
    "Emil"
]

function makeBalloon() {
    const size = canvas.width * (0.04 + Math.random() * 0.03);

    return {
        size,
        // Start maks ~20% av bredda utanfor, ikkje 100%
        x: Math.random() -80,

        y: Math.random() * (canvas.height - size),

        // Raskare (ca 2–3x det du hadde)
        speed: canvas.width * (0.003 + Math.random() * 0.003),

        isDead: false,

        navn: navn[Math.floor(Math.random() * navn.length)],

        health: Math.ceil(Math.random() * 3), // 1-3 treff for å sprekke ballongen

        changeY: 0
    };
}

function restartGame() {
    shot = 0;
    gameOver = false;
    gameHasHappened = true;
    balloons.length = 0;
    timeUsed = 0;

    shotEl.textContent = `${shot} / ${maxShot}`;
    timeEl.textContent = timeUsed.toFixed(2);
    timeRecordEl.textContent = timeRecord.toFixed(2);

    // Lag 10 ballongar
    for (let i = 0; i < 10; i++) {
        {
            balloons.push(makeBalloon());
        }
    }

    console.log(balloons);
}

ctx.textAlign = "center";
ctx.textBaseline = "middle"; // optional but helps vertical consistency

function drawGame() {
    // Tøm canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    for (const balloon of balloons) {
        ctx.drawImage(
            balloon.isDead ? balloonImgEks : balloonImg,
            balloon.x,
            balloon.y,
            balloon.size,
            balloon.size
        );

        const text = balloon.navn + " " + balloon.health;
        const centerX = balloon.x + balloon.size / 2;

        ctx.font = "10px Arial";

        // If text would go above canvas, draw below
        if (balloon.y - 20 < 0) {
            ctx.fillText(text, centerX, balloon.y + balloon.size + 10);
        } else {
            ctx.fillText(text, centerX, balloon.y - 10);
        }
    }
}

function updateBalloons() {
    for (const balloon of balloons) {
        balloon.x += balloon.speed;

        if (balloon.x > canvas.width && !balloon.isDead) {
            // Ballongen har flyttet seg ut av skjermen, lag en ny
            Object.assign(balloon, makeBalloon());
        }

        if (balloon.changeY > 0) {
            let change = Math.min(balloon.changeY, 5);
            balloon.y += change;
            balloon.changeY -= change;
        }

        if (balloon.isDead) {
            // Ballongen faller ned og forsvinner
            balloon.y += 5;
            if (balloon.y > canvas.height) {
                // Delete balloon
                balloons.splice(balloons.indexOf(balloon), 1);
            }
        }
    }
}

function runGame() {
    // Oppdater ballonger
    updateBalloons();

    // Tegn ballonger
    if (balloons.length > 0) {
        drawGame();
    } else if (gameHasHappened) {
        // Alle ballonger er borte, spilleren har vunnet vis gameOver skjerm med rekord og tid
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = "30px Arial";
        ctx.fillText("Gratulerer, du vant!", canvas.width / 2, canvas.height / 2 - 30);
        ctx.fillText(`Tid: ${timeUsed.toFixed(2)}s`, canvas.width / 2, canvas.height / 2);
        ctx.fillText(`Rekord: ${timeRecord.toFixed(2)}s`, canvas.width / 2, canvas.height / 2 + 30);
    }

    if (shot >= maxShot && !gameOver) {
        gameOver = true;
        if (timeUsed < timeRecord || timeRecord === 0) {
            timeRecord = timeUsed;
            timeRecordEl.textContent = timeRecord.toFixed(2);
            localStorage.setItem("timeRecord", timeRecord.toFixed(2));
        }
    }

    if (!gameOver) timeUsed += 1 / 60;

    if (gameOver) {
        restartBtn.textContent = "Spill igjen";
    } else {
        restartBtn.textContent = "Restart";
    }

    timeEl.textContent = timeUsed.toFixed(2);

    requestAnimationFrame(runGame);
}

// Hent ut mus koordinater
function getMousePos(e) {
    const rect = canvas.getBoundingClientRect();
    const scalex = canvas.width / rect.width;
    const scaley = canvas.height / rect.height;

    return {
        x: (e.clientX - rect.left) * scalex,
        y: (e.clientY - rect.top) * scaley
    }
}

function shootingBalloons(mousePos) {
    const skytelyd = new Audio("afx/shot.mp3");
    skytelyd.play();
    for (const balloon of balloons) {
        if (balloon.isDead) continue;
        const hitX = mousePos.x >= balloon.x && mousePos.x <= balloon.x + balloon.size;
        const hitY = mousePos.y >= balloon.y && mousePos.y <= balloon.y + balloon.size;

        if (hitX && hitY) {
            // Ballongen er truffet
            const hitSound = new Audio(treffLyder[Math.floor(Math.random() * treffLyder.length)]);
            hitSound.play();

            balloon.health--;
            if (balloon.health > 0) {
                balloon.changeY += 20;

                if (balloon.y + balloon.size > canvas.height) {
                    balloon.isDead = true;
                }
            } else {
                balloon.isDead = true;
            }

            if (!balloon.isDead) continue;

            shot++;
            shotEl.textContent = `${shot} / ${maxShot}`;

            balloon.isDead = true;
        }
    }
}

restartBtn.addEventListener("click", restartGame);

canvas.addEventListener("click", (e) => {
    const mouse = getMousePos(e);
    console.log(mouse);

    // TODO: Sjekk om musen er innenfor en ballong
    shootingBalloons(mouse);
});

timeRecordEl.addEventListener("click", () => {
    if (confirm("Vil du slette rekorden?")) {
        timeRecord = 0;
        timeRecordEl.textContent = timeRecord.toFixed(2);
        localStorage.removeItem("timeRecord");
    }
});

// Tegn velkomstskjermen
ctx.font = "30px Arial";
ctx.fillText("Klikk for å starte!", canvas.width / 2, canvas.height / 2);

runGame();
