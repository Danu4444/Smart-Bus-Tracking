<?php
$data = json_encode(['phone' => '1234567890', 'password' => 'wrong']);
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $data,
        'ignore_errors' => true
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents('https://smartbustracker.infinityfreeapp.com/api/passengerLogin.php', false, $context);
echo "RESPONSE:\n" . $result;
?>
