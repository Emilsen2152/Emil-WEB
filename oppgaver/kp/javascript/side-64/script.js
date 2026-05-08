const antallFrukt = "3";
const antallGronnsaker = "2";
const antallVarer = antallFrukt + antallGronnsaker;

document.write("Antall varer: " + antallVarer);
document.write("<br>");

const pris = prompt("Skriv inn prisen på varen i kroner uten moms:");

const prisMedMoms = Number(pris) * 1.25;

document.write(`Prisen på varen med moms er: ${prisMedMoms.toFixed(2)} kr`);

document.write("<br>");

const datamengde = prompt("Skriv inn datamengden i GB:");
const antSamtaler = prompt("Skriv inn antall samtaler:");
const ringetid = prompt("Skriv inn ringetiden i minutter:");
const antSMS = prompt("Skriv inn antall SMS:");
const antMMS = prompt("Skriv inn antall MMS:");

const kostnadDatamengde = Number(datamengde) * 10;
const kostnadSamtaler = Number(antSamtaler) * 0.89 + Number(ringetid) * 0.39;
const kostnadSMS = Number(antSMS) * 0.69;
const kostnadMMS = Number(antMMS) * 1.99;

const totalKostnad = kostnadDatamengde + kostnadSamtaler + kostnadSMS + kostnadMMS;

document.write(`Total kostnad for mobilbruk denne måneden er: ${totalKostnad.toFixed(2)} kr`);

document.write("<br>");

const array = ["Eple", "Banan", "Appelsin", "Druer", "Kiwi"];
document.write(`Siste frukta i arrayet er: ${array[array.length - 1]}`);
document.write("<br>");

const frukt1 = {
    navn: "Eple",
    pris: 15
}

const frukt2 = {
    navn: "Banan",
    pris: 10
}

const frukt3 = {
    navn: "Appelsin",
    pris: 12
}

frukt2.pris = 11;

document.write(`Prisen på ${frukt2.navn} er no: ${frukt2.pris} kr`);
