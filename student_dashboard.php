<?php
require_once 'config/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$error = '';
$success = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_name = trim($_POST['faculty_name']);
    $subject = trim($_POST['subject']);
    $rating = $_POST['rating'];
    $comments = trim($_POST['comments']);
    
    if (empty($faculty_name) || empty($subject) || empty($rating)) {
        $error = "Please fill all required fields!";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, student_name, student_email, course, semester, 
                                faculty_name, subject, rating, comments) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([
            $_SESSION['student_id'],
            $_SESSION['student_name'],
            $_SESSION['student_email'],
            $_SESSION['student_course'],
            $_SESSION['student_semester'],
            $faculty_name,
            $subject,
            $rating,
            $comments
        ])) {
            $success = "Feedback submitted successfully!";
        } else {
            $error = "Failed to submit feedback!";
        }
    }
}

// Get student's previous feedback
$stmt = $conn->prepare("SELECT * FROM feedback WHERE student_id = ? ORDER BY submitted_at DESC");
$stmt->execute([$_SESSION['student_id']]);
$previous_feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>! 👋</h2>
            <div class="student-info">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['student_email']); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($_SESSION['student_course']); ?></p>
                <p><strong>Semester:</strong> <?php echo htmlspecialchars($_SESSION['student_semester']); ?></p>
            </div>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <div class="form-container">
            <h3>📝 Submit Feedback</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Faculty Name *</label>
                    <input type="text" name="faculty_name" required>
                </div>
                
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label>Rating *</label>
                    <select name="rating" required>
                        <option value="">Select Rating</option>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent (5)</option>
                        <option value="4">⭐⭐⭐⭐ Very Good (4)</option>
                        <option value="3">⭐⭐⭐ Good (3)</option>
                        <option value="2">⭐⭐ Average (2)</option>
                        <option value="1">⭐ Poor (1)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Comments</label>
                    <textarea name="comments" rows="4" 
                              placeholder="Share your feedback about teaching methods, course content, etc."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>

        <?php if (count($previous_feedback) > 0): ?>
        <div class="feedback-history">
            <h3>📋 Your Previous Feedback</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Faculty</th>
                        <th>Subject</th>
                        <th>Rating</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($previous_feedback as $fb): ?>
                    <tr>
                        <td><?php echo date('d-M-Y', strtotime($fb['submitted_at'])); ?></td>
                        <td><?php echo htmlspecialchars($fb['faculty_name']); ?></td>
                        <td><?php echo htmlspecialchars($fb['subject']); ?></td>
                        <td><?php echo str_repeat('⭐', $fb['rating']); ?></td>
                        <td><?php echo htmlspecialchars($fb['comments'] ?: 'No comments'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
