<!DOCTYPE html>
<html>
<head>
  <title>Delete Data</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
  async function deleteData(type){
      let idField = document.getElementById(type+'Id');
      if(!idField.value) return alert("Enter ID");
      const url = `api.php?action=delete_${type}&${type}_id=${idField.value}`;
      const res = await fetch(url);
      const data = await res.json();
      alert(data.message || JSON.stringify(data));
  }
  </script>
</head>
<body class="container py-4">
  <h1>Delete Quiz Data</h1>
  <div class="mb-3">
    <label>Quiz ID</label>
    <input type="number" id="quizId" class="form-control">
    <button class="btn btn-danger mt-1" onclick="deleteData('quiz')">Delete Quiz</button>
  </div>
  <div class="mb-3">
    <label>Round ID</label>
    <input type="number" id="roundId" class="form-control">
    <button class="btn btn-danger mt-1" onclick="deleteData('round')">Delete Round</button>
  </div>
  <div class="mb-3">
    <label>Attempt ID</label>
    <input type="number" id="attemptId" class="form-control">
    <button class="btn btn-danger mt-1" onclick="deleteData('attempt')">Delete Attempt</button>
  </div>
</body>
</html>
