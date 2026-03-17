<?php
ob_start();
session_start();

$folder_name = basename(getcwd());
$display_name = ucwords(str_replace('-', ' ', $folder_name));

if (file_exists('title.txt')) {
    $display_name = file_get_contents('title.txt');
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../hub.php");
    exit;
}

$json_file = "data.json";
$file_exists = file_exists($json_file);
$data = $file_exists ? (json_decode(file_get_contents($json_file), true) ?? []) : [];

if (isset($_GET['set_thumb']) && $file_exists) {
    $index = $_GET['set_thumb'];
    if (isset($data[$index])) {
        $source = "uploads/" . $data[$index]['image'];
        $ext = pathinfo($source, PATHINFO_EXTENSION);

        $old_thumbs = glob("thumbnail.{jpg,jpeg,png,webp,gif}", GLOB_BRACE);
        foreach ($old_thumbs as $ot) unlink($ot);

        if (copy($source, "thumbnail." . $ext)) {
            header("Location: admin.php?msg=thumb_set");
            exit;
        }
    }
}

if (isset($_GET['delete']) && $file_exists) {
    $index = $_GET['delete'];
    if (isset($data[$index])) {
        $file_to_delete = "uploads/" . $data[$index]['image'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }

        array_splice($data, $index, 1);

        file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT));

        header("Location: admin.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($display_name); ?> - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2215%22 fill=%22%23222%22 stroke=%22%23ffaa00%22 stroke-width=%228%22/><text y=%2260%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22%23ffaa00%22 font-family=%22Arial%22 x=%2210%22>S</text><text y=%2285%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22white%22 font-family=%22Arial%22 x=%2245%22>F</text></svg>">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #111;
            color: #fff;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
        }

        .container {
            max-width: 98%;
            margin: auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
            gap: 15px;
        }

        h1 {
            margin: 0;
            font-weight: 300;
            font-size: 1.8rem;
        }

        .view-story {
            color: #00aaff;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #00aaff;
            border-radius: 6px;
            transition: 0.3s;
            white-space: nowrap;
        }

        .view-story:hover {
            background: #00aaff;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            table-layout: fixed;
            border-radius: 8px;
        }

        th,
        td {
            padding: 15px;
            border-bottom: 1px solid #333;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #000;
            color: #666;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .col-img {
            width: 90px;
        }

        .col-file {
            width: 180px;
        }

        .col-action {
            width: 180px;
            text-align: right;
            padding-right: 20px;
        }

        img.thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #444;
        }

        .filename {
            font-size: 10px;
            color: #666;
            font-family: monospace;
        }

        .story-text {
            margin-bottom: 6px;
        }

        .line1 {
            color: #fff;
            font-size: 15px;
            font-weight: 500;
        }

        .line2,
        .line3 {
            color: #aaa;
            font-size: 13px;
        }

        .btn-action {
            text-decoration: none;
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 4px;
            transition: 0.2s;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-thumb {
            color: #ffaa00;
            border: 1px solid #ffaa00;
            margin-right: 5px;
        }

        .btn-thumb:hover {
            background: #ffaa00;
            color: #000;
        }

        .btn-delete {
            color: #ff4444;
            border: 1px solid #ff4444;
        }

        .btn-delete:hover {
            background: #ff4444;
            color: #fff;
        }

        /* Factory Logo Styles */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: #222;
            border: 2px solid #ffaa00;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 20px rgba(255, 170, 0, 0.2);
        }

        .brand-icon::before {
            content: 'S';
            color: #ffaa00;
            font-weight: 900;
            font-size: 24px;
            position: absolute;
            left: 8px;
            top: 2px;
        }

        .brand-icon::after {
            content: 'F';
            color: #fff;
            font-weight: 900;
            font-size: 24px;
            position: absolute;
            right: 8px;
            bottom: 2px;
        }

        .brand-text {
            font-family: 'Segoe UI', sans-serif;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #eee;
        }

        .brand-text span {
            color: #ffaa00;
            font-weight: 900;
        }

        .status-msg {
            background: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        @media screen and (max-width: 768px) {
            thead {
                display: none;
            }

            table,
            tbody,
            tr,
            td {
                display: block;
                width: 100% !important;
            }

            tr {
                margin-bottom: 25px;
                border: 1px solid #333;
                border-radius: 12px;
                background: #1a1a1a;
                padding: 10px;
            }

            td {
                border: none;
                padding: 8px 5px;
                text-align: center;
            }

            .col-action {
                text-align: center;
            }

            img.thumb {
                width: 100%;
                height: auto;
                aspect-ratio: 1/1;
                max-width: 250px;
            }

            .btn-action {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div style="display: flex; align-items: center; gap: 30px;">
                <a href="../../" class="logo-container">
                    <div class="brand-icon"></div>
                    <div class="brand-text">Story<span>Factory</span></div>
                </a>
                <h1 style="border-left: 1px solid #333; padding-left: 30px; opacity: 0.8;">Management</h1>
            </div>

            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="index.php" class="view-story" target="_blank">View Story</a>
                <a href="../../hub.php?logout=1" style="color: #666; text-decoration: none; font-size: 12px; border: 1px solid #333; padding: 7px 12px; border-radius: 6px;">Logout</a>
            </div>
        </header>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'thumb_set'): ?>
            <div class="status-msg">Featured thumbnail updated!</div>
        <?php endif; ?>

        <?php if (!$file_exists || empty($data)): ?>
            <div style="text-align: center; padding: 80px 20px; background: #1a1a1a; border-radius: 8px; border: 2px dashed #333; color: #555;">
                <h2>No images uploaded yet</h2>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th class="col-img">Preview</th>
                        <th class="col-file">Filename</th>
                        <th>Story Content</th>
                        <th class="col-action">Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $i => $story): ?>
                        <tr>
                            <td class="col-img">
                                <img src="uploads/<?php echo htmlspecialchars($story['image']); ?>" class="thumb">
                            </td>
                            <td class="col-file">
                                <div class="filename"><?php echo htmlspecialchars($story['image']); ?></div>
                            </td>
                            <td>
                                <div class="story-text line1"><?php echo htmlspecialchars($story['line1'] ?? ''); ?></div>
                                <div class="story-text line2"><?php echo htmlspecialchars($story['line2'] ?? ''); ?></div>
                                <div class="story-text line3"><?php echo htmlspecialchars($story['line3'] ?? ''); ?></div>
                            </td>
                            <td class="col-action">
                                <a href="?set_thumb=<?php echo $i; ?>" class="btn-action btn-thumb">Set Cover</a>
                                <a href="?delete=<?php echo $i; ?>" class="btn-action btn-delete" onclick="return confirm('Delete image?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>