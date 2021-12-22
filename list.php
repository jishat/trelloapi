<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bootstrap.php');
use App\Trello\Trello;
use App\Session\Session;

// Decare all needed session
$key = Session::sessionGet('apiKey');
$secret = Session::sessionGet('secretKey');
$tkn = Session::sessionGet('tkn');
$success = Session::sessionGet('success'); 
$error = Session::sessionGet('error');

$trello = new Trello($key, $secret);


/**
 * If post for create
 * a new List
 * 
 */
if(isset($_POST['addList']) && count($_POST) > 1 && isset($tkn)){
    $url     = "https://api.trello.com/1/lists";
    $data = array(
        'name' => $_POST['name'],
        'idBoard' => $_POST['idBoard']
    );
    $result = $trello->action("POST", $data, $url);

    if($result !== FALSE){
        Session::sessionSet("success", "Successfully! Create a List");
    }else{
        Session::sessionSet("error", "Fail! Something Went Wrong");
    }
    header('Location: '.$_SERVER['REQUEST_URI']);
}


/**
 * If post for create
 * a new Card
 * 
 */
if(isset($_POST['addCard']) && count($_POST) > 1 && isset($tkn)){
    $url     = "https://api.trello.com/1/cards";
    $data = array(
        'idList' => $_POST['idList'],
        'name' => $_POST['name'],        
        'desc' => $_POST['desc']
    );
    $result = $trello->action("POST", $data, $url);

    if($result !== FALSE){
        Session::sessionSet("success", "Successfully! Create a card");
    }else{
        Session::sessionSet("error", "Fail! Something Went Wrong");
    }
    header('Location: '.$_SERVER['REQUEST_URI']);
}

/**
 * Get all list
 * of a board
 * 
 */
if(isset($tkn) && $tkn !== "" && isset($_GET['id'])){

    //Get all list of an board
    $url     = "https://api.trello.com/1/boards/".$_GET['id']."/lists";
    $data = $trello->getData($url);

    //Get a board info
    $url     = "https://api.trello.com/1/boards/".$_GET['id']; 
    $singleData = $trello->getData($url);

    if($data === false){
        echo "404! Not Found";
    }else{ 
        include_once(INC.'header.php'); 


/**
 * All Success & Error
 * Message will be show
 * 
 */?>
<div aria-live="polite" aria-atomic="true" class="bg-dark position-relative bd-example-toasts">
    <div class="toast-container position-absolute p-3 top-0 end-0" id="toastPlacement">
    <?php
    if(isset($success) && !empty($success)){?>
        <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"> <?= $success?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php
    unset($_SESSION['success']);
    }
    if(isset($error) && !empty($error)){ 
    ?>
        <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><?= $error?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php
    unset($_SESSION['error']);
    }
    
    ?>
    </div>
</div> 

<?php
/**
 * All Success & Error
 * Message will be show
 * 
 */?>
<section class="homeSection pt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
                <h3 class="headline">List of <strong><?= $singleData['name']?></strong></h3>
                <form action="" method="post">
                    <div class="input-group mb-3 listInput">
                        <input type="hidden" name="idBoard" value="<?= $_GET['id']?>">
                        <input type="text" name="name" class="form-control" placeholder="Enter List Name" aria-label="Enter List name" aria-describedby="button-addon2">
                        <button class="btn btn-dark" name="addList" type="submit" id="button-addon2">+ Add List</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            
            <?php
            foreach($data as $d){
                $url   = "https://api.trello.com/1/lists/".$d['id']."/cards";
                $cards = $trello->getData($url)
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= $d['name']?></h5>
                            <div>
                                <?php
                                if($cards){
                                    foreach($cards as $eachCard){ ?>

                                        <a href="javascript:void(0)" class="eachCard" 
                                            title="<?= $eachCard['name']?>" 
                                            data-bs-toggle="popover" 
                                            data-bs-trigger="focus" 
                                            data-bs-content="<?= !empty($eachCard['desc']) ? $eachCard['desc'] : 'No Desc' ?>">
                                            <?= $eachCard['name']?></a>
                                    <?
                                    }
                                }
                                ?>
                            </div>
                            <a  class="mt-4 btn btn-sm btn-success d-block" data-bs-toggle="collapse" href="#<?= str_replace(" ","-",$d['name'])?>" role="button" aria-expanded="false" aria-controls="<?= str_replace(" ","-",$d['name'])?>">+ Add Card</a>
                            <div class="collapse" id="<?= str_replace(" ","-",$d['name'])?>">
                                <div class="card card-body addCardBody">
                                    <form action="" method="post">
                                        <div>
                                            <input type="hidden" name="idList" value="<?= $d['id']?>">
                                            <input type="text" name="name" required class="form-control form-control-sm" placeholder="Enter Card Title">
                                            <textarea name="desc" required class="form-control form-control-sm" placeholder="Enter Card description"></textarea>
                                            <button class="btn btn-sm btn-secondary mt-2 d-block" type="submit" name="addCard">Submit Card</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            
        </div>
    </div>
</section>

        <?php include_once(INC.'footer.php');
    }
}else{
    echo "404 Not found";
}
?>
