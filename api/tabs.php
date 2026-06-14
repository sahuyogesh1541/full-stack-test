<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    // ── READ ──────────────────────────────────────────────────────────────────
    case 'GET':
        $conn = getConnection();

        if ($id) {
            // Single tab with its slides
            $stmt = $conn->prepare('SELECT * FROM tabs WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $tab = $stmt->get_result()->fetch_assoc();

            if (!$tab) {
                http_response_code(404);
                echo json_encode(['error' => 'Tab not found']);
                break;
            }

            $stmt2 = $conn->prepare('SELECT * FROM slides WHERE tab_id = ? ORDER BY sort_order');
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $tab['slides'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode($tab);
        } else {
            // All tabs with their slides
            $tabs = $conn->query('SELECT * FROM tabs ORDER BY sort_order')->fetch_all(MYSQLI_ASSOC);

            foreach ($tabs as &$tab) {
                $stmt = $conn->prepare('SELECT * FROM slides WHERE tab_id = ? ORDER BY sort_order');
                $stmt->bind_param('i', $tab['id']);
                $stmt->execute();
                $tab['slides'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }

            echo json_encode($tabs);
        }
        $conn->close();
        break;

    // ── CREATE ────────────────────────────────────────────────────────────────
    case 'POST':
        $conn = getConnection();

        if (empty($input['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Title is required']);
            break;
        }

        $title = $conn->real_escape_string($input['title']);
        $order = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        $stmt = $conn->prepare('INSERT INTO tabs (title, sort_order) VALUES (?, ?)');
        $stmt->bind_param('si', $title, $order);

        if ($stmt->execute()) {
            $newId = $conn->insert_id;

            // Insert slides if provided
            if (!empty($input['slides']) && is_array($input['slides'])) {
                $sStmt = $conn->prepare(
                    'INSERT INTO slides (tab_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?)'
                );
                foreach ($input['slides'] as $i => $slide) {
                    $sTitle = $conn->real_escape_string($slide['title'] ?? '');
                    $sDesc  = $conn->real_escape_string($slide['description'] ?? '');
                    $sImg   = $conn->real_escape_string($slide['image_url'] ?? '');
                    $sOrd   = (int)($slide['sort_order'] ?? $i + 1);
                    $sStmt->bind_param('isssi', $newId, $sTitle, $sDesc, $sImg, $sOrd);
                    $sStmt->execute();
                }
            }

            http_response_code(201);
            echo json_encode(['id' => $newId, 'message' => 'Tab created']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create tab']);
        }
        $conn->close();
        break;

    // ── UPDATE ────────────────────────────────────────────────────────────────
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for update']);
            break;
        }

        $conn  = getConnection();
        $title = $conn->real_escape_string($input['title'] ?? '');
        $order = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        $stmt = $conn->prepare('UPDATE tabs SET title = ?, sort_order = ? WHERE id = ?');
        $stmt->bind_param('sii', $title, $order, $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Tab updated']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Tab not found or no changes made']);
        }
        $conn->close();
        break;

    // ── DELETE ────────────────────────────────────────────────────────────────
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for delete']);
            break;
        }

        $conn = getConnection();
        $stmt = $conn->prepare('DELETE FROM tabs WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Tab deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Tab not found']);
        }
        $conn->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
