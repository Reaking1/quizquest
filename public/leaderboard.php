<?php
$quiz_id = $_GET['quiz_id'] ?? null;
$data = file_get_contents("http://localhost/quizquest/public/api.php?action=leaderboard&quiz_id=" . urlencode($quiz_id));
$rows = json_decode($data,true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Leaderboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h1>Leaderboard</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Player</th>
                <th>Quiz</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($rows as $i=>$r): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($r['player_name']) ?></td>
                <td><?= htmlspecialchars($r['quiz_title']) ?></td>
                <td><span class="badge bg-primary"><?= $r['total_points'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
