<?php include '../linya_db/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kiosk</title>
    <link rel="stylesheet" href="../css/kioskMenu.css">
</head>
<body>

<!-- Visitor Information Form -->
<div class="visitor-screen" id="visitorForm">
  <div class="kiosk-container">
    <div class="header-row">
      <h1 class="title">User Information</h1>
    </div>
    
    <div class="vf-field">
      <label class="vf-label">Full Name</label>
      <input type="text" id="visitorName" placeholder="Enter your full name" required>
    </div>
    
    <div class="vf-field">
      <label class="vf-label">Phone Number</label>
      <input type="tel" id="visitorPhone" placeholder="09XXXXXXXXX" required>
      <div class="vf-hint muted small" id="visitorPhoneHint" style="display:none;">
        Phone must be 11 digits starting with 09
      </div>
    </div>
    
    <div class="vf-field">
      <label class="vf-label">Email Address (Optional)</label>
      <input type="email" id="visitorEmail" placeholder="your.email@example.com">
      <div class="vf-hint muted small" id="visitorEmailHint" style="display:none;">
        Please enter a valid email address
      </div>
    </div>
    
    <div class="service-actions">
      <button class="primary-btn" id="visitorProceed" disabled>Done</button>
    </div>
  </div>
</div>

<!-- Office Selection Section -->
<div id="officeSection" style="display:none;">
  <div class="kiosk-container">
    <div class="header-row">
      <div class="title-icon">
        🏢
      </div>
      <h1 class="title">Select Office</h1>
      <div class="subtitle">
        <span id="subtitle" style="color: black;">
          Please choose which department you need to visit.
        </span>  
      </div>
    </div>
    
    <div class="grid" id="officeGrid">
      <button class="office-btn" onclick="loadServices(1, 'Registrar')">
        Registrar
      </button>
      <button class="office-btn" onclick="loadServices(2, 'Accounting')">
        Accounting
      </button>
      <button class="office-btn" onclick="loadServices(3, 'MIS')">
        MIS
      </button>
    </div>
  </div>
</div>

<!-- Service Selection Section -->
<div id="serviceSection" style="display:none;">
  <div class="kiosk-container">
    <div class="header-row">
      <button class="ghost-btn" id="backBtn" onclick="goBack()">×</button>
      <h1 class="title" id="serviceTitle">Services</h1>
      <div class="subtitle">
        Tap a service to select and press Confirm to continue. Check the PDF box after selecting a service to request a soft copy.
      </div>
    </div>
    
    <div class="controls-row">
      <span class="muted small" id="selectedCount">0 selected</span>
      <button id="multiSelectToggle">Select multiple</button>
    </div>
    
    <div class="cards">
      <div class="grid" id="serviceGrid">
        <!-- Services will be loaded here dynamically -->
      </div>
    </div>
    
    <div class="service-actions" id="serviceActions">
      <button class="primary-btn" id="confirmBtn" disabled>Confirm Selection</button>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="vf-modal" id="visitorConfirmModal" style="display:none;">
  <div class="vf-modal-card">
    <div class="title-icon">👤</div>
    <h3 class="title">PLEASE CONFIRM YOUR INFORMATION</h3>
    <div class="vf-confirm-row">
      <strong>Name:</strong> <span id="confirmName">-</span>
    </div>
    <div class="vf-confirm-row">
      <strong>Phone:</strong> <span id="confirmPhone">-</span>
    </div>
    <div class="vf-confirm-row">
      <strong>Email:</strong> <span id="confirmEmail">-</span>
    </div>
    
    <div class="confirmation-question">
      <p>Is this information correct?</p>
    </div>
    
    <div class="service-actions">
      <button class="secondary-btn" id="visitorCancel">Cancel</button>
      <button class="primary-btn" id="visitorConfirm">Confirm</button>
    </div>
  </div>
</div>

<!-- Running Ticker -->
<div class="ticker">
  <div class="ticker-text" id="tickerText">
Welcome new user! • Please fill in your details above accurately • Thank you for registering!
  </div>
</div>

<script src="../js/kioskMenu.js"></script>
</body>
</html>