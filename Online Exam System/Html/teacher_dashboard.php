<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <title>Create Exam</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      padding: 20px;
      background-color: #00587A;
    }
    .hidden { display: none; }
    .question-block {
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background: #f8f9fa;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    h4 {
      color: #4b0082;
      font-weight: bold;
    }
    .form-label {
      font-weight: 600;
    }
  </style>
  <script>
    function showQuestionZone() {
      const type = document.getElementById("type").value;
      document.getElementById("question-zone").classList.remove("hidden");
      document.getElementById("mcq-zone").style.display = "none";
      document.getElementById("lab-zone").style.display = "none";
      document.getElementById("ct-zone").style.display = "none";

      if (type === "quiz") {
        document.getElementById("mcq-zone").style.display = "block";
      } else if (type === "labexam") {
        document.getElementById("lab-zone").style.display = "block";
      } else if (type === "ct") {
        document.getElementById("ct-zone").style.display = "block";
      }
    }

    // Add MCQ Question
    function addMcqQuestion() {
      const container = document.getElementById("mcq-questions");
      const index = container.children.length + 1;
      const html = `
        <div class="question-block">
          <label class="form-label">Question ${index}:</label>
          <input type="text" name="questions[]" class="form-control mb-2" placeholder="Enter question text" required>

          <div class="row">
            <div class="col-md-6 mb-2">Option A: <input type="text" name="option_a[]" class="form-control" required></div>
            <div class="col-md-6 mb-2">Option B: <input type="text" name="option_b[]" class="form-control" required></div>
            <div class="col-md-6 mb-2">Option C: <input type="text" name="option_c[]" class="form-control" required></div>
            <div class="col-md-6 mb-2">Option D: <input type="text" name="option_d[]" class="form-control" required></div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Correct (A/B/C/D):</label>
              <input type="text" name="correct[]" maxlength="1" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Marks:</label>
              <input type="number" name="marks[]" class="form-control" min="1" value="1" required>
            </div>
          </div>
        </div>`;
      container.insertAdjacentHTML("beforeend", html);
    }

    // Add CT Question
    function addCtQuestion() {
      const container = document.getElementById("ct-questions");
      const index = container.children.length + 1;
      const html = `
        <div class="question-block">
          <label class="form-label">CT Question ${index}:</label>
          <input type="text" name="ct_questions[]" class="form-control mb-2" placeholder="Enter CT question" required>

          <label>Marks:</label>
          <input type="number" name="ct_marks[]" class="form-control" min="1" value="1" required>
        </div>`;
      container.insertAdjacentHTML("beforeend", html);
    }

    // Add Lab Question
    function addLabQuestion() {
      const container = document.getElementById("lab-questions");
      const index = container.children.length + 1;
      const html = `
        <div class="question-block">
          <label class="form-label">Lab Question ${index}:</label>
          <input type="text" name="lab_questions[]" class="form-control mb-2" placeholder="Enter lab question" required>

          <label>Marks:</label>
          <input type="number" name="lab_marks[]" class="form-control" min="1" value="1" required>
        </div>`;
      container.insertAdjacentHTML("beforeend", html);
    }

    // Auto hide success message
    setTimeout(() => {
      let msg = document.querySelector(".bg-success");
      if (msg) msg.style.display = "none";
    }, 2500);
  </script>
</head>

<body>
  <h1 class="text-center mb-4 text-light fw-bold">Create Exam</h1>

  <div class="card shadow-lg p-4">
    <form action="create_exam.php" method="post">
      <label class="form-label">Title:</label>
      <input type="text" name="title" class="form-control mb-3" placeholder="Enter exam title" required>

      <label class="form-label">Exam Type:</label>
      <select name="type" id="type" class="form-control mb-3" onchange="showQuestionZone()" required>
        <option value="">Select Type</option>
        <option value="quiz">Quiz (MCQ)</option>
        <option value="labexam">Lab Exam</option>
        <option value="ct">CT Exam</option>
      </select>

      <label class="form-label">Access Key:</label>
      <input type="text" name="access_key" class="form-control mb-3" required>

      <!-- Dynamic Question Zone -->
      <div id="question-zone" class="hidden">
        <!-- MCQ Zone -->
        <div id="mcq-zone" style="display: none;">
          <h4>MCQ Questions</h4>
          <div id="mcq-questions"></div>
          <button type="button" onclick="addMcqQuestion()" class="btn btn-primary mt-2">Add Question</button>
        </div>

        <!-- Lab Exam Zone -->
        <div id="lab-zone" style="display: none;">
          <h4>Lab Exam Questions</h4>
          <div id="lab-questions"></div>
          <button type="button" onclick="addLabQuestion()" class="btn btn-primary mt-2">Add Question</button>
        </div>

        <!-- CT Exam Zone -->
        <div id="ct-zone" style="display: none;">
          <h4>CT Questions</h4>
          <div id="ct-questions"></div>
          <button type="button" onclick="addCtQuestion()" class="btn btn-primary mt-2">Add Question</button>
        </div>
      </div>

      <br>
      <label class="form-label">Exam Duration (minutes):</label>
      <input type="number" name="duration" class="form-control mb-3" min="1" required>

      <label class="form-label">Start Time:</label>
      <input type="datetime-local" name="start_time" class="form-control mb-3">

      <label class="form-label">End Time:</label>
      <input type="datetime-local" name="end_time" class="form-control mb-4">

      <button type="submit" class="btn btn-warning w-100 fw-bold">Submit Exam</button>
    </form>
  </div>

  <br>
  <?php
    if (isset($_GET['success']) && $_GET['success'] == 1) {
      echo "<p class='bg-success text-white p-2 fw-bold text-center mt-3'>Exam created successfully!</p>";
    }
  ?>
</body>
</html>
