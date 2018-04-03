<?php
try {
    require '../../login/autoload.php';

    session_start();

    $request = new CSRFHandler;
    $auth = new AuthorizationHandler;

    if ($request->valid_token() && $auth->isLoggedIn()) {
        unset($_POST['csrf_token']);
        
        $conf = AppConfig::pullMultiSettings(array("base_dir","base_url","avatar_dir"));
        $uid = $_SESSION['uid'];
        $form = $_POST;

        if (array_key_exists('userimage', $form)) {
            $extension = 'jpg';

            $imgtarget = $conf["base_dir"].$conf["avatar_dir"]."/".$uid .".". $extension;

            $imgurl = $conf["base_url"].$conf["avatar_dir"]."/".$uid .".". $extension;

            $form['userimage'] = $imgurl;

            try {
                $upsert = profileData::upsertUserInfo($uid, $form);

                if ($upsert == 1 && array_key_exists('userimage', $form)) {
                    $imgresp = ImgHandler::putImage($imgtarget, $_POST['userimage']);

                    echo $imgresp;
                } else {
                    throw new Exception("<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Database/image update failed</div>");
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                die();
            }
        } else {
            try {
                $upsert = profileData::upsertUserInfo($uid, $form);

                if ($upsert == 1) {
                    echo $upsert;
                } else {
                    throw new Exception("<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Database update failed</div>");
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                die();
            }
        }
    } else {
        throw new Exception("Unauthorized");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
