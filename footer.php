    <footer style="text-align: center; margin-top: 40px; padding: 20px; color: #888; font-size: 0.85rem; font-family: 'Segoe UI', sans-serif;">

        <div style="margin-bottom: 15px; font-family: monospace;">
            <span style="text-transform: uppercase; font-size: 10px; letter-spacing: 1px;">Factory Time (Sofia):</span><br>
            <span id="server-clock" style="color: #ffaa00; font-size: 1.2rem; font-weight: bold;"><?= date('H:i:s') ?></span>
            <div style="font-size: 0.75rem; color: #555;"><?= date('d.m.Y') ?></div>
        </div>

        <hr style="border: 0; border-top: 1px solid #333; margin-bottom: 20px; max-width: 80%; margin-left: auto; margin-right: auto;">

        <p>&copy; <?= date('Y') ?> Story Factory - <em>Unofficial Learning Web App</em></p>

        <a href="mailto:filip@filip-peev.com" style="color: #ffaa00; text-decoration: none; font-weight: bold; transition: 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ffaa00'">Feedback</a>
    </footer>

    <script>
        function updateClock() {
            const now = new Date();
            const sofiaTime = now.toLocaleTimeString('en-GB', {
                timeZone: 'Europe/Sofia',
                hour12: false
            });
            const clockElement = document.getElementById('server-clock');
            if (clockElement) clockElement.textContent = sofiaTime;
        }
        setInterval(updateClock, 1000);
    </script>