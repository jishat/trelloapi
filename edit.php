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
 * If post for update a
 * board
 * 
 */
if(isset($_POST['editBoard']) && count($_POST) > 1 && isset($tkn)){
    $url     = "https://api.trello.com/1/boards/".$_GET['id'];
    $data = array(
        'id' => $_POST['id'],
        'name' => $_POST['name'],
        'desc' => $_POST['desc']
    );
    $result = $trello->action("PUT", $data, $url);

    if($result !== FALSE){
        Session::sessionSet("success", "Successfully! Updated board");
    }else{

        Session::sessionSet("error", "Fail! Something Went Wrong");
    }
    header('Location: '.$_SERVER['REQUEST_URI']);
} 

/**
 * Show the inoformation 
 * of a board
 * 
 */
if(isset($tkn) && $tkn !== "" && isset($_GET['id'])){

    //Get a board info
    $url     = "https://api.trello.com/1/boards/".$_GET['id']; 
    $singleData = $trello->getData($url);

    if($singleData === false){
        echo "404! Data not found";
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
 * Edit html section
 * 
 */?>
<section class="homeSection">
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                
                <form action="" class="homeForm" method="post">
                    <input type="hidden" name="id" value="<?= $singleData['id']?>">
                    <h4 class="text-center mb-3">Edit Board</h4>
                    <div class="mb-3">
                        <label for="name" class="form-label">Board Name</label>
                        <input type="text" value="<?= $singleData['name']?>" name="name" required class="form-control" id="name" placeholder="Enter Board Name">
                    </div>
                    <div>
                        <label for="desc"  class="form-label">Description</label>
                        <textarea name="desc" class="form-control" placeholder="Enter description" id="desc"><?= $singleData['desc']?></textarea>
                    </div>
                    <button type="submit" name="editBoard" class="btn btn-primary mt-3">Submit</button>
                </form>
            </div>
        </div>
    </div>
</section>


        <?php include_once(INC.'footer.php');
    }
}else{
    echo "404 Not found";
}
?>
