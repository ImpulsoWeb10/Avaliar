<?php
header('Content-Type: application/json');

// 1. Defina o seu Access Token do Mercado Pago
$access_token = "SEU_ACCESS_TOKEN_AQUI";

// 2. Recebe os dados enviados pelo front-end
$input = json_decode(file_get_contents('php_input'), true);
$slug = isset($input['slug']) ? trim($input['slug']) : 'meu-negocio';
$email = isset($input['email']) ? trim($input['email']) : 'cliente@email.com';

// 3. Monta o payload para cobrança via PIX
$data = [
    "transaction_amount" => 7.99,
    "description" => "Personalização de Link: avalia.site/" . $slug,
    "payment_method_id" => "pix",
    "payer" => [
        "email" => $email,
        "first_name" => "Cliente",
        "last_name" => "Impulso"
    ],
    "notification_url" => "https://avaliar.impulsoweb10.com.br/notificacao.php", // Seu webhook
    "external_reference" => $slug // Identificador interno para saber qual slug foi pago
];

// 4. Envia para a API do Mercado Pago
$ch = curl_init('https://api.mercadopago.com/v1/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token,
    'X-Idempotency-Key: ' . uniqid() // Garante que a cobrança não seja duplicada
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// 5. Retorna o QR Code e o Copia e Cola para a sua tela
if (isset($result['point_of_interaction'])) {
    $qr_code = $result['point_of_interaction']['transaction_data']['qr_code']; // Código copia e cola
    $qr_code_base64 = $result['point_of_interaction']['transaction_data']['qr_code_base64']; // Imagem Base64

    echo json_encode([
        'status' => 'success',
        'payment_id' => $result['id'],
        'qr_code' => $qr_code,
        'qr_code_base64' => $qr_code_base64
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao gerar pagamento',
        'details' => $result
    ]);
}
