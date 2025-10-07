    <?php
    session_start();
    if (!isset($_SESSION['email']) && $_SESSION['role'] !== 'teacher') {
      header("Location: Sign_in.html");
      exit();
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Teacher Dashboard</title>
      <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous" />
      <style>
        .card-body {
          border-radius: 15px;
          background: #fff5e6;
          color: #333;

        }

        .btn-custom {
          border-radius: 20px;
          padding: 8px 20px;
        }

        .card:hover {
          transform: translateY(5px);
        }

        .dashboard-title {
          margin-top: 30px;
          text-align: center;
        }

        img {
          height: 200px;
          object-fit: cover;
          border-bottom-left-radius: 15px;
          border-top-right-radius: 15px;

        }
      </style>
    </head>

    <body style="background-color: #261C2C; color:white">
      <div class="container">
        <h1 class="dashboard-title">Teacher Dashboard</h1>
        <h4 class="text-center">Welcome, <?php echo $_SESSION["name"]; ?> (Teacher)</h4>

        <div class="row justify-content-center mt-5 g-4" style="color: black;">
          <!-- Create Exam Card -->
          <div class="col-md-4">
            <div class="card h-100">
              <img src="/images/boy-doing-exam-preparation-illustration-concept-on-white-background-vector.jpg" class="card-img-top" alt="...">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">Create Exam</h5>
                <p class="card-text">
                  Easily create and customize exams for your students. Choose the exam type (MCQ, CT, or Lab), set the title,
                  description, and access key for secure participation. Add questions and set time limits with ease.
                </p>
                <div class="mt-auto">
                  <a href="teacher_dashboard.php" class="btn btn-primary btn-custom">➕ Create Exam</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Monitor Participants Card -->
          <div class="col-md-4">
            <div class="card h-100">
              <img src="/images/vector-illustration-business-team-analysis-monitoring-web-report-dashboard-monitor_675567-3177.avif" class="card-img-top" alt="...">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">Monitor Participants</h5>
                <p class="card-text">
                  Keep track of student participation in real time. View who has joined each exam, monitor their progress,
                  and ensure exam integrity. Access participant lists, attendance records, and submission status directly.
                </p>
                <div class="mt-auto">
                  <a href="monitor_participant.php" class="btn btn-success btn-custom">👥 View Participants</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </body>

    </html>