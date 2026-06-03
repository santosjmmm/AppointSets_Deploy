document.querySelector("form").addEventListener("submit", function(e){

    const service = document.querySelector('input[name="service"]:checked');
    const dentist = document.querySelector('input[name="dentist"]:checked');

    const errorTop = document.getElementById("errorTop");

    // Reset
    errorTop.style.display = "none";

    if(!service || !dentist){
        e.preventDefault();

        let message = "Please choose service and dentist";

        errorTop.innerText = message;


        errorTop.style.display = "block";
    }
});