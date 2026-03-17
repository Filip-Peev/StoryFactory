<?php

$target_dir = "uploads/";

// 1. Check if file was actually uploaded
if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: index.php?error=upload_failed");
    exit;
}

// 2. Get original filename and extension
$path_info = pathinfo($_FILES["image"]["name"]);
$original_name = $path_info['filename'];
$extension = strtolower($path_info['extension']);

// 3. Clean the filename (Replace special characters/spaces with dashes)
$original_name = preg_replace('/[^A-Za-z0-9\-]/', '-', $original_name);
$original_name = preg_replace('/-+/', '-', $original_name); // Cleanup double dashes
$original_name = trim($original_name, '-'); // Cleanup trailing dashes

// 4. Create the timestamped filename with ROUND BRACKETS
// Result format: Name-(2026-03-17)-(03-45-01).jpg
$timestamp = date("\(Y-m-d\)-\(H-i-s\)");
$filename = $original_name . "-" . $timestamp . "." . $extension;
$target_file = $target_dir . $filename;

// 5. Security: Check if it is actually an allowed image type
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($extension, $allowed_types) || getimagesize($_FILES["image"]["tmp_name"]) === false) {
    header("Location: index.php?error=invalid_file");
    exit;
}

// 6. Move the file and update JSON
if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    // Ensure JSON file exists
    if (!file_exists("data.json")) {
        file_put_contents("data.json", "[]");
    }

    $json_content = file_get_contents("data.json");
    $data = json_decode($json_content, true) ?? [];

    // Add new story to the array

    $newStory = [
        "image" => $filename,
        "line1" => substr($_POST["line1"] ?? "", 0, 200),
        "line2" => substr($_POST["line2"] ?? "", 0, 200),
        "line3" => substr($_POST["line3"] ?? "", 0, 200)
    ];

    $data[] = $newStory;

    // Save back to JSON
    file_put_contents("data.json", json_encode($data, JSON_PRETTY_PRINT));
}

header("Location: index.php");
exit;
