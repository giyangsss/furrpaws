<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "furrpawacc";

// Create connection
$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize notification message
$notification = "";
$notificationType = "";

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $user_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM users WHERE id=$user_id";
    
    if ($conn->query($delete_sql) === TRUE) {
        $notification = "User deleted successfully!";
        $notificationType = "success";
    } else {
        $notification = "Error deleting record: " . $conn->error;
        $notificationType = "error";
    }
}

// Fetch users from the database
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #2e3b4e;
            color: white;
        }

        .sidebar {
            width: 250px;
            background-color: #4f321b;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h1 {
            font-size: 24px;
            margin-bottom: 40px;
            color: #fff;
        }

        .sidebar ul {
            list-style-type: none;
        }

        .sidebar ul li {
            margin: 20px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            color: white;
        }

        .admin-button {
            background-color: #f0f0f0;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .admin-button:hover {
            background-color: #ddd;
        }

        .table-container {
            background-color: #3c4858;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            color: white;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #1f2733;
        }

        table tr:nth-child(even) {
            background-color: #3f4a5a;
        }

        .action-button {
            background-color: #777;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .action-button:hover {
            background-color: #666;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            left: 270px;
            color: white;
        }

        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            display: none;
            z-index: 1000;
            animation: fadeout 3s forwards;
        }

        .toast.success {
            background-color: green;
        }

        .toast.error {
            background-color: red;
        }

        @keyframes fadeout {
            0% {
                opacity: 1;
            }
            99% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                display: none;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
            color: #333;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .submit-button {
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .submit-button:hover {
            background-color: #45a049;
        }
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
            <h2>List of Users</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td>
                                    <button class="action-button" onclick="confirmDeleteUser(<?php echo $row['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>&copy; 2024 FURRPAWS. All rights reserved.</p>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteConfirmationModal()">&times;</span>
            <h3>Are you sure you want to delete this user?</h3>
            <div>
                <button class="submit-button" id="confirmDeleteButton">Yes</button>
                <button class="submit-button" onclick="closeDeleteConfirmationModal()">No</button>
            </div>
        </div>
    </div>

    <script>
        let userIdToDelete = null;

        function confirmDeleteUser(id) {
            userIdToDelete = id; // Store the ID of the user to delete
            document.getElementById("deleteConfirmationModal").style.display = "block"; // Show the delete confirmation modal
        }

        document.getElementById("confirmDeleteButton").onclick = function() {
            if (userIdToDelete) {
                window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?delete_id=" + userIdToDelete; // Perform the delete action
            }
        }

        function closeDeleteConfirmationModal() {
            document.getElementById("deleteConfirmationModal").style.display = "none"; // Hide the modal
        }

        // Show toast notification if there is a notification message
        const notification = "<?php echo $notification; ?>";
        const notificationType = "<?php echo $notificationType; ?>";
        if (notification) {
            const toast = document.getElementById("toast");
            toast.className = "toast " + notificationType; // Add the type class (success/error)
            toast.innerText = notification; // Set the message
            toast.style.display = "block"; // Show the toast
            setTimeout(() => { toast.style.display = "none"; }, 3000); // Hide after 3 seconds
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
