<?php

require_once("./database.php");
require_once("./user.php");
require_once("./picture.php");
require_once("./tag.php");

Database::initDatabase();
$db = Database::getDatabase();
$user = new User();
$picture = new Picture();
$tag = new Tag();
$logged = false;

try {

    $request_method = $_SERVER["REQUEST_METHOD"];
    if(!empty($_GET['request'])){

        $url = explode("/", filter_var($_GET['request'], FILTER_SANITIZE_URL));

        $stmt = $db->query("SELECT email, uuid, username, password FROM user");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo "API- Erreur de connexion";
            exit;
        } else {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            
            if($url[0] == "user" && $request_method == 'GET'){
                foreach($results as $result){
                    if(($username == $result['email'] || $username == $result['username']) && password_verify($password, $result['password'])){
                        $logged = true;
                    }
                }
            } else {
                foreach($results as $result){
                    if(($username == $result['email'] || $username == $result['username']) && $password == $result['password']){
                        $logged = true;
                    }
                }
            }
        }

        if($logged){
            switch($url[0]){
                case "user":
                    if($request_method == 'GET' && !empty($url[1])){
                        $user->getPlayerAccount($db, $url[1]);
                    }
                    break;
                case "users":
                    //save.redblock.fr/api/users
    
                    switch($request_method){
                        case 'GET':
                            //get user
                            $user->getPlayersAccounts($db);
                            break;
                        case 'POST':
                            //add user
                            break;
                        case 'PUT':
                            //edit user
                            break;
                        case 'DELETE':
                            //remove user
                            break;
                        default:
                            throw new Exception("API- Mauvaise méthode utilisé");
                    }
                    break;
    
                case "tags":
                    //save.redblock.fr/api/tags
    
                    switch($request_method){
                        case 'GET':
                            //get tag
                            if(empty($url[1])){
                                $tag->getTags($db);
                            } else {
                                $tag->getTagsByCategorie($db, $url[1]);
                            }
                            break;
                        case 'POST':
                            //add tag
                            break;
                        case 'PUT':
                            //edit tag
                            break;
                        case 'DELETE':
                            //remove tag
                            break;
                        default:
                            throw new Exception("API- Mauvaise méthode utilisé");
                    }
    
                    break;
                case "pictures":
                    //save.redblock.fr/api/pictures
    
                    switch($request_method){
                        case 'GET':
                            //get picture(s)
                            $picture->getPicturesArray($db);
                            break;
                        case 'POST':
                            //add picture
                            $picture->addPicture($db, json_decode(file_get_contents("php://input"), true));
                            break;
                        case 'PUT':
                            //edit picture
                            break;
                        case 'DELETE':
                            //remove picture
                            $picture->removePicture($db);
                            break;
                        default:
                            throw new Exception("API- Mauvaise méthode utilisé");
                    }
                    break;
                default: throw new Exception("API- La demande n'est pas valide, vérifiez l'url.");
            }
        } else {
            throw new Exception("API- La connexion à échoué.");
        }
    } else {
        throw new Exception("API- Problème de récupération de données.");
    }
} catch(Exception $e){
    $erreur = [
        "message" => $e->getMessage(),
        "code" => $e->getCode()
    ];
    print_r($erreur);
}
?>