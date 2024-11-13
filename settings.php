<?php
// admin.php

// Start session and check for admin login
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$message = ""; // Initialize message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['welcome_image'])) {
    $target_dir = "photo/"; // Directory to save uploaded images
    $target_file = $target_dir . basename($_FILES["welcome_image"]["name"]);
    
    // Check if the file is an image
    $check = getimagesize($_FILES["welcome_image"]["tmp_name"]);
    if ($check !== false) {
        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["welcome_image"]["tmp_name"], $target_file)) {
            // Save the file name in a text file
            file_put_contents('current_welcome_image.txt', basename($_FILES["welcome_image"]["name"]));
            $message = "The file ". htmlspecialchars(basename($_FILES["welcome_image"]["name"])). " has been uploaded.";
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    } else {
        $message = "File is not an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Change Welcome Image</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h2 {
            margin-bottom: 20px;
        }
        form {
            margin-top: 20px;
        }
        input[type="file"] {
            margin: 10px 0;
        }
        input[type="submit"] {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h2>Change Welcome Image</h2>
<?php if ($message) echo "<p>$message</p>"; ?>
<form action="" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="welcome_image" id="welcome_image" required>
    <input type="submit" value="Upload Image" name="submit">
</form>

</body>
</html>
