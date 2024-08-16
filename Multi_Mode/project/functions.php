<?php 
session_start();

// Connect to database
$db = mysqli_connect('localhost', 'root', '', 'reg');

// Variable declaration
$username = "";
$email = "";
$errors = array(); 

// Call the register() function if register_btn is clicked
if (isset($_POST['register_btn'])) {
    register();
}

// REGISTER USER
function register() {
    global $db, $errors, $username, $email;

    // Grab form values
    $username = e($_POST['username']);
    $email = e($_POST['email']);
    $password_1 = e($_POST['password_1']);
    $password_2 = e($_POST['password_2']);

    // Form validation
    if (empty($username)) { 
        array_push($errors, "Username is required"); 
    }
    if (empty($email)) { 
        array_push($errors, "Email is required"); 
    }
    if (empty($password_1)) { 
        array_push($errors, "Password is required"); 
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }

    // Set user type (default to 'user')
    $user_type = 'user';

    // Attempt registration if no errors on form
    if (count($errors) == 0) {
        $password = md5($password_1); // Encrypt password before saving in the database

        // Insert user into database with user type
        $query = "INSERT INTO users (username, email, user_type, password) VALUES ('$username', '$email', '$user_type', '$password')";
        mysqli_query($db, $query);

        // Get ID of the created user
        $logged_in_user_id = mysqli_insert_id($db);

        $_SESSION['user'] = getUserById($logged_in_user_id); // Put logged in user in session
        $_SESSION['success'] = "You are now registered and logged in";

        // Redirect to login page after successful registration
        header("location: login.php");
    }
}

// Function to retrieve user details by ID
function getUserById($id) {
    global $db;
    $query = "SELECT * FROM users WHERE id=" . $id;
    $result = mysqli_query($db, $query);
    return mysqli_fetch_assoc($result);
}

// Escape string
function e($val) {
    global $db;
    return mysqli_real_escape_string($db, trim($val));
}

// Function to display error messages
function display_error() {
    global $errors;

    if (count($errors) > 0) {
        echo '<div class="error">';
        foreach ($errors as $error) {
            echo $error .'<br>';
        }
        echo '</div>';
    }
} 

// Function to check if user is logged in
function isLoggedIn() {
    if (isset($_SESSION['user'])) {
        return true;
    } else {
        return false;
    }
} 

// Log user out if logout button clicked
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user']);
    header("location: login.php");
}

// Call the login() function if login_btn is clicked
if (isset($_POST['login_btn'])) {
    login();
}

// LOGIN USER
function login() {
    global $db, $username, $errors;

    // Grab form values
    $username = e($_POST['username']);
    $password = e($_POST['password']);

    // Make sure form is filled properly
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    // Attempt login if no errors on form
    if (count($errors) == 0) {
        $password = md5($password);

        $query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
        $results = mysqli_query($db, $query);

        if (mysqli_num_rows($results) == 1) { // User found
            // Check if user is admin or user
            $logged_in_user = mysqli_fetch_assoc($results);
            if ($logged_in_user['user_type'] == 'admin') {
                $_SESSION['user'] = $logged_in_user;
                $_SESSION['success'] = "You are now logged in";
                header('location: admin/home.php');               
            } else {
                $_SESSION['user'] = $logged_in_user;
                $_SESSION['success'] = "You are now logged in";
                header('location: index.php');
            }
        } else {
            array_push($errors, "Wrong username/password combination");
        }
    }
} 

// Function to check if user is an admin
function isAdmin() {
    if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin' ) {
        return true;
    } else {
        return false;
    }
}
?>