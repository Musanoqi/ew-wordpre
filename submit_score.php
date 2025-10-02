<?php
// submit_score.php (Versi Super Detektif)

$log_file = 'debug_log.txt';
// Baris ini akan menghapus log lama dan membuat file baru setiap kali script dijalankan
file_put_contents($log_file, "--- LOG BARU (submit_score) PADA " . date("Y-m-d H:i:s") . " ---\n");

// Fungsi helper untuk mempermudah penulisan log
function write_log($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

write_log("Script submit_score.php dimulai.");

// Cetak semua data POST yang diterima untuk debugging
write_log("Mendebug isi _POST: " . print_r($_POST, true));

// Cek apakah data yang dibutuhkan benar-benar ada
if (isset($_POST['player_id']) && isset($_POST['level_id']) && isset($_POST['time'])) {

    write_log("Data POST lengkap diterima.");
    include 'db_connect.php';
    write_log("File db_connect.php berhasil di-include.");

    if ($conn->connect_error) {
        write_log("FATAL: Koneksi ke DB GAGAL: " . $conn->connect_error);
        die(); // Hentikan script
    }
    write_log("Koneksi DB berhasil.");

    $player_id = (int)$_POST['player_id'];
    $level_id = (int)$_POST['level_id'];
    $time = (float)$_POST['time'];
    write_log("Data di-assign: player_id=$player_id, level_id=$level_id, time=$time");
    
    // Cek apakah sudah ada skor untuk player dan level ini
    write_log("Mempersiapkan statement SELECT untuk cek skor...");
    $stmt_check = $conn->prepare("SELECT time FROM scores WHERE player_id = ? AND level_id = ?");
    $stmt_check->bind_param("ii", $player_id, $level_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    write_log("Statement SELECT berhasil dieksekusi.");
    
    if ($stmt_check->num_rows > 0) {
        write_log("Skor lama ditemukan. Membandingkan waktu...");
        $stmt_check->bind_result($existing_time);
        $stmt_check->fetch();
        
        if ($time < $existing_time) {
            write_log("Waktu baru ($time) lebih baik dari waktu lama ($existing_time). Mengupdate skor...");
            $stmt_update = $conn->prepare("UPDATE scores SET time = ? WHERE player_id = ? AND level_id = ?");
            $stmt_update->bind_param("dii", $time, $player_id, $level_id);
            if ($stmt_update->execute()) { echo "Success: Score updated"; } else { echo "Error: " . $stmt_update->error; }
            $stmt_update->close();
        } else {
            write_log("Waktu baru tidak lebih baik. Tidak ada update.");
            echo "Success: Score not better";
        }
    } else {
        write_log("Skor lama tidak ditemukan. Memasukkan skor baru...");
        $stmt_insert = $conn->prepare("INSERT INTO scores (player_id, level_id, time) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iid", $player_id, $level_id, $time);
        if ($stmt_insert->execute()) { echo "Success: Score inserted"; } else { echo "Error: " . $stmt_insert->error; }
        $stmt_insert->close();
    }

    $stmt_check->close();
    $conn->close();
    write_log("Koneksi ditutup. Script selesai.");

} else {
    write_log("FATAL: Data POST tidak lengkap.");
    echo "Error: Data skor tidak dikirim dengan lengkap.";
}
?>