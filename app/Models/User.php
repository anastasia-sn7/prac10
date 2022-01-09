<?php
namespace model;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
class User {
    private string $surname;
    private string $name;
    private string $email;
    private string $password;
    private string $gender;
    private string $roleID;

    public function __construct($name = '', $email = '', $gender = '',$password = '',$surname = '',$roleID='')
    {
        $this->surname   = $surname;
        $this->email  = $email;
        $this->name   = $name;
        $this->password  = $password;
        $this->gender = $gender;
        $this->roleID = $roleID;
    }

    public static function byId($conn, $id)
    {
        $command = "SELECT * FROM users WHERE userID=$id";
        $result = $conn->query($command);
        return $result->fetch_assoc();

    }

    public static function search($conn, $text)
    {
        $text = trim($text);
        $myusers = self::all($conn);
        $myarray = array();
        foreach ($myusers as $myuser) {
            array_push($myarray, $myuser['userID'], $myuser['surname'] . " " . $myuser['name']);
        }
        $found = array_search($text, $myarray);
        $values = array_values( $myarray );
        return $values[$found - 1] ?? -1;
    }

    public static function uploadImage() : string
    {
        $target_dir = "\public\uploads\\";
        $dir = dirname(__DIR__, 2);
        $target_dir = $dir . $target_dir;
        $target_file = $target_dir . $_FILES["photo"]["name"];
        echo "photo: " . $_FILES["photo"]["name"] . "<br>";
        var_dump($_FILES["photo"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            $uploadOk = ($check !== false) ? 1 : 0;
        }

        if (file_exists($target_file) || ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif") ) {
            $uploadOk = 0;
        }

        if ($uploadOk == 1 && move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            return $_FILES["photo"]["name"];
        }

        return "";
   }

    public function add($conn)
    {
        $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$this->email'");
        if ($res->num_rows != 0) {
            var_dump($res);
            echo "<br>" . $this->email;
            $_SESSION['alert']['emailExists'] = true;
            return false;
        }
        $avatarName = self::uploadImage();
        $sqlRequest =
            "INSERT INTO users(email, password, surname, name, gender, avatarName, roleID)
             VALUES ('$this->email', '$this->password','$this->surname', '$this->name', '$this->gender','$avatarName', '$this->roleID')";
        $res = mysqli_query($conn, $sqlRequest);

        if ($res) {
            $_SESSION['alert']['registration'] = true;
            return true;
        }
        return false;
    }

    public function getRoles($conn)
    {
        $sql = "SELECT * FROM roles";
        $result = $conn->query($sql); //виконання запиту
        if ($result->num_rows > 0) {
            $arr = [];
            while ( $db_field = $result->fetch_assoc() ) {
                $arr[] = $db_field;
            }
            return $arr;
        } else {
            return [];
        }
    }

    public static function all($conn) {
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql); //виконання запиту
        if ($result->num_rows > 0) {
            $arr = [];
            while ( $db_field = $result->fetch_assoc() ) {
                $arr[] = $db_field;
            }
            return $arr;
        } else {
            return [];
        }
    }

    public static function update($conn, $id, $data)
    {
        /**
         * @var string $email
         * @var string $name
         * @var string $gender
         * @var string $pathtoimg
         */
        print_r($data);
        $sql = "UPDATE `users` SET `email`='$data->email',`name`='$data->name', `gender`='$data->gender',`surname`='$data->surname', `roleID`='$data->roleID' ";
        if ($data->password != "old")
            $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            $sql .= ",`password`='$data->password'";
        if (isset($_FILES["photo"]["name"]) && trim($_FILES["photo"]["name"]) != "") {
            self::deleteImageByID($conn, $id);
            $pathtoimg = self::uploadImage();
            $sql .= ",`avatarName`='$pathtoimg'";
        }
        $sql .= " WHERE userID=$id";

        echo "<br>SQL: " . $sql . "<br>";
        $res = mysqli_query($conn, $sql);
        if ($res) {
            return true;
        }

        return false;



    }
    public static function delete($conn, $id)
    {
        self::deleteImageByID($conn, $id);
        $sql = "DELETE FROM comments where pageID=$id";
        $res = mysqli_query($conn, $sql);
        $sql = "DELETE FROM users WHERE userID=$id";
        echo "SQL Request: " . $sql . "<br>";
        echo "ID: " . $id;
        $res = mysqli_query($conn, $sql);
        if ($res) {
            return true;
        }
    }

    public function show($conn, $id)
    {
        $command = "SELECT * FROM users WHERE userID=$id";
        $result = $conn->query($command);
        return $result->fetch_assoc();
    }

    private static function deleteImageByID ($conn, $id)
    {
        $command = "SELECT * FROM users WHERE userID=$id";
        $result = $conn->query($command);
        $result = $result->fetch_assoc();
        echo "<br>Result: " . $result['avatarName'] . "<br>";

        if (trim($result['avatarName']) !== "") {
            $target_dir = "\public\uploads\\";
            $dir = dirname(__DIR__, 2);
            $target_dir = $dir . $target_dir;
            $target_file = $target_dir . $result['avatarName'];
            echo "<br>Myphoto: " . $result['avatarName'] . "<br>";
            if (isset($_FILES["photo"]["name"]) && trim($_FILES["photo"]["name"]) != "")
                unlink($target_file);
        }
    }

}
