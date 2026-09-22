<?php
// Recebe a notificação do Mercado Pago
$access_token = "SEU_ACCESS_TOKEN_AQUI";
$body = json_decode(file_get_contents('php://input'), true);

if (isset($body['data']['id']) && $body['type'] === 'payment') {
    $payment_id = $body['data']['id'];

    // Consulta o status real do pagamento no Mercado Pago por segurança
    $ch = curl_init("https://api.mercadopago.com/v1/payments/{$payment_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    $payment_info = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // Se o pagamento foi aprovado
    if (isset($payment_info['status']) && $payment_info['status'] === 'approved') {
        $slug_pago = $payment_info['external_reference'];
        
        // TODO: Aqui você executa o UPDATE no seu banco de dados
        // ex: UPDATE negocios SET status = 'pago' WHERE slug = '$slug_pago';
    }
}

http_response_code(200); // Retorna OK para o Mercado Pago
