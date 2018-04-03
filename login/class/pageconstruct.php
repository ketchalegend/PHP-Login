<?php
/**
* Builds the site skeleton, handles redirects and security
**/
class PageConstruct extends AppConfig
{
    /**
    * IP address
    * @var string
    */
    public static $ip;
    public $auth;

    public function __construct(AuthorizationHandler $auth)
    {
        parent::__construct();
        $this->auth = $auth;
    }
    /**
    * `$this->htmlhead` pulls begging part of `<head>` section of page from `app_config` table.
    * `secureheader.php` handles redirects and security.
    * `globalincludes.php` handles required js and css libraries for login script
    **/
    public function buildHead($pagetype = 'page', $title = 'Page')
    {
        $ip = $_SERVER["REMOTE_ADDR"];

        $this->secureHeader($pagetype);

        echo $this->htmlhead;
        echo "<title>".$title."</title>";

        require $this->base_dir . "/login/partials/globalincludes.php";

        if ($this->auth->isLoggedIn()) {
            $csrf = new CSRFHandler;
            echo $csrf->generate_meta_tag();
            return $csrf;
        } else {
            return null;
        }
    }
    /**
    * Builds page navbar
    **/
    public function pullNav($username = null, $pagetype = 'page', $barmenu = null)
    {
        $url = $this->base_url;
        $mainlogo = $this->mainlogo;

        include $this->base_dir . "/login/partials/nav.php";
    }

    /**
    * Checks page security and handles auth redirects
    */
    public function secureHeader($pagetype)
    {
        if (!$this->auth->pageOk($pagetype)) {
            // Not authorized...

            if ($this->auth->isLoggedIn()) {
                // User is either logged in as admin but tries to access superadmin page,
                // or logged in as regular user but trying to access admin page.
                // Do not append refurl, then we could get stuuck in a loop...
                header("location:".$this->base_url);
            } else {
                // User not logged in...
                $refurl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                session_destroy();
                header("location:".$this->base_url."/login/index.php?refurl=".$refurl);
            }
            exit;
        } elseif ($this->auth->isLoggedIn() && $pagetype == "loginpage") {
            if (array_key_exists("refurl", $_GET)) {

              //Goes to referred url
                $refurl = urldecode($_GET["refurl"]);

                header("location:".$refurl);
            } else {
                header("location:".$this->base_url."/index.php");
            }
        }
    }
}
