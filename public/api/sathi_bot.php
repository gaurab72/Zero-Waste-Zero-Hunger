<?php
// public/api/sathi_bot.php

header('Content-Type: application/json');

// 1. Basic Security & Config
require_once '../../config/db.php'; // Optional, if we want to log chats later
$config = require_once '../../config/sathi.php';

// Allow CORS if needed (for now same domain is fine)
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

// 2. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please say something!']);
    exit;
}

// 3. Prepare Payload for OpenAI
$apiKey = $config['openai_api_key'];
$model = $config['model'] ?? 'gpt-3.5-turbo';
$systemPrompt = $config['system_prompt'];

// Conversation history could be passed from frontend in a real app
// For a simple widget, we'll just send system + user message (stateless per request, 
// strictly speaking, but usually we want some context. implementing full history 
// requires session storage of chat or passing it back and forth).
// Let's implement a simple "append to history" if provided, or just 1-turn.
// PRO TIP: To make it feel like a chat, we usually pass the last few messages.
// For V1, let's keep it simple: System + User.

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => $userMessage]
];

// If client sends history, use it? (Optional enhancement)
if (isset($input['history']) && is_array($input['history'])) {
    // History should be like [['role'=>'user','content'=>'...'], ['role'=>'assistant','content'=>'...']]
    // We put system prompt first, then history, then current message
    $messages = array_merge(
        [['role' => 'system', 'content' => $systemPrompt]], 
        $input['history'],
        [['role' => 'user', 'content' => $userMessage]]
    );
}

// 4. Call OpenAI API using cURL
$ch = curl_init();

$data = [
    'model' => $model,
    'messages' => $messages,
    'temperature' => 0.7,
];

curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Request Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// 5. Parse Response
$result = json_decode($response, true);

if (isset($result['choices'][0]['message']['content'])) {
    $botReply = $result['choices'][0]['message']['content'];
    echo json_encode(['reply' => $botReply]);
} else {
    // Log error for debugging
    // error_log(print_r($result, true));
    
    // Friendly error or Fallback
    $errMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown error from AI';
    
    // If quota exceeded or other error, fallback to local keyword matching
    // harmless debug log: error_log("OpenAI Error: $errMsg");
    
    $fallbackReply = getFallbackReply($userMessage);
    echo json_encode(['reply' => $fallbackReply]);
}

// Simple Fallback Logic
function getFallbackReply($msg) {
    $msg = strtolower($msg);
    
    if (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false || strpos($msg, 'namaste') !== false) {
        return "Namaste! 🙏 How can I help you today? Ask me about our vision, how to donate, or contact info!";
    }
    
    if (strpos($msg, 'vision') !== false || strpos($msg, 'mission') !== false || strpos($msg, 'about') !== false) {
        return "Our vision is to create a compassionate network where every wedding and birthday party contributes to the well-being of the most vulnerable. We connect event organizers with orphanages to share surplus food. 🍛";
    }
    
    if (strpos($msg, 'contact') !== false || strpos($msg, 'email') !== false || strpos($msg, 'phone') !== false) {
        return "You can reach us at:\n📧 gaurabhamal23@gmail.com\n📞 9815114901\n📍 Pokhara 17, Chhorapatan";
    }
    
    if (strpos($msg, 'team') !== false || strpos($msg, 'founder') !== false) {
        return "Our team is led by Gaurab Hamal (Founder & Lead Developer) and Subodh Paudel (Co-Founder & Operations). Top lads! 👨‍💻";
    }
    
    if (strpos($msg, 'donate') !== false || strpos($msg, 'money') !== false) {
        return "Your support changes lives! Please click the 'Donate 💰' button in the menu or register to donate food.";
    }
    
    return "I'm listening! Could you tell me a bit more? I can help you with our vision, how to donate food, or how to contact our team.";
}
