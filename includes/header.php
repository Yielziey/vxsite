<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>VX Esports - One Standard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="assets/images/vx-logo.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS for Header and Footer -->
    <style>
        :root {
            --vx-red: #ff2a2a;
            --vx-red-dark: #b30000;
            --vx-dark: #0a0a0a;
            --vx-dark-secondary: #141414;
        }

        /* Header Styling */
        .vx-header {
            background-color: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #222;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
            transition: background-color 0.3s ease;
        }

        .navbar-brand .logo-img {
            width: 35px;
            height: 35px;
            transition: transform 0.3s ease;
        }
        .navbar-brand:hover .logo-img {
            transform: rotate(180deg);
        }

        .navbar-brand .logo-text {
            font-family: 'Xirod', sans-serif; /* Make sure you have this font */
            color: var(--vx-red);
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            font-family: 'Titillium Web', sans-serif;
            font-weight: 700;
            color: #ccc;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--vx-red);
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #fff;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 80%;
        }

        .navbar-toggler {
            border-color: rgba(255, 42, 42, 0.5);
        }
        .navbar-toggler-icon {
             background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 42, 42, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .dropdown-menu {
            background-color: var(--vx-dark-secondary);
            border: 1px solid #222;
        }
        .dropdown-item {
            color: #ccc;
            font-family: 'Titillium Web', sans-serif;
            font-weight: 600;
        }
        .dropdown-item:hover {
            background-color: var(--vx-red);
            color: #fff;
        }
    </style>
</head>
<body>
  <header class="vx-header">
    <nav class="navbar navbar-expand-lg">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="main.php">
          <img src="assets/images/vx-logo.png" alt="VX Logo" class="me-2 logo-img"/>
          <span class="logo-text">Vexillum</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#vxNavbar" aria-controls="vxNavbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="vxNavbar">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0" style="gap: 10px;">
            <li class="nav-item"><a class="nav-link" href="news.php">News</a></li>
            <!-- UPDATED: About link is now a dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">About</a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="aboutDropdown">
                <li><a class="dropdown-item" href="aboutus.php">Xentro</a></li>
                <li><a class="dropdown-item" href="vexaura.php">VexAura</a></li>
                <li><a class="dropdown-item" href="vxpodcast.php">VX Podcast</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="divisionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Division</a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="divisionDropdown">
                <li><a class="dropdown-item" href="Teams.php">Teams</a></li>
                <li><a class="dropdown-item" href="creators.php">Creators</a></li>
                <li><a class="dropdown-item" href="sponsor.php">Sponsors</a></li>
                <li><a class="dropdown-item" href="store.php">Store</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
            <li class="nav-item"><a class="nav-link" href="live.php">Live Now</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

