<?php
include('../linya_db/config.php');

function getAccountsData($conn) {
    $data = [];
    $sql = "SELECT A.user_id, A.user_name, A.password, A.name, R.role_name, O.office_description     FROM accounts A INNER JOIN offices O on A.office = O.office_id INNER JOIN roles R on A.user_type = R.role_id  "; // change table name if different
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}