<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Story</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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
        <h2>Upload Image Story</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">Invalid upload. Please check file type and text length.</div>
        <?php endif; ?>

        <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
            <div id="preview-container">
                <img id="preview-img" src="#" alt="Preview">
            </div>

            Image: <input type="file" name="image" id="imageInput" required><br><br>

            Line 1: <input type="text" name="line1" maxlength="200"><br>
            Line 2: <input type="text" name="line2" maxlength="200"><br>
            Line 3: <input type="text" name="line3" maxlength="200">
            <div class="char-count">Limit: 200 characters per line</div><br>

            <button type="submit" style="padding: 10px 20px; cursor: pointer;">Upload Story</button>
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