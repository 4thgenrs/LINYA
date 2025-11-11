<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    header('Location: queueSelect.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LINYA | Queueing Management System</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- AOS (Animate On Scroll) -->
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
      background: url('university.png') no-repeat center center fixed;
      background-size: cover;
    }
    /* Navbar */
    .navbar {
      background: #1b5e20;
      backdrop-filter: blur(6px);
      transition: all 0.3s ease;
    }
    .navbar-brand {
      font-weight: 700;
      color: #fff !important;
      letter-spacing: 1px;
    }
    .nav-link {
      color: #fff !important;
      margin: 0 8px;
      font-weight: 500;
      transition: 0.3s;
    }
    .nav-link:hover {
      color: #1b5e20 !important;
    }

    /* Hero Section */
    .hero {
      color: #000;
      text-align: center;
      padding: 120px 20px;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: 700;
    }
    
    .hero p {
      max-width: 700px;
      margin: 20px auto 30px;
      font-size: 1.1rem;
      color: #000;
    }
    .hero .btn-primary {
      border-radius: 30px;
      padding: 10px 30px;
      font-weight: 600;
    }

    /* Features */
    .features {
      background: #fff;
      padding: 80px 20px;
    }
    .feature-box {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      padding: 30px 25px;
      text-align: center;
      transition: all 0.3s ease;
    }
    .feature-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    }
    .feature-box i {
      color: #1b5e20;
      font-size: 2.5rem;
      margin-bottom: 15px;
    }

    /* About */
    .about {
      padding: 60px;
    }
    .about h2 {
      font-weight: 1000;
    }
    .about p {
      font-size: 1.05rem;
      line-height: 1.6;
      color: #000;
    }

    /* Contact */
    .contact {
      background: #fff;
      padding: 80px 20px;
    }
    .contact .form-control {
      border-radius: 10px;
    }
    .contact button {
      border-radius: 10px;
      padding: 10px 25px;
    }

    /* Footer */
    footer {
      background: #1b5e20;
      color: #fff;
      text-align: center;
      padding: 15px;
    }

    /* Modal */
    .modal-content {
      border-radius: 15px;
    }
    .btn-submit {
      background-color: #1b5e20;
      border: none;
      color: #fff;
      width: 100%;
      border-radius: 8px;
      padding: 10px;
      font-weight: 500;
    }
    .btn-submit:hover {
      background-color: #1b5e20;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#"><i class="fa-solid fa-network-wired me-2"></i>LINYA</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li><a class="nav-link" href="#hero">Home</a></li>
          <li><a class="nav-link" href="#features">Features</a></li>
          <li><a class="nav-link" href="#about">About</a></li>
          <li><a class="nav-link" href="#contact">Contact</a></li>
          <li>
            <button class="btn btn-success ms-lg-3" data-bs-toggle="modal" data-bs-target="#loginModal">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Login
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero" id="hero">
    <div class="container" data-aos="fade-up">
      <h1>Welcome to LINYA</h1>
      <p>Live Interaction Navigation of Your Appointment — your smart queueing management system that simplifies appointments, enhances efficiency, and reduces waiting times.</p>
      <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
        Get Started
      </button>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features" id="features">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold text-success">Features</h2>
        <p class="text-muted">Experience a streamlined and interactive appointment process.</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4" data-aos="zoom-in">
          <div class="feature-box">
            <i class="fa-solid fa-clock"></i>
            <h5>Real-Time Queue Updates</h5>
            <p>Stay informed with live updates of your appointment status anytime, anywhere.</p>
          </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
          <div class="feature-box">
            <i class="fa-solid fa-users"></i>
            <h5>Multi-Office Management</h5>
            <p>Manage multiple departments or service points in one unified system.</p>
          </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
          <div class="feature-box">
            <i class="fa-solid fa-chart-line"></i>
            <h5>Analytics & Reports</h5>
            <p>Monitor performance and generate insightful data to improve operations.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="about" id="about">
    <div class="container" data-aos="fade-up">
      <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
          <img src="UCC.png" alt="LINYA Illustration" class="img-fluid" style="max-width: 500px;">
        </div>
        <div class="col-md-6">
          <h2>About LINYA</h2>
          <p>LINYA (Live Interaction Navigation of Your Appointment) is a queueing management solution designed to modernize how institutions handle appointments. It allows clients to book, monitor, and complete their services efficiently through an intelligent digital system that promotes organization and convenience.</p>
          <p>Developed to assist administrative offices, service centers, and institutions in delivering faster and more transparent service management.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact" id="contact">
    <div class="container" data-aos="fade-up">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-success">Contact Us</h2>
        <p class="text-muted">We’d love to hear from you! Reach out for questions, feedback, or collaboration.</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-md-8">
          <form>
            <div class="mb-3">
              <input type="text" class="form-control" placeholder="Your Name" required>
            </div>
            <div class="mb-3">
              <input type="email" class="form-control" placeholder="Your Email" required>
            </div>
            <div class="mb-3">
              <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
            </div>
            <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success px-5 py-2">
              Send Message
            </button>
          </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    © 2025 LINYA Queueing Management System | All Rights Reserved
  </footer>

  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-4" style="border-radius: 20px;">
        <div class="text-center mb-3">
          <i class="fa-solid fa-user-lock fa-3x text-success mb-2"></i>
          <h5 class="modal-title fw-bold" id="loginModalLabel">Login to LINYA</h5>
        </div>

        <div class="modal-body">
          <form id="loginForm" action="includes/index.php" method="POST" class="text-center">
            <div class="mb-3 text-start">
              <label for="username" class="form-label fw-semibold">Username</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="user_name" placeholder="Enter username" required>
              </div>
            </div>

            <div class="mb-3 text-start">
              <label for="password" class="form-label fw-semibold">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
              </div>
            </div>

            <div id="error" class="text-danger mb-2" style="display:none"></div>

            <button id="submitBtn" type="submit" class="btn btn-success w-100 rounded-pill py-2 mt-2">Login</button>
          </form>
        </div>

        <div class="modal-footer border-0 justify-content-center">
          <small>Don’t have an account? <a href="#" class="text-success">Sign up</a></small>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="../js/login.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000, once: true });
  </script>
</body>
</html>
