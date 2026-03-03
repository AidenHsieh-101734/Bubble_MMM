<?php

function sendSuccess($data = null, $message = 'Success', $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function sendError($message = 'An error occurred', $statusCode = 400, $errors = null)
{
    http_response_code($statusCode);
    $response = [
        'success' => false,
        'message' => $message
    ];

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

function sendUnauthorized($message = 'Unauthorized access')
{
    sendError($message, 401);
}

function sendForbidden($message = 'Access forbidden')
{
    sendError($message, 403);
}

function sendNotFound($message = 'Resource not found')
{
    sendError($message, 404);
}

function sendValidationError($errors, $message = 'Validation failed')
{
    sendError($message, 422, $errors);
}

function sendPaginated($data, $page, $pageSize, $total, $message = 'Success')
{
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'pagination' => [
            'page' => (int) $page,
            'pageSize' => (int) $pageSize,
            'total' => (int) $total,
            'totalPages' => ceil($total / $pageSize)
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
