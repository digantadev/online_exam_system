<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: Sign_in.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'online_exam_system', 3307);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

date_default_timezone_set('Asia/Dhaka');

$exam = null;
$questions = [];
$error = "";

// ✅ Handle access key submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['access_key'])) {
    $access_key = $_POST['access_key'];
    $stmt = $conn->prepare("SELECT * FROM exams WHERE access_key = ?");
    $stmt->bind_param("s", $access_key);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $exam = $res->fetch_assoc();
        $now = date("Y-m-d H:i:s");

        if ($now < $exam['start_time']) {
            $error = "The exam hasn’t started yet!";
            $exam = null;
        } elseif ($now > $exam['end_time']) {
            $error = "This exam has already ended!";
            $exam = null;
        } else {
            // ✅ Check if student already submitted
            $checkStmt = $conn->prepare("SELECT id FROM exam_submissions WHERE exam_id=? AND student_id=?");
            $checkStmt->bind_param("ii", $exam['id'], $_SESSION['user_id']);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();
            if ($checkRes->num_rows > 0) {
                $error = "You have already participated in this exam!";
                $exam = null;
            } else {
                // ✅ Get questions
                $qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id=?");
                $qstmt->bind_param("i", $exam['id']);
                $qstmt->execute();
                $qres = $qstmt->get_result();
                while ($qrow = $qres->fetch_assoc()) {
                    if ($exam['type'] === 'quiz') {
                        $ostmt = $conn->prepare("SELECT * FROM options WHERE question_id=?");
                        $ostmt->bind_param("i", $qrow['id']);
                        $ostmt->execute();
                        $ores = $ostmt->get_result();
                        $options = [];
                        while ($orow = $ores->fetch_assoc()) $options[] = $orow;
                        $qrow['options'] = $options;
                    }
                    $questions[] = $qrow;
                }

                // ✅ Timer session
                if (!isset($_SESSION['exam_start'][$exam['id']])) {
                    $_SESSION['exam_start'][$exam['id']] = time();
                }
                $elapsed = time() - $_SESSION['exam_start'][$exam['id']];
                $remaining = max(0, ($exam['duration'] * 60) - $elapsed);
            }
        }
    } else {
        $error = "Invalid Access Key!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Take Exam</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<?php if ($exam && $exam['type'] === 'labexam'): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/theme/eclipse.min.css">
<?php endif; ?>

<style>
.option-card {
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.2s ease-in-out;
}
.option-card:hover {
    background-color: #adb6b4c6;
    color: black;
    transform: scale(1.02);
}
.btn-check:checked + .option-card {
    background-color: #671e9eff;
    color: yellow;
    border-color: #0dfdc9ff;
    box-shadow: 0 0 8px rgba(13,110,253,0.5);
}
.quill-editor {
    background: white;
    border-radius: 8px;
    border: 1px solid #ced4da;
}
</style>
</head>

<body style="background-color: #294c6fff;">
<div class="container mt-5">
<h2 class="text-center text-light mb-4">Take Exam</h2>

<?php if (!$exam): ?>
    <form method="post" class="text-center">
        <input type="text" name="access_key" placeholder="Enter Access Key" required class="form-control w-50 d-inline">
        <button type="submit" class="btn btn-primary">Enter</button>
    </form>
    <?php if ($error) echo "<p class='text-danger text-center mt-3'>$error</p>"; ?>

<?php else: ?>
    <h3 class="mt-4 text-light"><?php echo htmlspecialchars($exam['title']); ?> (<?php echo htmlspecialchars($exam['type']); ?>)</h3>
    <p id="timer" class="text-warning fw-bold"></p>

    <form id="examForm" action="submit_exam.php" method="post">
        <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">

        <?php foreach ($questions as $i => $q): ?>
        <div class="card mt-3">
            <div class="card-body">
                <p><b>Q<?php echo $i + 1; ?>:</b> <?php echo htmlspecialchars($q['question']); ?></p>

                <?php if ($exam['type'] === 'quiz'): ?>
                    <div class="row">
                        <?php foreach ($q['options'] as $opt): ?>
                            <div class="col-md-6 mb-3">
                                <input type="radio" class="btn-check"
                                    name="answers[<?php echo $q['id']; ?>]"
                                    id="opt_<?php echo $opt['id']; ?>"
                                    value="<?php echo $opt['id']; ?>" required>
                                <label class="btn btn-outline-primary w-100 text-start p-3 option-card"
                                    for="opt_<?php echo $opt['id']; ?>">
                                    <?php echo htmlspecialchars($opt['option_text']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php elseif ($exam['type'] === 'ct'): ?>
                    <div id="quill_<?php echo $q['id']; ?>" class="quill-editor" style="height:200px;"></div>
                    <input type="hidden" name="answers[<?php echo $q['id']; ?>]" id="hidden_<?php echo $q['id']; ?>">

                <?php elseif ($exam['type'] === 'labexam'): ?>
                    <label>Select Language:</label>
                    <select name="language[<?php echo $q['id']; ?>]" class="form-select w-25 mb-2">
                        <option value="c">C</option>
                        <option value="cpp">C++</option>
                        <option value="java">Java</option>
                        <option value="python">Python</option>
                        <option value="javascript">JavaScript</option>
                    </select>
                    <textarea id="code_<?php echo $q['id']; ?>" name="answers[<?php echo $q['id']; ?>]" rows="15"></textarea>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-success mt-3">Submit Exam</button>
    </form>

    <!-- 🔒 Prevent Leaving or Refreshing the Page -->
    <script>
        window.onbeforeunload = function() {
            return "⚠️ You cannot leave this exam before submitting!";
        };

        // Disable Back Button
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.pushState(null, null, location.href);
            alert("You cannot go back during the exam!");
        };
    </script>

    <!-- 🕒 Timer -->
    <script>
    let duration = <?php echo $remaining ?? ($exam['duration'] * 60); ?>;
    let timerE1 = document.getElementById("timer");
    let interval = setInterval(()=>{
        let min = Math.floor(duration/60);
        let sec = duration%60;
        timerE1.textContent = `⏰ Time Left: ${min}:${sec<10?'0':''}${sec}`;
        if(duration<=0){ 
            clearInterval(interval); 
            alert("⏳ Time is up! Submitting exam...");
            window.onbeforeunload = null;
            document.getElementById("examForm").submit(); 
        }
        duration--;
    },1000);
    </script>

    <!-- ✍️ Quill Setup -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
    const quillEditors = {};
    <?php foreach ($questions as $q): if ($exam['type'] === 'ct'): ?>
        quillEditors[<?php echo $q['id']; ?>] = new Quill("#quill_<?php echo $q['id']; ?>", {
            theme: "snow",
            placeholder: "Write your answer here...",
            modules: { toolbar: [ [{ header: [1, 2, 3, 4, 5, 6] }], [{ script: "sub" }, { script: "super" }], [{ size: ['small', false, 'large', 'huge'] }], ["bold", "italic", "underline"], ["code-block", "blockquote"], [{ color: [] }, { background: [] }], [{ font: [] }], [{ align: [] }], [{ list: "ordered" }, { list: "bullet" }], ["formula"], ["clean"] ] }
        });
    <?php endif; endforeach; ?>

    // ✅ Confirm Before Submit + Save Quill Data
    document.getElementById("examForm").addEventListener("submit", function(e){
        const confirmSubmit = confirm("⚠️ Are you sure you want to submit your exam?\nYou cannot change your answers after this.");
        if (!confirmSubmit) {
            e.preventDefault();
            return;
        }
        window.onbeforeunload = null;
        Object.keys(quillEditors).forEach(qid=>{
            let html = quillEditors[qid].root.innerHTML.trim();
            document.getElementById("hidden_"+qid).value = html;
        });
    });
    </script>

    <!-- 💻 CodeMirror for Lab Exam -->
    <?php if ($exam['type'] === 'labexam'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/python/python.min.js"></script>
    <script>
    const editors = {};
    <?php foreach ($questions as $q): ?>
        editors[<?php echo $q['id']; ?>] = CodeMirror.fromTextArea(
            document.getElementById("code_<?php echo $q['id']; ?>"),
            { lineNumbers:true, theme:"eclipse", mode:"text/x-c++src", indentUnit:4, tabSize:4 }
        );
    <?php endforeach; ?>

    document.getElementById("examForm").addEventListener("submit", function(){
        Object.values(editors).forEach(ed => ed.save());
    });

    document.querySelectorAll('select[name^="language"]').forEach(sel => {
        sel.addEventListener('change', e => {
            const qid = e.target.name.match(/\d+/)[0];
            const selectedLang = e.target.value.toLowerCase();
            const modeMap = {
                c: "text/x-csrc",
                cpp: "text/x-c++src",
                java: "text/x-java",
                python: "text/x-python",
                javascript: "text/javascript"
            };
            if (editors[qid] && modeMap[selectedLang]) {
                editors[qid].setOption("mode", modeMap[selectedLang]);
            }
        });
    });
    </script>
    <?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
