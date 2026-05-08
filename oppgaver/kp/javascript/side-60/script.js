//Høgde og bredde på eit rektangel
const hogde = 10;
const bredde = 5;

// Arealet til rektangelet
const areal = hogde * bredde;

// Omkretsen til rektangelet
const omkrets = 2 * (hogde + bredde);

// Skriv ut resultata
document.write(`Arealet til rektangelet er: ${areal} <br>`);
document.write(`Omkretsen til rektangelet er: ${omkrets} <br>`);

document.write(`Terningkast: ${Math.floor(Math.random() * 6) + 1} <br>`);

document.write(`Tilfeldig tal mellom 0 og 10: ${Math.floor(Math.random() * 10)} <br>`);

document.write(`Tilfeldig tal mellom 10 og 20: ${Math.floor(Math.random() * 10) + 10} <br>`);

// Spør om radiusen til ei kule
const kuleRadius = Number(prompt("Skriv inn radiusen til kula:"));

// Kalkuler og skriv ut omkrets, volum og areal til kula, med sjekk om gyldig input
if (!isNaN(kuleRadius) && kuleRadius > 0) {
    document.write(`Omkretsen til kula er: ${2 * Math.PI * kuleRadius} <br>`);
    document.write(`Volumet til kula er: ${(4 * Math.PI * Math.pow(kuleRadius, 3))/3} <br>`);
    document.write(`Arealet til kula er: ${4 * Math.PI * Math.pow(kuleRadius, 2)} <br>`);

} else {
    document.write("Vennligst skriv inn eit gyldig positivt tal for radiusen. <br>");
};

const antallPoeng = Number(prompt("Skriv inn poengsummen på prøven"));
const maksPoeng = Number(prompt("Skriv inn maks poengsum på prøven"));

if (!isNaN(antallPoeng) && !isNaN(maksPoeng) && maksPoeng > 0) {
    const karakter = Math.round((antallPoeng / maksPoeng) * 6);

    document.write(`Karakteren din er: ${karakter} <br>`);
} else {
    document.write("Vennligst skriv inn gyldige tal for poengsummen og maks poengsum. <br>");
}

const vekt = Number(prompt("Skriv inn vekta di i kg:"));
const hoyde = Number(prompt("Skriv inn høgda di i meter:"));

if (!isNaN(vekt) && !isNaN(hoyde) && hoyde > 0) {
    const kmi = vekt / (hoyde * hoyde);
    document.write(`KMI-en din er: ${kmi.toFixed(2)} <br>`);
} else {
    document.write("Vennligst skriv inn gyldige tal for vekt og høgde. <br>");
}
    