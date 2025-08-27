<!DOCTYPE html>
<html>
<head>
    <title>Create Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    async function submitQuestion(event){
        event.preventDefault();
        const type = document.getElementById('type').value;
        const b = {
            text: document.getElementById('text').value,
            type: type,
            difficulty: document.getElementById('difficulty').value,
            option_a: document.getElementById('option_a').value,
            option_b: document.getElementById('option_b').value,
            option_c: document.getElementById('option_c').value,
            option_d: document.getElementById('option_d').value,
            correct_answer: document.getElementById('correct_answer').value
        };
        const res = await fetch('api.php?action=create_question', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(b)
        });
        const data = await res.json();
        alert('Question Created! ID: '+data.id);
    }
    function toggleOptions(){
        const type = document.getElementById('type').value;
        document.getElementById('mcqOptions').style.display = type==='MCQ'?'block':'none';
    }
    </script>
</head>
<body class="container py-4">
    <h1>Create Question</h1>
    <form onsubmit="submitQuestion(event)">
        <div class="mb-3">
            <label class="form-label">Question Text</label>
            <textarea class="form-control" id="text" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select" id="type" onchange="toggleOptions()">
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
</div>

<!-- Always show this -->
<div class="mb-3">
    <label class="form-label" id="correctLabel">Correct Answer (A/B/C/D)</label>
    <input type="text" class="form-control" id="correct_answer">
</div>


        <button type="submit" class="btn btn-primary">Create Question</button>
    </form>
</body>
</html>
