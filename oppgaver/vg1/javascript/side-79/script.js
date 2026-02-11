const tall = prompt("Skriv inn et tall:");

if (tall > 10) {
    alert("Tallet er større enn 10.");
} else {
    alert("Tallet er ikke større enn 10.");
}

const svar = prompt("1+1")
if (svar == 2) {
    alert("Du svarte riktig!");
} else {
    alert("Du svarte feil!");
}

const dag = prompt("Kva dag er det?")
const dato = prompt("Kva dato er det?")

if (dag == "fredag" && dato == 13) {
    alert("Pass på, det er fredag den 13.!");
}