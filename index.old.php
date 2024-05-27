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
    $checkin_script = "<script>playCheckinSound('$checkin_sound'); playMusic('$music_sound');</script>";
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
            $message = "You are $mini_game.";
            break;
        case 'notification':
            $notification = $_SESSION['notifications'][array_rand($_SESSION['notifications'])];
            $message = $notification;
            break;
        case 'status':
            $status_sound = $_SESSION['status_sound'];
            $message = $_SESSION['notifications'][array_rand($_SESSION['notifications'])];
            break;
        default:
            $message = "You are waiting...";
    }
}

// Calculate elapsed time
$elapsed_time = $_SESSION['game_started'] ? (time() - $_SESSION['start_time']) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Waiting Room Simulator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 600px;
            max-width: 100%;
        }
        .header {
            padding: 20px;
            background-color: #e0e0e0;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content p {
            margin: 10px 0;
        }
        .buttons {
            margin-bottom: 20px;
        }
        .buttons button {
            margin: 5px;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #556B2F; /* Dull green color */
            color: #fff;
            transition: background-color 0.3s ease;
        }
        .buttons button:hover {
            background-color: #6B8E23; /* Slightly brighter dull green */
        }
        .image-container {
            text-align: center;
            background-color: #ddd;
        }
        .image-container img {
            width: 100%;
            height: auto;
        }
        #timer {
            display: none; /* Hide the timer initially */
        }
    </style>
    <script>
        let startTime = <?= $_SESSION['start_time'] ?> * 1000; // PHP time in milliseconds
        let initialElapsedTime = <?= $elapsed_time ?> * 1000; // Initial elapsed time in milliseconds
        let musicElement;
        let statusElement = new Audio("patient.mp3");

        function updateTime() {
            let now = new Date().getTime();
            let totalElapsed = initialElapsedTime + (now - startTime);
            let seconds = Math.floor(totalElapsed / 1000);
            let minutes = Math.floor(seconds / 60);
            seconds = seconds % 60;
            document.getElementById('timer').innerText = `Time waited: ${minutes} minutes ${seconds} seconds`;
        }

        function randomNpcInteraction() {
            const npcBehaviors = <?= json_encode($_SESSION['npc_behaviors']) ?>;
            const npcSounds = <?= json_encode($_SESSION['npc_sounds']) ?>;
            const randomIndex = Math.floor(Math.random() * npcBehaviors.length);
            const npcMessage = `Someone is ${npcBehaviors[randomIndex]}.`;
            document.getElementById('npcMessage').innerText = npcMessage;
            const audioElement = new Audio(npcSounds[randomIndex]);
            audioElement.play();
        }

        function startGame() {
            document.getElementById('timer').style.display = 'block'; // Show the timer when the game starts
            setInterval(updateTime, 1000);
            setInterval(randomNpcInteraction, Math.random() * (30000 - 10000) + 10000); // Random interval between 10 to 30 seconds
        }

        function playStatusSound() {
            statusElement.play();
        }

        function playCheckinSound(sound) {
            const audioElement = new Audio(sound);
            audioElement.play();
        }

        function playMusic(sound) {
            musicElement = new Audio(sound);
            musicElement.loop = true;
            musicElement.play();
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
            <p id="timer">
                Time waited: <?= floor($elapsed_time / 60) ?> minutes <?= $elapsed_time % 60 ?> seconds
            </p>
            <p id="npcMessage"><?= $message ?></p>
            <div class="buttons">
                <form method="post">
                    <input type="hidden" id="statusSound" value="<?= $status_sound ?>">
                    <?php if (!$_SESSION['game_started']): ?>
                        <button type="submit" name="action" value="check_in">Check In</button>
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