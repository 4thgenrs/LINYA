<?php
include('../linya_db/config.php');

function getRegistrarQueue($conn) {
    $data = [];
    $sql = "SELECT Q.queue_id, Q.queue_number, Q.date_created, S.service_description, O.office_description 
            FROM queue Q 
            INNER JOIN services S ON Q.service_id = S.service_id 
            INNER JOIN offices O ON S.office = O.office_id 
            WHERE O.office_description = 'Registrar'
            ORDER BY Q.date_created DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $registrar_data[] = $row;
        }
    }
    return $registrar_data;

}