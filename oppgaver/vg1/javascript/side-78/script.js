const imgKron = document.getElementById("imgKron");
const tilfeldigTall = Math.random();

if (tilfeldigTall < 0.5) {
    imgKron.src = "kron.jpg";
} else {
    imgKron.src = "mynt.jpg";
}