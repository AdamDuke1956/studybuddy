<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Study Buddy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-5">
            <a class="navbar-brand" href="#"><i class="fas fa-book-open"></i> STUDYBUDDY</a>
            <div class="collapse navbar-collapse justify-content-center">
                <div class="navbar-nav">
                    <a class="nav-link" href="index.php">HOME</a>
                    <a class="nav-link active" href="loginpage.php">LOG IN</a>
                    <a class="nav-link" href="signuppage.php">SIGN UP</a>
                </div>
            </div>
            <div class="nav-icons">
                <button id="themeToggle"><i class="fas fa-sun"></i></button>
                <i class="fas fa-cog"></i>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="sb-card">
            <h3>WELCOME BACK</h3>
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
            <?php } ?>
            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success text-center"><?php echo $_GET['success']; ?></div>
            <?php } ?>

            <form action="auth.php" method="post">
                <div class="sb-input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="sb-input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn-purple">LOG IN</button>
            </form>
            <div class="text-center mt-4">
                <small>Not a member? <a href="signuppage.php" style="color: #6f42c1; font-weight: bold;">Sign
                        Up</a></small>
            </div>
        </div>
    </div>

    <div class="footer-custom">
        <i class="fas fa-envelope"></i> studybuddy@gmail.com
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const body = document.body;

        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light-mode';
        body.classList.add(currentTheme);
        updateThemeIcon(currentTheme);

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const newTheme = body.classList.contains('dark-mode') ? 'dark-mode' : 'light-mode';
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            playSound('toggle');
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark-mode') {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        }

        // Button Click Sound
        document.querySelectorAll('.btn-purple').forEach(button => {
            button.addEventListener('click', () => {
                playSound('click');
            });
        });

        // Play Sound Function
        function playSound(type) {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            if (type === 'click') {
                oscillator.frequency.value = 800;
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            } else if (type === 'toggle') {
                oscillator.frequency.value = 600;
                gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.15);
            }
        }
    </script>
</body>

</html>