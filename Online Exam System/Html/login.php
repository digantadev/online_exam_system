<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$role = $_POST['role'];
$email = $_POST['email'];
$password = $_POST['password'];
$sql="SELECT * FROM users WHERE role = ? AND email= ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ss",$role,$email);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows === 1)
{
    $user=$result->fetch_assoc();
    if(password_verify($password,$user['password']))
    {
        $_SESSION['user_id']= $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // Redirect based on role
        if($user['role']=== 'student')
        {
            header("Location: student_dashboard_menu.php");
        }
        else
        {
            header("Location: teacher_dashboard_menu.php");
        }
        exit();
    }
    else
    {
        echo "Invalid password";
    }
}else{
    echo "User not found!";
}
$stmt->close();
$conn->close();



?>