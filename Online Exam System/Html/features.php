<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Features - Online Exam System</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background:linear-gradient(90deg, #007bff8a, #5106d2ff, #0099ff);
      margin: 0;
      padding: 0;
      color: #333;
    }

    header {
      background: linear-gradient(90deg, #007bff, #6a11cb, #2575fc);
      color: white;
      padding: 18px 0;
      text-align: center;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .container {
      max-width: 1100px;
      margin: 30px auto;
      background: burlywood;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    h1, h2 {
      text-align: center;
      margin-bottom: 15px;
    }

    .feature {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      border: 1px solid #ddd;
      border-radius: 10px;
      margin: 20px 0;
      padding: 20px;
      background: #fafafa;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .feature:hover {
      transform: scale(1.01);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .feature img {
      max-width: 48%;
      border-radius: 10px;
      margin: 10px;
      transition: transform 0.3s ease;
      cursor: pointer;
    }

    .feature img:hover {
      transform: scale(1.05);
    }

    .feature-content {
      flex: 1;
      min-width: 250px;
      padding: 10px 20px;
    }

    .feature-content h3 {
      color: #007bff;
      margin-top: 0;
    }

    footer {
      text-align: center;
      padding: 15px;
      background: linear-gradient(90deg, #007bff, #6a11cb, #2575fc);
      color: white;
      margin-top: 30px;
    }

    @media (max-width: 768px) {
      .feature {
        flex-direction: column;
        text-align: center;
      }

      .feature img {
        max-width: 90%;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Online Exam System - Features Overview</h1>
  </header>

  <div class="container">
    <h2>Explore All System Features</h2>

    <div class="feature">
      <div class="feature-content">
        <h3>🧑‍🏫 Teacher Dashboard</h3>
        <p>Teachers can create exams, manage students, view participants, and check all submissions with marks and comments.</p>
      </div>
      <img src="/images/teacher_dashboard.png" alt="Teacher Dashboard">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>👨‍🎓 Student Dashboard</h3>
        <p>Students can view available exams, attend exams using access keys, and review their performance.</p>
      </div>
      <img src="/images/Student_dashboard.png" alt="Student Dashboard">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>📝 Create Exam</h3>
        <p>Teachers can easily create new exams by selecting type (MCQ, CT, or Lab), setting passkeys, and scheduling them.</p>
      </div>
      <img src="/images/Create_exam .png" alt="Create Exam">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>🔐 Sign In & Sign Up</h3>
        <p>Separate sign in and sign up pages for students and teachers, ensuring secure and role-based access control.</p>
      </div>
      <img src="/images/signin.png" alt="Sign In">
      <img src="/images/signup.png" alt="Sign Up">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>🎯 MCQ Exam</h3>
        <p>Automatic scoring system for multiple-choice exams with instant evaluation after submission.</p>
      </div>
      <img src="/images/Mcq.png" alt="MCQ Exam">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>🧾 CT Exam</h3>
        <p>Students can answer descriptive questions. Teachers can later review and assign marks manually.</p>
      </div>
      <img src="/images/ct.png" alt="CT Exam">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>💻 Lab Exam</h3>
        <p>For programming-based exams, students submit code written in supported languages for teacher evaluation.</p>
      </div>
      <img src="/images/lab.png" alt="Lab Exam">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>🔑 Enter Access Key</h3>
        <p>Students enter unique access keys provided by teachers to join specific exams securely.</p>
      </div>
      <img src="/images/enter_access_key.png" alt="Enter Access Key">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>👥 Monitor Participants</h3>
        <p>Teachers can view the list of students who have attended each exam and track submission times.</p>
      </div>
      <img src="/images/monitor_participants.png" alt="Monitor Participants">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>📊 View Exam Score</h3>
        <p>Shows score percentage and detailed analysis of performance with progress bars.</p>
      </div>
      <img src="/images/exam_score.png" alt="Exam Score">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>📈 My Performance</h3>
        <p>Students can review overall performance statistics including total exams taken and average percentage.</p>
      </div>
      <img src="/images/myperformance.png" alt="My Performance">
    </div>

    <div class="feature">
      <div class="feature-content">
        <h3>📂 My Submissions</h3>
        <p>Students can view all their past submissions along with teacher feedback and scores.</p>
      </div>
      <img src="/images/mysubmission.png" alt="My Submissions">
    </div>

  </div>

  <footer>
    <p>&copy; <?php echo date('Y'); ?> Online Exam System | Developed by Diganta Das Niloy</p>
  </footer>

</body>
</html>
