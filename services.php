<?php
$db_server = "192.168.0.100";
$db_user = "furrpaws_furrpawacc";
$db_pass = "t7em2zEqBDaWDTctYAjb";
$db_name = "furrpaws_furrpawacc";

// Create connection
$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize notification message
$notification = "";
$notificationType = "";

// Handle form submission for adding new services
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Insert the new service into the service_list table
    $insert_sql = "INSERT INTO service_list (name, description, status) VALUES ('$service_name', '$description', '$status')";
    if ($conn->query($insert_sql) === TRUE) {
        $service_id = $conn->insert_id;
        
        // Insert sizes and prices
        if (isset($_POST['sizes']) && isset($_POST['prices'])) {
            $sizes = $_POST['sizes'];
            $prices = $_POST['prices'];
            for ($i = 0; $i < count($sizes); $i++) {
                $size_sql = "INSERT INTO service_sizes (service_id, size, price) VALUES ($service_id, '{$sizes[$i]}', {$prices[$i]})";
                $conn->query($size_sql);
            }
        }
        
        $notification = "Service added successfully!";
        $notificationType = "success";
    } else {
        $notification = "Error: " . $conn->error;
        $notificationType = "error";
    }
}

// Handle form submission for updating a service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    $service_id = $_POST['service_id'];
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Update the service in the service_list table
    $update_sql = "UPDATE service_list SET name='$service_name', description='$description', status='$status' WHERE id=$service_id";
    if ($conn->query($update_sql) === TRUE) {
        // Clear existing sizes
        $clear_sizes_sql = "DELETE FROM service_sizes WHERE service_id = $service_id";
        $conn->query($clear_sizes_sql);

        // Insert new sizes and prices
        if (isset($_POST['edit_sizes']) && isset($_POST['edit_prices'])) {
            $sizes = $_POST['edit_sizes'];
            $prices = $_POST['edit_prices'];
            for ($i = 0; $i < count($sizes); $i++) {
                $size_sql = "INSERT INTO service_sizes (service_id, size, price) VALUES ($service_id, '{$sizes[$i]}', {$prices[$i]})";
                $conn->query($size_sql);
            }
        }
        
        $notification = "Service updated successfully!";
        $notificationType = "success";
    } else {
        $notification = "Error: " . $conn->error;
        $notificationType = "error";
    }
}

// Handle service deletion
if (isset($_GET['delete_id'])) {
    $service_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM service_list WHERE id=$service_id";
    if ($conn->query($delete_sql) === TRUE) {
        $notification = "Service deleted successfully!";
        $notificationType = "success";
    } else {
        $notification = "Error deleting record: " . $conn->error;
        $notificationType = "error";
    }
}

