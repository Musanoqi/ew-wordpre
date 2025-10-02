<?php
// register.php (Versi Super Detektif dengan File Logging)

$log_file = 'debug_log.txt';
// Baris ini akan menghapus log lama dan membuat file baru setiap kali script dijalankan
file_put_contents($log_file, "--- LOG BARU PADA " . date("Y-m-d H:i:s") . " ---\n");

// Fungsi helper untuk mempermudah penulisan log
function write_log($message) {
    global $log_file;
    // FILE_APPEND memastikan teks ditambahkan di baris baru, bukan menimpa
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

write_log("Script register.php dimulai.");

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    write_log("Data POST diterima. Username: " . $username);

    // Coba include file koneksi
    if (!@include 'db_connect.php') {
        write_log("FATAL: Gagal me-load file db_connect.php. Periksa nama dan lokasi file.");
        die(); // Hentikan script
    }
    write_log("File db_connect.php berhasil di-load.");

    // Cek koneksi
    if ($conn->connect_error) {
        write_log("FATAL: Koneksi ke DB GAGAL: " . $conn->connect_error);
        die();
    }
    write_log("Koneksi DB berhasil.");

    // Cek duplikasi username
    write_log("Mempersiapkan statement SELECT untuk cek username...");
    $stmt = $conn->prepare("SELECT id FROM players WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    write_log("Statement SELECT berhasil dieksekusi.");

    if ($stmt->num_rows > 0) {
        write_log("Hasil: Username sudah ada. Mengirim balasan 'Error'.");
        echo "Error: Username sudah digunakan.";
    } else {
        write_log("Hasil: Username tersedia. Melakukan hashing password...");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        write_log("Hashing password selesai.");

        write_log("Mempersiapkan statement INSERT...");
        $stmt_insert = $conn->prepare("INSERT INTO players (username, password) VALUES (?, ?)");
        $stmt_insert->bind_param("ss", $username, $hashed_password);
        
        write_log("Mencoba eksekusi INSERT...");
        if ($stmt_insert->execute()) {
            write_log("Eksekusi INSERT berhasil. Mengirim balasan 'Success'.");
            echo "Success";
        } else {
            write_log("FATAL: Eksekusi INSERT GAGAL: " . $stmt_insert->error);
            echo "Error: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }

    $stmt->close();
    $conn->close();
    write_log("Koneksi ditutup. Script selesai.");

} else {
    write_log("FATAL: Data POST username/password tidak lengkap.");
    echo "Error: Username atau password tidak dikirim.";
}
?>