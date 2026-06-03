
const password = document.getElementById("password");

const ruleLength = document.getElementById("rule-length");
const ruleCase = document.getElementById("rule-case");
const ruleSpecial = document.getElementById("rule-special");

password.addEventListener("input", function(){

    const val = password.value;

    // LENGTH
    if(val.length >= 8 && val.length <= 12){
        ruleLength.classList.add("valid");
        ruleLength.classList.remove("invalid");
        ruleLength.querySelector(".circle").textContent = "✔";
    } else {
        ruleLength.classList.add("invalid");
        ruleLength.classList.remove("valid");
        ruleLength.querySelector(".circle").textContent = "o";
    }

    // CASE
    if(/[a-z]/.test(val) && /[A-Z]/.test(val)){
        ruleCase.classList.add("valid");
        ruleCase.classList.remove("invalid");
        ruleCase.querySelector(".circle").textContent = "✔";
    } else {
        ruleCase.classList.add("invalid");
        ruleCase.classList.remove("valid");
        ruleCase.querySelector(".circle").textContent = "o";
    }

    // SPECIAL CHAR
    if(/[!@#$%^&*(),.?":{}|<>]/.test(val)){
        ruleSpecial.classList.add("valid");
        ruleSpecial.classList.remove("invalid");
        ruleSpecial.querySelector(".circle").textContent = "✔";
    } else {
        ruleSpecial.classList.add("invalid");
        ruleSpecial.classList.remove("valid");
        ruleSpecial.querySelector(".circle").textContent = "o";
    }

});