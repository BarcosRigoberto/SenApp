<?php
<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if ($data['accion'] === 'incrementar') {
    if (!isset($_SESSION['correctas'])) {
        $_SESSION['correctas'] = 0;
    }
    $_SESSION['correctas']++;
    
    if (!isset($_SESSION['ejercicios_completados'])) {
        $_SESSION['ejercicios_completados'] = [];
    }
    if (!isset($_SESSION['ejercicios_completados'][$data['nivel']])) {
        $_SESSION['ejercicios_completados'][$data['nivel']] = [];
    }
    $_SESSION['ejercicios_completados'][$data['nivel']][] = $data['id'];
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'correctas' => $_SESSION['correctas']]);
}