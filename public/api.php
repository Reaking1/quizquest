<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/validators.php';
require_once __DIR__ . '/../src/ScoreService.php';

$pdo = DB::pdo();
$action = $_GET['action'] ?? 'ping';

header('Content-Type: application/json');

function respond($data, int $code = 200): void {
  http_response_code($code);
  echo json_encode($data);
}

try {
  switch ($action) {
    case 'ping':
      respond(['ok' => true, 'ts' => date('c')]);
      break;

    /* 1) Create Quiz Questions */
   case 'create_question': {
    $b = json_input();
    require_fields($b, ['text', 'type', 'difficulty']);

    if ($b['type'] === 'MCQ') {
        require_fields($b, ['option_a','option_b','option_c','option_d','correct_answer']);

        // Map the selected key (A/B/C/D) to full text
        $optionMap = [
            'A' => $b['option_a'],
            'B' => $b['option_b'],
            'C' => $b['option_c'],
            'D' => $b['option_d'],
        ];
        $correctAnswerFullText = $optionMap[$b['correct_answer']] ?? null;
    } else {
        $correctAnswerFullText = $b['correct_answer'] ?? null;
    }

    $sql = "INSERT INTO questions
            (text, type, difficulty, option_a, option_b, option_c, option_d, correct_answer)
            VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $b['text'],
        $b['type'],
        $b['difficulty'],
        $b['option_a'] ?? null,
        $b['option_b'] ?? null,
        $b['option_c'] ?? null,
        $b['option_d'] ?? null,
        $correctAnswerFullText,
    ]);

    respond(['id' => (int)$pdo->lastInsertId()], 201);
    break;
}

    /* 2) Manage Quiz & Rounds */
    case 'create_quiz': {
      $b = json_input();
      require_fields($b, ['title']);
      $stmt = $pdo->prepare("INSERT INTO quizzes (title) VALUES (?)");
      $stmt->execute([$b['title']]);
      respond(['quiz_id' => (int)$pdo->lastInsertId()], 201);
      break;
    }

    case 'create_round': {
      $b = json_input();
      require_fields($b, ['quiz_id','name','time_limit_sec','order_no']);
      $stmt = $pdo->prepare(
        "INSERT INTO rounds (quiz_id, name, theme, time_limit_sec, order_no)
         VALUES (?,?,?,?,?)"
      );
      $stmt->execute([
        (int)$b['quiz_id'],
        $b['name'],
        $b['theme'] ?? null,
        (int)$b['time_limit_sec'],
        (int)$b['order_no'],
      ]);
      respond(['round_id' => (int)$pdo->lastInsertId()], 201);
      break;
    }

    case 'attach_question': {
      $b = json_input();
      require_fields($b, ['round_id','question_id','order_no']);
      $stmt = $pdo->prepare(
        "INSERT INTO round_questions (round_id, question_id, order_no)
         VALUES (?,?,?)"
      );
      $stmt->execute([
        (int)$b['round_id'],
        (int)$b['question_id'],
        (int)$b['order_no'],
      ]);
      respond(['ok' => true], 201);
      break;
    }

    /* 3) Record Player Scores (via attempts) */
    case 'record_attempt': {
      $b = json_input();
      require_fields($b, ['player_name','quiz_id','round_id','question_id','answer_text']);

      $quizId = (int)$b['quiz_id'];
      $roundId = (int)$b['round_id'];
      $questionId = (int)$b['question_id'];
      $answerText = (string)$b['answer_text'];

      $pdo->beginTransaction();

      // ensure player exists
      $sel = $pdo->prepare("SELECT id FROM players WHERE name=?");
      $sel->execute([$b['player_name']]);
      $playerId = $sel->fetchColumn();
      if (!$playerId) {
        $ins = $pdo->prepare("INSERT INTO players (name) VALUES (?)");
        $ins->execute([$b['player_name']]);
        $playerId = (int)$pdo->lastInsertId();
      } else {
        $playerId = (int)$playerId;
      }

      // verify round belongs to quiz
      $rq = $pdo->prepare("SELECT quiz_id FROM rounds WHERE id=?");
      $rq->execute([$roundId]);
      $round = $rq->fetch();
      if (!$round) throw new RuntimeException('Round not found');
      if ((int)$round['quiz_id'] !== $quizId) {
        throw new RuntimeException('Round does not belong to the specified quiz');
      }

      // verify question exists and is attached to the round
      $q = $pdo->prepare("SELECT type, difficulty, correct_answer FROM questions WHERE id=?");
      $q->execute([$questionId]);
      $qrow = $q->fetch();
      if (!$qrow) throw new RuntimeException('Question not found');

      $map = $pdo->prepare("SELECT 1 FROM round_questions WHERE round_id=? AND question_id=?");
      $map->execute([$roundId, $questionId]);
      if (!$map->fetchColumn()) {
        throw new RuntimeException('Question not attached to this round');
      }

      // auto-mark MCQ only
      $isCorrect = 0;
      if ($qrow['type'] === 'MCQ') {
        $isCorrect = (strcasecmp(trim($answerText), trim((string)$qrow['correct_answer'])) === 0) ? 1 : 0;
      }

      // simple difficulty weighting
      $w = ['EASY' => 1, 'MEDIUM' => 2, 'HARD' => 3];
      $points = $isCorrect ? ($w[$qrow['difficulty']] ?? 1) : 0;

      $insA = $pdo->prepare(
        "INSERT INTO attempts (player_id, quiz_id, round_id, question_id, answer_text, is_correct, points)
         VALUES (?,?,?,?,?,?,?)"
      );
      $insA->execute([$playerId, $quizId, $roundId, $questionId, $answerText, $isCorrect, $points]);

      // update aggregate
      $total = ScoreService::recalcScore($playerId, $quizId);
      $pdo->commit();

      respond([
        'player_id'     => $playerId,
        'is_correct'    => (bool)$isCorrect,
        'earned_points' => $points,
        'total_points'  => $total,
      ], 201);
      break;
    }

    /* 4) Search Past Scores */
    case 'search_scores': {
      $name = $_GET['name'] ?? null;
      $date = $_GET['date'] ?? null;          // YYYY-MM-DD
      $difficulty = $_GET['difficulty'] ?? null; // EASY | MEDIUM | HARD

      $sql = "SELECT a.answered_at,
                     p.name,
                     qz.title AS quiz,
                     a.points,
                     a.is_correct,
                     qu.difficulty
              FROM attempts a
              JOIN players  p  ON p.id = a.player_id
              JOIN quizzes  qz ON qz.id = a.quiz_id
              LEFT JOIN questions qu ON qu.id = a.question_id
              WHERE 1=1";
      $args = [];

      if ($name) {
        $sql .= " AND p.name LIKE ?";
        $args[] = "%$name%";
      }
      if ($date) {
        $sql .= " AND DATE(a.answered_at) = ?";
        $args[] = $date;
      }
      if ($difficulty) {
        $sql .= " AND qu.difficulty = ?";
        $args[] = $difficulty;
      }

      $sql .= " ORDER BY a.answered_at DESC LIMIT 200";

      $stmt = $pdo->prepare($sql);
      $stmt->execute($args);
      respond($stmt->fetchAll());
      break;
    }

    /* 5) Update Scores: bonus/correction */
    case 'adjust_score': {
      $b = json_input();
      require_fields($b, ['player_name','quiz_id','delta']);

      $quizId = (int)$b['quiz_id'];
      $delta  = (int)$b['delta'];

      $pdo->beginTransaction();

      $sel = $pdo->prepare("SELECT id FROM players WHERE name=?");
      $sel->execute([$b['player_name']]);
      $pid = $sel->fetchColumn();
      if (!$pid) throw new RuntimeException('Player not found');
      $pid = (int)$pid;

      // Use NULL round_id & question_id to keep FKs valid
      $ins = $pdo->prepare(
        "INSERT INTO attempts (player_id, quiz_id, round_id, question_id, answer_text, is_correct, points)
         VALUES (?,?,?,?,?,?,?)"
      );
      $ins->execute([$pid, $quizId, null, null, 'ADJUSTMENT', 1, $delta]);

      $total = ScoreService::recalcScore($pid, $quizId);
      $pdo->commit();

      respond(['ok' => true, 'new_total' => $total], 201);
      break;
    }

    /* 6) Delete Quiz Data (cascade) */
    case 'delete_quiz': {
      $b = json_input();
      require_fields($b, ['quiz_id']);
      $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id=?");
      $stmt->execute([(int)$b['quiz_id']]);
      respond(['deleted' => $stmt->rowCount() > 0]);
      break;
    }

    /* 7) Leaderboard */
    case 'leaderboard': {
      $quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
      $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
      $limit  = max(1, min($limit, 100)); // clamp

      if ($quizId > 0) {
        $sql = "SELECT quiz_id, quiz_title, player_id, player_name, total_points
                FROM v_leaderboard
                WHERE quiz_id = ?
                ORDER BY total_points DESC, player_name ASC
                LIMIT $limit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quizId]);
      } else {
        $sql = "SELECT quiz_id, quiz_title, player_id, player_name, total_points
                FROM v_leaderboard
                ORDER BY total_points DESC, player_name ASC
                LIMIT $limit";
        $stmt = $pdo->query($sql);
      }

      respond($stmt->fetchAll());
      break;
    }

    // in api.php
