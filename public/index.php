<!DOCTYPE html>
<html>
<head>
    <title>Quiz Quest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📚 Quiz Quest Dashboard</h1>
        <nav>
            <a href="index.php" class="btn btn-outline-primary btn-sm">Home</a>
        </nav>
    </div>

    <!-- Instructions -->
    <div class="alert alert-info">
        <h5>ℹ️ How to Use Quiz Quest</h5>
        <ul>
            <li><b>Create Quizzes:</b> Build quizzes with multiple rounds.</li>
            <li><b>Create Questions:</b> Only multiple-choice (MCQ). Use <code>A</code>, <code>B</code>, <code>C</code>, <code>D</code> as the correct answer.</li>
            <li><b>Difficulty:</b> Choose Easy (1 pt), Medium (2 pts), Hard (3 pts).</li>
            <li><b>Play Quiz:</b> Players register with a name + email, choose quiz & round, and answer questions.</li>
            <li><b>Leaderboard:</b> Shows live scores.</li>
            <li><b>Search Scores:</b> Find past scores by player name, quiz date, or difficulty.</li>
            <li><b>Update Scores:</b> Fix mistakes or award bonus points.</li>
            <li><b>Delete Data:</b> Remove quizzes, rounds, or attempts.</li>
        </ul>
    </div>

    <!-- Navigation -->
    <div class="list-group">
        <a href="create_quiz.php" class="list-group-item list-group-item-action">📝 Create Quiz</a>
        <a href="create_question.php" class="list-group-item list-group-item-action">❓ Create Question</a>
        <a href="manage_rounds.php" class="list-group-item list-group-item-action">📂 Manage Rounds</a>
        <a href="play_quiz.php" class="list-group-item list-group-item-action">▶️ Play Quiz</a>
        <a href="leaderboard.php" class="list-group-item list-group-item-action">🏆 Leaderboard</a>
        <a href="scores.php" class="list-group-item list-group-item-action">🔍 Search Scores</a>
        <a href="update_score.php" class="list-group-item list-group-item-action">✏️ Update Scores</a>
        <a href="delete_data.php" class="list-group-item list-group-item-action">🗑️ Delete Quiz Data</a>
    </div>
</body>
</html>
