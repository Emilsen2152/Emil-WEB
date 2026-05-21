const tern1 = Math.floor(Math.random() * 6) + 1;
const tern2 = Math.floor(Math.random() * 6) + 1;
const tern3 = Math.floor(Math.random() * 6) + 1;
document.write("Resultat: " + tern1 + ", " + tern2 + ", og " + tern3 + "<br><br>");
if (tern1 === tern2 && tern2 === tern3) {
    document.write("Du fekk tre like!<br>");
}
const sum = tern1 + tern2 + tern3;
if (sum >= 15) {
    document.write("Summen av terningane er femten eller meir.");
}