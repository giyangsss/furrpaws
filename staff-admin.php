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
        .calendar-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h1>FURRPAWS</h1>
        <ul>
            <li><a href="staff-admin.php">Dashboard</a></li>
            <li><a href="staff-appointment.php">Appointment List</a></li>
            <li><a href="staff-services.php">Service List</a></li>
        </ul>
    </div>

    <div class="dashboard">
        <div class="header">
            <h2>Welcome to FURRPAWS</h2>
            <button class="admin-button">Administrator Admin</button>
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

        <div class="footer">
            <p>Copyright © 2024. All rights reserved.</p>
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
        });
    </script>

</body>
</html>

<?php
$conn->close(); // Close the connection
?>
