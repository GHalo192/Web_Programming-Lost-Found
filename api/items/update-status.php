
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';

// Return JSON response
function respond(int $status, array $body): void {
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// Only allow PATCH
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    header('Allow: PATCH');

    respond(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// Check login
if (
    !isset($_SESSION['user_id']) ||
    !is_numeric($_SESSION['user_id']) ||
    (int) $_SESSION['user_id'] <= 0
) {
    respond(401, [
        'success' => false,
        'message' => 'You must log in first.'
    ]);
}

$current_user_id = (int) $_SESSION['user_id'];

// check token goes here



// Get item ID from URL
$item_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if ($item_id === false || $item_id === null || $item_id <= 0) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid item ID.'
    ]);
}

// Read JSON request body
$raw_body = file_get_contents('php://input');
$body = json_decode($raw_body, true);

if (!is_array($body) || !isset($body['status'])) {
    respond(400, [
        'success' => false,
        'message' => 'Missing status field.'
    ]);
}

// Validate new status
$new_status = $body['status'];

$allowed_statuses = ['OPEN', 'RESOLVED', 'CLOSED'];

if (
    !is_string($new_status) ||
    !in_array($new_status, $allowed_statuses, true)
) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid status.'
    ]);
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Find item and lock the row
    $stmt = $conn->prepare("
        SELECT item_id, user_id, status
        FROM Items
        WHERE item_id = ?
        FOR UPDATE
    ");

    $stmt->bind_param('i', $item_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $stmt->close();

    // Check whether item exists
    if (!$item) {
        $conn->rollback();

        respond(404, [
            'success' => false,
            'message' => 'Item not found.'
        ]);
    }

    // Check ownership
    if ((int) $item['user_id'] !== $current_user_id) {
        $conn->rollback();

        respond(403, [
            'success' => false,
            'message' => 'You do not have permission to update this item.'
        ]);
    }

    $current_status = $item['status'];

    // CLOSED is a final state
    if (
        $current_status === 'CLOSED' &&
        $new_status !== 'CLOSED'
    ) {
        $conn->rollback();

        respond(409, [
            'success' => false,
            'message' => 'A closed item cannot change its status.'
        ]);
    }

    // No update needed if status is unchanged
    if ($current_status === $new_status) {
        $conn->commit();

        respond(200, [
            'success' => true,
            'message' => 'Item status is already up to date.',
            'data' => [
                'item_id' => (int) $item_id,
                'status' => $current_status
            ]
        ]);
    }

    // Update item status
    $stmt = $conn->prepare("
        UPDATE Items
        SET status = ?
        WHERE item_id = ?
        AND user_id = ?
        AND status <> 'CLOSED'
    ");

    $stmt->bind_param(
        'sii',
        $new_status,
        $item_id,
        $current_user_id
    );

    $stmt->execute();
    $stmt->close();

    $conn->commit();

    respond(200, [
        'success' => true,
        'message' => 'Item status updated successfully.',
        'data' => [
            'item_id' => (int) $item_id,
            'previous_status' => $current_status,
            'status' => $new_status
        ]
    ]);

} catch (Throwable $e) {

    $conn->rollback();

    error_log('Update item status error: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'An internal server error occurred.'
    ]);
}
