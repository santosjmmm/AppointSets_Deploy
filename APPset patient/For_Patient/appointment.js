function openDetailsModal(service, dentist, datetime, id) {
    document.getElementById('modalService').innerText = service;
    document.getElementById('modalDentist').innerText = dentist;
    document.getElementById('modalDateTime').innerText = datetime;
    // Crucial: Set the ID for the hidden form field
    document.getElementById('cancelAptId').value = id; 
    document.getElementById('appointmentModal').style.display = 'flex';
}

function openCancelConfirm() {
    document.getElementById('appointmentModal').style.display = 'none';
    document.getElementById('cancelConfirmModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Close modal if user clicks outside the content box
window.onclick = function(event) {
    if (event.target.className === 'modal-overlay') {
        event.target.style.display = 'none';
    }
}