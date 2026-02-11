const currentNumberElement = document.getElementById("current-number");

const hellButton = document.getElementById("start-hell");

let intervalId;

hellButton.addEventListener("click", () => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
        document.body.style.backgroundColor = "";
        currentNumberElement.textContent = "";
        hellButton.textContent = "NB! Fare for epilepsianfall";
        return;
    }

    const intervall = prompt("Skriv inn intervall i millisekunder (f.eks. 100):", "100");
    if (isNaN(intervall) || intervall <= 0) {
        alert("Vennligst skriv inn et gyldig positivt tall for intervallet.");
        return;
    }

    hellButton.textContent = "Stopp helvete";
    intervalId = setInterval(() => {
        const tilfeldigTall = Math.floor(Math.random() * 10);
        currentNumberElement.textContent = "Tilfeldig tall: " + tilfeldigTall;
        if (tilfeldigTall < 4) {
            document.body.style.backgroundColor = "red";
        } else if (tilfeldigTall === 5) {
            document.body.style.backgroundColor = "blue";
        } else {
            document.body.style.backgroundColor = "green";
        }
    }, parseInt(intervall));
});