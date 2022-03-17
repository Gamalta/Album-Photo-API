<?php
class Picture {
    static $dateFormat = "Y-m-d";
    static $reponseHeader = "Content-Type: application/json";

    function addPicture($db, $jsonObject){

        $uuid = $jsonObject["uuid"];
        $image = $jsonObject["image"];
        $author = $jsonObject["author"];
        $timesTamp = $jsonObject["date"];
        $tags = $jsonObject["tags"];

        $date = new DateTime();
        $date->setTimestamp($timesTamp/1000);
        $date->setTimezone(new DateTimeZone("Europe/Paris"));

        $location = "images/".$date->format('Y/m/d');
        $imgPath = $location.'/'.$uuid;

        if(!is_dir($location)) {
            mkdir($location, 0777, true);
        }
        $result = file_put_contents($imgPath.".jpg", base64_decode($image));

        $sourceImage = imagecreatefromjpeg($imgPath.".jpg");
        $orgWidth = imagesx($sourceImage);
        $orgHeight = imagesy($sourceImage);
        $thumbHeight = floor($orgHeight * (128 / $orgWidth));
        $destImage = imagecreatetruecolor(128, $thumbHeight);
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, 128, $thumbHeight, $orgWidth, $orgHeight);
        imagejpeg($destImage, $imgPath.".ico");
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        if($result){
            
            $stmt = $db->prepare("INSERT INTO picture(uuid, location, author, date) VALUES (:uuid, :location, :author, :date)");
            $stmt->execute(['uuid' => $uuid, 'location' => $location, 'author' => $author, 'date' => $date->format(self::$dateFormat)]);
            $stmt -> closeCursor();

            $stmt = $db->prepare("INSERT INTO picture_tag(picture, tag, tag_category) VALUES (:picture, :tag, :tag_category)");
            foreach($tags as $tag){
                $stmt->execute(['picture' => $uuid, 'tag' => $tag["name"], 'tag_category' => $tag["category"]]);
            }
            $stmt -> closeCursor();

            
						
        } else {

            throw new Exception("API- L'enregistrement de la photo a échoué");
        }
    }

    function getPicturesArray($db){

        if(isset($_GET['uuid'])){
            $uuid = $_GET['uuid'];
            $realUuid = substr($uuid, strpos($uuid, ":")+1);
            if(substr($uuid, 0, 4) === "jpg:"){-
                self::getPictureByUniqueId($db, $realUuid);
            }else if(substr($uuid, 0, 4) === "ico:"){
                self::getMiniatureByUniqueId($db, $realUuid);
            }
            return;
        }
        
        $condition = array();

        if(isset($_GET['tags'])){
            array_push($condition, "picture_tag.tag IN (".implode(',', $_GET['tags']).')');

        }
        if(isset($_GET['dates'])){
            $dates = array();
            $date = new DateTime();
            $date->setTimezone(new DateTimeZone("Europe/Paris"));
            foreach($_GET['dates'] as $timesTamp){

                $date->setTimestamp($timesTamp/1000);
                array_push($dates, $date->format(self::$dateFormat));
            } 
            array_push($condition, "picture.date IN ('".implode("','", $dates)."')");

        }
        if(isset($_GET['section'])){
            $startDate = new DateTime();
            $startDate->setTimezone(new DateTimeZone("Europe/Paris"));
            $startDate->setTimestamp($_GET['section'][0]/1000);
            $endDate = new DateTime();
            $endDate->setTimezone(new DateTimeZone("Europe/Paris"));
            $endDate->setTimestamp($_GET['section'][1]/1000);
            array_push($condition, "picture.date >= " .$startDate->format(self::$dateFormat)." and picture.date <= ".$endDate->format(self::$dateFormat));

        }
        $stmt = null;
        if(empty($condition)){
            $stmt = $db->query("SELECT uuid from picture");
        } else {
            $stmt = $db->query("SELECT uuid from picture inner join picture_tag on picture.uuid = picture_tag.picture where ". implode(' and ', $condition));
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header(self::$reponseHeader);
        echo json_encode(array('pictures' => $result));
        $stmt -> closeCursor();
    }

    function getMiniatureByUniqueId($db, $uuid){
        $stmt = $db->query("SELECT * from picture where uuid = '$uuid'");
        $result = $stmt->fetch();
        $stmt -> closeCursor();
        $img = file_get_contents($result['location']."/$uuid".".ico");
        $tags = [];
        $stmt = $db->query("SELECT tag from picture_tag where picture = '$uuid'");
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt -> closeCursor();

        $date = new DateTime($result['date']);
        $date->format("U");

        $json = array(
            "uuid"=> $result['uuid'],
            "image"=> base64_encode($img),
            "tags"=> json_encode($tags),
            "date"=> $date->getTimestamp(),
            "author"=> $result['author']
        );
        echo json_encode($json);
    }

    function getPictureByUniqueId($db, $uuid){

        $stmt = $db->query("SELECT * from picture where uuid = '$uuid'");
        $result = $stmt->fetch();
        $stmt -> closeCursor();
        $img = file_get_contents($result['location']."/$uuid".".jpg");
        $tags = [];
        $stmt = $db->query("SELECT tag from picture_tag where picture = '$uuid'");
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt -> closeCursor();

        $date = new DateTime($result['date']);
        $date->format("U");

        $json = array(
            "uuid"=> $result['uuid'],
            "image"=> base64_encode($img),
            "tags"=> json_encode($tags),
            "date"=> $date->getTimestamp(),
            "author"=> $result['author']
        );
        echo json_encode($json);
    }

    function removePicture($db){

        if(isset($_GET['uuid'])){

            $uuid = $_GET['uuid'];

            $stmt = $db->query("SELECT * FROM picture WHERE uuid='$uuid'");
            $result = $stmt->fetch();
            $stmt -> closeCursor();
            unlink($result['location']."/$uuid".".jpg");
            unlink($result['location']."/$uuid".".ico");
            $db->query("DELETE FROM picture WHERE uuid='$uuid'");

        } else {
            throw new Exception("API- Suppresion d'image invalide uuid");
        }
    }
}
?>