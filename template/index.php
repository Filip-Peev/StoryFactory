<?php
session_start();

// Fallback: Use the folder name logic you already have
$folder_name = basename(getcwd());
$display_name = ucwords(str_replace('-', ' ', $folder_name));

// The Upgrade: If the 'note' exists, use the exact True Name
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
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #111;
            color: white;
            scroll-behavior: smooth;
        }

        .uploadbox {
            padding: 40px 20px;
            background: #000;
            text-align: center;
            border-bottom: 1px solid #222;
            scroll-snap-align: start;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 400px;
        }

        input[type="text"] {
            width: 80%;
            max-width: 400px;
            padding: 10px;
            margin: 5px 0;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 5px;
        }

        .char-count {
            font-size: 11px;
            color: #888;
            margin-bottom: 10px;
        }

        #preview-container {
            margin-bottom: 15px;
            display: none;
        }

        #preview-img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #444;
        }

        .error-msg {
            background: #ff4444;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .story {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .story img {
            max-width: 90%;
            max-height: 70%;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .story img:hover {
            transform: scale(1.03);
        }

        .text {
            margin-top: 25px;
            text-align: center;
            padding: 0 20px;
        }

        .text p {
            margin: 8px 0;
            font-size: 20px;
            font-weight: 300;
            line-height: 1.4;
            max-width: 600px;
        }

        .navbuttons {
            position: fixed;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 1000;
        }

        .navbuttons button {
            width: 50px;
            height: 50px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            background: rgba(45, 45, 45, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: white;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .navbuttons button svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .navbuttons button:active {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.6);
        }

        .navbuttons button:active {
            transition: none;
            background: rgba(255, 255, 255, 0.25);
        }

        .navbuttons button:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ffaa00;
        }

        .navbuttons button:hover svg {
            stroke: #fff;
        }

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

        @media (max-width: 768px) {
            html {
                scroll-snap-type: y mandatory;
            }

            .story {
                scroll-snap-align: start;
                scroll-snap-stop: always;
            }

            .navbuttons {
                right: 10px;
            }

            .navbuttons button {
                width: 45px;
                height: 45px;
            }

            .story img:hover {
                transform: none;
            }
        }

        #lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        #lightbox img {
            max-width: 95%;
            max-height: 95%;
            border-radius: 10px;
        }

        #close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            padding: 10px;
            user-select: none;
        }

        .btn-upload {
            background: #ffaa00;
            color: #000;
            border: none;
            padding: 12px 30px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-upload:hover {
            background: #ffcc66;
            box-shadow: 0 5px 15px rgba(255, 170, 0, 0.3);
        }

        /* Custom "Browse" Button */
        .custom-file-upload {
            display: inline-block;
            padding: 8px 20px;
            cursor: pointer;
            background: #222;
            border: 1px solid #444;
            border-radius: 5px;
            font-size: 14px;
            color: #aaa;
            transition: 0.3s;
            margin-bottom: 10px;
        }

        .custom-file-upload:hover {
            background: #333;
            border-color: #ffaa00;
            color: #fff;
        }

        /* Hide the actual file input */
        #imageInput {
            display: none;
        }

        .uploadbox h2 {
            font-weight: 300;
            letter-spacing: 1px;
            margin-bottom: 30px;
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="navbuttons">
        <button onclick="goTop()" title="First Story"><svg viewBox="0 0 24 24">
                <path d="M12 19V5M5 12l7-7 7 7" />
            </svg></button>
        <button onclick="prevStory()" title="Previous"><svg viewBox="0 0 24 24">
                <path d="M18 15l-6-6-6 6" />
            </svg></button>
        <button onclick="nextStory()" title="Next"><svg viewBox="0 0 24 24">
                <path d="M6 9l6 6 6-6" />
            </svg></button>
        <button onclick="goBottom()" title="Last Story"><svg viewBox="0 0 24 24">
                <path d="M12 5v14M5 12l7 7 7-7" />
            </svg></button>
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
        echo "<div class='story' id='story$i'>";
        echo "<img src='uploads/{$story['image']}' class='clickable' data-src='uploads/{$story['image']}'>";
        echo "<div class='text'>";
        echo "<p>" . htmlspecialchars($story['line1']) . "</p>";
        echo "<p>" . htmlspecialchars($story['line2']) . "</p>";
        echo "<p>" . htmlspecialchars($story['line3']) . "</p>";
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
                stories[0].scrollIntoView({
                    behavior: "auto"
                });
                updateCurrentIndex();
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