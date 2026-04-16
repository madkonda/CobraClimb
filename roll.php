<?php
// ============================================================
// roll.php — CobraClimb Dice Roll Processor (POST handler)
// CSC 4370/6370 · Spring 2026
// All game logic runs SERVER-SIDE — no JavaScript game logic
// ============================================================
require 'includes/auth_check.php';
require 'includes/config.php';

// ── Guard: only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: game.php');
    exit();
}

// ── Guard: game must be active ──
if (!isset($_SESSION['game']) || $_SESSION['game']['status'] !== 'active') {
    header('Location: game.php');
    exit();
}

$g      = &$_SESSION['game'];
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'roll';
$turn   = $g['turn'];

// ── Handle SKIP turn (player landed on skip tile) ──
if ($action === 'skip') {
    $g['skip_next'][$turn] = false;
    $g['turn'] = ($turn === 1) ? 2 : 1;
    header('Location: game.php?event=' . urlencode('⏭ Turn skipped!') . '&evtype=bonus');
    exit();
}

// ════════════════════════════════════════════════════════════
//  MAIN DICE ROLL LOGIC
// ════════════════════════════════════════════════════════════

// 1. Roll the dice (PHP server-side — requirement §4)
$roll = rand(1, 6);

// 2. Calculate new raw position
$currentPos = $g['positions'][$turn];
$newPos     = $currentPos + $roll;

// Clamp to 100 (can't go past finish)
if ($newPos > 100) {
    $newPos = 100 - ($newPos - 100);   // Bounce back mechanic
}

// 3. Increment turn counter & update score
$g['turns_taken'][$turn]++;
$g['scores'][$turn] += SCORE_PER_TURN;   // Deduct per turn

// 4. Track event for UI feedback
$eventMsg  = '';
$eventType = 'normal';
$extraRoll = false;

// 5. Check SNAKES (head → tail)
if (isset(SNAKES[$newPos])) {
    $tail      = SNAKES[$newPos];
    $eventMsg  = "🐍 Cobra! Slid from cell $newPos → $tail!";
    $eventType = 'snake';
    $g['snake_hits'][$turn]++;
    $g['scores'][$turn] += SCORE_SNAKE_PENALTY;
    $newPos = $tail;
}
// 6. Check LADDERS (base → top)
elseif (isset(LADDERS[$newPos])) {
    $top       = LADDERS[$newPos];
    $eventMsg  = "🪜 Ladder! Climbed from cell $newPos → $top!";
    $eventType = 'ladder';
    $g['ladder_climbs'][$turn]++;
    $g['scores'][$turn] += SCORE_LADDER_BONUS;
    $newPos = $top;
}
// 7. Check BONUS TILES
elseif (in_array($newPos, BONUS_EXTRA_ROLL)) {
    $eventMsg  = "🎁 Bonus! Roll again — extra turn!";
    $eventType = 'bonus';
    $extraRoll = true;
}
elseif (in_array($newPos, BONUS_SKIP_TURN)) {
    $eventMsg  = "💀 Ouch! You lose your next turn!";
    $eventType = 'bonus';
    $g['skip_next'][$turn] = true;
}
elseif (in_array($newPos, BONUS_WARP)) {
    // Warp: teleport to random position 40–60 (excluding 50)
    $warpOptions = array_diff(range(40, 60), [50]);
    $warpDest    = $warpOptions[array_rand($warpOptions)];
    $eventMsg    = "⚡ Warp! Teleported from cell $newPos → $warpDest!";
    $eventType   = 'bonus';
    $newPos      = $warpDest;
}

// 8. Update position in session
$g['positions'][$turn] = $newPos;

// 9. Enforce score floor
$g['scores'][$turn] = max(SCORE_MINIMUM, $g['scores'][$turn]);

// 10. Log the roll to history
$playerName = $g['players'][$turn];
$g['roll_history'][] = [
    'player' => $playerName,
    'roll'   => $roll,
    'from'   => $currentPos,
    'to'     => $newPos,
    'event'  => $eventMsg,
    'type'   => $eventType,
    'turn'   => $g['turns_taken'][$turn],
];

// ── Keep roll history manageable: trim to last 30 entries to prevent session bloat ──
if (count($g['roll_history']) > 30) {
    $g['roll_history'] = array_slice($g['roll_history'], -30);
}

// 11. Check WIN CONDITION: player reached cell 100
if ($newPos >= 100) {
    $finalScore          = calcFinalScore($turn);
    $g['scores'][$turn]  = $finalScore;
    $g['status']         = 'won';
    $g['winner']         = $turn;
    $g['end_time']       = time();

    // Save to session leaderboard
    if (!isset($_SESSION['leaderboard'])) {
        $_SESSION['leaderboard'] = [];
    }
    $_SESSION['leaderboard'][] = [
        'username'  => $playerName,
        'score'     => $finalScore,
        'turns'     => $g['turns_taken'][$turn],
        'snakes'    => $g['snake_hits'][$turn],
        'ladders'   => $g['ladder_climbs'][$turn],
        'duration'  => $g['end_time'] - $g['start_time'],
        'date'      => date('M d, Y g:i A'),
        'player_no' => $turn,
    ];

    // Persist top 10 leaderboard in cookie (7 days)
    $top10 = $_SESSION['leaderboard'];
    usort($top10, fn($a, $b) => $b['score'] <=> $a['score']);
    $top10 = array_slice($top10, 0, 10);
    setcookie(
        'cobra_leaderboard',
        base64_encode(serialize($top10)),
        time() + (7 * 24 * 3600),   // 7 days
        '/',
        '',
        false,   // secure (set true on HTTPS)
        true     // httponly
    );

    header('Location: leaderboard.php?winner=' . urlencode($playerName) .
           '&score=' . $finalScore .
           '&turns=' . $g['turns_taken'][$turn]);
    exit();
}

// 12. Advance turn (unless extra roll bonus)
if (!$extraRoll) {
    $g['turn'] = ($turn === 1) ? 2 : 1;
}

// 13. Redirect back to game board with event info
$redirect = 'game.php?roll=' . $roll;
if ($eventMsg)  $redirect .= '&event='  . urlencode($eventMsg);
if ($eventType) $redirect .= '&evtype=' . urlencode($eventType);

header('Location: ' . $redirect);
exit();
