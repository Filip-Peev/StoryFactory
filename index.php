<?php
date_default_timezone_set('Europe/Sofia');
ob_start();
session_start();

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

    if (mb_strlen($original_name) > 24 || empty(trim($original_name))) {
        header("Location: index.php?msg=invalid_name");
        exit;
    }

    $folder_name = strtolower(preg_replace('/[^A-Za-z0-9]/', ' ', $original_name));
    $folder_name = str_replace(' ', '-', trim(preg_replace('/ +/', ' ', $folder_name)));
    $target_dir = $root_stories . $folder_name;

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
        mkdir($target_dir . '/uploads', 0777, true);

        file_put_contents($target_dir . '/title.txt', $original_name);
        file_put_contents($target_dir . '/date.txt', date("F j, Y"));

        $files_to_copy = ['index.php', 'admin.php', 'style.css', 'upload.php', 'style-admin.css'];
        foreach ($files_to_copy as $file) {
            $src = 'template/' . $file;
            $dest = $target_dir . '/' . $file;
            if (file_exists($src)) copy($src, $dest);
        }

        header("Location: index.php?msg=created");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Story Factory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2215%22 fill=%22%23222%22 stroke=%22%23ffaa00%22 stroke-width=%228%22/><text y=%2260%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22%23ffaa00%22 font-family=%22Arial%22 x=%2210%22>S</text><text y=%2285%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22white%22 font-family=%22Arial%22 x=%2245%22>F</text></svg>">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="header-flex">
            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/" class="logo-container">
                <div class="brand-icon"></div>
                <div class="brand-text">Story<span>Factory</span></div>
            </a>
            <?php if ($isAdmin): ?>
                <a href="hub.php?logout=1" class="admin-logout">Logout</a>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin && isset($_GET['msg'])): ?>
            <div class="status-msg">Action successful: <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="create-box">
                <form method="post">
                    <input type="text" name="story_name" placeholder="Enter New Story Title..." maxlength="24" minlength="2" required>
                    <button type="submit" class="primary-btn">Create a Story</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="story-grid">
            <?php
            if (!is_dir($root_stories)) mkdir($root_stories, 0777, true);

            $dirs = array_filter(glob($root_stories . '*'), 'is_dir');

            // Sort stories by the date stored in date.txt (newest first)
            usort($dirs, function ($a, $b) {
                $fileA = $a . '/date.txt';
                $fileB = $b . '/date.txt';

                // Get timestamps: try date.txt first, fallback to folder creation time
                $timeA = file_exists($fileA) ? strtotime(file_get_contents($fileA)) : filectime($a);
                $timeB = file_exists($fileB) ? strtotime(file_get_contents($fileB)) : filectime($b);

                return $timeB <=> $timeA;
            });


            foreach ($dirs as $dir):
                $folder_name = basename($dir);
                $title_file = $dir . '/title.txt';
                $date_file = $dir . '/date.txt';

                $display_name = file_exists($title_file) ? file_get_contents($title_file) : ucwords(str_replace('-', ' ', $folder_name));

                $display_date = file_exists($date_file) ? file_get_contents($date_file) : date("F j, Y", filectime($dir));
                $custom_thumb = glob($dir . "/thumbnail.{jpg,jpeg,png,webp,gif}", GLOB_BRACE);

                if (!empty($custom_thumb)) {
                    $thumbnail = $custom_thumb[0];
                } else {
                    $uploaded_images = glob($dir . "/uploads/*.{jpg,jpeg,png,webp,gif}", GLOB_BRACE);
                    $thumbnail = !empty($uploaded_images) ? $uploaded_images[0] : null;
                }
            ?>
                <div class="story-card">
                    <a href="<?php echo $dir; ?>/" class="card-link" style="padding:0;">
                        <div class="card-preview-box">
                            <?php if ($thumbnail): ?>
                                <img src="<?php echo $thumbnail; ?>" alt="Story Preview">
                            <?php else: ?>
                                <div class="no-image-text">No Images Yet</div>
                            <?php endif; ?>
                        </div>

                        <div style="padding: 20px;">
                            <strong style="display: block; font-size: 16px; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($display_name); ?>
                            </strong>
                            <span class="story-date"><?php echo $display_date; ?></span>
                        </div>
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

    <?php include './footer.php'; ?>

</body>

</html>