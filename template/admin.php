<?php
ob_start(); // Buffer output to prevent "Headers already sent" errors
session_start();

// 1. SESSION CHECK
// Ensures only the person logged in via hub.php can access this dashboard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirects back to the root hub.php
    header("Location: ../../hub.php");
    exit;
}

$json_file = "data.json";
$file_exists = file_exists($json_file);
$data = $file_exists ? (json_decode(file_get_contents($json_file), true) ?? []) : [];

// HANDLE DELETION
if (isset($_GET['delete']) && $file_exists) {
    $index = $_GET['delete'];
    if (isset($data[$index])) {
        // 1. Delete the physical image file
        $file_to_delete = "uploads/" . $data[$index]['image'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }

        // 2. Remove from array and re-index
        array_splice($data, $index, 1);

        // 3. Save back to JSON
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
    <title>Story Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #111;
            color: #fff;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
            overflow-x: hidden;
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
            width: 130px;
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

        .btn-delete {
            color: #ff4444;
            text-decoration: none;
            font-size: 13px;
            padding: 8px 14px;
            border: 1px solid #ff4444;
            border-radius: 6px;
            transition: 0.2s;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-delete:hover {
            background: #ff4444;
            color: #fff;
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
                padding-right: 5px;
            }

            img.thumb {
                width: 100%;
                height: auto;
                aspect-ratio: 1/1;
                max-width: 250px;
            }

            .btn-delete {
                width: 100%;
                display: block;
            }
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: #1a1a1a;
            border-radius: 8px;
            border: 2px dashed #333;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Story Management</h1>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="index.php" class="view-story">View Story</a>
                <a href="../../hub.php?logout=1"
                    style="color: #666; text-decoration: none; font-size: 12px; border: 1px solid #333; padding: 7px 12px; border-radius: 6px; transition: 0.3s;"
                    onmouseover="this.style.color='#ff4444'; this.style.borderColor='#ff4444';"
                    onmouseout="this.style.color='#666'; this.style.borderColor='#333';">
                    Logout
                </a>
            </div>
        </header>

        <?php if (!$file_exists || empty($data)): ?>
            <div class="empty-state">
                <h2>No data found</h2>
                <p>The <strong>data.json</strong> file doesn't exist yet or has no stories.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th class="col-img">Preview</th>
                        <th class="col-file">Filename</th>
                        <th>Story Content</th>
                        <th class="col-action">Action</th>
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
                                <a href="?delete=<?php echo $i; ?>"
                                    class="btn-delete"
                                    onclick="return confirm('Delete this story permanently?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>