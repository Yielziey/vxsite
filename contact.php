<?php 
require_once 'includes/header.php';
// Ikokonekta natin ito sa database para sa future use, tulad ng pagkuha ng contact info
require_once 'db_connect.php'; 
?>

<style>
/* --- BAGONG CSS PARA SA CONTACT PAGE --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
    --vx-text-muted: #888;
}

.contact-page-container {
    background-color: var(--vx-dark);
    color: var(--vx-text);
    padding-top: 140px; /* Para sa fixed header */
    padding-bottom: 5rem;
}

.contact-header {
    text-align: center;
    margin-bottom: 4rem;
}

.contact-header h1 {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(2.8rem, 8vw, 4rem);
    color: var(--vx-red);
    text-shadow: 0 0 15px var(--vx-red);
}

.contact-header p {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1.2rem;
    max-width: 600px;
    margin: 1rem auto 0;
    color: #ccc;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
}

@media (min-width: 992px) {
    .contact-grid {
        grid-template-columns: 1fr 1.5fr; /* Info sa kaliwa, form sa kanan */
    }
}

/* Contact Info Styling */
.contact-info h3 {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    margin-bottom: 1.5rem;
}

.contact-info p {
    font-family: 'Titillium Web', sans-serif;
    line-height: 1.8;
    color: #bbb;
}

.contact-info .info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.contact-info .info-item i {
    font-size: 1.5rem;
    color: var(--vx-red);
    width: 30px; /* Para pantay-pantay */
}

.contact-info .info-item a {
    color: var(--vx-text);
    text-decoration: none;
    font-weight: 600;
}
.contact-info .info-item a:hover {
    color: var(--vx-red);
}


/* Contact Form Styling */
.contact-form-wrapper {
    background-color: var(--vx-dark-secondary);
    padding: 2.5rem;
    border-radius: 8px;
    border: 1px solid #222;
}
.contact-form-wrapper h3 {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    margin-bottom: 2rem;
    text-align: center;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-control, .form-select {
    background-color: var(--vx-dark);
    color: var(--vx-text);
    border: 1px solid #333;
    border-radius: 5px;
    padding: 0.8rem 1rem;
}
.form-control::placeholder {
    color: var(--vx-text-muted);
}
.form-control:focus, .form-select:focus {
    background-color: var(--vx-dark);
    color: var(--vx-text);
    border-color: var(--vx-red);
    box-shadow: 0 0 10px rgba(255, 42, 42, 0.2);
}
.submit-btn {
    background: var(--vx-red);
    color: #fff;
    font-family: 'Xirod', sans-serif;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 5px;
    width: 100%;
    text-transform: uppercase;
    font-size: 1.1rem;
    transition: background-color 0.3s ease;
}
.submit-btn:hover {
    background-color: var(--vx-red-dark);
}
</style>

<main class="contact-page-container">
    <div class="container">
        <section class="contact-header">
            <h1>Get in Touch</h1>
            <p>Have questions, sponsorship proposals, or partnership ideas? We'd love to hear from you. Use the form below or reach out through our channels.</p>
        </section>

        <div class="contact-grid">
            <!-- Left Side: Contact Info -->
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p>For direct inquiries, you can reach us through the following channels. We typically respond within 24-48 hours.</p>
                
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email Us</strong><br>
                        <a href="mailto:vexillum.family@proton.me">vexillum.family@proton.me</a>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fab fa-discord"></i>
                    <div>
                        <strong>Join our Discord</strong><br>
                        <a href="https://discord.gg/q5FC6bJ6qn" target="_blank">discord.gg/q5FC6bJ6qn</a>
                    </div>
                </div>

                <h3 class="mt-5">Follow Us</h3>
                <div class="info-item">
                    <div class="footer-socials d-flex gap-3">
                            <li><a href="https://www.facebook.com/the.vexillum" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="https://www.youtube.com/@The.Vexillum" target="_blank" title="Youtube"><i class="fab fa-youtube"></i></a></li>
                            <li><a href="https://www.instagram.com/titohaymeh/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="https://open.spotify.com/artist/37QynDch02SFAB3ojVl6r2" target="_blank" title="Spotify"><i class="fab fa-spotify"></i></a></li>
                    </div>
                </div>
            </div>

            <!-- Right Side: Contact Form -->
            <div class="contact-form-wrapper">
                <h3>Send an Inquiry</h3>
                <form action="contact_send.php" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" name="fname" placeholder="First Name*" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" name="lname" placeholder="Last Name*" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" name="email" placeholder="Email Address*" required>
                    </div>
                    <div class="form-group">
                        <select class="form-select" name="topic" required>
                            <option value="" selected disabled>Select a topic*</option>
                            <option value="Sponsorship">Sponsorship</option>
                            <option value="Partner">Partner</option>
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Apply as Content Creator">Apply as Content Creator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" name="message" rows="5" placeholder="Your Message*" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- Kailangan mo ng Font Awesome para sa icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php require_once 'includes/footer.php'; ?>
