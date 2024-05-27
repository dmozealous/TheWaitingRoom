<?php
session_start();

// Initialize game state
if (!isset($_SESSION['game_started'])) {
    $_SESSION['game_started'] = false;
    $_SESSION['start_time'] = 0;
    $_SESSION['elapsed_time'] = 0;
    $_SESSION['waiting_time'] = 0;
    $_SESSION['activities'] = [
        "reading outdated magazines",
        "staring at a broken TV",
        "listening to the radio"
    ];
    $_SESSION['npc_behaviors'] = [
        "sneezing",
        "checking their text messages",
        "fidgeting"
    ];
    $_SESSION['npc_sounds'] = [
        "sneeze.mp3",
        "text.mp3",
        "fidget.mp3"
    ];
    $_SESSION['mini_games'] = [
        "sorting through old paperwork",
        "untangling a mess of phone cords"
    ];
    $_SESSION['notifications'] = [
        "The doctor will see you soon.",
        "Your appointment is coming up.",
        "Please wait a little longer."
    ];
    $_SESSION['status_sound'] = "patient.mp3";
    $_SESSION['checkin_sound'] = "checkin.mp3";
    $_SESSION['music_sound'] = "music.mp3";
}

// Handle check-in
$checkin_script = "";
if (isset($_POST['action']) && $_POST['action'] == 'check_in') {
    $_SESSION['game_started'] = true;
    $_SESSION['start_time'] = time();
    $_SESSION['elapsed_time'] = 0; // Initialize elapsed_time when game starts
    $checkin_sound = $_SESSION['checkin_sound'];
    $music_sound = $_SESSION['music_sound'];
    $checkin_script = "<script>playCheckinSound('$checkin_sound');</script>";
}

// Perform an action
$message = "You are waiting...";
$status_sound = "";
if (isset($_POST['action']) && $_SESSION['game_started']) {
    switch ($_POST['action']) {
        case 'activity':
            $activity = $_SESSION['activities'][array_rand($_SESSION['activities'])];
            $message = "You are $activity.";
            break;
        case 'mini_game':
            $mini_game = $_SESSION['mini_games'][array_rand($_SESSION['mini_games'])];
            $message = "You are playing $mini_game.";
            break;
        case 'status':
            $notification = $_SESSION['notifications'][array_rand($_SESSION['notifications'])];
            $message = $notification;
            $status_sound = $_SESSION['status_sound'];
            $checkin_script = "<script>playStatusSound();</script>";
            break;
    }
}

// Calculate elapsed time
if ($_SESSION['game_started']) {
    $_SESSION['elapsed_time'] = time() - $_SESSION['start_time'];
}
$elapsed_time = $_SESSION['elapsed_time'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Waiting Room Simulator</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        let musicElement = new Audio('<?= $_SESSION['music_sound'] ?>');
        musicElement.loop = true;
        musicElement.volume = 0.1;  // Reduce the volume to 10%

        function startGame() {
            document.getElementById('npcMessage').style.display = 'block';
            document.getElementById('timer').style.display = 'block';
            musicElement.play();
            setInterval(updateTimer, 1000);
            setInterval(randomNpcInteraction, Math.random() * (30000 - 10000) + 10000); // Random interval between 10 to 30 seconds
        }

        function playStatusSound() {
            const audioElement = new Audio('<?= $_SESSION['status_sound'] ?>');
            audioElement.play();
        }

        function playCheckinSound(sound) {
            const audioElement = new Audio(sound);
            audioElement.play();
        }

        function playMusic() {
            musicElement.play();
        }

        function updateTimer() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'timer.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('timer').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function randomNpcInteraction() {
            const npcBehaviors = <?= json_encode($_SESSION['npc_behaviors']) ?>;
            const npcSounds = <?= json_encode($_SESSION['npc_sounds']) ?>;
            const randomIndex = Math.floor(Math.random() * npcBehaviors.length);
            const npcBehavior = npcBehaviors[randomIndex];
            const npcSound = npcSounds[randomIndex];
            
            document.getElementById('npcMessage').innerHTML = "Someone is " + npcBehavior;
            
            const audioElement = new Audio(npcSound);
            audioElement.play();
        }

        <?php if ($_SESSION['game_started']): ?>
        window.onload = function() {
            startGame();
        };
        <?php endif; ?>
    </script>
</head>
<body>
    <?= $checkin_script ?>
    <div class="container">
        <div class="header">
            <h1>Waiting Room Simulator</h1>
        </div>
        <div class="content">
            <p id="timer" style="display: none;">
                Time waited: <?= floor($elapsed_time / 60) ?> minutes <?= $elapsed_time % 60 ?> seconds
            </p>
            <p id="npcMessage" style="display: <?= $_SESSION['game_started'] ? 'block' : 'none' ?>;"><?= $message ?></p>
            <div class="buttons">
                <form method="post">
                    <input type="hidden" id="statusSound" value="<?= $status_sound ?>">
                    <?php if (!$_SESSION['game_started']): ?>
                        <button type="submit" name="action" value="check_in" onclick="playMusic()">Check In</button>
                    <?php else: ?>
                        <button type="submit" name="action" value="activity">Do an Activity</button>
                        <button type="submit" name="action" value="mini_game">Play a Mini-Game</button>
                        <button type="submit" name="action" value="status" onclick="playStatusSound()">Check Status</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="image-container">
            <img src="waiting_room.jpg" alt="Waiting Room">
        </div>
    </div>
</body>
</html>