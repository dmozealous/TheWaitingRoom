<?php
session_start();

if ($_SESSION['game_started']) {
    $_SESSION['elapsed_time'] = time() - $_SESSION['start_time'];
    $elapsed_time = $_SESSION['elapsed_time'];
    echo "Time waited: " . floor($elapsed_time / 60) . " minutes " . $elapsed_time % 60 . " seconds";
} else {
    echo "Time waited: 0 minutes 0 seconds";
}
?>