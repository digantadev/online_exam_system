<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if($conn->connect_error){ die("DB Error: ".$conn->connect_error); }

$submission_id = intval($_POST['submission_id']);
$scores = $_POST['scores'] ?? [];

// update each answer’s teacher_score
$total = 0;
foreach($scores as $answer_id => $mark){
    $mark = intval($mark);
    $stmt = $conn->prepare("UPDATE exam_answers SET teacher_score=? WHERE id=?");
    $stmt->bind_param("ii",$mark,$answer_id);
    $stmt->execute();
    $stmt->close();
    $total += $mark;
}

// save total into exam_submissions
$stmt = $conn->prepare("UPDATE exam_submissions SET score=? WHERE id=?");
$stmt->bind_param("ii",$total,$submission_id);
$stmt->execute();
$stmt->close();

$conn->close();
header("Location: teacher_dashboard.php?evaluated=1");
exit();
