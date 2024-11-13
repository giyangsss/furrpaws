<?php

?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gradient Background Homepage</title>
	<link rel="stylesheet" type="text/css" href="css/homepage.css">
</head>
<body>  
	<div class="dashboard">
		<div class="dashboard-inner">
			<h1>FURRPAWS</h1>
			<ul>
				<li><a href="#">H</a></li>
				<li><a href="#">About</a></li>
				<li><a href="booking.php">Book Now!</a></li>
				<li class="account" id="login-button"><a href="login.php">ACCOUNT</a></li>
			</ul>
		</div>

	</div>

	<!-- LOGIN POPUP-->

	<div id="login-popup" style="display: none;">
		<div class="login-form">
			
            <img src="image/logo.PNG" alt="Logo" class="login-logo">
            <p class="content" style="font-size: 25px; font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; font-weight: bold; margin-bottom: -10px;"> ADMIN </h1>
           
			
					<p class="text">Username:</p>
					<input type="text" placeholder="Username" required>
					<p class="text">Password:</p>
					<input type="password" placeholder="Password" required>
					<input type="submit" value="Login">
		</div>
	</div>


	<!-- SLIDESHOW-->

	<div id="slideshow-container">
		<img src="photo/pics.jpg" alt="Image 1" class="slideshow-image">
		<img src="photo/pics2.jpg" alt="Image 1" class="slideshow-image">
		
	  <button id="prev-button">  </button>
	  <button id="next-button">  </button>
	</div>
		
	
	
 <!-- werl-->
	
	<script src="homepage.js"></script>
	<img src="photo/home wall.jpg" alt="Image" class="lower-right-image">

<!-- SLIDESHOW-->

	<div class="services">
		<div class="service-container">
		  <div class="service-image-container">
			<img src="photo/groom1.jpg" alt="Image 1">
		  </div>
		</div>
		<div class="service-container">
		  <div class="service-image-container">
			<img src="photo/FOOD1.JPG" alt="Image 2">
		  </div>
		</div>
		<div class="service-container">
		  <div class="service-image-container">
			<img src="photo/ACCESS1.JPG" alt="Image 3">
				
		  </div>
		  
		</div>
  
 <div class="lower-headline">
  <div class="facebook-logo">
    <a href="https://www.facebook.com/alvinmancera13" target="_blank">
      <img src="photo/1314170_facebook_internet_logo_network_sign_icon.png" alt="Facebook Logo" >
	</a>
	<p>© Copyright Furr Paws 2024 </p>
  </div>
</div>

</div>
<button class="about" >BOOK NOW</button>

</body>
</html>