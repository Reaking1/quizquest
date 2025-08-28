<!DOCTYPE html>
<html>
<head>
    <title>Manage Rounds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    let quiz_id = null;

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

        // If quiz_id is passed in URL, preselect it
        const params = new URLSearchParams(window.location.search);
        if(params.get('quiz_id')){
            quizSelect.value = params.get('quiz_id');
            quiz_id = params.get('quiz_id');
            loadRounds();
        }
    }

    async function loadRounds(){
        quiz_id = document.getElementById('quiz_id').value;
        if(!quiz_id) return;

        const res = await fetch(`api.php?action=get_rounds&quiz_id=${quiz_id}`);
        const rounds = await res.json();

        const roundsDiv = document.getElementById('roundsList');
        roundsDiv.innerHTML = '';
        rounds.forEach(r => {
            const div = document.createElement('div');
            div.className = 'alert alert-secondary';
            div.innerHTML = `<strong>${r.name}</strong> - Theme: ${r.theme || '-'} | Time: ${r.time_limit_sec}s | Order: ${r.order_no}`;
            roundsDiv.appendChild(div);
        });
    }

    async function createRound(event){
        event.preventDefault();

        if(!quiz_id){
            return alert('Select a quiz first');
        }

        const name = document.getElementById('name').value.trim();
        const theme = document.getElementById('theme').value.trim();
        const time_limit_sec = parseInt(document.getElementById('time_limit_sec').value);
        const order_no = parseInt(document.getElementById('order_no').value);

        if(!name) return alert('Enter a round name');

        const res = await fetch('api.php?action=create_round',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({quiz_id, name, theme, time_limit_sec, order_no})
        });

        const data = await res.json();
        if(data.round_id){
            alert('Round created! ID: ' + data.round_id);
            document.getElementById('roundForm').reset();
            loadRounds();
        } else {
            alert('Error creating round: ' + (data.error || 'Unknown error'));
        }
    }

    window.onload = loadQuizzes;
    </script>
</head>
<body class="container py-4">
    <h1>Manage Rounds</h1>

    <div class="mb-3">
        <label class="form-label">Select Quiz</label>
        <select id="quiz_id" class="form-select" onchange="loadRounds()"></select>
    </div>

    <form id="roundForm" onsubmit="createRound(event)">
        <div class="mb-3">
            <label class="form-label">Round Name</label>
            <input type="text" class="form-control" id="name" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Theme</label>
            <input type="text" class="form-control" id="theme">
        </div>
        <div class="mb-3">
            <label class="form-label">Time Limit (seconds)</label>
            <input type="number" class="form-control" id="time_limit_sec" value="30" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Order No</label>
            <input type="number" class="form-control" id="order_no" value="1" required>
        </div>
        <button class="btn btn-primary">Create Round</button>
    </form>

    <h3 class="mt-4">Existing Rounds</h3>
    <div id="roundsList"></div>
</body>
</html>
