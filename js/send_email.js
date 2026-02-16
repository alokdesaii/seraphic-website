document.addEventListener('DOMContentLoaded', function() {
  var contactForm = document.getElementById('contactForm');
  if (contactForm == null) return;
  var successMessage = document.getElementById('successMessage');
  var errorMessage = document.getElementById('errorMessage');
  var submitButton = contactForm.querySelector('button[type="submit"]');

  contactForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Disable the submit button and show loading indicator
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

    // Get reCAPTCHA token
    grecaptcha.execute('YOUR_SITE_KEY', { action: 'contact_form' }).then(function(token) {
      // Set the token in the hidden field
      document.getElementById('recaptchaToken').value = token;

      var formData = new FormData(contactForm);

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'send_email.php');
      xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
          // Restore submit button state
          submitButton.disabled = false;
          submitButton.innerHTML = 'Send Message';

          if (xhr.status === 200) {
            successMessage.classList.remove('d-none');
            errorMessage.classList.add('d-none');
            contactForm.reset();
          } else {
            errorMessage.classList.remove('d-none');
            successMessage.classList.add('d-none');
          }
        }
      };
      xhr.send(formData);
    });
  });
});
