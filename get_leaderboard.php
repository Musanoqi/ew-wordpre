<?php
include 'db_connect.php';

$level_id = $_GET['level_id'];

// Ambil 10 skor teratas (waktu tercepat)
$stmt = $conn->prepare("SELECT p.username, s.time FROM scores s JOIN players p ON s.player_id = p.id WHERE s.level_id = ? ORDER BY s.time ASC LIMIT 10");
$stmt->bind_param("i", $level_id);
$stmt->execute();
$result = $stmt->get_result();

$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}

echo json_encode($leaderboard);

$stmt->close();
$conn->close();
?>