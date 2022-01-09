<?php
$myPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
require $myPath;
use Model\Authorization;
use Model\User;

class UsersController {
    private $conn;
    public function __construct($db) {
        $this->conn = $db->getConnect();
    }

    public function index() {
        $users = (new User())::all($this->conn);
        $user = new User();
        $roles = $user->getRoles($this->conn);
        include_once 'views/users.php';
    }

    public function addForm(){
        $user = new User();
        $roles = $user->getRoles($this->conn);
        include_once 'views/addUser.php';
    }

    public function add() {
        $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password2 = filter_input(INPUT_POST, 'password2', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $roleID = filter_input(INPUT_POST, 'roles', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert']['wrongEmailFormat'] = true;
            header('Location: ?controller=users');
        }
        if ($password != $password2) {
            $_SESSION['alert']['wrongPassword'] = true;
            header('Location: ?controller=users');
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        echo "roleID: " . $roleID . "<br>";
        if (trim($name) !== "" && trim($email) !== "" && trim($gender) !== "" && trim($password) !== "" && trim($roleID) !== "") {
            // додати користувача
            $user = new User($name, $email, $gender, $password, $surname, $roleID);
            $user->add($this->conn);
            echo $name . $email . $password . $gender . "<br>";
        }
        header('Location: ?controller');
    }

    public function delete() {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (trim($id) !== "" && is_numeric($id)) {
            (new User())::delete($this->conn, $id);
        }
        header('Location: ?controller=users');
    }

    public function show() {
        $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $id = $_GET['id'];
        if (trim($id) !== "" && is_numeric($id)) {
            $user = (new User())::byId($this->conn, trim($id));
            $users = (new User())::all($this->conn);
            $roles = (new User())->getRoles($this->conn);
        }
        include_once 'views/showUser.php';
    }

    public function edit() {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $roleID = filter_input(INPUT_POST, 'roles', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (trim($password) == "")
            $password = "old";
        echo "<br>RoleID: " . $password . "<br>";

        if (trim($name) !== "" && trim($email) !== "" && trim($gender) !== "" && trim($id) !== "" && trim($password) !== "" && trim($surname) !== "" && trim($roleID) !== "") {
            $user = new User($name, $email, $gender, $password);
            $user = new User($name, $email, $gender, $password, $surname, $roleID);
            $user->update($this->conn, $id, $user);
        }

        header('Location: ?controller=users');
    }

    public function search() {
        $text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (trim($text) !== "" ) {
            $user = (new User())::search($this->conn, $text);
            if ($user == -1) {
                $_SESSION['alert']['notFound'] = true;
                echo $_SESSION['alert']['notFound'];
                header('Location: ?controller=users');
            }
            else {
                $user = (new User())::byId($this->conn, $user);
                include_once 'views/showUser.php';
            }
        }

    }

    public function addComment() {
        $userID = filter_input(INPUT_POST, 'userID');
        $pageID = filter_input(INPUT_POST, 'pageID');
        $commentText = filter_input(INPUT_POST, 'comment');

        (new Comment())::addComment($this->conn, $pageID, $userID, $commentText);
        $str ='Location: ?controller=users&action=show&id=' . $pageID;
        header($str);
        include_once 'views/showUser.php';
    }

    public function deleteComment() {
        $commentID = filter_input(INPUT_POST, 'commentID');
        $pageID = filter_input(INPUT_POST, 'pageID');
        print_r($_POST);
        print_r($_GET);
        (new Comment())::deleteCommentByID($this->conn, $commentID);

        $str ='Location: ?controller=users&action=show&id=' . $pageID;
        header($str);
    }

}
