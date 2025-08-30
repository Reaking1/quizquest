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
    document.getElementById('questionCounter').textContent = 
        `Question ${currentQuestionIndex+1} of ${questions.length}`;
    const optionsDiv = document.getElementById('options');
    optionsDiv.innerHTML = '';

    ['A','B','C','D'].forEach(letter=>{
        const key = 'option_' + letter.toLowerCase();
        if(q[key]){
            optionsDiv.innerHTML += `
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" 
                       name="mcqOption" id="opt${letter}" value="${q[key]}">
                <label class="form-check-label" for="opt${letter}">
                  ${letter}) ${q[key]}
                </label>
              </div>
            `;
        }
    });

    document.getElementById('submitDiv').style.display = 'block';
}



    // 6️⃣ Submit answer
// 6️⃣ Submit answer
async function submitAnswer(){
    const q = questions[currentQuestionIndex];
    let answerText = null;

    if(q.type === 'MCQ'){
        const selected = document.querySelector('input[name="mcqOption"]:checked');
        if(!selected){
            alert('Please select an option');
            return;
        }
        answerText = selected.value;  // actual text of option
    } else {
        answerText = document.getElementById('answerInput').value.trim();
        if(!answerText){
            alert('Please enter an answer');
            return;
        }
    }

    const playerName = document.getElementById('player_name').value;

    try {
        const res = await fetch(`api.php?action=record_attempt`, {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                player_id,     // ✅ the integer id
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

        // ✅ Give feedback
        alert(data.is_correct 
            ? `✅ Correct! +${data.earned_points} points` 
            : `❌ Wrong! Correct answer was: ${q.correct_answer}`);

        // Move to next question
        currentQuestionIndex++;
        if(currentQuestionIndex < questions.length){
            showQuestion();
        } else {
            alert('🎉 Quiz Finished! Check leaderboard.');
            window.location.href = 'leaderboard.php?quiz_id=' + quiz_id;
        }
    } catch(err){
        console.error(err);
        alert("Network error submitting answer.");
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
        <!-- Quiz Questions -->
<div id="quizDiv" style="display:none; margin-top:20px;">
    <h4 id="questionCounter"></h4>
    <h3 id="questionText"></h3>
    <div id="options" class="mb-3"></div>
    
    <!-- Shared submit button (for MCQ and OPEN) -->
    <div id="submitDiv" style="display:none;">
        <button class="btn btn-success mt-2" onclick="submitAnswer()">Submit Answer</button>
    </div>

    <!-- Open question input -->
    <div id="answerDiv" style="display:none;">
        <input type="text" class="form-control mb-2" id="answerInput">
    </div>
</div>

</body>
</html>
