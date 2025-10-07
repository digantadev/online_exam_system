<?php
$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if($conn->connect_error)
{
    die("Connection failed:".$conn->connect_error);
}
$role=$_POST['role'];
$name=$_POST['name'];
$email=$_POST['email'];
$password=password_hash($_POST['password'],PASSWORD_DEFAULT);
$sql="INSERT INTO users (role,name,email,password) VALUES (?,?,?,?)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ssss",$role,$name,$email,$password);

if($stmt->execute())
{
    header("Location:Sign_in.html");
}
else
{
    echo "Error: ".$stmt->error;
}
$stmt->close();
$conn->close();



?>