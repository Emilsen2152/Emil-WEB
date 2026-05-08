const svar = prompt("Hvem skrev Et dukkehjem?")
const svarLower = svar.toLowerCase()

if (svarLower === "henrik ibsen" || svarLower === "ibsen") {
    alert("Riktig!");
} else {
    alert("Beklager, feil svar.");
}
document.write("Takk for at du deltok.");