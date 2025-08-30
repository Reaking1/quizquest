<!DOCTYPE html>
<html>
<head>
  <title>Update Score</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
async function searchScores(){
    const name = document.getElementById('playerName').value.trim();
    if(!name) return alert("Enter a player name");

    const res = await fetch(`api.php?action=get_scores&player_name=${encodeURIComponent(name)}`);
    
    let scores;
    try {
        scores = await res.json();
    } catch(err){
        console.error(err);
        return alert("Failed to parse scores. Check API response.");
    }

    if(!Array.isArray(scores)){
        console.error(scores);
        return alert("API did not return a valid array of scores.");
    }

    const tbody = document.getElementById('scoresBody');
    tbody.innerHTML = '';
    scores.forEach(s => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${s.attempt_id}</td>
            <td>${s.player_name}</td>
            <td>${s.quiz_title}</td>
            <td>${s.round_name}</td>
            <td>${s.points}</td>
            <td>
              <input type="number" id="points_${s.attempt_id}" value="${s.points}" class="form-control form-control-sm" style="width:90px">
            </td>
            <td>
              <button class="btn btn-warning btn-sm" onclick="updateScore(${s.attempt_id})">Update</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

  async function updateScore(attemptId){
      const newPoints = document.getElementById(`points_${attemptId}`).value;
      if(!newPoints) return alert("Enter new points");

      const res = await fetch('api.php?action=update_score', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ attempt_id: attemptId, points: parseInt(newPoints) })
      });
      const data = await res.json();
      alert(data.message || JSON.stringify(data));
      searchScores(); // refresh table
  }
  </script>
</head>
<body class="container py-4">
  <h1>Update Player Scores</h1>

  <div class="mb-3">
    <label>Search by Player Name</label>
    <input type="text" id="playerName" class="form-control">
  </div>
  <button class="btn btn-primary mb-3" onclick="searchScores()">Search</button>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Attempt ID</th>
        <th>Player</th>
        <th>Quiz</th>
        <th>Round</th>
        <th>Current Points</th>
        <th>New Points</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="scoresBody"></tbody>
  </table>
</body>
</html>
