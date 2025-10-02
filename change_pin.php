<?php
header('Content-Type: application/json; charset=utf-8');
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';
$old = $input['old'] ?? '';
$new = $input['new'] ?? '';
if(!preg_match('/^[a-z0-9]+$/',$id) || !preg_match('/^\d{4}$/',$old) || !preg_match('/^\d{4}$/',$new)){
  echo json_encode(['ok'=>false]);
  exit;
}
$file = __DIR__."/cvs/$id.json";
if(!file_exists($file)){
  echo json_encode(['ok'=>false]);
  exit;
}
$content = json_decode(file_get_contents($file), true);
if(($content['pin'] ?? '') !== $old){
  echo json_encode(['ok'=>false]);
  exit;
}
$content['pin'] = $new;
if(file_put_contents($file, json_encode($content, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) !== false){
  echo json_encode(['ok'=>true]);
}else{
  echo json_encode(['ok'=>false]);
}
?>
