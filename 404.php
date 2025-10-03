<?php 
// Sinasabi nito sa browser na ito ay isang "404 Not Found" error
http_response_code(404);
include 'includes/header.php'; 
?>

<style>
:root {
    --vx-red: #ff2a2a;
    --vx-dark: #0a0a0a;
    --vx-text: #e0e0e0;
}

.not-found-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    min-height: 100vh; /* Ginawang 100vh para sakupin ang buong screen */
    padding-top: 80px; /* Para hindi matakpan ng header */
    font-family: 'Titillium Web', sans-serif;
    color: var(--vx-text);
    background-color: var(--vx-dark);
}

.glitch-text {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(5rem, 20vw, 10rem);
    color: white;
    position: relative;
    animation: glitch 1s infinite;
    margin: 0;
    line-height: 1;
}

.glitch-text::before,
.glitch-text::after {
    content: '404';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--vx-dark);
}

.glitch-text::before {
    left: 2px;
    text-shadow: -2px 0 var(--vx-red);
    clip: rect(44px, 450px, 56px, 0);
    animation: glitch-anim-1 2s infinite linear alternate-reverse;
}

.glitch-text::after {
    left: -2px;
    text-shadow: -2px 0 #00ffff, 2px 2px var(--vx-red);
    clip: rect(85px, 450px, 90px, 0);
    animation: glitch-anim-2 3s infinite linear alternate-reverse;
}

@keyframes glitch {
    0%, 100% { transform: skewX(0deg); }
    10% { transform: skewX(5deg); }
    20% { transform: skewX(-5deg); }
    30% { transform: skewX(5deg); }
    40% { transform: skewX(-3deg); }
    50% { transform: skewX(3deg); }
    60% { transform: skewX(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip: rect(42px, 9999px, 44px, 0); }
    5% { clip: rect(12px, 9999px, 60px, 0); }
    100% { clip: rect(50px, 9999px, 102px, 0); }
}

@keyframes glitch-anim-2 {
    0% { clip: rect(2px, 9999px, 98px, 0); }
    10% { clip: rect(80px, 9999px, 45px, 0); }
    100% { clip: rect(40px, 9999px, 130px, 0); }
}

.not-found-container h2 {
    font-size: clamp(1.5rem, 5vw, 2.5rem);
    text-transform: uppercase;
    letter-spacing: 4px;
    color: var(--vx-text);
    margin-top: 1rem;
}

.not-found-container p {
    font-size: 1.1rem;
    color: #888;
    max-width: 500px;
    margin: 1rem 0 2rem 0;
}

.back-home-btn {
    display: inline-block;
    padding: 12px 30px;
    border: 2px solid var(--vx-red);
    border-radius: 5px;
    color: var(--vx-red);
    text-decoration: none;
    text-transform: uppercase;
    font-weight: bold;
    letter-spacing: 1px;
    transition: background-color 0.3s, color 0.3s, box-shadow 0.3s;
}

.back-home-btn:hover {
    background-color: var(--vx-red);
    color: white;
    box-shadow: 0 0 15px rgba(255, 42, 42, 0.5);
}

</style>

<main>
    <div class="not-found-container">
        <h1 class="glitch-text">404</h1>
        <h2>Page Not Found</h2>
        <p>The page you're looking for might have been moved, deleted, or maybe it never existed in the first place.</p>
        <a href="main.php" class="back-home-btn">Return to Home</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

