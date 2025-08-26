<!DOCTYPE html>
<html>
<head>
    <title>Manage Rounds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    async function loadQuizzes(){
        const res = await fetch('api.php?action=search_scores'); // quick hack to get quizzes
        const data = await res.json();
        const select = document.getElementById('quiz_id');
        select.innerHTML = '';
        data.forEach(d => {
            if(d.quiz_title){
                const opt = document.createElement('option');
                opt.value = d.quiz_id || d.quiz_id;
                opt.textContent = d.quiz_title;
                select.appendChild(opt);
            }
        });
    }

    async function createRound(event){
        event.preventDefault();
        const quiz_id = document.getElementById('quiz_id').value;
        const name = document.getElementById('name').value;
        const theme = document.getElementById('theme').value;
        const time_limit_sec = parseInt(document.getElementById('time_limit_sec').value);
        const order_no = parseInt(document.getElementById('order_no').value);

        const res = await fetch('api.php?action=create_round',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({quiz_id,name,theme,time_limit_sec,order_no})
        });
        const data = await res.json();
        alert('Round Created! ID: '+data.round_id);
    }

    window.onload = loadQuizzes;
    </script>
</head>
<body class="container py-4">
    <h1>Manage Rounds</h1>
    <form onsubmit="createRound(event)">
        <div class="mb-3">
            <label class="form-label">Select Quiz</label>
            <select id="quiz_id" class="form-select" required></select>
        </div>
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
</body>
</html>
