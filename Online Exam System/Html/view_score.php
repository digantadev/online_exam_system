<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$submission_id = $_GET['submission_id'] ?? 0;

// ✅ Fetch exam info
$sql_exam = "
SELECT es.student_id, es.exam_id, es.score, e.title AS exam_title, e.type AS exam_type
FROM exam_submissions es
JOIN exams e ON es.exam_id = e.id
WHERE es.id = ?
LIMIT 1
";
$stmt = $conn->prepare($sql_exam);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result_exam = $stmt->get_result();

if ($result_exam->num_rows === 0) {
    die("❌ Invalid submission ID.");
}

$exam = $result_exam->fetch_assoc();
$student_id = $exam['student_id'];
$exam_id    = $exam['exam_id'];
$exam_title = $exam['exam_title'];
$exam_type  = $exam['exam_type'];
$student_score = $exam['score'] ?? 0;
$stmt->close();

// ✅ Get all answers
$sql_ans = "
SELECT q.id AS qid, q.question, q.marks,
       ea.option_id, ea.answer_text, ea.language,
       ea.teacher_score, ea.teacher_comment,
       o.option_text AS chosen_option, o.is_correct,
       (SELECT option_text FROM options WHERE question_id=q.id AND is_correct=1 LIMIT 1) AS correct_answer
FROM exam_answers ea
JOIN questions q ON ea.question_id = q.id
LEFT JOIN options o ON ea.option_id = o.id
WHERE ea.submission_id = ?
";
$stmt = $conn->prepare($sql_ans);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result_ans = $stmt->get_result();

// ✅ Calculate total allocated marks
$total_marks_sql = "SELECT SUM(marks) AS total_marks FROM questions WHERE exam_id = ?";
$stmt2 = $conn->prepare($total_marks_sql);
$stmt2->bind_param("i", $exam_id);
$stmt2->execute();
$total_marks_res = $stmt2->get_result()->fetch_assoc();
$total_allocated_marks = $total_marks_res['total_marks'] ?? 0;
$stmt2->close();

// ✅ Check if teacher evaluated (CT/Lab only)
$evaluation_check = false;
if ($exam_type === 'ct' || $exam_type === 'labexam') {
    $check_sql = "SELECT COUNT(*) AS evaluated FROM exam_answers WHERE submission_id = ? AND teacher_score IS NOT NULL";
    $stmt3 = $conn->prepare($check_sql);
    $stmt3->bind_param("i", $submission_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result()->fetch_assoc();
    $evaluation_check = ($res3['evaluated'] > 0);
    $stmt3->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Score Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #16697A;
        }
        .card {
            border-radius: 12px;
            background-color: antiquewhite;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .progress {
            height: 25px;
            font-weight: bold;
        }
        .comment-box, .answer-box {
            white-space: pre-wrap;
        }
    </style>
</head>

<body class="container py-4">
    <h2 class="mb-3 text-light">📊 Exam Score Summary</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><b>Exam:</b> <?= htmlspecialchars($exam_title) ?></p>
            <p><b>Student ID:</b> <?= htmlspecialchars($student_id) ?></p>
            <p><b>Exam Type:</b> <?= strtoupper($exam_type) ?></p>
        </div>
    </div>

    <h4 class="mt-4 text-light">Details</h4>
    <table class="table table-bordered table-striped">
        <tr class="table-dark">
            <th>QID</th>
            <th>Question</th>
            <th>Marks</th>
            <th>Student Answer</th>
            <?php if ($exam_type === 'quiz'): ?>
                <th>Correct Answer</th>
            <?php endif; ?>
            <th>Teacher Comment</th>
        </tr>

        <?php while ($row = $result_ans->fetch_assoc()): ?>
        <tr>
            <td><?= $row['qid'] ?></td>
            <td><?= htmlspecialchars($row['question']) ?></td>
            <td><?= $row['marks'] ?></td>
            <td>
                <?php
                if ($exam_type === 'quiz') {
                    echo $row['chosen_option'] ? htmlspecialchars($row['chosen_option']) : "<i>No answer</i>";
                } else {
                    echo nl2br(htmlspecialchars_decode($row['answer_text'] ?? "<i>No answer</i>"));
                }
                ?>
            </td>
            <?php if ($exam_type === 'quiz'): ?>
                <td><?= htmlspecialchars($row['correct_answer']) ?></td>
            <?php endif; ?>
            <td><?= nl2br(htmlspecialchars_decode($row['teacher_comment'] ?? "-")) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php
    // ✅ Compute percentage
    $percentage = ($total_allocated_marks > 0) ? ($student_score / $total_allocated_marks) * 100 : 0;
    if ($percentage >= 90) $message = '🌟 Excellent!';
    elseif ($percentage >= 75) $message = '👍 Good job!';
    elseif ($percentage >= 50) $message = '🙂 Keep Practicing';
    else $message = '😟 Needs Improvement';
    ?>

    <?php if ($exam_type === 'quiz'): ?>
        <!-- ✅ QUIZ Summary -->
        <div class="card mt-4">
            <div class="card-body">
                <h5>📈 QUIZ Summary</h5>
                <p><b>Obtained Marks:</b> <?= $student_score ?> / <?= $total_allocated_marks ?></p>
                <p><b>Percentage:</b> <?= number_format($percentage, 2) ?>%</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                        style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= number_format($percentage, 2) ?>%
                    </div>
                </div>
                <p class="mt-2 fw-bold"><?= $message ?></p>
            </div>
        </div>

    <?php elseif ($exam_type === 'ct' || $exam_type === 'labexam'): ?>
        <?php if ($evaluation_check): ?>
            <!-- ✅ Evaluated CT/Lab -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5>📈 <?= strtoupper($exam_type) ?> Summary</h5>
                    <p><b>Obtained Marks:</b> <?= $student_score ?> / <?= $total_allocated_marks ?></p>
                    <p><b>Percentage:</b> <?= number_format($percentage, 2) ?>%</p>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                            style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                            <?= number_format($percentage, 2) ?>%
                        </div>
                    </div>
                    <p class="mt-2 fw-bold"><?= $message ?></p>
                </div>
            </div>
        <?php else: ?>
            <!-- 🕒 Pending Evaluation -->
            <div class="card mt-4 border-warning">
                <div class="card-body text-center">
                    <h5>🕒 Pending Teacher Evaluation</h5>
                    <p>Your <?= strtoupper($exam_type) ?> submission is under review by your teacher.</p>
                    <p>Please check back later to see your score.</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <a href="student_dashboard_menu.php" class="btn btn-secondary mt-4">⬅ Back to Dashboard</a>
</body>
</html>