// Fetch services from the database
$sql = "SELECT * FROM service_list";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service List</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #2e3b4e; color: white; }
        .sidebar { width: 250px; background-color: #4f321b; height: 100vh; padding: 20px; position: fixed; }
        .sidebar h1 { font-size: 24px; margin-bottom: 40px; color: #fff; }
        .sidebar ul { list-style-type: none; }
        .sidebar ul li { margin: 20px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 18px; }
        .sidebar ul li a:hover { text-decoration: underline; }
        .main-content { margin-left: 270px; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .header h2 { color: white; }
        .admin-button { background-color: #f0f0f0; color: #000; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .admin-button:hover { background-color: #ddd; }
        .table-container { background-color: #3c4858; padding: 20px; border-radius: 10px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; color: white; border-bottom: 1px solid #ddd; }
        table th { background-color: #1f2733; }
        table tr:nth-child(even) { background-color: #3f4a5a; }
        .status-badge { padding: 5px 10px; border-radius: 5px; color: white; }
        .status-active { background-color: green; }
        .status-inactive { background-color: red; }
        .action-button { background-color: #777; color: white; padding: 5px 10px; border-radius: 5px; cursor: pointer; border: none; }
        .action-button:hover { background-color: #666; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); }
        .modal-content { background-color: #3c4858; margin: 10% auto; padding: 20px; border-radius: 10px; width: 50%; color: white; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close:hover, .close:focus { color: white; cursor: pointer; }
        .footer { position: absolute; bottom: 20px; left: 270px; color: white; }
        .input-field { margin-bottom: 15px; }
        .input-field input, .input-field select { width: 100%; padding: 10px; border-radius: 5px; border: none; }
        .submit-button { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .submit-button:hover { background-color: #45a049; }
        .toast { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); padding: 15px 20px; border-radius: 5px; color: white; display: none; z-index: 1000; animation: fadeout 3s forwards; }
        .toast.success { background-color: green; }
        .toast.error { background-color: red; }
        @keyframes fadeout { 0% { opacity: 1; } 99% { opacity: 1; } 100% { opacity: 0; display: none; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h1>FURRPAWS</h1>
    <ul>
        <li><a href="admin.php">Dashboard</a></li>
        <li><a href="appointment.php">Appointment List</a></li>
        <li><a href="services.php">Service List</a></li>
        <li><a href="users.php">User List</a></li>
        <li><a href="#">Settings</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h2>List of Services</h2>
        <button class="admin-button" onclick="openModal()">+ Add New Service</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['description'] ?></td>
                        <td>
                            <span class="status-badge <?= $row['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-button" onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['name'] ?>', '<?= $row['description'] ?>', '<?= $row['status'] ?>')">Edit</button>
                            <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"><button class="action-button">Delete</button></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add New Service Modal -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Service</h2>
            <form method="POST">
                <div class="input-field">
                    <label for="service_name">Service Name</label>
                    <input type="text" id="service_name" name="service_name" required>
                </div>
                <div class="input-field">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" required>
                </div>
                <div class="input-field">
                    <label for="status">Status</label>
                    <select name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div id="sizes-container">
                    <div class="size-price-input">
                        <input type="text" name="sizes[]" placeholder="Size" required>
                        <input type="number" name="prices[]" placeholder="Price" required>
                    </div>
                </div>
                <button type="button" onclick="addSizeInput()">Add Another Size</button>
                <br><br>
                <button type="submit" name="add_service" class="submit-button">Add Service</button>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editServiceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Service</h2>
            <form method="POST">
                <input type="hidden" id="edit_service_id" name="service_id">
                <div class="input-field">
                    <label for="edit_service_name">Service Name</label>
                    <input type="text" id="edit_service_name" name="service_name" required>
                </div>
                <div class="input-field">
                    <label for="edit_description">Description</label>
                    <input type="text" id="edit_description" name="description" required>
                </div>
                <div class="input-field">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div id="edit_sizes-container"></div>
                <button type="button" onclick="addEditSizeInput()">Add Another Size</button>
                <br><br>
                <button type="submit" name="update_service" class="submit-button">Update Service</button>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 FURRPAWS. All Rights Reserved.</p>
    </div>
</div>

<!-- Toast Message -->
<div id="toast" class="toast <?= $notificationType ?>">
    <?= $notification ?>
</div>

<script>
    function openModal() {
        document.getElementById("addServiceModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("addServiceModal").style.display = "none";
    }

    function openEditModal(id, name, description, status) {
        document.getElementById("edit_service_id").value = id;
        document.getElementById("edit_service_name").value = name;
        document.getElementById("edit_description").value = description;
        document.getElementById("edit_status").value = status;
        
        fetchSizes(id);
        document.getElementById("editServiceModal").style.display = "block";
    }

    function fetchSizes(serviceId) {
        const sizes = ['Small', 'Medium'];  // Example sizes
        const prices = [10, 20];  // Example prices
        
        const container = document.getElementById("edit_sizes-container");
        container.innerHTML = '';  
        for (let i = 0; i < sizes.length; i++) {
            container.innerHTML += `<div class="size-price-input">
                <input type="text" name="edit_sizes[]" value="${sizes[i]}" required>
                <input type="number" name="edit_prices[]" value="${prices[i]}" required>
            </div>`;
        }
    }

    function closeEditModal() {
        document.getElementById("editServiceModal").style.display = "none";
    }

    function addSizeInput() {
        const container = document.getElementById("sizes-container");
        container.innerHTML += `<div class="size-price-input">
            <input type="text" name="sizes[]" placeholder="Size" required>
            <input type="number" name="prices[]" placeholder="Price" required>
        </div>`;
    }

    function addEditSizeInput() {
        const container = document.getElementById("edit_sizes-container");
        container.innerHTML += `<div class="size-price-input">
            <input type="text" name="edit_sizes[]" placeholder="Size" required>
            <input type="number" name="edit_prices[]" placeholder="Price" required>
        </div>`;
    }

    window.onload = function() {
        const toast = document.getElementById("toast");
        if (toast.innerText) {
            toast.style.display = "block";
        }
    }
</script>

</body>
</html>
