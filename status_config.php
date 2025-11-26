<?php
function getStatusIds($conn) {
    $statusMap = [];
    $sql = "SELECT idStatus, status FROM tb_statuspedido";
    $result = $conn->query($sql);
    
    while($row = $result->fetch_assoc()) {
        $statusMap[strtolower($row['status'])] = $row['idStatus'];
    }
    
    return $statusMap;
}

function getStatusInfo($conn) {
    $statusInfo = [];
    $sql = "SELECT idStatus, status, descricao FROM tb_statuspedido ORDER BY idStatus";
    $result = $conn->query($sql);
    
    while($row = $result->fetch_assoc()) {
        $statusInfo[$row['idStatus']] = $row;
    }
    
    return $statusInfo;
}
?>