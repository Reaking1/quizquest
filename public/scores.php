<!DOCTYPE html>
<html>
<head>
  <title>Search Total Scores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
  async function searchScores(){
      const name = document.getElementById('playerName').value.trim();
      const quizId = document.getElementById('quizSelect').value;

      const params = new URLSearchParams();
      if(name) params.append('player_name', name);
      if(quizId) params.append('quiz_id', quizId);

      try {
          const res = await fetch('api.php?action=get_total_scores&'+params.toString());
          const data = await res.json();

          const table = document.getElementById('results');
          table.innerHTML = `<tr>
            <th>Player</th>
            <th>Quiz</th>
            <th>Total Points</th>
            <th>Last Answered</th>
          </tr>`;

          if(Array.isArray(data)){
              data.forEach(r=>{
                  table.innerHTML += `<tr>
                      <td>${r.player_name}</td>
                      <td>${r.quiz_title}</td>
                      <td>${r.total_points}</td>
                      <td>${r.last_answered}</td>
                  </tr>`;
              });
          } else if(data.error){
              alert("Error: " + data.error);
          }
      } catch(err){
          console.error(err);
          alert("Network or server error while fetching scores.");
      }
  }

  async function loadQuizzes(){
      const res = await fetch('api.php?action=get_quizzes');
      const quizzes = await res.json();
      const sel = document.getElementById('quizSelect');
      sel.innerHTML = '<option value="">All Quizzes</option>';
      quizzes.forEach(q => {
          const opt = document.createElement('option');
          opt.value = q.id;
          opt.textContent = q.title;
          sel.appendChild(opt);
      });
  }

  window.onload = loadQuizzes;
  </script>
</head>
<body class="container py-4">
  <h1>Search Total Scores</h1>
  <div class="row mb-3">
    <div class="col"><input id="playerName" class="form-control" placeholder="Player Name"></div>
    <div class="col">
      <select id="quizSelect" class="form-select"></select>
    </div>
    <div class="col"><button class="btn btn-primary" onclick="searchScores()">Search</button></div>
  </div>

  <table class="table table-bordered" id="results"></table>
</body>
</html>
