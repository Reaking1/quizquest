<!DOCTYPE html>
<html>
<head>
    <title>Play Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

    <!-- ✅ Header -->
    <header class="mb-4 p-3 bg-dark text-white rounded">
        <h1 class="text-center">🎮 QuizQuest</h1>
        <p class="text-center mb-0">Test your knowledge, earn points, climb the leaderboard!</p>
    </header>

    <!-- ✅ Instructions -->
    <section class="mb-4 p-3 border rounded bg-light">
        <h4>📖 How to Play</h4>
        <ul>
            <li><b>Register:</b> Enter your name and email to join the game.</li>
            <li><b>Select a Quiz & Round:</b> Choose which quiz and round you want to play.</li>
            <li><b>Answer Questions:</b> 
                <ul>
                    <li><b>MCQ (Multiple Choice):</b> When creating MCQ questions in the admin panel, make sure options are labeled as <code>A</code>, <code>B</code>, <code>C</code>, <code>D</code>. Players will click on one to submit.</li>
                    <li><b>Open-Ended:</b> If the answer is a word like <code>cat</code>, you can allow multiple variations by separating them with commas when creating the question.  
                        <br>Example: <code>cat,Cat,CAT</code></li>
                </ul>
            </li>
            <li><b>Scoring:</b> Points are awarded based on difficulty:
                <ul>
                    <li>Easy = +1 point</li>
                    <li>Medium = +2 points</li>
                    <li>Hard = +3 points</li>
                </ul>
            </li>
            <li><b>Finish:</b> At the end of the round, your total score is calculated and shown on the leaderboard.</li>
        </ul>
    </section>

    <!-- Existing Game Code -->
    <h2>Play Quiz</h2>

    <!-- Register -->
    <div id="registerDiv">
        <form onsubmit="registerPlayer(event)">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" id="player_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="player_email" required>
            </div>
            <button class="btn btn-primary">Register</button>
        </form>
    </div>

    <!-- (rest of your quiz code here) -->

</body>
</html>
