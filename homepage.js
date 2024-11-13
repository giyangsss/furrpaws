const slideshowContainer = document.getElementById('slideshow-container');
const slideshowImages = document.querySelectorAll('.slideshow-image');
const prevButton = document.getElementById('prev-button');
const nextButton = document.getElementById('next-button');

let currentSlide = 0;

// Hide all images except the first one
slideshowImages.forEach((image, index) => {
  if (index !== 0) {
    image.style.display = 'none';
  }
});


prevButton.addEventListener('click', () => {
  currentSlide--;
  if (currentSlide < 0) {
    currentSlide = slideshowImages.length - 1;
  }
  updateSlideshow();
});

nextButton.addEventListener('click', () => {
  currentSlide++;
  if (currentSlide >= slideshowImages.length) {
    currentSlide = 0;
  }
  updateSlideshow();
});


function updateSlideshow() {
  slideshowImages.forEach((image, index) => {
    if (index === currentSlide) {
      image.style.display = 'block';
    } else {
      image.style.display = 'none';
    }
  });
}


setInterval(() => {
  nextButton.click();
}, 4000); 

const aboutButton = document.getElementById('about-button');
const whitebox = document.getElementById('whitebox');

aboutButton.addEventListener('click', () => {
  whitebox.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  });
});



document.querySelector('.login-form').addEventListener('submit', function(event) {
  event.preventDefault(); // Prevent the default form submission

  const email = document.querySelector('input[type="text"]').value;
  const password = document.querySelector('input[type="password"]').value;

  fetch('db.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
          action: 'login',
          email: email,
          password: password
      })
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert("Login successful!");
          // Redirect or update the UI as needed
      } else {
          alert("Login failed! Please check your credentials.");
      }
  })
  .catch(error => console.error('Error:', error));
});
