<?php
session_start(); // Start session to use session variables

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = htmlspecialchars(trim($_POST["fullname"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $contact = htmlspecialchars(trim($_POST["contact"]));
    $schedule = htmlspecialchars(trim($_POST["schedule"]));
    $time = htmlspecialchars(trim($_POST["time"])); // Capture the time input

    // Concatenate date and time
    $scheduleDateTime = $schedule . ' ' . $time;

    // Get selected services and sizes
    $services = isset($_POST['services']) ? $_POST['services'] : [];
    $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : []; // Capture sizes

    // Calculate total price
    $totalPrice = 0;
    if (!empty($services)) {
        // Fetch service costs from the database
        $placeholders = implode(',', array_fill(0, count($services), '?'));
        $sql = "SELECT cost FROM service_list WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($services)), ...$services);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $totalPrice += $row['cost'];
        }

        $stmt->close();
    }

    // Prepare SQL query to insert the appointment
    $stmt = $conn->prepare("INSERT INTO `appointment_list` (`fullname`, `contact`, `email`, `schedule`, `services`, `sizes`, `total_price`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $servicesList = implode(", ", $services); // Convert array to comma-separated string
    $sizesList = implode(", ", $sizes); // Convert array to comma-separated string for sizes
    $stmt->bind_param("ssssssi", $fullname, $contact, $email, $scheduleDateTime, $servicesList, $sizesList, $totalPrice);

    if ($stmt->execute()) {
        // Store success message in session
        $_SESSION['message'] = "Booking successful! Total Price: ₱" . $totalPrice;
        // Redirect to the same page to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit(); // Ensure no further code is executed
    } else {
        // Store error message in session
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Display any success or error messages
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear session messages after displaying them
unset($_SESSION['message'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment</title>
     <link rel="icon" href="photo/logo.PNG">
    <link rel="stylesheet" href="styles.css">
    <style>

    
       body {
    font-family: Arial, sans-serif;
    background-color: #2e3b4e;
    color: white;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.container {
    width: 90%;
    max-width: 700px;
    margin: auto;
    background-color: #333;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
}

/* Responsive adjustments for smaller screens */



label {
    display: block;
    margin-bottom: 10px;
}

input, select {
    width: 97%;
    padding: 10px;
    margin-bottom: 20px;
    border: none;
    border-radius: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .container {
        width: 88%;
    }

    input, select{
        width: 94%;
        margin: 0 auto;
    }

    input, select{
        padding: 10px;
        margin-bottom: 20px;
        border: none;
        border-radius: 5px;
    }

   
}

table, th, td {
    border: 1px solid #555;
}

th, td {
    padding: 10px;
    text-align: center;
}

th {
    background-color: #444;
}

td {
    background-color: #555;
}

.total {
    text-align: right;
    font-weight: bold;
    margin-bottom: 20px;
}

button {
    background-color: #007BFF;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    max-width: 200px;
}

button:hover {
    background-color: #0056b3;
}

.pet-size {
    display: none; /* Initially hidden */
}

/* Modal styling */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #333;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #555;
    width: 90%;
    max-width: 500px;
    color: white;
    border-radius: 10px;
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

/* Ensure table responsiveness */
@media (max-width: 768px) {
    th, td {
        padding: 8px;
        font-size: 14px;
    }
}

/* Button responsiveness */
@media (max-width: 480px) {
    button {
        font-size: 14px;
        padding: 8px 16px;
    }

    .modal-content {
        width: 95%;
    }
}

    </style>
    <script>
        function calculateTotal() {
            const checkboxes = document.querySelectorAll("input[name='services[]']:checked");
            let total = 0;
            checkboxes.forEach((checkbox) => {
                total += parseFloat(checkbox.getAttribute('data-cost'));
            });
            document.getElementById("total").innerText = total.toFixed(2); // Update the total in the DOM
        }

        function togglePetSizeDropdown(serviceSelect) {
            const petSizeDropdowns = document.querySelectorAll('.pet-size');
            petSizeDropdowns.forEach(dropdown => {
                dropdown.style.display = 'none'; // Hide all initially
            });
            const selectedService = serviceSelect.value;
            if (selectedService) {
                const sizeDropdown = document.getElementById(`size-${selectedService}`);
                if (sizeDropdown) {
                    sizeDropdown.style.display = 'block'; // Show relevant size dropdown
                }
            }
        }

        // Function to show the modal
        function showModal(message) {
            const modal = document.getElementById("confirmationModal");
            const modalContent = document.getElementById("modalMessage");
            modalContent.innerText = message;
            modal.style.display = "block";
        }

        // Close the modal
        function closeModal() {
            document.getElementById("confirmationModal").style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById("confirmationModal");
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</head>
<body>


<div class="container">
    <h2>Book an Appointment</h2>
    <form method="post">
        <label for="fullname">Fullname:</label>
        <input type="text" id="fullname" name="fullname" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="contact">Contact #:</label>
        <input type="text" id="contact" name="contact" required>

        <label for="schedule">Schedule Date:</label>
        <input type="date" id="schedule" name="schedule" required>

        <label for="time">Schedule Time:</label>
        <input type="time" id="time" name="time" required>

        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Service</th>
                    <th>Description</th>
                    <th>Cost</th>
                    <th>Pet Size</th> <!-- New column for pet size -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch only active services from the database
                $sql = "SELECT * FROM service_list WHERE status = 'active'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td>
                        <input type="checkbox" name="services[]" value="<?php echo $row['id']; ?>" data-cost="<?php echo $row['cost']; ?>" onchange="calculateTotal(); togglePetSizeDropdown(this);">
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>₱<?php echo number_format($row['cost'], 2); ?></td>
                    <td>
                        <select name="sizes[]" id="size-<?php echo $row['id']; ?>" class="pet-size">
                            <option value="">Select Size</option>
                            <option value="small">Small</option>
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                            <option value="xl">Extra Large</option>
                        </select>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="5">No services available at the moment.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total">
            <label>Total: ₱<span id="total">0.00</span></label>
        </div>

        <button type="submit">Book Now</button>
    </form>
</div>

<!-- Modal for confirmation message -->
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <p id="modalMessage"></p>
    </div>
</div>

<?php
// Close the database connection after all queries are done
$conn->close();

// If there's a message set in session, show the modal
if (!empty($message)) {
    echo "<script>showModal('".addslashes($message)."');</script>";
}

// If there's an error set in session, show the modal
if (!empty($error)) {
    echo "<script>showModal('".addslashes($error)."');</script>";
}
?>

</body>
</html>
