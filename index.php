<?php
ob_start();
session_start();

// Simple check: You are either admin or you are a visitor.
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$root_stories = 'stories/';

function deleteDirectory($dir)
{
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

if (!$isAdmin && ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['msg']))) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') die("Unauthorized");
}

if ($isAdmin && isset($_POST['action']) && $_POST['action'] == 'delete' && !empty($_POST['folder_name'])) {
    $target = $root_stories . basename($_POST['folder_name']);
    if (file_exists($target) && is_dir($target)) {
        deleteDirectory($target);
    }
    header("Location: index.php?msg=deleted");
    exit;
}

// Handle Rename
if ($isAdmin && isset($_POST['action']) && $_POST['action'] == 'rename' && !empty($_POST['old_name']) && !empty($_POST['new_name'])) {
    $old_folder = $root_stories . basename($_POST['old_name']);

    $new_true_name = preg_replace('/[<>\"\'&]/', '', strip_tags($_POST['new_name']));

    $new_safe_name = strtolower(preg_replace('/[^A-Za-z0-9]/', ' ', $new_true_name));
    $new_safe_name = str_replace(' ', '-', trim(preg_replace('/ +/', ' ', $new_safe_name)));
    $new_folder = $root_stories . $new_safe_name;

    if (file_exists($old_folder)) {
        file_put_contents($old_folder . '/title.txt', $new_true_name);

        if (!file_exists($new_folder)) {
            rename($old_folder, $new_folder);
        }
    }
    header("Location: index.php?msg=renamed");
    exit;
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['story_name']) && !isset($_POST['action'])) {
    $original_name = preg_replace('/[<>\"\'&]/', '', strip_tags($_POST['story_name']));
    $folder_name = strtolower(preg_replace('/[^A-Za-z0-9]/', ' ', $original_name));
    $folder_name = str_replace(' ', '-', trim(preg_replace('/ +/', ' ', $folder_name)));
    $target_dir = $root_stories . $folder_name;

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
        mkdir($target_dir . '/uploads', 0777, true);

        file_put_contents($target_dir . '/title.txt', $original_name);

        file_put_contents($target_dir . '/date.txt', date("F j, Y"));

        $files_to_copy = ['index.php', 'admin.php', 'upload.php'];
        foreach ($files_to_copy as $file) {
            $src = 'template/' . $file;
            $dest = $target_dir . '/' . $file;
            if (file_exists($src)) copy($src, $dest);
        }
    }
    header("Location: index.php?msg=created");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Story Factory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2215%22 fill=%22%23222%22 stroke=%22%23ffaa00%22 stroke-width=%228%22/><text y=%2260%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22%23ffaa00%22 font-family=%22Arial%22 x=%2210%22>S</text><text y=%2285%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22white%22 font-family=%22Arial%22 x=%2245%22>F</text></svg>">
    <style>
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

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #111;
            color: white;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .create-box {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #333;
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 6px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        .primary-btn {
            width: 100%;
            padding: 12px;
            background: #00aaff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .story-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .story-card {
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card-link {
            display: block;
            padding: 30px 20px;
            text-align: center;
            text-decoration: none;
            color: white;
            flex-grow: 1;
            transition: 0.2s;
        }

        .card-link:hover {
            background: #222;
        }

        .folder-icon {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }

        .card-actions {
            display: flex;
            border-top: 1px solid #333;
            background: #151515;
        }

        .action-btn {
            flex: 1;
            padding: 12px 5px;
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.2s;
            text-decoration: none;
            text-align: center;
            border-right: 1px solid #333;
        }

        .action-btn:last-child {
            border-right: none;
        }

        .action-btn:hover {
            background: #222;
            color: #fff;
        }

        .btn-delete:hover {
            color: #ff4444;
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

        .story-date {
            display: block;
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 8px;
            font-weight: bold;
        }

        .story-date::before {
            content: '●';
            color: #ffaa00;
            margin-right: 5px;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-flex">
            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/" class="logo-container">
                <div class="brand-icon"></div>
                <div class="brand-text">Story<span>Factory</span></div>
            </a>
            <?php if ($isAdmin): ?>
                <a href="hub.php?logout=1" style="color:#666; text-decoration:none; font-size:12px; border: 1px solid #333; padding: 5px 10px; border-radius: 4px;">Exit Admin Mode</a>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin && isset($_GET['msg'])): ?>
            <div class="status-msg">Action successful: <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="create-box">
                <form method="post">
                    <input type="text" name="story_name" placeholder="Enter New Story Title..." required>
                    <button type="submit" class="primary-btn">Create a Story</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="story-grid">
            <?php
            if (!is_dir($root_stories)) mkdir($root_stories, 0777, true);

            $dirs = array_filter(glob($root_stories . '*'), 'is_dir');
            foreach ($dirs as $dir):
                $folder_name = basename($dir);
                $title_file = $dir . '/title.txt';
                $date_file = $dir . '/date.txt';

                $display_name = file_exists($title_file) ? file_get_contents($title_file) : ucwords(str_replace('-', ' ', $folder_name));

                $display_date = file_exists($date_file) ? file_get_contents($date_file) : date("F j, Y", filectime($dir));
            ?>
                <div class="story-card">
                    <a href="<?php echo $dir; ?>/index.php" target="_blank" class="card-link">
                        <span class="folder-icon">📁</span>
                        <strong><?php echo htmlspecialchars($display_name); ?></strong>
                        <span class="story-date"><?php echo $display_date; ?></span>
                    </a>
                    <?php if ($isAdmin): ?>
                        <div class="card-actions">
                            <button class="action-btn" onclick="renameFolder('<?php echo $folder_name; ?>', '<?php echo addslashes($display_name); ?>')">Rename</button>
                            <a href="<?php echo $dir; ?>/admin.php" target="_blank" class="action-btn">Admin</a>
                            <button class="action-btn btn-delete" onclick="deleteFolder('<?php echo $folder_name; ?>', '<?php echo addslashes($display_name); ?>')">Delete</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php if ($isAdmin): ?>
        <form id="masterForm" method="post" style="display:none;">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="old_name" id="oldNameInput">
            <input type="hidden" name="new_name" id="newNameInput">
            <input type="hidden" name="folder_name" id="folderNameInput">
        </form>

        <script>
            function renameFolder(folderName, trueName) {
                let newName = prompt("Rename story '" + trueName + "' to:", trueName);
                if (newName && newName.trim() !== "" && newName.trim() !== trueName) {
                    document.getElementById('formAction').value = 'rename';
                    document.getElementById('oldNameInput').value = folderName;
                    document.getElementById('newNameInput').value = newName;
                    document.getElementById('masterForm').submit();
                }
            }

            function deleteFolder(folderName, trueName) {
                if (confirm("ARE YOU SURE?\n\nThis will permanently delete '" + trueName + "' and all contents. This cannot be undone.")) {
                    document.getElementById('formAction').value = 'delete';
                    document.getElementById('folderNameInput').value = folderName;
                    document.getElementById('masterForm').submit();
                }
            }
        </script>
    <?php endif; ?>
</body>

</html>