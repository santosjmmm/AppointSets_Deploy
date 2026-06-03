function triggerUpload(imgDiv) {
        imgDiv.querySelector('.file-input').click();
    }

    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                input.parentElement.style.backgroundImage = `url('${e.target.result}')`;
            }
            reader.readAsDataURL(file);
        }
    }

    function addService() {
        const list = document.getElementById('servicesList');
        const newItem = document.createElement('div');
        newItem.className = 'service-item';
        newItem.innerHTML = `
            <div class="service-image" onclick="triggerUpload(this)">
                <input type="file" class="file-input" accept="image/*" onchange="previewImage(this)">
            </div>
            <div class="service-details">
                <div class="service-info">
                    <h3 class="editable-title">New Service</h3>
                    <p class="editable-desc">Add description here...</p>
                    <div class="service-price editable-price">₱0.00</div>
                </div>
                <div class="btn-group">
                    <button onclick="toggleEdit(this)" class="book-btn">Edit</button>
                    <button onclick="deleteService(this)" class="delete-btn">Delete</button>
                </div>
            </div>
        `;
        list.appendChild(newItem);
    }
   function enableEdit(id) {
    const service = document.getElementById("service-" + id);

    service.querySelector(".view-mode").style.display = "none";
    service.querySelector(".edit-mode").style.display = "block";

    service.querySelector(".btn-group").style.display = "none";
    service.querySelector(".edit-actions").style.display = "flex";
}

function cancelEdit(id) {
    const service = document.getElementById("service-" + id);

    service.querySelector(".view-mode").style.display = "block";
    service.querySelector(".edit-mode").style.display = "none";

    service.querySelector(".btn-group").style.display = "flex";
    service.querySelector(".edit-actions").style.display = "none";
}