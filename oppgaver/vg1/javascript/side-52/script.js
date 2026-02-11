const var1 = "Hei eg heiter Emil!";
document.write(var1);
document.write("<br>");

const var21 = "Hei eg heiter Emil!";
const var22 = "Eg bur på Voss.";

document.write("<div id='changing-text'>" + var21 + "</div>");

const changingTextDiv = document.getElementById('changing-text');

setInterval(() => {
    if (changingTextDiv.innerHTML === var21) {
        changingTextDiv.innerHTML = var22;
    } else {
        changingTextDiv.innerHTML = var21;
    }
}, 2000);

const fornavn = "Emil Velken";
const etternavn = "Soldal";

document.write(fornavn + " " + etternavn);

document.write("<br>");

const navnInput = prompt("Skriv inn namnet ditt:");
document.write("Velkommen " + navnInput);

document.write("<br>");

document.write(`Velkommen til klassefesten, ${navnInput}! Det blir bra ${navnInput}.
    Kos deg masse, ${navnInput}! Håper du får det kjekt, ${navnInput}! Håper å sjå deg der, ${navnInput}!
`);

document.write("<br>");

const adjektiv1 = prompt("Skriv inn eit adjektiv:");
const adjektiv2 = prompt("Skriv inn eit adjektiv:");
const adjektiv3 = prompt("Skriv inn eit adjektiv:");
const adjektiv4 = prompt("Skriv inn eit adjektiv:");
const adjektiv5 = prompt("Skriv inn eit adjektiv:");
const adjektiv6 = prompt("Skriv inn eit adjektiv:");

document.write(`Det var ein ${adjektiv1} gong ein ${adjektiv2} mann som budde i eit ${adjektiv3} hus.
Han hadde ein ${adjektiv4} hund og ein ${adjektiv5} katt.
Katten var veldig ${adjektiv6}.`);