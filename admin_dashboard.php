<?php
require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle delete operations
if (isset($_GET['delete_feedback'])) {
    $feedback_id = $_GET['delete_feedback'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE feedback_id = ?");
    $stmt->execute([$feedback_id]);
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['delete_student'])) {
    $student_id = $_GET['delete_student'];
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    header("Location: admin_dashboard.php");
    exit();
}

// Get all students
$stmt = $conn->query("SELECT * FROM students ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all feedback
$stmt = $conn->query("SELECT * FROM feedback ORDER BY submitted_at DESC");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$total_students = count($students);
$total_feedback = count($feedbacks);
$stmt = $conn->query("SELECT AVG(rating) as avg_rating FROM feedback");
$avg_rating = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Admin Dashboard 👨‍💼</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_feedback; ?></h3>
                <p>Total Feedback</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $avg_rating ? number_format($avg_rating, 2) : '0.00'; ?></h3>
                <p>Average Rating</p>
            </div>
        </div>

        <div class="data-section">
            <h3>📊 All Feedback Responses</h3>
            <?php if (count($feedbacks) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Faculty</th>
                        <th>Subject</th>
                        <th>Rating</th>
                        <th>Comments</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $fb): ?>
                    <tr>
                        <td><?php echo $fb['feedback_id']; ?></td>
                        <td><?php echo htmlspecialchars($fb['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($fb['student_email']); ?></td>
                        <td><?php echo htmlspecialchars($fb['course']); ?></td>
                        <td><?php echo htmlspecialchars($fb['semester']); ?></td>
                        <td><?php echo htmlspecialchars($fb['faculty_name']); ?></td>
                        <td><?php echo htmlspecialchars($fb['subject']); ?></td>
                        <td><?php echo str_repeat('⭐', $fb['rating']); ?></td>
                        <td><?php echo htmlspecialchars($fb['comments'] ?: '-'); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($fb['submitted_at'])); ?></td>
                        <td>
                            <a href="?delete_feedback=<?php echo $fb['feedback_id']; ?>" 
                               class="btn-small btn-danger"
                               onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No feedback submissions yet.</p>
            <?php endif; ?>
        </div>

        <div class="data-section">
            <h3>👨‍🎓 Registered Students</h3>
            <?php if (count($students) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Registration Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                        <td><?php echo htmlspecialchars($student['course']); ?></td>
                        <td><?php echo htmlspecialchars($student['semester']); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($student['created_at'])); ?></td>
                        <td>
                            <a href="?delete_student=<?php echo $student['student_id']; ?>" 
                               class="btn-small btn-danger"
                               onclick="return confirm('Are you sure? This will also delete all feedback by this student!')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No registered students yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
