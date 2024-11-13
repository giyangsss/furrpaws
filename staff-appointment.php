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

// Fetch bookings
$sql = "SELECT * FROM `appointment_list` ORDER BY `created_at` DESC";
$result = $conn->query($sql);

// Handle form submissions for updating and deleting appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $id = $_POST['id'];
        $action = $_POST['action'];

        // Use prepared statements
        if ($action === 'verify') {
            $status = 'Verified';
            $update_sql = $conn->prepare("UPDATE appointment_list SET status=? WHERE id=?");
            $update_sql->bind_param("si", $status, $id);
            $update_sql->execute();
            $update_sql->close();

            // Fetch the user's email for sending the notification
            $email_sql = $conn->prepare("SELECT email, fullname FROM appointment_list WHERE id=?");
            $email_sql->bind_param("i", $id);
            $email_sql->execute();
            $email_result = $email_sql->get_result();
            if ($email_result->num_rows > 0) {
                $email_row = $email_result->fetch_assoc();
                $to = $email_row['email'];
                $subject = "Appointment Verified";
                $message = "Dear " . $email_row['fullname'] . ",\n\nYour appointment has been verified.\n\nThank you!";
                $headers = "From: giyanggaming4@gmail.com"; // Change to a valid sender address

                // Send email
                mail($to, $subject, $message, $headers);
            }
            $email_sql->close();
        } elseif ($action === 'decline') {
            $status = 'Declined';
            $update_sql = $conn->prepare("UPDATE appointment_list SET status=? WHERE id=?");
            $update_sql->bind_param("si", $status, $id);
            $update_sql->execute();
            $update_sql->close();
        } elseif ($action === 'delete') {
            $delete_sql = $conn->prepare("DELETE FROM appointment_list WHERE id=?");
            $delete_sql->bind_param("i", $id);
            $delete_sql->execute();
            $delete_sql->close();
        }
    }

    // Redirect back to the appointment list
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to fetch appointment details
function get_appointment_details($conn, $id) {
    $sql = $conn->prepare("SELECT * FROM appointment_list WHERE id = ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    $result = $sql->get_result();
    return $result->fetch_assoc();
}

if (isset($_GET['id'])) {
    $appointmentDetails = get_appointment_details($conn, $_GET['id']);
    echo json_encode($appointmentDetails);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FURRPAWS Appointment List</title>
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

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
        }

        .status-not-verified {
            background-color: orange;
        }

        .status-declined {
            background-color: red;
        }

        .status-verified {
            background-color: green;
        }

        .action-dropdown {
            padding: 5px;
            border-radius: 5px;
            margin-right: 5px;
            background-color: #fff;
            color: #000;
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

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #3c4858;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            color: white;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: white;
            text-decoration: none;
            cursor: pointer;
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
            <h2>List of Appointments</h2>
            <button class="admin-button">Administrator Admin</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Created</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    // Display the fetched data
    $counter = 1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $counter++ . '</td>';
            echo '<td>' . $row['created_at'] . '</td>';
            echo '<td>' . $row['fullname'] . '</td>';
            echo '<td>' . $row['email'] . '</td>';
            echo '<td>' . $row['contact'] . '</td>';
            echo '<td>' . $row['schedule'] . '</td>';
            // Display status, defaulting to "Not Verified"
            echo '<td><span class="status-badge status-' . (strtolower($row['status']) ?: 'not-verified') . '">' . ($row['status'] ?: 'Not Verified') . '</span></td>';

            echo '<td>';
            echo '<button class="action-button" onclick="viewDetails(' . $row['id'] . ')">View Details</button>';
            echo '<form style="display:inline;" method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
            echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
            echo '<select class="action-dropdown" name="action" onchange="this.form.submit()">';
            echo '<option value="">Actions</option>';
            echo '<option value="verify">Verify</option>';
            echo '<option value="decline">Decline</option>';
            echo '<option value="delete">Delete</option>';
            echo '</select>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8">No appointments found.</td></tr>';
    }
    ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>&copy; Copyright © 2024. All rights reserved</p>
        </div>
    </div>

    <!-- Modal -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Appointment Details</h2>
            <p id="modalContent"></p>
        </div>
    </div>

    <script>
        function viewDetails(appointmentId) {
            const modal = document.getElementById("appointmentModal");
            const modalContent = document.getElementById("modalContent");

            // Make an AJAX request to fetch the appointment details
            fetch(`appointment.php?id=${appointmentId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate the modal with appointment details
                    modalContent.innerHTML = `
                        <strong>Full Name:</strong> ${data.fullname}<br>
                        <strong>Email:</strong> ${data.email}<br>
                        <strong>Contact:</strong> ${data.contact}<br>
                        <strong>Schedule:</strong> ${data.schedule}<br>
                        <strong>Status:</strong> <span class="status-badge status-${data.status.toLowerCase()}">${data.status}</span><br>
                        <strong>Services:</strong> ${data.services}<br>
                        <strong>Size:</strong> ${data.sizes}<br>
                    `;
                    modal.style.display = "block"; // Show the modal
                })
                .catch(error => console.error('Error fetching appointment details:', error));
        }

        function closeModal() {
            const modal = document.getElementById("appointmentModal");
            modal.style.display = "none"; // Hide the modal
        }

        // Close the modal if the user clicks anywhere outside of the modal
        window.onclick = function(event) {
            const modal = document.getElementById("appointmentModal");
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
