<?php
session_start();

$folder_name = basename(getcwd());
$display_name = ucwords(str_replace('-', ' ', $folder_name));

if (file_exists('title.txt')) {
    $display_name = file_get_contents('title.txt');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($display_name); ?> - Story Factory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2215%22 fill=%22%23222%22 stroke=%22%23ffaa00%22 stroke-width=%228%22/><text y=%2260%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22%23ffaa00%22 font-family=%22Arial%22 x=%2210%22>S</text><text y=%2285%22 font-size=%2250%22 font-weight=%22bold%22 fill=%22white%22 font-family=%22Arial%22 x=%2245%22>F</text></svg>">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="navbuttons">

        <a href="../../" title="Gallery" class="nav-gallery-btn">
            <svg viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
        </a>
        <button onclick="goTop()" title="First Story">
            <svg viewBox="0 0 24 24">
                <path d="M12 19V5M5 12l7-7 7 7" />
            </svg>
        </button>
        <button onclick="prevStory()" title="Previous">
            <svg viewBox="0 0 24 24">
                <path d="M18 15l-6-6-6 6" />
            </svg>
        </button>
        <button onclick="nextStory()" title="Next">
            <svg viewBox="0 0 24 24">
                <path d="M6 9l6 6 6-6" />
            </svg>
        </button>
        <button onclick="goBottom()" title="Last Story">
            <svg viewBox="0 0 24 24">
                <path d="M12 5v14M5 12l7 7 7-7" />
            </svg>
        </button>
    </div>

    <div class="uploadbox">
        <a href="../../" class="logo-container" style="margin-bottom:20px;">
            <div class="brand-icon"></div>
            <div class="brand-text">Story<span>Factory</span></div>
        </a>
        <h2>Upload Image Story</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">Invalid upload. Please check file type and text length.</div>
        <?php endif; ?>

        <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
            <div id="preview-container">
                <img id="preview-img" src="#" alt="Preview">
            </div>

            <label for="imageInput" class="custom-file-upload">
                <svg style="width:14px; vertical-align:middle; margin-right:8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Choose Image
            </label>
            <input type="file" name="image" id="imageInput" required>

            <br>

            <input type="text" name="line1" placeholder="Optional Text Line" maxlength="200"><br>
            <input type="text" name="line2" placeholder="Optional Text Line" maxlength="200"><br>
            <input type="text" name="line3" placeholder="Optional Text Line" maxlength="200">
            <div class="char-count">Limit: 200 characters per line</div><br>

            <button type="submit" class="btn-upload">Upload Story</button>
        </form>
    </div>
    <?php
    $json_raw = file_exists("data.json") ? file_get_contents("data.json") : "[]";
    $data = json_decode($json_raw, true) ?? [];

    foreach ($data as $i => $story) {
        $loadingAttr = ($i === 0 ? "loading='eager'" : "loading='lazy'");

        echo "<div class='story' id='story$i'>";
        echo "<img $loadingAttr src='uploads/{$story['image']}' class='clickable' data-src='uploads/{$story['image']}'>";
        echo "<div class='text'>";
        echo "<p>" . htmlspecialchars($story['line1'] ?? '') . "</p>";
        echo "<p>" . htmlspecialchars($story['line2'] ?? '') . "</p>";
        echo "<p>" . htmlspecialchars($story['line3'] ?? '') . "</p>";
        echo "</div></div>";
    }
    ?>

    <div id="lightbox" onclick="closeLightbox()">
        <span id="close">&times;</span>
        <img id="lightbox-img">
    </div>

    <script>
        let current = 0;
        const stories = document.querySelectorAll(".story");

        const imageInput = document.getElementById('imageInput');
        const previewImg = document.getElementById('preview-img');
        const previewContainer = document.getElementById('preview-container');

        imageInput.onchange = evt => {
            const [file] = imageInput.files;
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert("Only JPG, PNG, GIF, and WEBP allowed!");
                    imageInput.value = "";
                    previewContainer.style.display = "none";
                    return;
                }
                previewImg.src = URL.createObjectURL(file);
                previewContainer.style.display = "block";
            }
        }

        window.addEventListener("load", () => {
            if (stories.length > 0) {
                setTimeout(() => {
                    stories[0].scrollIntoView({
                        behavior: "auto"
                    });
                    updateCurrentIndex();
                }, 50);
            }
        });

        function showStory(index) {
            if (index < 0) index = 0;
            if (index >= stories.length) index = stories.length - 1;
            current = index;
            stories[current].scrollIntoView({
                behavior: "smooth"
            });
        }

        function nextStory() {
            showStory(current + 1);
        }

        function prevStory() {
            showStory(current - 1);
        }

        function goTop() {
            if (stories.length > 0) showStory(0);
        }

        function goBottom() {
            if (stories.length > 0) showStory(stories.length - 1);
        }

        function updateCurrentIndex() {
            let closestIndex = 0;
            let minDistance = Infinity;
            stories.forEach((story, index) => {
                const rect = story.getBoundingClientRect();
                const distance = Math.abs(rect.top);
                if (distance < minDistance) {
                    minDistance = distance;
                    closestIndex = index;
                }
            });
            current = closestIndex;
        }

        window.addEventListener("scroll", updateCurrentIndex);

        const lightbox = document.getElementById("lightbox");
        const lightboxImg = document.getElementById("lightbox-img");

        document.querySelectorAll(".clickable").forEach(img => {
            img.addEventListener("click", () => {
                lightbox.style.display = "flex";
                lightboxImg.src = img.dataset.src;
            });
        });

        function closeLightbox() {
            lightbox.style.display = "none";
        }
    </script>
</body>

</html>