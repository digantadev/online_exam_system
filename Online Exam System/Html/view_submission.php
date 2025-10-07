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

$sql = "SELECT e.type, q.id as qid, q.question, ea.answer_text, ea.option_id, ea.language,
               o.option_text AS chosen_option, o.is_correct, 
               ea.teacher_score, ea.teacher_comment,
               (SELECT option_text FROM options WHERE question_id=q.id AND is_correct=1 LIMIT 1) AS correct_answer
        FROM exam_answers ea
        JOIN exam_submissions es ON ea.submission_id = es.id
        JOIN exams e ON es.exam_id = e.id
        JOIN questions q ON ea.question_id = q.id
        LEFT JOIN options o ON ea.option_id = o.id
        WHERE ea.submission_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CodeMirror for labexam -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/theme/eclipse.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/python/python.min.js"></script>

    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        .readonly-quill .ql-toolbar { display: none; }
        .readonly-quill .ql-container {
            border: 1px solid #ddd;
            border-radius: 6px;
            min-height: 80px;
            background: #fdfdfd;
        }
        body
        {
            background-color: #449bacff;
        }
        .card-body
        {
            background-color: blanchedalmond;
        }
    </style>
</head>
<body class="container py-4">

<h2>📄 My Submission</h2>

<?php 
$quillBlocks = []; // collect both answers + comments to init later
while($row = $result->fetch_assoc()): ?>
    <div class="card my-3">
        <div class="card-body">
            <p><b>Q:</b> <?= htmlspecialchars($row['question']) ?></p>

            <?php if ($row['type'] === 'quiz'): ?>
                <p><b>Your Answer:</b></p>
                <?php if ($row['chosen_option']): ?>
                    <div id="ans_<?= $row['qid'] ?>" class="readonly-quill"></div>
                    <?php $quillBlocks[] = ["id" => "ans_".$row['qid'], "html" => $row['chosen_option']]; ?>
                <?php else: ?>
                    <p><i>No answer</i></p>
                <?php endif; ?>
                <?php if ($row['is_correct']): ?>
                    <p class="text-success">✅ Correct</p>
                <?php else: ?>
                    <p class="text-danger">❌ Wrong</p>
                    <p><b>Correct Answer:</b> <?= htmlspecialchars($row['correct_answer']) ?></p>
                <?php endif; ?>

            <?php elseif ($row['type'] === 'ct'): ?>
                <p><b>Your Answer:</b></p>
                <div id="ans_<?= $row['qid'] ?>" class="readonly-quill"></div>
                <?php $quillBlocks[] = ["id" => "ans_".$row['qid'], "html" => $row['answer_text']]; ?>
                <p><b>Teacher Score:</b> 
                    <?= ($row['teacher_score'] !== null) ? $row['teacher_score'] : "⏳ Pending" ?>
                </p>
                <p><b>Teacher Comment:</b></p>
                <?php if ($row['teacher_comment']): ?>
                    <div id="comment_<?= $row['qid'] ?>" class="readonly-quill"></div>
                    <?php $quillBlocks[] = ["id" => "comment_".$row['qid'], "html" => $row['teacher_comment']]; ?>
                <?php else: ?>
                    <p>No comment</p>
                <?php endif; ?>

            <?php elseif ($row['type'] === 'labexam'): ?>
                <p><b>Language:</b> <?= htmlspecialchars($row['language']) ?></p>
                <label><b>Your Code:</b></label>
                <textarea id="code_<?= $row['qid'] ?>" readonly><?= htmlspecialchars($row['answer_text']) ?></textarea>
                <p><b>Teacher Score:</b> 
                    <?= ($row['teacher_score'] !== null) ? $row['teacher_score'] : "⏳ Pending" ?>
                </p>
                <p><b>Teacher Comment:</b></p>
                <?php if ($row['teacher_comment']): ?>
                    <div id="comment_<?= $row['qid'] ?>" class="readonly-quill"></div>
                    <?php $quillBlocks[] = ["id" => "comment_".$row['qid'], "html" => $row['teacher_comment']]; ?>
                <?php else: ?>
                    <p>No comment</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>

<script>
// ✅ CodeMirror for labexam code
<?php
$result->data_seek(0);
while($row = $result->fetch_assoc()):
    if ($row['type'] === 'labexam'):
        $mode = "text/x-c++src";
        if ($row['language'] === "c") $mode = "text/x-csrc";
        if ($row['language'] === "cpp") $mode = "text/x-c++src";
        if ($row['language'] === "java") $mode = "text/x-java";
        if ($row['language'] === "python") $mode = "text/x-python";
?>
CodeMirror.fromTextArea(document.getElementById("code_<?= $row['qid'] ?>"), {
    lineNumbers: true,
    theme: "eclipse",
    mode: "<?= $mode ?>",
    readOnly: true
});
<?php
    endif;
endwhile;
?>

// ✅ Quill readonly for answers & comments
<?php foreach($quillBlocks as $q): ?>
var quill_<?= $q['id'] ?> = new Quill("#<?= $q['id'] ?>", {
    readOnly: true,
    theme: "snow",
    modules: { toolbar: false }
});
quill_<?= $q['id'] ?>.root.innerHTML = <?= json_encode($q['html']) ?>;
<?php endforeach; ?>
</script>
<a href="view_score.php?submission_id=<?= $submission_id ?>" 
   class="btn btn-primary mt-4">View Score</a>
<a href="performance.php?submission_id=<?= $submission_id ?>" 
   class="btn btn-primary mt-4">Back</a>

</body>
</html>
