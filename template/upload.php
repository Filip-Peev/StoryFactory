<?php

$target_dir = "uploads/";

if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: index.php?error=upload_failed");
    exit;
}

$path_info = pathinfo($_FILES["image"]["name"]);
$original_name = $path_info['filename'];
$extension = strtolower($path_info['extension']);

$original_name = preg_replace('/[^A-Za-z0-9\-]/', '-', $original_name);
$original_name = preg_replace('/-+/', '-', $original_name);
$original_name = trim($original_name, '-');

$timestamp = date("\(Y-m-d\)-\(H-i-s\)");
$filename = $original_name . "-" . $timestamp . "." . $extension;
$target_file = $target_dir . $filename;

$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($extension, $allowed_types) || getimagesize($_FILES["image"]["tmp_name"]) === false) {
    header("Location: index.php?error=invalid_file");
    exit;
}

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    if (!file_exists("data.json")) {
        file_put_contents("data.json", "[]");
    }

    $json_content = file_get_contents("data.json");
    $data = json_decode($json_content, true) ?? [];

    $newStory = [
        "image" => $filename,
        "line1" => substr($_POST["line1"] ?? "", 0, 200),
        "line2" => substr($_POST["line2"] ?? "", 0, 200),
        "line3" => substr($_POST["line3"] ?? "", 0, 200)
    ];

    $data[] = $newStory;

    file_put_contents("data.json", json_encode($data, JSON_PRETTY_PRINT));
}

header("Location: index.php");
exit;
