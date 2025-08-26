<?php
require_once __DIR__ . '/../config/db.php';


class ScoreService {
public static function recalcScore(int $playerId, int $quizId): int {
$pdo = DB::pdo();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(points),0) AS pts FROM attempts WHERE player_id=? AND quiz_id=?");
$stmt->execute([$playerId, $quizId]);
$total = (int)$stmt->fetchColumn();


$up = $pdo->prepare("INSERT INTO scores (player_id, quiz_id, total_points)
VALUES (?,?,?)
ON DUPLICATE KEY UPDATE total_points=VALUES(total_points)");
$up->execute([$playerId, $quizId, $total]);
return $total;
}
}