<?php
// telegram-webhook.php
$TELEGRAM_BOT_TOKEN = '7570694771:AAFAoEF5z1r-4Z3prVHnlG8k11LUcOy38M0'; // Approval bot
$SUPABASE_URL = 'https://qoisqrzlfspzxtnkgyyr.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFvaXNxcnpsZnNwenh0bmtneXlyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjY4MDA0MzMsImV4cCI6MjA4MjM3NjQzM30.jp97VkUx6IFARFzFw3YXJJfBZmdnnCPHM95Q1WAqHnc';

// Debug logging
$logFile = 'telegram-webhook.log';
$update = json_decode(file_get_contents('php://input'), true);
$logData = date('Y-m-d H:i:s') . " - Update received: " . json_encode($update) . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

if (isset($_GET['test'])) {
    echo "Webhook is working!";
    exit;
}

// Get the update
if (!$update) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - No update data received\n", FILE_APPEND);
    exit;
}

// Debug: Log what we received
error_log("Telegram Webhook Received: " . print_r($update, true));

if (isset($update['callback_query'])) {
    $callback = $update['callback_query'];
    $callbackId = $callback['id'];
    $data = $callback['data'];
    $messageId = $callback['message']['message_id'];
    $chatId = $callback['message']['chat']['id'];
    $fromId = $callback['from']['id'];

    $answerUrl = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/answerCallbackQuery";

$answerData = [
    'callback_query_id' => $callbackId,
    'text' => 'Processing approval...'
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/json',
        'content' => json_encode($answerData)
    ]
];

file_get_contents($answerUrl, false, stream_context_create($options));


    // 1. Stop the loading spinner on the button immediately
    $answerUrl = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/answerCallbackQuery";
    $answerData = ['callback_query_id' => $callbackId, 'text' => 'Processing...'];
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($answerData)
        ]
    ];
    @file_get_contents($answerUrl, false, stream_context_create($options));

    // 2. Check if this is an Approve button click
    if (strpos($data, 'approve_') === 0) {
        sendTelegramMessage($chatId, "🟡 Approve button clicked. Processing...", $messageId);
        $parts = explode('_', $data);
        $responseId = $parts[1]; // Get the Response ID from the button
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Processing approval for: $responseId\n", FILE_APPEND);

        try {
            // A. Get the Response Data from DB
            $response = getResponse($responseId);
            
            if (!$response) {
                sendTelegramMessage($chatId, "❌ Error: Response ID $responseId not found.", $messageId);
            } else {
                $requestId = $response['request_id'];
                
                // B. Get the Request Data
                $request = getRequest($requestId);
                
                // C. Check if there are spots left
                $remainingCount = getRemainingCount($requestId);
                
                if ($remainingCount <= 0) {
                    sendTelegramMessage($chatId, "❌ This request is already fully filled.", $messageId);
                } else {
                    // D. Update Response Status to 'approved'
                    updateResponseStatus($responseId, 'approved');
                    
                    // E. Update Count (reduce persons left)
                    $newRemaining = updateRequestCount($requestId);
                    
                    // F. CRITICAL: Notify Affiliate (Unlocks contact info on their screen)
                    notifyAffiliate($response['affiliate_id'], $request);
                    
                    // G. Update Telegram Message (Show Admin it is done)
                    $msg = "✅ *APPROVED!* \n\n" .
                           "*Affiliate:* " . $response['affiliate_name'] . "\n" .
                           "*User ID:* `" . $response['affiliate_user_id'] . "`\n\n" .
                           "🔓 *Contact Info Unlocked:*\n" .
                           "👤 " . $request['contact_name'] . "\n" .
                           "📱 `" . $request['contact_phone'] . "`\n\n" .
                           "📋 *Persons Left:* " . max(0, $newRemaining);

                    sendTelegramMessage($chatId, $msg, $messageId);
                    
                    function editMessageReplyMarkup($chatId, $messageId) {
    global $TELEGRAM_BOT_TOKEN;

    $url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/editMessageReplyMarkup";

    $data = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approved', 'callback_data' => 'done']
                ]
            ]
        ])
    ];

    file_get_contents($url . '?' . http_build_query($data));
}

        } catch (Exception $e) {
            error_log("Approval Error: " . $e->getMessage());
            sendTelegramMessage($chatId, "❌ System Error: " . $e->getMessage(), $messageId);
        }
    }
    
    // Exit to ensure no other output interferes
    exit;
}


