<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = $_POST['title'];
$type = $_POST['type'];
$access_key = $_POST['access_key'];
$teacher_id = $_SESSION['user_id'];
$duration = intval($_POST['duration']);
$start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
$end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;

// Insert exam
$sql = "INSERT INTO exams (title, type, access_key, duration, start_time, end_time, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssissi", $title, $type, $access_key, $duration, $start_time, $end_time, $teacher_id);
$stmt->execute();
$exam_id = $stmt->insert_id;
$stmt->close();

// 🟢 Handle Quiz (MCQ) Questions with Marks
if ($type === 'quiz' && isset($_POST['questions'])) {
    foreach ($_POST['questions'] as $index => $question_text) {
        $marks = isset($_POST['marks'][$index]) ? intval($_POST['marks'][$index]) : 1;

        // Insert question with marks
        $stmt = $conn->prepare("INSERT INTO questions (exam_id, question, marks) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $exam_id, $question_text, $marks);
        $stmt->execute();
        $question_id = $stmt->insert_id;
        $stmt->close();

        // Insert options
        $options = [
            'A' => $_POST['option_a'][$index],
            'B' => $_POST['option_b'][$index],
            'C' => $_POST['option_c'][$index],
            'D' => $_POST['option_d'][$index],
        ];
        $correct_option = strtoupper($_POST['correct'][$index]);

        foreach ($options as $key => $opt_text) {
            $is_correct = ($key === $correct_option) ? 1 : 0;
            $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $question_id, $opt_text, $is_correct);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// 🟢 Handle Lab Exam Questions with Marks
if ($type === 'labexam' && isset($_POST['lab_questions'])) {
    foreach ($_POST['lab_questions'] as $index => $question_text) {
        if (!empty(trim($question_text))) {
            $marks = isset($_POST['lab_marks'][$index]) ? intval($_POST['lab_marks'][$index]) : 1;
            $stmt = $conn->prepare("INSERT INTO questions (exam_id, question, marks) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $exam_id, trim($question_text), $marks);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// 🟢 Handle CT Exam Questions with Marks
if ($type === 'ct' && isset($_POST['ct_questions'])) {
    foreach ($_POST['ct_questions'] as $index => $question_text) {
        if (!empty(trim($question_text))) {
            $marks = isset($_POST['ct_marks'][$index]) ? intval($_POST['ct_marks'][$index]) : 1;
            $stmt = $conn->prepare("INSERT INTO questions (exam_id, question, marks) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $exam_id, trim($question_text), $marks);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$conn->close();

// Redirect to dashboard
header("Location: teacher_dashboard.php?success=1");
exit();
?>
