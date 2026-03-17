<?php
ob_start(); // Start output buffering to prevent "Headers already sent" errors
session_start();

// 1. SESSION CHECK (Modified)
// We no longer redirect to hub.php automatically. 
// We just check if the admin is logged in or if it's a guest.
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$root_stories = 'stories/';

// Helper function to delete a folder
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

// --- HANDLE DELETE (Admin Only) ---
if (isset($_POST['action']) && $_POST['action'] == 'delete' && !empty($_POST['folder_name'])) {
    if (!$isAdmin) die("Unauthorized"); // Guests cannot trigger this
    $target = $root_stories . basename($_POST['folder_name']);
    if (file_exists($target) && is_dir($target)) {
        deleteDirectory($target);
    }
    header("Location: index.php?msg=deleted");
    exit;
}

// --- HANDLE RENAME (Admin Only) ---
if (isset($_POST['action']) && $_POST['action'] == 'rename' && !empty($_POST['old_name']) && !empty($_POST['new_name'])) {
    if (!$isAdmin) die("Unauthorized"); // Guests cannot trigger this
    $old_folder = $root_stories . basename($_POST['old_name']);
    $new_safe_name = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '-', $_POST['new_name']));
    $new_folder = $root_stories . $new_safe_name;

    if (file_exists($old_folder) && !file_exists($new_folder)) {
        rename($old_folder, $new_folder);
    }
    header("Location: index.php?msg=renamed");
    exit;
}

// --- HANDLE CREATE (Admin Only) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['story_name']) && !isset($_POST['action'])) {
    if (!$isAdmin) die("Unauthorized"); // Guests cannot trigger this
    $folder_name = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '-', $_POST['story_name']));
    $target_dir = $root_stories . $folder_name;

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
        mkdir($target_dir . '/uploads', 0777, true);
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
    <style>
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header-flex">
            <h1 style="margin:0;">
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/" style="text-decoration:none; color:inherit;">
                    Story Factory
                </a>
            </h1>

            <?php if ($isAdmin): ?>
                <a href="hub.php?logout=1" style="color:#666; text-decoration:none; font-size:12px; border: 1px solid #333; padding: 5px 10px; border-radius: 4px;">
                    Exit Admin Mode
                </a>
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
                $name = basename($dir);
                $display_name = ucwords(str_replace('-', ' ', $name));
            ?>
                <div class="story-card">
                    <a href="<?php echo $dir; ?>/index.php" target="_blank" class="card-link">
                        <span class="folder-icon">📁</span>
                        <strong><?php echo $display_name; ?></strong>
                    </a>
                    <?php if ($isAdmin): ?>
                        <div class="card-actions">
                            <button class="action-btn" onclick="renameFolder('<?php echo $name; ?>')">Rename</button>
                            <a href="<?php echo $dir; ?>/admin.php" target="_blank" class="action-btn">Admin</a>
                            <button class="action-btn btn-delete" onclick="deleteFolder('<?php echo $name; ?>')">Delete</button>
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
        function renameFolder(oldName) {
            let readable = oldName.replace(/-/g, ' ');
            let newName = prompt("Rename story '" + readable + "' to:", readable);
            if (newName && newName.trim() !== "" && newName.trim().toLowerCase() !== readable.toLowerCase()) {
                document.getElementById('formAction').value = 'rename';
                document.getElementById('oldNameInput').value = oldName;
                document.getElementById('newNameInput').value = newName;
                document.getElementById('masterForm').submit();
            }
        }

        function deleteFolder(name) {
            let readable = name.replace(/-/g, ' ');
            if (confirm("ARE YOU SURE?\n\nThis will permanently delete '" + readable + "' and all images. This cannot be undone.")) {
                document.getElementById('formAction').value = 'delete';
                document.getElementById('folderNameInput').value = name;
                document.getElementById('masterForm').submit();
            }
        }
    </script>
    <?php endif; ?>
</body>

</html>