// ================= CALENDAR =================
const today = new Date();

let currentYear = today.getFullYear();
let currentMonth = today.getMonth(); // 0 = January, correct

const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
];

const calendarBody = document.getElementById("calendarBody");
const monthYear = document.getElementById("monthYear");
const dateInput = document.getElementById("dateInput");
const timeInput = document.getElementById("timeInput");
const errorMsg = document.getElementById("errorMsg");

// Render Calendar
function renderCalendar() {

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    monthYear.innerText = `${monthNames[currentMonth]} ${currentYear}`;
    calendarBody.innerHTML = "";

    let date = 1;

    for (let i = 0; i < 6; i++) {
        let row = document.createElement("tr");

        for (let j = 0; j < 7; j++) {
            let cell = document.createElement("td");

            if (i === 0 && j < firstDay) {
                cell.innerHTML = "";
            } else if (date > daysInMonth) {
                break;
            } else {
                cell.innerText = date;
                cell.dataset.day = date;

                cell.onclick = () => {
                    document.querySelectorAll(".calendar td")
                        .forEach(td => td.classList.remove("selected"));

                    cell.classList.add("selected");

                    let day = cell.innerText; // ✅ correct value from clicked cell

                    let selectedDate = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;

                    dateInput.value = selectedDate;

                    console.log("Selected date:", selectedDate); // debug

                    hideError();
                };

                date++;
            }

            row.appendChild(cell);
        }

        calendarBody.appendChild(row);
    }
}

// Month Navigation
function prevMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar();
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar();
}

// ================= TIME + AM/PM =================
const ampmButtons = document.querySelectorAll('.ampm-btn');
const timeButtons = document.querySelectorAll('.time-btn');

let selectedPeriod = "AM";

// AM/PM Toggle
ampmButtons.forEach(btn => {
    btn.addEventListener('click', () => {

        ampmButtons.forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        selectedPeriod = btn.dataset.period;

        timeButtons.forEach(time => {
            if (time.dataset.period === selectedPeriod) {
                time.disabled = false;
                time.style.opacity = "1";
            } else {
                time.disabled = true;
                time.classList.remove('selected');
                time.style.opacity = "0.4";
            }
        });

        timeInput.value = "";
    });
});

// Time Selection
timeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.disabled) return;

        timeButtons.forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        timeInput.value = btn.textContent + " " + selectedPeriod;

        hideError();
    });
});

// ================= FORM VALIDATION =================
document.getElementById("step2Form").addEventListener("submit", function(e){

    if(!dateInput.value || !timeInput.value){
        e.preventDefault();
        errorMsg.style.display = "block";
        errorMsg.scrollIntoView({ behavior: "smooth", block: "center" });
    }
});

// Hide error helper
function hideError(){
    errorMsg.style.display = "none";
}

// ================= INIT =================
renderCalendar();

// Trigger default AM state on load
document.querySelector('.ampm-btn.selected').click();