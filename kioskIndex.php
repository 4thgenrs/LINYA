<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kiosk - Welcome</title>
  <link rel="stylesheet" href="css/kioskIndex.css">
</head>
<body>

  <!-- VIDEO AD SCREEN (Shows first) -->
  <div id="adScreen">
    <div class="ad-container">
      <video id="adVideo" autoplay muted loop playsinline>
        <source src="resources/kiosk_bg.mp4" type="video/mp4">
      </video>
      <div class="tap-to-start">
        <p>Tap anywhere to start</p>
        <div class="pulse-dot"></div>
      </div>
    </div>
  </div>

  <!-- GET TICKET SCREEN (Shows after video tap) -->
  <div id="getTicketScreen" style="display: none;">
    <!-- Get Ticket button -->
    <button class="main-btn" id="getTicketBtn">🎟 Get Ticket</button>
    
    <!-- Options with instructions -->
    <div class="options" id="options" style="display: none;">
      <button class="option-btn" id="newUserBtn">New User</button>
      <button class="option-btn" id="existingUserBtn">Existing User</button>
      <div class="instructions">
        <p><strong>New User</strong> – Select this if it's your first time and you need to register.</p>
        <p><strong>Existing User</strong> – Select this if you already have a record in the system.</p>
      </div>
    </div>
    
    <!-- Running ticker -->
    <div class="ticker">
      <div class="ticker-text">
        Reminder: Please get your ticket before approaching the counter. • Thank you for your cooperation.
      </div>
    </div>
  </div>

  <script src="js/kioskIndex.js"></script>
</body>
</html>