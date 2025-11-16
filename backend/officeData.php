<?php
include('../linya_db/config.php');

// Only run POST handling when this file is requested directly,
// not when it's included by an "includes" page.
//
// This lets includes/office.php include this file to get helper functions
// without executing the POST logic (which would call exit/send headers).
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_office'])) {
            $code = trim($_POST['office_code']);
            $desc = trim($_POST['office_description']);

            if ($code !== '' && $desc !== '') {
                // validate duplicates (office_code or office_description)
                $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM offices WHERE office_code = ? OR office_description = ?");
                if ($chk) {
                    $chk->bind_param('ss', $code, $desc);
                    $chk->execute();
                    $cres = $chk->get_result();
                    $cnt = 0;
                    if ($cres && ($crow = $cres->fetch_assoc())) $cnt = intval($crow['cnt']);
                    $chk->close();

                    if ($cnt > 0) {
                        header("Location: ../includes/office.php?error=duplicate_office");
                        exit;
                    }
                }

                addOffice($conn, $code, $desc);
            }
            header("Location: ../includes/office.php");
            exit;
        }

        if (isset($_POST['update_office'])) {
            $id = intval($_POST['office_id']);
            $code = trim($_POST['office_code']);
            $desc = trim($_POST['office_description']);
            // validate duplicates on update: don't allow code/description that matches another office
            $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM offices WHERE (office_code = ? OR office_description = ?) AND office_id != ?");
            if ($chk) {
                $chk->bind_param('ssi', $code, $desc, $id);
                $chk->execute();
                $cres = $chk->get_result();
                $cnt = 0;
                if ($cres && ($crow = $cres->fetch_assoc())) $cnt = intval($crow['cnt']);
                $chk->close();

                if ($cnt > 0) {
                    header("Location: ../includes/office.php?error=duplicate_office");
                    exit;
                }
            }

            updateOffice($conn, $id, $code, $desc);
            header("Location: ../includes/office.php");
            exit;
        }

        // TOGGLE enable/disable (replaces delete)
        if (isset($_POST['toggle_office'])) {
            $id = intval($_POST['office_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare("SELECT enabled FROM offices WHERE office_id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $current = 1;
                    if ($res && $row = $res->fetch_assoc()) {
                        $current = intval($row['enabled']);
                    }
                    $stmt->close();

                    $new = $current ? 0 : 1;
                    $u = $conn->prepare("UPDATE offices SET enabled = ? WHERE office_id = ?");
                    if ($u) {
                        $u->bind_param("ii", $new, $id);
                        $u->execute();
                        $u->close();
                    }
                }
            }
            header("Location: ../includes/office.php");
            exit;
        }

        // Delete Office (the existing logic) — after deleting redirect back to includes/office.php
        if (isset($_POST['delete_office'])) {
            $id = intval($_POST['office_id'] ?? 0);
            $null = null;
            if ($id > 0) {
                //check for active queues before deleting
                $checkStmt = $conn->prepare("SELECT COUNT(*) AS active_count FROM queues WHERE office_id = ?");
                if ($checkStmt) {           
                    $checkStmt->bind_param("i", $id);
                    $checkStmt->execute();
                    $checkRes = $checkStmt->get_result();
                        if ($checkRes && $row = $checkRes->fetch_assoc()) {
                            if (intval($row['active_count']) > 0) {
                                // There are active queues, do not delete
                                $checkStmt->close();
                                header("Location: ../includes/office.php?error=active_queues");
                                exit;
                            }
                        }
                    $checkStmt->close();
                }

                //Delete Services first
                $serviceStmt = $conn->prepare("DELETE FROM services WHERE office = ?");
                if ($serviceStmt) {
                    $serviceStmt->bind_param("i", $id);
                    $serviceStmt->execute();
                    $serviceStmt->close();
                }


                //Unassign Office from Accounts (set to NULL)
                $accountStmt = $conn->prepare("UPDATE accounts SET office = NULL WHERE office = ?");
                if ($accountStmt) {
                    $accountStmt->bind_param("i", $id);
                    $accountStmt->execute();
                    $accountStmt->close();
                }

                // Preserve finished_queues.office_id (history). Do NOT clear finished queue office references.

                // Unassign office from office_windows by setting office_id to NULL
                // Delete all windows that belong to this office
                $officeWindowStmt = $conn->prepare("DELETE FROM office_windows WHERE office_id = ?");
                if ($officeWindowStmt) {
                    $officeWindowStmt->bind_param("i", $id);
                    $officeWindowStmt->execute();
                    $officeWindowStmt->close();
                }

                $officeCounterStmt = $conn->prepare("DELETE FROM queue_counters WHERE office_id = ?");
                if ($officeCounterStmt) {
                    $officeCounterStmt->bind_param("i", $id);
                    $officeCounterStmt->execute();
                    $officeCounterStmt->close();
                }

                //Delete Office latter
                $officeStmt = $conn->prepare("DELETE FROM offices WHERE office_id = ?");
                if ($officeStmt) {
                    $officeStmt->bind_param("i", $id);
                    $officeStmt->execute();
                    $officeStmt->close();
                }
            }
            header("Location: ../includes/office.php");
            exit;
        }
    }
}

// Fetch all offices after processing
$offices = getOfficeData($conn);


// ======================
// 🧩 FUNCTIONS
// ======================

function getOfficeData($conn) {
    $data = [];
    // Note: ensure offices table has 'enabled' column (see provided ALTER TABLE)
    $sql = "SELECT office_id, office_code, office_description, enabled FROM offices ORDER BY office_id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function addOffice($conn, $code, $description) {
    $stmt = $conn->prepare("INSERT INTO offices (office_code, office_description) VALUES (?, ?)");
    $stmt->bind_param("ss", $code, $description);
    return $stmt->execute();
}

function updateOffice($conn, $id, $code, $description) {
    $stmt = $conn->prepare("UPDATE offices SET office_code = ?, office_description = ? WHERE office_id = ?");
    $stmt->bind_param("ssi", $code, $description, $id);
    return $stmt->execute();
}

function deleteOffice($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM offices WHERE office_id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
