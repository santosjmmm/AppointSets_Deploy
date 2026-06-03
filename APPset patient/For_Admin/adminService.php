<?php
$conn = new mysqli("localhost", "root", "", "db_appsets");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =====================
// HELPER: IMAGE UPLOAD
// =====================
function uploadImage($file){
    if(isset($file) && $file['error'] === 0){
        $targetDir = "uploads/";

        if(!is_dir($targetDir)){
            mkdir($targetDir, 0777, true);
        }

        $imageName = time() . "_" . basename($file["name"]);
        $targetFile = $targetDir . $imageName;

        if(move_uploaded_file($file["tmp_name"], $targetFile)){
            return $imageName;
        }
    }
    return "";
}
// =====================
// ADD NEW SERVICE
// =====================
if(isset($_POST['add'])){
    $name     = trim($_POST['service_name']);
    $desc     = htmlspecialchars($_POST['description']);
    $price    = floatval($_POST['price']);
    $duration = htmlspecialchars($_POST['duration']);

    // 🔍 CHECK IF SERVICE EXISTS
    $check = $conn->prepare("SELECT * FROM tb_service WHERE service_name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $resultCheck = $check->get_result();

    if($resultCheck->num_rows > 0){
        $error = "Service already exist";
    } else {

        $imageName = uploadImage($_FILES['service_image']);
        $status = "Available";

        $stmt = $conn->prepare("INSERT INTO tb_service (service_name, description, price, duration, service_image, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $name, $desc, $price, $duration, $imageName, $status);

        if($stmt->execute()){
            header("Location: adminService.php");
            exit();
        }
    }
}
// =====================
// UPDATE SERVICE
// =====================
if(isset($_POST['update'])){
    $id       = intval($_POST['service_id']);
    $name     = htmlspecialchars($_POST['service_name']);
    $desc     = htmlspecialchars($_POST['description']);
    $price    = floatval($_POST['price']);
    $duration = htmlspecialchars($_POST['duration']);

    $currentImage = $_POST['current_image'];
    $newImage = uploadImage($_FILES['service_image']);

    $imageName = !empty($newImage) ? $newImage : $currentImage;

    if(!empty($newImage) && !empty($currentImage) && file_exists("uploads/".$currentImage)){
        unlink("uploads/".$currentImage);
    }

    $stmt = $conn->prepare("UPDATE tb_service SET service_name=?, description=?, price=?, duration=?, service_image=? WHERE service_id=?");
    $stmt->bind_param("sssssi", $name, $desc, $price, $duration, $imageName, $id);

    if($stmt->execute()){
        header("Location: adminService.php");
        exit();
    }
}

// =====================
// TOGGLE STATUS
// =====================
if(isset($_POST['toggle_status'])){
    $id = intval($_POST['service_id']);
    $currentStatus = trim(strtolower($_POST['current_status'])); // lowercase

    $newStatus = ($currentStatus === 'available') ? 'Unavailable' : 'Available';

    $stmt = $conn->prepare("UPDATE tb_service SET status=? WHERE service_id=?");
    $stmt->bind_param("si", $newStatus, $id);
    $stmt->execute();

    header("Location: adminService.php");
    exit();
}

// =====================
// FETCH DATA
// =====================
$result = $conn->query("SELECT * FROM tb_service");
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C'Smiles Dental Center - Manage Services</title>
<link rel="stylesheet" href="adminService.css">
</head>

<body>

<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">c'smiles<span>Dental Center</span></div>
    </div>
    <nav>
        <a href="../For_Patient/admin_dash.php">Home</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
        <a href="login.html">Log out</a>
    </nav>
</header>

<div class="main-content-area">
<main>
<div class="services-container">
<h1>Our Services</h1>


 <div class="scroll-container">
<div class="services-list">

<?php while($row = $result->fetch_assoc()): ?>
<form method="POST" enctype="multipart/form-data">
<div class="service-item <?= (strtolower($row['status']) === 'unavailable') ? 'unavailable' : ''; ?>" 
     id="service-<?= $row['service_id']; ?>">

    <div class="service-image"
        style="background-image: url('<?= !empty($row['service_image']) ? 'uploads/'.$row['service_image'] : 'default.jpg'; ?>');">
    </div>

    <div class="service-details">

        <div class="view-mode">
            <h3><?= htmlspecialchars($row['service_name']); ?></h3>
            <p><?= htmlspecialchars($row['description']); ?></p>
            <div class="service-price"><?= htmlspecialchars($row['price']); ?></div>
            <p><strong>Duration:</strong> <?= htmlspecialchars($row['duration']); ?></p>
        </div>

        <div class="edit-mode" style="display:none;">
            <input type="text" name="service_name" value="<?= htmlspecialchars($row['service_name']); ?>">
            <input type="text" name="description" value="<?= htmlspecialchars($row['description']); ?>">
            <input type="text" name="price" value="<?= $row['price']; ?>">
            <input type="text" name="duration" value="<?= htmlspecialchars($row['duration']); ?>">
            <input type="file" name="service_image">

        </div>

        <!-- BUTTONS -->
        <div class="btn-group" style="margin-left:auto; gap:10px;">
    
    <!-- Edit Button (BLUE stays) -->
    <button type="button" onclick="enableEdit(<?= $row['service_id']; ?>)" class="book-btn">
        Edit
    </button>

    <!-- Toggle Button (GREEN / RED) -->
    <form method="POST" style="display:inline;">
        <input type="hidden" name="service_id" value="<?= $row['service_id']; ?>">
        <input type="hidden" name="current_status" value="<?= $row['status']; ?>">
        <input type="hidden" name="current_image" value="<?= $row['service_image']; ?>">

        <button type="submit" name="toggle_status"
            class="<?= (strtolower($row['status']) === 'available') ? 'available-btn' : 'unavailable-btn'; ?>">
            <?= (strtolower($row['status']) === 'available') ? 'Available' : 'Unavailable'; ?>
        </button>
    </form>

</div>

    </div>

    <div class="edit-actions" style="display:none;">
       <button type="submit" name="update" class="save-btn">Save</button>
        <button type="button" onclick="cancelEdit(<?= $row['service_id']; ?>)" class="cancel-btn">Cancel</button>
    </div>

</div>
<?php endwhile; ?>

</div>
</div>
</form>
<!-- ADD SERVICE FORM -->
<div class="add-service-wrapper">
    <?php if(!empty($error)): ?>
    <div class="error-box">
        <?= $error ?>
    </div>
<?php endif; ?>
<form method="POST" enctype="multipart/form-data">

    <input type="text" name="service_name" placeholder="Service Name" required>
    <input type="text" name="description" placeholder="Description" required>
    <input type="text" name="price" placeholder="Price" required>
    <input type="text" name="duration" placeholder="Duration (e.g. 30 mins)" required>

    <input type="file" name="service_image" accept="image/*" required>

    <button type="submit" name="add" class="add-btn">+ Add New Service</button>

</form>
</div>
</div>
</div>
</main>
</div>

<script src="adminService.js"></script>

<footer>
  <div class="footer-content">
    &copy; 2026 C'smiles Dental Center. All rights reserved.
  </div>
</footer>

</body>
</html>