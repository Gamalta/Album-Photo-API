<?php
class Tag {
    
    function getTags($db){
        
        $stmt = $db->query("SELECT name, color, creator, createdAt, category from tag");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('tags' => $result));
        $stmt -> closeCursor();
    }
    
    function getTagsByCategorie($db, $categoyName){

        $stmt = $db->prepare("SELECT tag.name, tag.color, tag.creator, tag.createdAt, tag_category.name as 'category' from tag inner join tag_category on tag.category = tag_category.name where tag_category.name = :name");
        $stmt->execute(['name' => $categoyName]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode(array('tags' => $result));
        $stmt -> closeCursor();
    }
}
?>