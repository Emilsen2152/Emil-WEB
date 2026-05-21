const radius = prompt("Skriv inn radius på sirkelen:");
let areal = Math.PI * Math.pow(radius, 2);
let omkrets = 2 * Math.PI * radius;
areal = Math.round(areal);
omkrets = Math.round(omkrets);
document.write("Dersom sirkelen har radius " + radius + ", så er arealet " + areal + " og omkretsen " + omkrets + ".");