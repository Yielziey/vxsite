<style>
    /* --- FINAL CSS PARA SA FOOTER --- */
    :root {
        --vx-red: #ff2a2a;
        --vx-dark-secondary: #141414;
        --vx-text-muted: #888;
    }

    .vx-footer-final {
        background-color: var(--vx-dark-secondary);
        color: var(--vx-text-muted);
        padding: 4rem 2rem;
        margin-top: auto;
        border-top: 1px solid #2a2a2a;
    }

    .vx-footer-final .footer-grid {
        display: grid;
        grid-template-columns: 1fr; /* Default to single column for mobile */
        gap: 2.5rem;
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }

    /* Magiging 3 columns lang sa malalaking screen */
    @media (min-width: 768px) {
        .vx-footer-final .footer-grid {
            grid-template-columns: repeat(3, 1fr);
            text-align: left;
        }
    }
    
    .footer-section {
        display: flex;
        flex-direction: column;
        align-items: center; /* Center align items for mobile */
    }

    @media (min-width: 768px) {
        .footer-section {
            align-items: flex-start; /* Left align for desktop */
        }
    }

    .footer-section h5 {
        font-family: 'Xirod', sans-serif;
        color: var(--vx-red);
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        font-size: 1.1rem;
        letter-spacing: 1px;
    }
    
    .footer-section.about .footer-logo {
        width: 100px;
        height: auto;
        margin-bottom: 1rem;
    }

    .footer-section p {
        font-family: 'Titillium Web', sans-serif;
        font-size: 0.9rem;
        line-height: 1.7;
        max-width: 300px;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        font-family: 'Titillium Web', sans-serif;
        color: var(--vx-text-muted);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    .footer-links a:hover {
        color: var(--vx-red);
    }

    .footer-socials {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        gap: 1.5rem;
    }

    .footer-socials a {
        color: var(--vx-text-muted);
        font-size: 1.5rem;
        transition: color 0.3s ease, transform 0.3s ease;
    }
    .footer-socials a:hover {
        color: var(--vx-red);
        transform: translateY(-3px);
    }

    /* Copyright Text Styling */
    .footer-copyright {
        font-family: 'Titillium Web', sans-serif;
        font-size: 0.9rem;
        margin-top: 2rem; /* Nagdagdag ng space sa pagitan ng icons at text */
    }
    .footer-copyright strong {
        color: var(--vx-red);
    }

/* --- Extra Responsive Fixes for Footer --- */
.vx-footer-final {
    width: 100%;
    box-sizing: border-box;
    -webkit-text-size-adjust: 100%; /* iOS text scaling fix */
    overflow-x: hidden;
}

@media (max-width: 480px) {
    .vx-footer-final {
        padding: 2rem 1rem;
    }
    .vx-footer-final .footer-grid {
        gap: 1.5rem;
    }
}

</style>

<footer class="vx-footer-final">
    <div class="footer-grid">
        <!-- About Section -->
        <div class="footer-section about">
            <img src="assets/images/vx-logo.png" alt="VX Logo" class="footer-logo">
            <p>Premier esports division focused on dominance, discipline, and loyalty. One Standard.</p>
        </div>

        <!-- Quick Links Section -->
        <div class="footer-section">
            <h5>Quick Links</h5>
            <ul class="footer-links">
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="Teams.php">Teams</a></li>
                <li><a href="store.php">Store</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <!-- Follow Us & Copyright Section -->
        <div class="footer-section">
            <h5>Follow Us</h5>
            <ul class="footer-socials">
                <li><a href="https://www.facebook.com/the.vexillum" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="https://www.youtube.com/@The.Vexillum" target="_blank" title="Youtube"><i class="fab fa-youtube"></i></a></li>
                <li><a href="https://www.instagram.com/titohaymeh/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                <li><a href="https://discord.gg/q5FC6bJ6qn" target="_blank" title="Discord"><i class="fab fa-discord"></i></a></li>
                <li><a href="https://open.spotify.com/artist/37QynDch02SFAB3ojVl6r2" target="_blank" title="Spotify"><i class="fab fa-spotify"></i></a></li>
            </ul>
            
            <!-- Inilipat ang copyright dito -->
            <p class="footer-copyright">
              © <?php echo date("Y"); ?> VEXILLUM. All rights reserved.<br>
              Made by <strong>Yielziey</strong> · Est. 2023
            </p>
        </div>
    </div>
</footer>

<!-- Kailangan mo ng Font Awesome para sa social icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

