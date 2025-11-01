<?php

include('../backend/queueData.php');

// Handle AJAX actions directly from this file so we don't need an extra endpoint file.
if (isset($_REQUEST['action'])) {
  header('Content-Type: application/json');
  $window = isset($_REQUEST['window']) ? intval($_REQUEST['window']) : 1;
  
  if ($_REQUEST['action'] === 'next') {
    $office = isset($_REQUEST['office']) ? intval($_REQUEST['office']) : 1;
    $res = getNextLine($conn, $office, $window);
    if ($res) {
      echo json_encode(['success' => true, 'data' => $res]);
    } else {
      echo json_encode(['success' => false, 'message' => 'No waiting queue item to call.']);
    }
  }
  else if ($_REQUEST['action'] === 'finish') {
    $res = finishCurrentQueue($conn, $window);
    if ($res) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to finish the current queue.']);
    }
  }
  else if ($_REQUEST['action'] === 'break') {
    $isBreak = isset($_REQUEST['isBreak']) ? filter_var($_REQUEST['isBreak'], FILTER_VALIDATE_BOOLEAN) : true;
    $res = setWindowBreak($conn, $window, $isBreak);
    if ($res) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to update break status.']);
    }
  }
  exit;
}

$queue_data = getOfficeQueue($conn, 1); // Get queue for office ID 1
$display_on_air = displayOnAir($conn, 1); // Display ongoing for window 1

//NOTE: Sample window and office for buttons
$sample_window = 2;
$sample_office = 2;
?>

  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Queue Window with List</title>
    <link rel="stylesheet" href="../css/queueSelect.css" />
  </head>
  <body>
    <div class="wrap">

        <div class="box" role="group" aria-label="Window Control" data-has-ongoing="<?php echo is_array($display_on_air) ? 'true' : 'false'; ?>">
        <div id="window" class="window" aria-live="polite">Window 1</div>
        <div id="number" class="number">
          <?php 
            if (isset($_SESSION['window_1_break']) && $_SESSION['window_1_break']) {
              echo 'On Break';
            } else {
              echo is_array($display_on_air) ? $display_on_air['queue_code'] : 'Waiting';
            }
          ?>
        </div>
        <div><i>
          <?php echo is_array($display_on_air) ? 'Ongoing' : ''; ?>
        </i></div>
        
        <div class="controls">
          <!-- Numbers in parameters are for session -->
          <button id="finished" type="button" onclick="finishCurrent(1,1)" <?php echo !is_array($display_on_air) ? 'disabled' : ''; ?>>Finished</button>
          <button id="onBreak" type="button" onclick="toggleBreak(1,1)" data-break="false">Break</button>
          <button id="nextQueue" type="button" onclick="callNext(1,1)" <?php echo is_array($display_on_air) ? 'disabled' : ''; ?>>Call Next</button>
        </div>
      </div>

      <div class="queue-box" aria-label="Queue List">
        <div class="queue-title">Queue List</div>
        <?php foreach ($queue_data as $qd) { ?>
              <div class="queue-item"><?php echo $qd['queue_code']; ?></div>
        <?php } ?>
      </div>
    </div>

    <script>
      // Toggle break status for the window
      function toggleBreak(windowNumber) {
        var breakBtn = document.getElementById('onBreak');
        var isBreak = breakBtn.dataset.break !== 'true'; // Toggle the state
        
        var url = '?action=break&window=' + encodeURIComponent(windowNumber) + '&isBreak=' + isBreak;
        fetch(url, { credentials: 'same-origin' })
          .then(function(response) { 
            return response.json();
          })
          .then(function(data) {
            if (data && data.success) {
              // Update break button state
              breakBtn.dataset.break = isBreak;
              breakBtn.textContent = isBreak ? 'Resume' : 'Break';
              
              if (isBreak) {
                // Going on break
                var numberEl = document.getElementById('number');
                if (numberEl) numberEl.textContent = 'On Break';

                // Clear the Ongoing text
                var ongoingI = document.querySelector('.box i');
                if (ongoingI) ongoingI.textContent = '';

                // Disable other buttons during break
                document.getElementById('finished').disabled = true;
                document.getElementById('nextQueue').disabled = true;
                
                // Set break visual state
                document.querySelector('.box').dataset.hasOngoing = 'false';
              } else {
                // Resuming from break
                var numberEl = document.getElementById('number');
                if (numberEl) numberEl.textContent = 'Waiting';

                // Enable Next button when resuming
                document.getElementById('nextQueue').disabled = false;
                
                // Clear break visual state
                document.querySelector('.box').dataset.hasOngoing = 'false';
              }
              window.location.reload();
            } else {
              alert((data && data.message) ? data.message : 'Failed to update break status');
              
            }
          })
          .catch(function(err) {
            console.error('Error updating break status:', err);
            alert('Error updating break status. See console for details. 0');
          });
      }

      // Mark the current queue as finished
      function finishCurrent(windowNumber) {
        var url = '?action=finish&window=' + encodeURIComponent(windowNumber);
        fetch(url, { credentials: 'same-origin' })
          .then(function(response) { 
            return response.json();
          })
          .then(function(data) {
            if (data && data.success) {
              // Update the display to show no current queue
              var numberEl = document.getElementById('number');
              if (numberEl) numberEl.textContent = 'Waiting';

              // Clear the Ongoing text
              var ongoingI = document.querySelector('.box i');
              if (ongoingI) ongoingI.textContent = '';

              // Update button states
              document.querySelector('.box').dataset.hasOngoing = 'false';
              document.getElementById('finished').disabled = true;
              document.getElementById('onBreak').disabled = false;
              document.getElementById('nextQueue').disabled = false;

              // Reload to refresh the queue list
              window.location.reload();
            } else {
              alert((data && data.message) ? data.message : 'Failed to finish the current queue');
            }
          })
          .catch(function(err) {
            console.error('Error finishing queue:', err);
            alert('Error finishing queue. See console for details. 1');
          });
      }

      // Call the backend endpoint to get the next queue number for a window
      function callNext(windowNumber,officeNumber) {
        var url = '?action=next&window=' + encodeURIComponent(windowNumber) + '&office=' + encodeURIComponent(officeNumber);
        fetch(url, { credentials: 'same-origin' })
          .then(function(response) { 
            return response.json(); // Parse JSON directly
          })
          .then(function(data) {
            if (data && data.success) {
              var code = data.data.queue_code || data.data.queueCode || data.data.number;
              // Update the on-air number/code
              var numberEl = document.getElementById('number');
              if (numberEl) numberEl.textContent = code;

              // Show Ongoing text
              var ongoingI = document.querySelector('.box i');
              if (ongoingI) ongoingI.textContent = 'Ongoing';

              // Update button states
              document.querySelector('.box').dataset.hasOngoing = 'true';
              document.getElementById('finished').disabled = false;
              document.getElementById('onBreak').disabled = true;
              document.getElementById('nextQueue').disabled = true;

              // Reload the page to refresh the queue list
              // This ensures we see the accurate queue state
              window.location.reload();
            } else {
              alert((data && data.message) ? data.message : 'No next queue available');
            }
          })
          .catch(function(err) {
            console.error('Error calling next:', err);
            alert('Error calling next queue. See console for details. 2');
          });
      }
    </script>
  </body>
  </html>