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

if (isset($_POST['action']) && $_POST['action'] == 'update_text') {
    $index = $_POST['index'];
    if (isset($data[$index])) {
        $data[$index]['line1'] = strip_tags($_POST['line1']);
        $data[$index]['line2'] = strip_tags($_POST['line2']);
        $data[$index]['line3'] = strip_tags($_POST['line3']);

        file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT));
        echo "success";
        exit;
    }
}

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
    <link rel="stylesheet" href="style-admin.css">

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
                <a href="../../hub.php?logout=1" class="admin-logout">Logout</a>
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
                                <div id="view-<?php echo $i; ?>">
                                    <div class="story-text line1"><?php echo htmlspecialchars($story['line1'] ?? ''); ?></div>
                                    <div class="story-text line2"><?php echo htmlspecialchars($story['line2'] ?? ''); ?></div>
                                    <div class="story-text line3"><?php echo htmlspecialchars($story['line3'] ?? ''); ?></div>
                                </div>

                                <div id="edit-<?php echo $i; ?>" style="display:none;">
                                    <input type="text" id="input1-<?php echo $i; ?>" class="edit-input" value="<?php echo htmlspecialchars($story['line1'] ?? ''); ?>" maxlength="200">
                                    <input type="text" id="input2-<?php echo $i; ?>" class="edit-input" value="<?php echo htmlspecialchars($story['line2'] ?? ''); ?>" maxlength="200">
                                    <input type="text" id="input3-<?php echo $i; ?>" class="edit-input" value="<?php echo htmlspecialchars($story['line3'] ?? ''); ?>" maxlength="200">
                                    <div class="edit-actions">
                                        <button onclick="saveEdit(<?php echo $i; ?>)" class="btn-save">Save</button>
                                        <button onclick="toggleEdit(<?php echo $i; ?>)" class="btn-cancel">Cancel</button>
                                    </div>
                                </div>
                            </td>
                            <td class="col-action">
                                <div class="action-flex">
                                    <button onclick="toggleEdit(<?php echo $i; ?>)" class="btn-action btn-edit">Edit Text</button>
                                    <a href="?set_thumb=<?php echo $i; ?>" class="btn-action btn-thumb">Set Cover</a>
                                    <a href="?delete=<?php echo $i; ?>" class="btn-action btn-delete" onclick="return confirm('Delete image?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function toggleEdit(index) {
            const view = document.getElementById('view-' + index);
            const edit = document.getElementById('edit-' + index);
            const isEditing = edit.style.display === 'block';

            view.style.display = isEditing ? 'block' : 'none';
            edit.style.display = isEditing ? 'none' : 'block';
        }

        function saveEdit(index) {
            const formData = new FormData();
            formData.append('action', 'update_text');
            formData.append('index', index);
            formData.append('line1', document.getElementById('input1-' + index).value);
            formData.append('line2', document.getElementById('input2-' + index).value);
            formData.append('line3', document.getElementById('input3-' + index).value);

            fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        location.reload();
                    } else {
                        alert("Error saving changes.");
                    }
                })
                .catch(err => alert("Connection error."));
        }
    </script>
</body>

</html>