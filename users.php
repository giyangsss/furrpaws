<?php
// Database connection
$db_server = "192.168.0.100";
$db_user = "furrpaws_furrpawacc";
$db_pass = "t7em2zEqBDaWDTctYAjb";
$db_name = "furrpaws_furrpawacc";

$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; // Initialize message variable

// Handle form submissions for adding new users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $type = $_POST['type'];
        $date_added = date('Y-m-d H:i:s');

        $insert_sql = "INSERT INTO users (firstname, lastname, username, password, last_login, type, date_added) 
                       VALUES ('$firstname', '$lastname', '$username', '$password', NULL, '$type', '$date_added')";

        if ($conn->query($insert_sql) === TRUE) {
            $message = "New user created successfully";
        } else {
            $message = "Error: " . $conn->error;
        }
        // Redirect to avoid resubmission
        header('Location: users.php?message=' . urlencode($message));
        exit;
    }

    // Handle user edits
    if (isset($_POST['edit_user'])) {
        $id = $_POST['user_id'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $username = $_POST['username'];
        $type = $_POST['type'];

        $update_sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', username='$username', type='$type' WHERE id='$id'";

        if ($conn->query($update_sql) === TRUE) {
            $message = "User updated successfully";
        } else {
            $message = "Error: " . $conn->error;
        }
        // Redirect to avoid resubmission
        header('Location: users.php?message=' . urlencode($message));
        exit;
    }

    // Handle user deletion
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['user_id'];

        $delete_sql = "DELETE FROM users WHERE id='$id'";
        if ($conn->query($delete_sql) === TRUE) {
            $message = "User deleted successfully";
        } else {
            $message = "Error: " . $conn->error;
        }
        // Redirect to avoid resubmission
        header('Location: users.php?message=' . urlencode($message));
        exit;
    }
    
}

// Fetch users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Users List</title>
    <link rel="stylesheet" href="css/users.css">
    <style>

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
        .logout-button {
            background-color: #ff4d4d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .logout-button:hover {
            background-color: #e60000;
        }
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); }

        .modal-content { 
            background-color: #3c4858; 
            margin: 10% auto; 
            padding: 20px; 
            border-radius: 10px; 
            width: 50%; color: white; }

        .close { 
            color: #aaa; 
            float: right; 
            font-size: 28px; 
            font-weight: bold; }

        .close:hover, .close:focus { 
            color: white; 
            cursor: pointer; }
            
        #alertModal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }
        #alertContent {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 60%; 
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>FURRPAWS</h2>
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="appointment.php">Appointment List</a></li>
            <li><a href="services.php">Service List</a></li>
            <li><a href="users.php">User List</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
    </div>
        <div class="main-content">
            <div class="user-list">
                <h2>List of System Users</h2>
                <button class="add-btn" onclick="document.getElementById('userModal').style.display='block'">+ Create New</button>
                
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>User Type</th>
                            <th>Date Added</th>
                            <th>Last Login</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $counter = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$counter}</td>";
                                echo "<td>{$row['firstname']} {$row['lastname']}</td>";
                                echo "<td>{$row['username']}</td>";
                                echo "<td>{$row['type']}</td>";
                                echo "<td>{$row['date_added']}</td>";
                                echo "<td>" . ($row['last_login'] ? $row['last_login'] : 'Never') . "</td>";
                                echo "<td>
                                        <form action='' method='post' style='display:inline;'>
                                            <input type='hidden' name='user_id' value='{$row['id']}'>
                                            <select name='action' class='action-dropdown' onchange='this.form.submit()'>
                                                <option value=''>Select Action</option>
                                                <option value='edit'>Edit</option>
                                                <option value='delete'>Delete</option>
                                            </select>
                                        </form>
                                      </td>";
                                echo "</tr>";
                                $counter++;
                            }
                        } else {
                            echo "<tr><td colspan='7'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Modal for Creating New User -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('userModal').style.display='none'">&times;</span>
            <h2>Add New User</h2>
            <form method="POST">
                <label for="firstname">First Name:</label>
                <input type="text" name="firstname" required>
                
                <label for="lastname">Last Name:</label>
                <input type="text" name="lastname" required>
                
                <label for="username">Username:</label>
                <input type="text" name="username" required>
                
                <label for="password">Password:</label>
                <input type="password" name="password" required>
                
                <label for="type">User Type:</label>
                <select name="type" required>
                    <option value="Admin">Admin</option>
                    <option value="Staff">Staff</option>
                </select>
                
                <button type="submit" name="create_user">Create User</button>
            </form>
        </div>
    </div>

    <!-- User Modal for Editing User -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editUserModal').style.display='none'">&times;</span>
            <h2>Edit User</h2>
            <form id="editUserForm" method="POST">
                <input type="hidden" name="user_id" id="editUserId">
                <label for="firstname">First Name:</label>
                <input type="text" name="firstname" id="editFirstName" required>
                
                <label for="lastname">Last Name:</label>
                <input type="text" name="lastname" id="editLastName" required>
                
                <label for="username">Username:</label>
                <input type="text" name="username" id="editUsername" required>
                
                
                <label for="type">User Type:</label>
                <select name="type" id="editUserType" required>
                    <option value="Admin">Admin</option>
                    <option value="Staff">Staff</option>
                </select>
                
                <button type="submit" name="edit_user">Update User</button>
            </form>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="modal">
        <div id="alertContent" class="modal-content">
            <span class="close" onclick="document.getElementById('alertModal').style.display='none'">&times;</span>
            <p id="alertMessage"></p>
        </div>
    </div>

    <script>
        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            const editModal = document.getElementById('editUserModal');
            const alertModal = document.getElementById('alertModal');
            if (event.target == modal || event.target == editModal || event.target == alertModal) {
                modal.style.display = "none";
                editModal.style.display = "none";
                alertModal.style.display = "none";
            }
        }

        // Show alert modal
        function showAlert(message) {
            document.getElementById('alertMessage').innerText = message;
            document.getElementById('alertModal').style.display = 'block';
            setTimeout(() => {
                document.getElementById('alertModal').style.display = 'none';
            }, 5000); // Hide after 5 seconds
        }

        // Populate edit form fields
        function populateEditForm(id, firstname, lastname, username, type) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editFirstName').value = firstname;
            document.getElementById('editLastName').value = lastname;
            document.getElementById('editUsername').value = username;
            document.getElementById('editUserType').value = type;
            document.getElementById('editUserModal').style.display = 'block';
        }

        // Show the alert message if any
        <?php if (isset($_GET['message'])) { ?>
            showAlert("<?php echo htmlspecialchars($_GET['message']); ?>");
        <?php } ?>

        // Handle the action dropdown
        document.querySelectorAll('.action-dropdown').forEach(dropdown => {
            dropdown.onchange = function() {
                const action = this.value;
                const row = this.closest('tr');
                const userId = row.querySelector('input[name="user_id"]').value;

                if (action === 'edit') {
                    const names = row.cells[1].innerText.split(' ');
                    const firstname = names[0];
                    const lastname = names[1];
                    const username = row.cells[2].innerText;
                    const type = row.cells[3].innerText;

                    populateEditForm(userId, firstname, lastname, username, type);
                } else if (action === 'delete') {
                    if (confirm('Are you sure you want to delete this user?')) {
                        // Set the value of the hidden input to trigger deletion
                        row.querySelector('input[name="user_id"]').value = userId;
                        this.closest('form').submit();
                    } else {
                        this.selectedIndex = 0; // Reset dropdown
                    }
                }
            };
        });
    </script>
</body>
</html>

<?php
$conn->close(); // Close the connection
?>
