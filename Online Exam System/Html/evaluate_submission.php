<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

$submission_id = $_GET['submission_id'] ?? 0;

// ===== SAVE EVALUATION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['answers'] as $answer_id => $data) {
        $score   = !empty($data['score'])   ? intval($data['score']) : null;
        $comment = !empty($data['comment']) ? $data['comment'] : null;

        $stmt = $conn->prepare("UPDATE exam_answers 
                                SET teacher_score = ?, teacher_comment = ? 
                                WHERE id = ?");
        $stmt->bind_param("isi", $score, $comment, $answer_id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("UPDATE exam_submissions 
                            SET score = (SELECT SUM(teacher_score) 
                                         FROM exam_answers 
                                         WHERE submission_id = ?) 
                            WHERE id = ?");
    $stmt->bind_param("ii", $submission_id, $submission_id);
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success'>✅ Evaluation saved successfully!</div>";
}

// ===== FETCH EXAM TYPE =====
$stmt = $conn->prepare("SELECT type FROM exams 
                        JOIN exam_submissions es ON exams.id = es.exam_id 
                        WHERE es.id = ?");
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$stmt->bind_result($exam_type);
$stmt->fetch();
$stmt->close();

// ===== FETCH ANSWERS =====
$sql = "SELECT ea.id as answer_id, q.question, ea.answer_text, ea.language,
               ea.teacher_score, ea.teacher_comment
        FROM exam_answers ea
        JOIN questions q ON ea.question_id = q.id
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
    <title>Evaluate Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CodeMirror -->
    <?php if ($exam_type === 'labexam'): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/theme/eclipse.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/python/python.min.js"></script>
    <?php endif; ?>

    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <style>
        .quill-view {
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 150px;
            background: #fdfdfd;
            padding: 10px;
        }
        .quill-editor {
            border: 1px solid #ccc;
            border-radius: 5px;
            min-height: 100px;
            background: #fff;
        }
        body
        {
            background-color: cadetblue;
        }
    </style>
</head>
<body class="container py-4">

<h2>📝 Evaluate Submission</h2>

<form method="POST" id="evalForm">
<?php while($row = $result->fetch_assoc()): ?>
    <div class="card my-3">
        <div class="card-body">
            <p><b>Question:</b> <?= htmlspecialchars($row['question']) ?></p>

            <?php if ($exam_type === 'labexam'): ?>
                <p><b>Language:</b> <?= htmlspecialchars($row['language']) ?></p>
            <?php endif; ?>

            <label><b>Student Answer:</b></label>
            <?php if ($exam_type === 'labexam'): ?>
                <textarea id="code_<?= $row['answer_id'] ?>" readonly><?= htmlspecialchars($row['answer_text']) ?></textarea>

            <?php elseif ($exam_type === 'ct'): ?>
                <!-- Quill readonly view preserving formatting -->
                <div id="answer_view_<?= $row['answer_id'] ?>" class="quill-view"></div>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var view = new Quill("#answer_view_<?= $row['answer_id'] ?>", {
                        theme: "snow",
                        readOnly: true,
                        modules: { toolbar: false }
                    });
                    // Restore saved HTML (not plain text)
                    view.root.innerHTML = <?= json_encode($row['answer_text']) ?>;
                });
                </script>
            <?php else: ?>
                <textarea class="form-control" rows="6" readonly><?= htmlspecialchars($row['answer_text']) ?></textarea>
            <?php endif; ?>

            <div class="mb-2 mt-3">
                <label class="form-label">Score:</label>
                <input type="number" class="form-control" 
                       name="answers[<?= $row['answer_id'] ?>][score]" 
                       value="<?= htmlspecialchars($row['teacher_score']) ?>" min="0">
            </div>

            <div class="mb-2">
                <label class="form-label">Comment:</label>
                <div id="comment_editor_<?= $row['answer_id'] ?>" class="quill-editor"></div>
                <input type="hidden" name="answers[<?= $row['answer_id'] ?>][comment]" 
                       id="comment_input_<?= $row['answer_id'] ?>" 
                       value="<?= htmlspecialchars($row['teacher_comment']) ?>">
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var editor = new Quill("#comment_editor_<?= $row['answer_id'] ?>", {
                        theme: "snow",
                        modules: {
                            toolbar: [
                                [{ 'font': [] }, { 'size': [] }],
                                ['bold', 'italic', 'underline'],
                                [{ 'script': 'sub'}, { 'script': 'super' }],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                ['clean']
                            ]
                        }
                    });
                    editor.root.innerHTML = <?= json_encode($row['teacher_comment']) ?>;
                    document.getElementById("evalForm").addEventListener("submit", function() {
                        document.getElementById("comment_input_<?= $row['answer_id'] ?>").value = editor.root.innerHTML.trim();
                    });
                });
                </script>
            </div>
        </div>
    </div>
<?php endwhile; ?>

    <button type="submit" class="btn btn-success">💾 Save Evaluation</button>
    <a class="btn btn-primary" role="button" href="monitor_participant.php">Exit</a>
</form>

<?php if ($exam_type === 'labexam'): ?>
<script>
<?php
$result->data_seek(0);
while($row = $result->fetch_assoc()):
    $mode = "text/x-c++src";
    if ($row['language'] === "c") $mode = "text/x-csrc";
    if ($row['language'] === "cpp") $mode = "text/x-c++src";
    if ($row['language'] === "java") $mode = "text/x-java";
    if ($row['language'] === "python") $mode = "text/x-python";
?>
CodeMirror.fromTextArea(document.getElementById("code_<?= $row['answer_id'] ?>"), {
    lineNumbers: true,
    theme: "eclipse",
    mode: "<?= $mode ?>",
    readOnly: true
});
<?php endwhile; ?>
</script>
<?php endif; ?>

</body>
</html>
