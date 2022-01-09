<?php
namespace Controller;
$myPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
require $myPath;
use Model\Role;

class RolesController {
    private $conn;
    public function __construct($db) {
        $this->conn = $db->getConnect();
    }

    public function index() {
        $roles = (new Role())::all($this->conn);
        include_once 'views/roles.php';
    }

    public function addForm(){
        include_once 'views/addRole.php';
    }

    public function add()
    {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (trim($title) !== "") {
            $role = new Role($title);
            $role->add($this->conn);
        }
        header('Location: ?controller=roles');
    }

    public function delete() {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (trim($id) !== "" && is_numeric($id)) {
            (new Role())::delete($this->conn, $id);
        }
        header('Location: ?controller=roles');
    }

    public function show() {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (trim($id) !== "" && is_numeric($id)) {
            $role = (new Role())::byId($this->conn, $id);
        }
        include_once 'views/showRole.php';
    }

    public function edit() {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (trim($title) !== "" && trim($id) !== "") {
            $role = new Role($title);
            $role->update($this->conn, $id, $role);
        }

        header('Location: ?controller=roles');
    }

}