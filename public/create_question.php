<!DOCTYPE html>
<html>
<head>
    <title>Create Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    let rounds = [];

    // Load quizzes and rounds
    async function loadQuizzes(){
        const res = await fetch('api.php?action=get_quizzes');
        const quizzes = await res.json();

        const quizSelect = document.getElementById('quiz_id');
        quizSelect.innerHTML = '<option value="">-- Select Quiz --</option>';
        quizzes.forEach(q => {
            const opt = document.createElement('option');
            opt.value = q.id;
            opt.textContent = q.title;
            quizSelect.appendChild(opt);
        });
    }

    async function loadRounds(){
        const quizId = document.getElementById('quiz_id').value;
        if(!quizId) return;

        const res = await fetch(`api.php?action=get_rounds&quiz_id=${quizId}`);
        rounds = await res.json();

        const roundSelect = document.getElementById('round_id');
        roundSelect.innerHTML = '<option value="">-- Select Round --</option>';
        rounds.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            roundSelect.appendChild(opt);
        });
    }

    function toggleOptions(){
        const type = document.getElementById('type').value;
        document.getElementById('mcqOptions').style.display = type === 'MCQ' ? 'block' : 'none';
        document.getElementById('openAnswer').style.display = type === 'OPEN' ? 'block' : 'none';
    }

    async function submitQuestion(event){
        event.preventDefault();

        const quizId = document.getElementById('quiz_id').value;
        const roundId = document.getElementById('round_id').value;
        if(!quizId || !roundId){
            return alert('Please select a quiz and round');
        }

        const type = document.getElementById('type').value;
        const text = document.getElementById('text').value.trim();
        const difficulty = document.getElementById('difficulty').value;

        if(!text) return alert('Enter question text');

        const payload = {
            text, type, difficulty,
            option_a: document.getElementById('option_a').value || null,
            option_b: document.getElementById('option_b').value || null,
            option_c: document.getElementById('option_c').value || null,
            option_d: document.getElementById('option_d').value || null,
            correct_answer: document.getElementById('correct_answer').value || null
        };

        try {
            // Create question
            const res = await fetch('api.php?action=create_question', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            const questionId = data.id;

            // Attach to round
            await fetch('api.php?action=attach_question', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ round_id: roundId, question_id: questionId, order_no: 1 })
            });

            alert('Question created and attached to round!');
            document.getElementById('questionForm').reset();
            toggleOptions(); // reset visibility
        } catch(err){
            console.error(err);
            alert('Error creating question.');
        }
    }

    window.onload = loadQuizzes;
    </script>
</head>
<body class="container py-4">
    <h1>Create Question</h1>
    <form id="questionForm" onsubmit="submitQuestion(event)">
        <div class="mb-3">
            <label class="form-label">Select Quiz</label>
            <select id="quiz_id" class="form-select" onchange="loadRounds()" required></select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Round</label>
            <select id="round_id" class="form-select" required></select>
        </div>

        <div class="mb-3">
            <label class="form-label">Question Text</label>
            <textarea class="form-control" id="text" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select" id="type" onchange="toggleOptions()" required>
                <option value="MCQ">MCQ</option>
                <option value="OPEN">Open-ended</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Difficulty</label>
            <select class="form-select" id="difficulty">
                <option value="EASY">EASY</option>
                <option value="MEDIUM">MEDIUM</option>
                <option value="HARD">HARD</option>
            </select>
        </div>

        <div id="mcqOptions">
            <div class="mb-3">
                <label class="form-label">Option A</label>
                <input type="text" class="form-control" id="option_a">
            </div>
            <div class="mb-3">
                <label class="form-label">Option B</label>
                <input type="text" class="form-control" id="option_b">
            </div>
            <div class="mb-3">
                <label class="form-label">Option C</label>
                <input type="text" class="form-control" id="option_c">
            </div>
            <div class="mb-3">
                <label class="form-label">Option D</label>
                <input type="text" class="form-control" id="option_d">
            </div>
            <div class="mb-3">
                <label class="form-label">Correct Answer (A/B/C/D)</label>
                <input type="text" class="form-control" id="correct_answer">
            </div>
        </div>

        <div id="openAnswer" style="display:none;">
            <div class="mb-3">
                <label class="form-label">Open-ended Answer (optional)</label>
                <input type="text" class="form-control" id="correct_answer_open">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Question</button>
    </form>
</body>
</html>
