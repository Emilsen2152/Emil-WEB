const canvas = document.getElementById("game");
const scoreEl = document.getElementById("poeng");
const restartBtn = document.getElementById("restartGame");

let score = 0;
let gameOver = false;
const maxScore = 10;

const balloons = [];

function makeBalloon() {
    const size = canvas.width * (0.04 + Math.random() * 0.03); // scales with width

    return {
        size,
        x: -size - Math.random() * canvas.width,   // start off-screen left
        y: Math.random() * (canvas.height - size), // inside canvas vertically
        speed: canvas.width * (0.001 + Math.random() * 0.002) // speed scales too
    };
}

function restartGame() {
    score = 0;
    gameOver = false;
    balloons.length = 0;

    // Lag 5 ballongar
    for (let i = 0; i < 5; i++) {
        {
            balloons.push(makeBalloon());
        }
    }

    console.log(balloons);
}

function drawGame() {
    for (const balloon of balloons) {
        console.log(`Tegner ballong på (${balloon.x}, ${balloon.y}) med størrelse ${balloon.size}`);
    }
}

function updateBalloons() {
    for (const balloon of balloons) {
        balloon.x += balloon.speed;
    }
}

function runGame() {
    // Oppdater ballonger
    updateBalloons();

    // Tegn ballonger
    drawGame();

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

restartBtn.addEventListener("click", restartGame);

canvas.addEventListener("click", (e) => {
    const mouse = getMousePos(e);
    console.log(mouse);

    // TODO: Sjekk om musen er innenfor en ballong
})

restartGame();
runGame();
