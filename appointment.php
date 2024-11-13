<?php

// Database connection details
$db_server = "192.168.0.100";
$db_user = "furrpaws_furrpawacc";
$db_pass = "t7em2zEqBDaWDTctYAjb";
$db_name = "furrpaws_furrpawacc";

// PHPMailer includes (make sure to update the paths to the PHPMailer files)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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

        // Use prepared statements for status updates
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
                $fullname = $email_row['fullname'];
                $subject = "Appointment Verified";
                $message = "Dear $fullname,\n\nYour appointment has been verified.\n\nThank you!";

                // Send the email notification
                try {
                    $mail = new PHPMailer(true);

                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'giyanggaming4@gmail.com';  // Your email address
                    $mail->Password = 'efjpdlhgzdqymoxi';  // Your email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('giyanggaming4@gmail.com', 'Appointment System');  // Replace with your "From" address
                    $mail->addAddress($to, $fullname);  // Send email to the user

                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body = $message;


                    $mail->send();
                    echo 'Appointment verified and email sent to ' . $to;
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
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

// Close the database connection
$conn->close();
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
            <li><a href="services.php">Services</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Appointment List</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Created At</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = 'status-not-verified';
                            if ($row['status'] == 'Verified') {
                                $statusClass = 'status-verified';
                            } elseif ($row['status'] == 'Declined') {
                                $statusClass = 'status-declined';
                            }
                    ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><?= $row['fullname'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><span class="status-badge <?= $statusClass ?>"><?= $row['status'] ?></span></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <select name="action" class="action-dropdown">
                                        <option value="verify" <?= ($row['status'] == 'Verified') ? 'disabled' : '' ?>>Verify</option>
                                        <option value="decline" <?= ($row['status'] == 'Declined') ? 'disabled' : '' ?>>Decline</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="submit" class="action-button">Submit</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6'>No appointments found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Appointment Details -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Appointment Details</h3>
            <p id="appointmentDetails"></p>
        </div>
    </div>

    <script>
        // Script to show appointment details in modal
        function showDetails(id) {
            fetch('?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('appointmentDetails').innerText = JSON.stringify(data, null, 2);
                    document.getElementById('appointmentModal').style.display = "block";
                });
        }

        // Close modal
        document.querySelector('.close').addEventListener('click', () => {
            document.getElementById('appointmentModal').style.display = "none";
        });
    </script>

</body>
</html>
