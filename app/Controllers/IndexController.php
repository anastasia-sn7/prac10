<?php
$myPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
require $myPath;
use Model\Authorization;
use Model\User;
use Controller\RolesController;

class IndexController
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db->getConnect();
    }

    public function index()
    {
        $users = (new User())::all($this->conn);
        $user = new User();
        $roles = $user->getRoles($this->conn);

        include_once 'views/users.php';
    }

    public function auth()
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (trim($email) !== "" && trim($password) !== "") {
            $auth = new Authorization();
            $auth->auth($this->conn, $email, $password);
        }
        header('Location: ?controller&action=index');
    }

    public function logout()
    {
        $auth = new Authorization();
        $auth->logout();
        header('Location: ?controller');
    }
}
