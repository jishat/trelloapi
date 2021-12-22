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
 * If post for delete
 * a board
 * 
 */
if(isset($_POST['deleteBoard']) && count($_POST) > 1 && isset($tkn)){
    $url     = "https://api.trello.com/1/boards/".$_POST['id'];
    $data = array();
    $result = $trello->action("DELETE", $data, $url);

    if($result !== FALSE){
        Session::sessionSet("success", "Successfully! Delete a board");
    }else{

        Session::sessionSet("error", "Fail! Something Went Wrong");
    }
    header('Location: '.$_SERVER['REQUEST_URI']);
}

/**
 * If post a new board
 * then occur this condition
 * 
 */
if(isset($_POST['addBoard']) && count($_POST) > 1 && isset($tkn)){
    $url     = "https://api.trello.com/1/boards";
    $data = array(
        'idOrganization' => $_POST['idOrganization'],
        'name' => $_POST['name'],
        'desc' => $_POST['desc'],
        'prefs_background' =>'grey'
    );
    $result = $trello->action("POST", $data, $url);

    if($result !== FALSE){
        Session::sessionSet("success", "Successfully! Created new board");
    }else{
        Session::sessionSet("error", "Fail! Something Went Wrong");
    }
    header('Location: '.$_SERVER['REQUEST_URI']);
} 

/**
 * Show all Boards
 * by an organization id
 * 
 */
if(isset($tkn) && $tkn !== "" && isset($_GET['id'])){

    //Get all boards of an organization
    $data = array();
    $url     = "https://api.trello.com/1/organizations/".$_GET['id']."/boards"; 
    $getData = $trello->getData($url);

    //Get a Organization info
    $url     = "https://api.trello.com/1/organizations/".$_GET['id']; 
    $singleData = $trello->getData($url);

    if($getData === false){
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
 * Boards html section
 * 
 */?>
<section class="otherSection pt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
                <h2>Boards of <strong><?= $singleData['displayName']?></strong></h2>
                <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">+ Add Board</a>
            </div>
        </div>
        <div class="row">
            
            <?php
            foreach($getData as $d){ 
                $img = $d['prefs']['backgroundImageScaled'];
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php 
                        if(isset($img[2]['url'])){ ?>
                            <img src="<?= $img[2]['url']?>" class="card-img-top boardImg" alt="<?= $d['name']?>">
                        <?php
                        }else{
                            echo '<div style="background-color:'.$d['prefs']['background'].';" class="boardImg"></div>';
                        }
                        ?>
                    
                        <div class="card-body">
                            <h5 class="card-title"><?= $d['name']?></h5>
                            <p class="card-text"><?= $d['desc']?></p>
                             <div class="d-flex justify-content-between align-items-center">
                                <a href="<?= WEBROOT.'list?id='.$d['id']?>" class="btn btn-primary">view List</a>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?= WEBROOT.'edit?id='.$d['id']?>" class="btn btn-success btn-sm">Edit</a>
                                    <form action="" method="post">
                                        <input type="hidden" name="id" value="<?= $d['id']?>">
                                        <button type="submit" name="deleteBoard" class="btn ms-2 btn-sm btn-danger">Delete</button>
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

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <form action="" method="post">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add new Boards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idOrganization" value="<?= $singleData['id']?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Board Name</label>
                    <input type="text" name="name" required class="form-control" id="name" placeholder="Enter Board Name">
                </div>
                <div>
                    <label for="desc"  class="form-label">Description</label>
                    <textarea name="desc" required class="form-control" placeholder="Enter description" id="desc"></textarea>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="submit" name="addBoard" class="btn btn-success">Save Board</button>
            </div>
      </form>
    </div>
  </div>
</div>


        <?php include_once(INC.'footer.php');
    }
}else{
    echo "404 Not found";
}
?>
