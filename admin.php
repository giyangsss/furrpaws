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

// Fetch appointment counts
$pending_query = "SELECT COUNT(*) as pending_count FROM appointment_list WHERE status IS NULL OR status = ''";
$verified_query = "SELECT COUNT(*) as verified_count FROM appointment_list WHERE status='Verified'";

// Fetch occupied dates and their statuses
$occupied_query = "SELECT schedule, status FROM appointment_list WHERE status='Verified' OR status IS NULL OR status = ''";
$occupied_result = $conn->query($occupied_query);
$occupied_dates = [];
while ($row = $occupied_result->fetch_assoc()) {
    $occupied_dates[] = [
        'date' => $row['schedule'], 
        'status' => $row['status']
    ];
}

$pending_result = $conn->query($pending_query);
$verified_result = $conn->query($verified_query);

$pending_count = $pending_result->fetch_assoc()['pending_count'];
$verified_count = $verified_result->fetch_assoc()['verified_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FURRPAWS Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js'></script>
    <style>
        /* Add your custom styles here */
      /* Calendar Container */
.calendar-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

/* Modal styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
}

.modal-content {
    background-color: #3c4858; /* Dark background */
    margin: 10% auto; /* 10% from the top and centered */
    padding: 20px;
    border-radius: 10px; /* Rounded corners */
    width: 50%; /* Could be more or less, depending on screen size */
    color: white; /* Text color */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover, .close:focus {
    color: white; /* Change color on hover */
    cursor: pointer;
}

/* Logout button */
.logout-button {
    position: relative;
    background-color: #ff4d4d; /* Red color for logout */
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    text-align: center;
}

.logout-button:hover {
    background-color: #e60000; /* Darker red on hover */
}

/* Media queries for responsiveness */

/* For screen width 992px and above */
@media (min-width: 992px) {
    .modal-content {
        width: 50%; /* Modal width for larger screens */
    }

    .logout-button {
        position: relative;
        top: 310px; /* Keeps the button in the right place on larger screens */
    }
}

/* For screen widths 768px to 991px */
@media (max-width: 991px) {
    .modal-content {
        width: 60%; /* Modal width slightly larger on medium screens */
    }

    .logout-button {
        position: relative;
        top: 270px; /* Adjust position of logout button */
    }
}

/* For screen widths 576px to 767px */
@media (max-width: 767px) {
    .modal-content {
        width: 75%; /* Modal width more flexible for smaller screens */
    }

    .logout-button {
        position: relative;
        top: 230px; /* Adjust position of logout button */
    }
}

/* For screen widths 575px and below */
@media (max-width: 575px) {
    .modal-content {
        width: 90%; /* Modal width nearly full width on small screens */
    }

    .logout-button {
        position: relative;
        top: 190px; /* Adjust position of logout button */
    }
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
            <li><button class="logout-button" onclick="logout()">Logout</button></li> <!-- Logout button -->
        </ul>
    </div>

    <div class="dashboard">
        <div class="header">
            <h2>Welcome to FURRPAWS</h2>
            <button class="admin-button" id="adminButton">Administrator Admin</button>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <div class="icon">📅</div>
                <h3>Pending Requests</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="card">
                <div class="icon">✔️</div>
                <h3>Verified Appointment</h3>
                <p><?php echo $verified_count; ?></p>
            </div>
        </div>

        <div class="calendar-container">
            <div id='calendar'></div>
        </div>

    </div>

    <!-- The Modal -->
    <div id="adminModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Admin Details</h2>
            <form id="adminForm">
                <label for="adminName">Name:</label>
                <input type="text" id="adminName" name="adminName" required><br><br>
                <label for="adminUsername">Username:</label>
                <input type="text" id="adminUsername" name="adminUsername" required><br><br>
                <label for="adminPassword">Password:</label>
                <input type="password" id="adminPassword" name="adminPassword" required><br><br>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var occupiedDates = <?php echo json_encode($occupied_dates); ?>;

            $('#calendar').fullCalendar({
                editable: false,
                events: occupiedDates.map(function(entry) {
                    return {
                        title: 'Occupied',
                        start: entry.date,
                        color: entry.status === 'Verified' ? 'green' : 'orange' // Set color based on status
                    };
                }),
                dayRender: function(date, cell) {
                    // Check if the date is today
                    if (date.isSame(new Date(), 'day')) {
                        cell.css('background-color', 'gray'); // Change background color of today's date
                        cell.css('color', 'white'); // Change text color to white for better visibility
                    }
                }
            });

            // Get the modal
            var modal = $('#adminModal');

            // Get the button that opens the modal
            var btn = $('#adminButton');

            // Get the <span> element that closes the modal
            var span = $('.close');

            // When the user clicks the button, open the modal 
            btn.on('click', function() {
                modal.show();
            });

            // When the user clicks on <span> (x), close the modal
            span.on('click', function() {
                modal.hide();
            });

            // When the user clicks anywhere outside of the modal, close it
            $(window).on('click', function(event) {
                if (event.target == modal[0]) {
                    modal.hide();
                }
            });

            // Handle the form submission
            $('#adminForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Here you would typically send the data to the server using AJAX or a form submission
                // Example: 
                // $.post('update_admin.php', $(this).serialize());

                // Close the modal after saving (simulate success)
                alert('Admin details updated successfully!');
                modal.hide();
            });
        });

        // Logout function
        function logout() {
    if (confirm("Are you sure you want to log out?")) {
        window.location.href = 'logout.php'; // Ensure this path is correct
    }
}

    </script>

</body>
</html>

<?php
$conn->close(); // Close the connection
?>
