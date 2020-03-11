<?php
require_once '../vendor/autoload.php';
require '../config/database.php';

use Steampixel\Route;
use Rakit\Validation\Validator;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$dotenv = Dotenv\Dotenv::createImmutable('..');
$dotenv->load();
        
$db = new DatabaseService(getenv('DB_HOST'), getenv('DB_NAME'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
$dbconnection = $db->getConnection();


//create a new user
Route::add('/users', function() {
    $user = $_POST;

    //validate user input
    $validator = new Validator;

    $validation = $validator->validate($_POST, [
        'firstName'             => 'required',
        'lastName'              => 'required',
        'email'                 => 'required|email',
        'gender'                => 'required|in:male,female',
        'password'              => 'required|min:6'
    ]);

    if ($validation->fails()) {
        http_response_code(400);
        return;
    }
    
    //insert the new user in db
    $query = "INSERT INTO users(first_name, last_name, email, gender, password) values(:first_name, :last_name, :email, :gender, :password)";
    $stmt = $GLOBALS['dbconnection']->prepare($query);
    $hashedPassword = md5($user['password']);
    $stmt->bindParam(':first_name', $user['firstName']);
    $stmt->bindParam(':last_name', $user['lastName']);
    $stmt->bindParam(':email', $user['email']);
    $stmt->bindParam(':gender', $user['gender']);
    $stmt->bindParam(':password', $hashedPassword);
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'message' => 'User was added'
        ]);
    } else {
        
        if (strpos($stmt->errorInfo()[2], 'email') === false) {
            http_response_code(500);
            error_log(json_encode($stmt->errorInfo()));
            $errorMessage = 'Something went wrong';
        } else {
            http_response_code(412);
            $errorMessage = 'Email already exists';
        }

        echo json_encode([
            'error' => $errorMessage
        ]);
    }
}, 'POST');

//get all users
Route::add('/users', function() {
    $query = "SELECT * FROM users";
    $stmt = $GLOBALS['dbconnection']->prepare($query);

    if ($stmt->execute()) {
        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = array(
                'id' => $row['id'],
                'name' => $row['first_name'].' '.$row['last_name'],
                'email' => $row['email'],
                'gender' => $row['gender'],
                'createdAt' => $row['created_at']
            );
        }

        echo json_encode($result);
    }
}, 'GET');

//any other request
Route::add('(.*)', function() {
    echo 'hey';
}, 'POST');


// Run the router
Route::run('/');


?>