<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: Sign_in.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

$exam_id    = intval($_POST['exam_id']);
$student_id = $_SESSION['user_id'];

// ✅ Step 1: Prevent duplicate submission
$check = $conn->prepare("SELECT id FROM exam_submissions WHERE exam_id=? AND student_id=?");
$check->bind_param("ii", $exam_id, $student_id);
$check->execute();
$checkRes = $check->get_result();
if ($checkRes->num_rows > 0) {
    $check->close();
    header("Location: student_dashboard_menu.php?already_submitted=1");
    exit();
}
$check->close();

// ✅ Step 2: Get exam type
$stmt = $conn->prepare("SELECT type FROM exams WHERE id=?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$stmt->bind_result($exam_type);
$stmt->fetch();
$stmt->close();

// ✅ Step 3: Start transaction for data safety
$conn->begin_transaction();

try {
    $score = null;

    // Insert submission record
    $stmt = $conn->prepare("INSERT INTO exam_submissions (exam_id, student_id, score, submitted_at) VALUES (?,?,?,NOW())");
    $stmt->bind_param("iii", $exam_id, $student_id, $score);
    $stmt->execute();
    $submission_id = $stmt->insert_id;
    $stmt->close();

    // ================= QUIZ =================
    if ($exam_type === 'quiz' && !empty($_POST['answers'])) {
        $score = 0;

        foreach ($_POST['answers'] as $question_id => $option_id) {
            $stmt = $conn->prepare("SELECT is_correct FROM options WHERE id=?");
            $stmt->bind_param("i", $option_id);
            $stmt->execute();
            $stmt->bind_result($is_correct);
            $stmt->fetch();
            $stmt->close();

            if ($is_correct == 1) $score++;

            $stmt = $conn->prepare("INSERT INTO exam_answers (submission_id, question_id, option_id) VALUES (?,?,?)");
            $stmt->bind_param("iii", $submission_id, $question_id, $option_id);
            $stmt->execute();
            $stmt->close();
        }

        // Update quiz score
        $stmt = $conn->prepare("UPDATE exam_submissions SET score=? WHERE id=?");
        $stmt->bind_param("ii", $score, $submission_id);
        $stmt->execute();
        $stmt->close();
    }

    // ================= CT / LAB =================
    elseif (($exam_type === 'ct' || $exam_type === 'labexam') && !empty($_POST['answers'])) {
        foreach ($_POST['answers'] as $question_id => $answer_text) {
            $language = isset($_POST['language'][$question_id]) ? $_POST['language'][$question_id] : null;

            // Quill answers come as HTML; decode safely
            $decoded_answer = htmlspecialchars_decode($answer_text, ENT_QUOTES);

            $stmt = $conn->prepare("INSERT INTO exam_answers (submission_id, question_id, answer_text, language) VALUES (?,?,?,?)");
            $stmt->bind_param("iiss", $submission_id, $question_id, $decoded_answer, $language);
            $stmt->execute();
            $stmt->close();
        }
    }

    // ✅ Commit transaction
    $conn->commit();

    // ✅ Clear session exam timer
    if (isset($_SESSION['exam_start'][$exam_id])) {
        unset($_SESSION['exam_start'][$exam_id]);
    }

    header("Location: student_dashboard_menu.php?submitted=1");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Submission failed: " . $e->getMessage());
}
?>
