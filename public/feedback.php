<?php
// public/feedback.php
// Dependencies and session are handled by navbar.php, which is included later.
// However, we need $pdo before navbar for rating_data, so we include it here.
require_once '../config/db.php';


$page_title = 'Give Feedback';
$success_msg = '';
$error_msg = '';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Helper to calculate average rating
function getAverageRating($pdo) {
    $stmt = $pdo->query("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM feedbacks");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$rating_data = getAverageRating($pdo);
$avg_rating = round($rating_data['avg_rating'], 1);
$total_ratings = $rating_data['count'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);

    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error_msg = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedbacks (user_id, name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $subject, $message, $rating]);
            $success_msg = 'Thank you for your feedback! We appreciate your input.';
            
            // Refresh rating data after submission
            $rating_data = getAverageRating($pdo);
            $avg_rating = round($rating_data['avg_rating'], 1);
            $total_ratings = $rating_data['count'];
            
        } catch (PDOException $e) {
            $error_msg = 'Something went wrong. Please try again.';
            // error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | ZeroWaste-ZeroHunger</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .feedback-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: var(--bg-panel);
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-input);
            color: var(--text-main);
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        .rating-group {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }
        .star-rating {
            display: none;
        }
        .star-label {
            cursor: pointer;
            font-size: 1.5rem;
            color: #cbd5e1;
            transition: color 0.2s;
        }
        .star-rating:checked ~ .star-label,
        .star-rating:hover ~ .star-label,
        .star-label:hover {
            color: #fbbf24;
        }
        /* Trick for star rating: reverse order in DOM to handle "checked ~ label" siblings correctly */
        .rating-wrapper {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<main class="container">
    <div class="feedback-container">
        <h2 style="text-align: center; margin-bottom: 10px;">We Value Your Feedback 💬</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 20px;">Help us improve ZeroWaste-ZeroHunger for everyone.</p>

        <!-- Average Rating Display -->
        <div style="text-align: center; margin-bottom: 30px; padding: 15px; background: rgba(0,0,0,0.02); border-radius: 8px;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--text-main);">
                <?php echo $avg_rating > 0 ? $avg_rating : '0.0'; ?> <span style="font-size: 1.2rem; color: #fbbf24;">★</span>
            </div>
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                Based on <?php echo $total_ratings; ?> <?php echo $total_ratings == 1 ? 'review' : 'reviews'; ?>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Your Name *</label>
                <input type="text" name="name" class="form-control" value="<?php echo isset($_SESSION['user_id']) ? 'User #' . $_SESSION['user_id'] : ''; ?>" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required placeholder="john@example.com">
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Feature request, Bug report, etc.">
            </div>

            <div class="form-group">
                <label class="form-label">Rating</label>
                <div class="rating-wrapper">
                    <!-- 5 stars -->
                    <input type="radio" id="star5" name="rating" value="5" class="star-rating" checked>
                    <label for="star5" class="star-label">★</label>
                    <input type="radio" id="star4" name="rating" value="4" class="star-rating">
                    <label for="star4" class="star-label">★</label>
                    <input type="radio" id="star3" name="rating" value="3" class="star-rating">
                    <label for="star3" class="star-label">★</label>
                    <input type="radio" id="star2" name="rating" value="2" class="star-rating">
                    <label for="star2" class="star-label">★</label>
                    <input type="radio" id="star1" name="rating" value="1" class="star-rating">
                    <label for="star1" class="star-label">★</label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Your Message *</label>
                <textarea name="message" class="form-control" rows="5" required placeholder="Tell us what you think..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Feedback</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
