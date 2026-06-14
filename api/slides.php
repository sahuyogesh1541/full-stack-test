<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = isset($_GET['id'])     ? (int)$_GET['id']     : null;
$tabId  = isset($_GET['tab_id']) ? (int)$_GET['tab_id'] : null;

switch ($method) {

    case 'GET':
        $conn = getConnection();

        if ($id) {
            $stmt = $conn->prepare('SELECT * FROM slides WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $slide = $stmt->get_result()->fetch_assoc();
            echo $slide ? json_encode($slide) : (http_response_code(404) && json_encode(['error' => 'Slide not found']));
        } elseif ($tabId) {
            $stmt = $conn->prepare('SELECT * FROM slides WHERE tab_id = ? ORDER BY sort_order');
            $stmt->bind_param('i', $tabId);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        } else {
            echo json_encode($conn->query('SELECT * FROM slides ORDER BY tab_id, sort_order')->fetch_all(MYSQLI_ASSOC));
        }
        $conn->close();
        break;

    case 'POST':
        $conn = getConnection();
        $required = ['tab_id', 'title', 'image_url'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field is required"]);
                $conn->close();
                exit;
            }
        }

        $tabId   = (int)$input['tab_id'];
        $title   = $conn->real_escape_string($input['title']);
        $desc    = $conn->real_escape_string($input['description'] ?? '');
        $imgUrl  = $conn->real_escape_string($input['image_url']);
        $order   = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        $stmt = $conn->prepare(
            'INSERT INTO slides (tab_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('isssi', $tabId, $title, $desc, $imgUrl, $order);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['id' => $conn->insert_id, 'message' => 'Slide created']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create slide']);
        }
        $conn->close();
        break;

    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); break; }
        $conn   = getConnection();
        $title  = $conn->real_escape_string($input['title'] ?? '');
        $desc   = $conn->real_escape_string($input['description'] ?? '');
        $imgUrl = $conn->real_escape_string($input['image_url'] ?? '');
        $order  = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        $stmt = $conn->prepare(
            'UPDATE slides SET title = ?, description = ?, image_url = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->bind_param('sssii', $title, $desc, $imgUrl, $order, $id);
        $stmt->execute();
        echo $stmt->affected_rows > 0
            ? json_encode(['message' => 'Slide updated'])
            : (http_response_code(404) && json_encode(['error' => 'Slide not found']));
        $conn->close();
        break;

    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID required']); break; }
        $conn = getConnection();
        $stmt = $conn->prepare('DELETE FROM slides WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo $stmt->affected_rows > 0
            ? json_encode(['message' => 'Slide deleted'])
            : (http_response_code(404) && json_encode(['error' => 'Slide not found']));
        $conn->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
