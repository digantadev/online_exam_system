<?php
session_start();

// Only teacher can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: Sign_in.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$teacher_id = $_SESSION['user_id'];

// Get exam_id safely
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

// If no exam selected, show exam list created by this teacher
if ($exam_id === 0) {
    $res = $conn->prepare("SELECT id, title FROM exams WHERE created_by = ?");
    $res->bind_param("i", $teacher_id);
    $res->execute();
    $result = $res->get_result();

    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Select Exam</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background:#234C6A;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: Arial, sans-serif;
            }
            .exam-list {
                max-width: 600px;
                width: 100%;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                padding: 30px;
            }
            .exam-list h2 { 
                text-align:center; 
                margin-bottom:20px; 
                color:#0d6efd; 
                font-weight: bold;
            }
            .exam-list ul { list-style: none; padding: 0; }
            .exam-list li { margin: 12px 0; }
            .exam-list a {
                display: block;
                padding: 12px 18px;
                background: #335a95ff;
                color: #fff;
                border-radius: 8px;
                text-decoration: none;
                transition: 0.3s;
                font-weight: 500;
                text-align: center;
            }
            .exam-list a:hover { background:#0b5ed7; transform: scale(1.05); }
        </style>
    </head>
    <body>
        <div class="exam-list">
            <h2>Select an Exam</h2><ul>';
    while ($row = $result->fetch_assoc()) {
        echo "<li><a href='monitor_participant.php?exam_id=" . $row['id'] . "'>" . htmlspecialchars($row['title']) . "</a></li>";
    }
    echo "</ul></div></body></html>";
    exit;
}

// Fetch submissions for this exam
$sql = "
    SELECT es.id, es.student_id, es.exam_id, es.score, es.submitted_at,
           u.name AS student_name
    FROM exam_submissions es
    JOIN users u ON es.student_id = u.id
    WHERE es.exam_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monitor Participants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #234C6A;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        .page-container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #0d6efd;
            margin-bottom: 25px;
            font-weight: bold;
        }
        table th {
            background:#0d6efd;
            color:black;
            text-align: center;
        }
        table td {
            vertical-align: middle;
            text-align: center;
        }
        .btn-warning {
            font-weight: 600;
        }
        .evaluated {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <h2>Participants for Exam ID: <?php echo $exam_id; ?></h2>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Score</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo $row['score'] !== null ? $row['score'] : '<span class="text-muted">Pending</span>'; ?></td>
                        <td><?php echo $row['submitted_at']; ?></td>
                        <td>
                            <?php if ($row['score'] === null): ?>
                                <a href="evaluate_submission.php?submission_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Evaluate</a>
                            <?php else: ?>
                                <span class="evaluated">✔ Evaluated</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