case 'register_player': {
    $b = json_input();
    require_fields($b, ['name','email']);
    $sel = $pdo->prepare("SELECT id FROM players WHERE email=?");
    $sel->execute([$b['email']]);
    $pid = $sel->fetchColumn();
    if (!$pid) {
        $ins = $pdo->prepare("INSERT INTO players (name,email) VALUES (?,?)");
        $ins->execute([$b['name'],$b['email']]);
        $pid = (int)$pdo->lastInsertId();
    }
    respond(['player_id'=>$pid,'name'=>$b['name'],'email'=>$b['email']]);
    break;
}

case 'get_questions_by_round': {
    $round_id = (int)($_GET['round_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT q.* 
                           FROM questions q 
                           JOIN round_questions rq ON rq.question_id=q.id 
                           WHERE rq.round_id=? ORDER BY rq.order_no");
    $stmt->execute([$round_id]);
    respond($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;
}
/* Get all quizzes */
case 'get_quizzes': {
    $stmt = $pdo->query("SELECT id, title FROM quizzes ORDER BY id DESC");
    respond($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;
}
case 'get_rounds': {
    $quiz_id = (int)($_GET['quiz_id'] ?? 0);
    if($quiz_id <= 0) {
        respond([], 400);
        break;
    }

    $stmt = $pdo->prepare("SELECT id, name, theme, time_limit_sec, order_no 
                           FROM rounds WHERE quiz_id=? ORDER BY order_no");
    $stmt->execute([$quiz_id]);
    respond($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;
}


    default:
      respond(['error' => 'Unknown action'], 404);
  }
} catch (Throwable $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  respond(['error' => $e->getMessage()], 400);
}
