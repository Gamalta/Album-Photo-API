<?php
class User {

    function getPlayersAccounts($db){

        $stmt = $db->query("SELECT email, uuid, username, password FROM user");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('users' => $result));
        $stmt -> closeCursor();
    }

    function getPlayerAccount($db, $key){

        $stmt = $db->prepare("SELECT email, uuid, username, password FROM user WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $key, 'email' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($result);
        $stmt -> closeCursor();
    }
}
?>