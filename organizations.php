<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bootstrap.php');
use App\Trello\Trello;
use App\Session\Session;

$key = Session::sessionGet('apiKey');
$secret = Session::sessionGet('secretKey');
$tkn = Session::sessionGet('tkn');
$trello = new Trello($key, $secret);



if(isset($tkn) && $tkn !== ""){
    $url = "https://api.trello.com/1/members/me/organizations";
    $data = $trello->getData($url);
    if($data === false){
        echo "404! Data not found. Please Login again";
    }else{ 

        include_once(INC.'header.php'); ?>



<section class="homeSection pt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <h2>Organizations</h2>
            </div>
        </div>
        <div class="row">
            
            <?php
            foreach($data as $d){ ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= $d['displayName']?></h5>
                            <p class="card-text"><?= $d['desc']?></p>
                            <a href="<?= WEBROOT.'boards?id='.$d['id']?>" class="btn btn-primary">See Boards</a>
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
    header('location:'.WEBROOT.'');
}
?>