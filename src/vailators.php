<?php
function require_fields(array $body, array $fields) {
foreach ($fields as $f) {
if (!isset($body[$f]) || $body[$f] === '') {
http_response_code(422);
echo json_encode(["error" => "Missing field: $f"]);
exit;
}
}
}


function json_input(): array {
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
return is_array($data) ? $data : $_POST;
}


function json_out($data) {
header('Content-Type: application/json');
echo json_encode($data);
}