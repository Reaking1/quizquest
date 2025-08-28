<!DOCTYPE html>
<html>
<head>
    <title>Play Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    let player_id = null;
    let quiz_id = null;
    let round_id = null;
    let questions = [];
    let currentQuestionIndex = 0;

    // 1️⃣ Register Player
    async function registerPlayer(event){
        event.preventDefault();
        const name = document.getElementById('player_name').value.trim();
        const email = document.getElementById('player_email').value.trim();
        if(!name || !email) return alert('Enter name and email');

        const res = await fetch('api.php?action=register_player', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({name,email})
        });
        const data = await res.json();
        player_id = data.player_id;

        document.getElementById('registerDiv').style.display = 'none';
        document.getElementById('quizSelectDiv').style.display = 'block';
        loadQuizzes();
    }

    // 2️⃣ Load all quizzes
    async function loadQuizzes(){
        const res = await fetch('api.php?action=get_quizzes');
        const quizzes = await res.json();
        const select = document.getElementById('quizSelect');
        select.innerHTML = '<option value="">-- Select Quiz --</option>';
        quizzes.forEach(q => {
            const opt = document.createElement('option');
            opt.value = q.id;
            opt.textContent = q.title;
            select.appendChild(opt);
        });
    }

    // 3️⃣ Load rounds for selected quiz
    async function loadRounds(){
        quiz_id = document.getElementById('quizSelect').value;
        if(!quiz_id) return;

        const res = await fetch(`api.php?action=get_rounds&quiz_id=${quiz_id}`);
        const rounds = await res.json();
        const roundSelect = document.getElementById('roundSelect');
        roundSelect.innerHTML = '<option value="">-- Select Round --</option>';
        rounds.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            roundSelect.appendChild(opt);
        });
        document.getElementById('roundDiv').style.display = 'block';
    }

    // 4️⃣ Start the quiz
    async function startQuiz(){
        round_id = document.getElementById('roundSelect').value;
        if(!quiz_id || !round_id) return alert('Select quiz and round');

        const res = await fetch(`api.php?action=get_questions_by_round&round_id=${round_id}`);
        questions = await res.json();
        if(questions.length === 0) return alert('No questions in this round');

        currentQuestionIndex = 0;
        document.getElementById('quizDiv').style.display = 'block';
        document.getElementById('quizSelectDiv').style.display = 'none';
        showQuestion();
    }

    // 5️⃣ Show current question
    function showQuestion(){
        const q = questions[currentQuestionIndex];
        document.getElementById('questionText').textContent = q.text;
        const optionsDiv = document.getElementById('options');
        optionsDiv.innerHTML = '';

        if(q.type === 'MCQ'){
            ['option_a','option_b','option_c','option_d'].forEach(optKey=>{
                if(q[optKey]){
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-outline-primary m-1';
                    btn.textContent = q[optKey];
                    btn.onclick = ()=>submitAnswer(q[optKey]);
                    optionsDiv.appendChild(btn);
                }
            });
            document.getElementById('answerDiv').style.display = 'none';
        } else {
            document.getElementById('answerDiv').style.display = 'block';
            document.getElementById('answerInput').value = '';
        }
    }

    // 6️⃣ Submit answer
async function submitAnswer(answerText=null){
    const q = questions[currentQuestionIndex];
    if(q.type !== 'MCQ') {
        answerText = document.getElementById('answerInput').value;
    }

    const playerName = document.getElementById('player_name').value;

    const res = await fetch(`api.php?action=record_attempt`,{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            player_name: playerName,  // <- send name, not ID
            quiz_id,
            round_id,
            question_id: q.id,
            answer_text: answerText
        })
    });
    const data = await res.json();

    if(data.error){
        alert("Error: " + data.error);
        console.error(data);
        return;
    }

    alert(data.is_correct?`Correct! +${data.earned_points}`:`Wrong!`);
    currentQuestionIndex++;
    if(currentQuestionIndex < questions.length){
        showQuestion();
    } else {
        alert('Quiz Finished! Check leaderboard.');
        window.location.href='leaderboard.php?quiz_id=' + quiz_id;
    }
}

    </script>
</head>
<body class="container py-4">
    <h1>Play Quiz</h1>

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

    <!-- Select Quiz -->
    <div id="quizSelectDiv" style="display:none; margin-top:20px;">
        <label class="form-label">Select Quiz:</label>
        <select id="quizSelect" class="form-select mb-2" onchange="loadRounds()"></select>
    </div>

    <!-- Select Round -->
    <div id="roundDiv" style="display:none; margin-top:10px;">
        <label class="form-label">Select Round:</label>
        <select id="roundSelect" class="form-select mb-2"></select>
        <button class="btn btn-success mt-2" onclick="startQuiz()">Start Quiz</button>
    </div>

    <!-- Quiz Questions -->
    <div id="quizDiv" style="display:none; margin-top:20px;">
        <h3 id="questionText"></h3>
        <div id="options" class="mb-3"></div>
        <div id="answerDiv" style="display:none;">
            <input type="text" class="form-control" id="answerInput">
            <button class="btn btn-success mt-2" onclick="submitAnswer()">Submit Answer</button>
        </div>
    </div>
</body>
</html>
