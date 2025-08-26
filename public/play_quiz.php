<!DOCTYPE html>
<html>
<head>
    <title>Play Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    let currentQuestionIndex = 0;
    let questions = [];
    let player_id = null;
    let quiz_id = 1; // you can select dynamically later
    let round_id = 1; // same

    async function registerPlayer(event){
        event.preventDefault();
        const name = document.getElementById('player_name').value;
        const email = document.getElementById('player_email').value;
        const res = await fetch('api.php?action=search_scores'); // hack to get player id or create
        const data = await res.json();
        player_id = 1; // for now
        document.getElementById('registerForm').style.display='none';
        loadQuestions();
    }

    async function loadQuestions(){
        // For demo, manually enter questions
        questions = [
            {id:1,text:"Who was first president?",type:"MCQ",option_a:"George",option_b:"John",option_c:"Abraham",option_d:"Thomas",correct_answer:"A"},
            {id:2,text:"Capital of France?",type:"OPEN",correct_answer:"Paris"}
        ];
        showQuestion();
    }

    async function submitAnswer(){
        const ans = document.getElementById('answer').value;
        const q = questions[currentQuestionIndex];
        const res = await fetch(`api.php?action=record_attempt`,{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                player_id, quiz_id, round_id, question_id:q.id, answer_text:ans
            })
        });
        const data = await res.json();
        alert(data.is_correct?`Correct! +${data.points}`:`Wrong!`);
        currentQuestionIndex++;
        if(currentQuestionIndex < questions.length){
            showQuestion();
        }else{
            alert('Quiz Finished! Check leaderboard.');
            window.location.href='leaderboard.php';
        }
    }

    function showQuestion(){
        const q = questions[currentQuestionIndex];
        document.getElementById('questionText').textContent = q.text;
        const ansInput = document.getElementById('answer');
        ansInput.value='';
        if(q.type==='MCQ'){
            ansInput.placeholder='Enter A/B/C/D';
        } else {
            ansInput.placeholder='Type your answer';
        }
    }
    </script>
</head>
<body class="container py-4">
    <h1>Play Quiz</h1>

    <div id="registerForm">
        <form onsubmit="registerPlayer(event)">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" id="player_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="player_email" required>
            </div>
            <button class="btn btn-primary">Register & Start Quiz</button>
        </form>
    </div>

    <div id="quizForm" style="margin-top:20px;">
        <h3 id="questionText"></h3>
        <div class="mb-3">
            <input type="text" class="form-control" id="answer">
        </div>
        <button class="btn btn-success" onclick="submitAnswer()">Submit Answer</button>
    </div>
</body>
</html>
