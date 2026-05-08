const svar = prompt("Hvem skrev Et dukkehjem?")
const svarLower = svar.toLowerCase()

if (svarLower === "henrik ibsen") {
    alert("Riktig! Eit poeng til deg.");
} else if (svarLower === "ibsen" || svarLower === "henrik") {
    alert("Du skreiv ikkje heile navnet rett. Du får eit halvt poeng.");
} else {
    alert("Beklager, feil svar.");
}