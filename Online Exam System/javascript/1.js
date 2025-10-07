(function () {
  emailjs.init("Kr8_JxFLgndqLsvq4"); // ✅ Public Key
})();

function sendmail() {
  let params = {
    name: document.getElementById("name").value,
    email: document.getElementById("email").value,
    subject: document.getElementById("subject").value,
    message: document.getElementById("message").value,
  };

  emailjs
    .send("service_cqyqoh2", "template_wlrtum6", params)
    .then(function (res) {
      alert("✅ Email Sent Successfully!");
      console.log("EmailJS response:", res);

      // ✅ Add message to testimonials dynamically
      addTestimonial(params.name, params.message);

      document.querySelector("form").reset(); // clear form
    })
    .catch(function (err) {
      alert("❌ Failed to send email.");
      console.error("EmailJS error:", err);
    });
}

// ✅ Improved Testimonial Adding Logic
function addTestimonial(name, message) {
  const container = document.getElementById("testimonialContainer");

  // Create new testimonial item
  const newItem = document.createElement("div");
  newItem.classList.add("carousel-item");
  newItem.innerHTML = `
    <div class="testimonial mx-auto" style="max-width: 700px;">
      <p>“${message}”</p>
      <h6 class="fw-bold mt-3">— ${name}</h6>
    </div>
  `;

  // Append testimonial at the end
  container.appendChild(newItem);

  // ✅ Reinitialize carousel properly
  const carouselElement = document.getElementById("testimonialCarousel");
  const carousel = bootstrap.Carousel.getInstance(carouselElement)
    || new bootstrap.Carousel(carouselElement);

  // Move to the newly added testimonial smoothly
  const totalItems = container.querySelectorAll(".carousel-item").length;
  carousel.to(totalItems - 1);
}
