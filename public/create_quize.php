<!DOCTYPE html>
<html>
<head>
    <title>Create Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    async function submitQuiz(event){
        event.preventDefault();
        const title = document.getElementById('title').value.trim();
        if(!title){
            alert("Please enter a quiz title.");
            return;
        }

        const res = await fetch('api.php?action=create_quiz', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({title})
        });

        const data = await res.json();
        if(data.quiz_id){
            document.getElementById("result").innerHTML = `
                <div class="alert alert-success">
                    ✅ Quiz Created Successfully!<br>
                    <strong>Quiz ID:</strong> ${data.quiz_id}<br>
                    <strong>Title:</strong> ${title}
                </div>
                <a href="create_round.php?quiz_id=${data.quiz_id}" class="btn btn-secondary mt-2">Add Rounds</a>
            `;
            document.getElementById("quizForm").reset();
        } else {
            alert("Error creating quiz.");
        }
    }
    </script>
</head>
<body class="container py-4">
    <h1>Create Quiz</h1>

    <form id="quizForm" onsubmit="submitQuiz(event)">
        <div class="mb-3">
            <label class="form-label">Quiz Title</label>
            <input type="text" class="form-control" id="title" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Quiz</button>
    </form>

    <div id="result" class="mt-3"></div>
</body>
</html>