function getResponse($responseId) {
    global $SUPABASE_URL, $SUPABASE_KEY;
    
    $url = $SUPABASE_URL . '/rest/v1/request_responses?id=eq.' . $responseId;
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Fetching response from: $url\n", FILE_APPEND);
    
    $options = [
        'http' => [
            'header' => [
                'apikey: ' . $SUPABASE_KEY,
                'Authorization: Bearer ' . $SUPABASE_KEY,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error fetching response: " . error_get_last()['message'] . "\n", FILE_APPEND);
        return null;
    }
    
    $data = json_decode($result, true);
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Response data: " . print_r($data, true) . "\n", FILE_APPEND);
    
    return $data[0] ?? null;
}

function getRequest($requestId) {
    global $SUPABASE_URL, $SUPABASE_KEY;
    
    $url = $SUPABASE_URL . '/rest/v1/requests?id=eq.' . $requestId;
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Fetching request from: $url\n", FILE_APPEND);
    
    $options = [
        'http' => [
            'header' => [
                'apikey: ' . $SUPABASE_KEY,
                'Authorization: Bearer ' . $SUPABASE_KEY,
                'Content-Type: application/json'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error fetching request: " . error_get_last()['message'] . "\n", FILE_APPEND);
        return null;
    }
    
    $data = json_decode($result, true);
    return $data[0] ?? null;
}

function getRemainingCount($requestId) {
    global $SUPABASE_URL, $SUPABASE_KEY;
    
    // Get count of approved responses
    $url = $SUPABASE_URL . '/rest/v1/request_responses?select=id&request_id=eq.' . $requestId . '&status=eq.approved';
    
    $options = [
        'http' => [
            'header' => [
                'apikey: ' . $SUPABASE_KEY,
                'Authorization: Bearer ' . $SUPABASE_KEY,
                'Content-Type: application/json'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error getting remaining count: " . error_get_last()['message'] . "\n", FILE_APPEND);
        return 0;
    }
    
    $responses = json_decode($result, true) ?? [];
    
    // Get request many_count
    $request = getRequest($requestId);
    $total = $request['many_count'] ?? 1;
    
    $remaining = max(0, $total - count($responses));
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Remaining count for request $requestId: $remaining (total: $total, approved: " . count($responses) . ")\n", FILE_APPEND);
    
    return $remaining;
}

function updateResponseStatus($responseId, $status) {
    global $SUPABASE_URL, $SUPABASE_KEY;
    
    $url = $SUPABASE_URL . '/rest/v1/request_responses?id=eq.' . $responseId;
    $data = json_encode([
        'status' => $status,
        'approved_at' => date('c'),
        'updated_at' => date('c')
    ]);
    
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Updating response status: $url\n", FILE_APPEND);
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Update data: $data\n", FILE_APPEND);
    
    $options = [
        'http' => [
            'method' => 'PATCH',
            'header' => [
                'apikey: ' . $SUPABASE_KEY,
                'Authorization: Bearer ' . $SUPABASE_KEY,
                'Content-Type: application/json',
                'Prefer: return=minimal'
            ],
            'content' => $data
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error updating response status: " . error_get_last()['message'] . "\n", FILE_APPEND);
    } else {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Response status updated successfully\n", FILE_APPEND);
    }
}

function updateRequestCount($requestId) {
    $remaining = getRemainingCount($requestId) - 1;
    
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - New remaining count after approval: $remaining\n", FILE_APPEND);
    
    // If no more capacity, update request status
    if ($remaining <= 0) {
        global $SUPABASE_URL, $SUPABASE_KEY;
        
        $url = $SUPABASE_URL . '/rest/v1/requests?id=eq.' . $requestId;
        $data = json_encode([
            'status' => 'filled',
            'updated_at' => date('c')
        ]);
        
        $options = [
            'http' => [
                'method' => 'PATCH',
                'header' => [
                    'apikey: ' . $SUPABASE_KEY,
                    'Authorization: Bearer ' . $SUPABASE_KEY,
                    'Content-Type: application/json',
                    'Prefer: return=minimal'
                ],
                'content' => $data
            ]
        ];
        
        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
        
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Request marked as filled\n", FILE_APPEND);
    }
    
    return $remaining;
}

function notifyAffiliate($affiliateId, $request) {
    global $SUPABASE_URL, $SUPABASE_KEY;
    
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Creating notification for affiliate $affiliateId\n", FILE_APPEND);
    
    // Create notification in database with ALL request data
    $url = $SUPABASE_URL . '/rest/v1/affiliate_notifications';
    $data = json_encode([
        'affiliate_id' => $affiliateId,
        'request_id' => $request['id'],
        'type' => 'approval',
        'message' => 'Your request has been approved!',
        'data' => json_encode([
            'request_code' => $request['request_code'],
            'contact_name' => $request['contact_name'],
            'contact_phone' => $request['contact_phone'],
            'requirements' => $request['requirements'] ?? '',
            'service_class' => $request['service_class'] ?? '',
            'service_type' => $request['service_type'] ?? '',
            'description' => $request['description'] ?? '',
            'location' => $request['location'] ?? '',
            'request_date' => $request['request_date'] ?? '',
            'request_time' => $request['request_time'] ?? ''
        ]),
        'created_at' => date('c'),
        'read' => false
    ]);
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'apikey: ' . $SUPABASE_KEY,
                'Authorization: Bearer ' . $SUPABASE_KEY,
                'Content-Type: application/json',
                'Prefer: return=minimal'
            ],
            'content' => $data
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error creating notification: " . error_get_last()['message'] . "\n", FILE_APPEND);
    } else {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Notification created successfully\n", FILE_APPEND);
    }
}

function sendTelegramMessage($chatId, $message, $replyToMessageId = null) {
    global $TELEGRAM_BOT_TOKEN;
    
    $url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];
    
    if ($replyToMessageId) {
        $data['reply_to_message_id'] = $replyToMessageId;
    }
    
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Sending Telegram message to $chatId\n", FILE_APPEND);
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error sending Telegram message: " . error_get_last()['message'] . "\n", FILE_APPEND);
    } else {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Telegram message sent: " . $result . "\n", FILE_APPEND);
    }
}

function editMessageReplyMarkup($chatId, $messageId) {
    global $TELEGRAM_BOT_TOKEN;
    
    $url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/editMessageReplyMarkup";
    $data = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'reply_markup' => json_encode([
            'inline_keyboard' => [[
                [
                    'text' => '✅ Already Approved',
                    'callback_data' => 'already_approved'
                ]
            ]]
        ])
    ];
    
    file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Editing message reply markup for $messageId\n", FILE_APPEND);
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Error editing message: " . error_get_last()['message'] . "\n", FILE_APPEND);
    } else {
        file_put_contents('telegram-webhook.log', date('Y-m-d H:i:s') . " - Message edited: " . $result . "\n", FILE_APPEND);
    }
}

// Set webhook
if (isset($_GET['setwebhook'])) {
$webhookUrl = 'https://trafficabuja.online/telegram-webhook.php'; // CHANGE THIS TO YOUR DOMAIN    $url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/setWebhook?url={$webhookUrl}";
    $result = file_get_contents($url);
    echo "Webhook set: " . $result;
    exit;
}

// Answer callback query to remove "loading" in Telegram
if (isset($update['callback_query'])) {
    $callbackId = $update['callback_query']['id'];
    $answerUrl = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/answerCallbackQuery";
    $answerData = [
        'callback_query_id' => $callbackId,
        'text' => 'Processing approval...'
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($answerData)
        ]
    ];
    
    $context = stream_context_create($options);
    @file_get_contents($answerUrl, false, $context);
}

// Always send 200 OK response to Telegram
http_response_code(200);
echo "OK";
?>