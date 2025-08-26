<!DOCTYPE html>
<html>
<head>
    <title>Create Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    async function submitQuiz(event){
        event.preventDefault();
        const title = document.getElementById('title').value;
        const res = await fetch('api.php?action=create_quiz', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({title})
        });
        const data = await res.json();
        alert('Quiz Created! ID: '+data.quiz_id);
    }
    </script>
</head>
<body class="container py-4">
    <h1>Create Quiz</h1>
    <form onsubmit="submitQuiz(event)">
        <div class="mb-3">
            <label class="form-label">Quiz Title</label>
            <input type="text" class="form-control" id="title" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Quiz</button>
    </form>
</body>
</html>
