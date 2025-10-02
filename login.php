<?php
include 'db_connect.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, password FROM players WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();
    
    if (password_verify($password, $hashed_password)) {
        // Password benar, kirim ID dan username dalam format JSON
        echo json_encode(['status' => 'Success', 'player_id' => $id, 'username' => $username]);
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Password salah.']);
    }
} else {
    echo json_encode(['status' => 'Error', 'message' => 'Username tidak ditemukan.']);
}

$stmt->close();
$conn->close();
?>