    <?php
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        header("Location: Sign_in.html");
        exit();
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Dashboard</title>
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
            img
            {
                height: 200px;
                object-fit: cover;
                border-bottom-left-radius: 15px;
                border-top-right-radius: 15px;
                
            }
        </style>
    </head>

    <body style="background-color: #0d2e33fb; color:white">
        <div class="container">
            <h1 class="dashboard-title">Student Dashboard</h1>
            <h4 class="text-center">Welcome,<?php echo $_SESSION['name']; ?>(Student)</h4>

            <div class="row justify-content-center mt-5 g-4" style="color: black;">
                <!-- Start Exam Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="/images/job-exam-test-vector-illustration_635131-1634.avif" class="card-img-top" alt="...">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Take Exam</h5>
                            <p class="card-text">
                                Access upcoming exams easily. Enter the provided access key to join, follow instructions, and complete the test within the allotted time. Stay focused and submit your answers securely.
                            </p>
                            <div class="mt-auto">
                                <a href="take_exam.php" class="btn btn-primary btn-custom">Start Exam</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monitor Participants Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="/images/boy-is-working-graph-management_118167-9736.avif" class="card-img-top" alt="...">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">My Performance</h5>
                            <p class="card-text">
                                Check your exam performance and progress. View scores, detailed results, and feedback for each test you’ve taken. Keep track of your improvements and identify areas to work on.
                            </p>
                            <div class="mt-auto">
                                <a href="performance.php" class="btn btn-success btn-custom">View Results</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>

    </html>