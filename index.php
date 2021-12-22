<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bootstrap.php');
use App\Session\Session;

include_once(INC.'header.php');?>

<section class="homeSection">
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <form action="authorize" class="homeForm" method="post">
                    <div class="mb-3">
                        <label for="apiKey" class="form-label">API Key</label>
                        <input type="text" value="3e954fe78a9e56535942e17f26eb98dc" name="apiKey" required class="form-control" id="apiKey" placeholder="Enter your API Key">
                    </div>
                    <div class="mb-3">
                        <label for="secretKey"  class="form-label">Secret Key</label>
                        <input type="text" value="55d4c4381b357c3f15a1e3a863cd20b90cfe8aa8a2875c93d955ab49d5d9c9e1" name="secretKey" required class="form-control" placeholder="Enter your Secret Key" id="secretKey">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</section>


<?php
include_once(INC.'footer.php');
?>


 