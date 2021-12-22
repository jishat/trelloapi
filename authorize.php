<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bootstrap.php');
use App\Trello\Trello;
use App\Session\Session;


if(isset($_POST['apiKey'], $_POST['secretKey'])){
    session::sessionSet("apiKey", $_POST['apiKey']);
    session::sessionSet("secretKey", $_POST['secretKey']);

    $trello = new Trello($_POST['apiKey'], $_POST['secretKey']);
    session::sessionSet("apiKey", $_POST['apiKey']);
    session::sessionSet("secretKey", $_POST['secretKey']);
    $trello->authorization();

}elseif(isset($_GET['oauth_verifier'], $_SESSION['oauth_token_secret'])){
    $trello = new Trello(session::sessionGet('apiKey'), session::sessionGet('secretKey'));
    $trello->getToken();
    header('location:'.WEBROOT.'organizations');
}else{
    echo "404 not found";
}