<?php
// ============================================================
// roll.php — CobraClimb Dice Roll Processor (POST handler)
// CSC 4370/6370 · Spring 2026
// All game logic runs SERVER-SIDE — no JavaScript game logic
// ============================================================
require 'includes/auth_check.php';
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: game.php'); exit();
}
if (!isset($_SESSION['game']) || $_SESSION['game']['status'] !== 'active') {
    header('Location: game.php'); exit();
}

$g      = &$_SESSION['game'];
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'roll';
$turn   = $g['turn'];

// Use session-stored board arrays for the active difficulty
$activeSnakes  = $g['snakes'];
$activeLadders = $g['ladders'];

// ── Handle SKIP turn ──
if ($action === 'skip') {
    $g['skip_next'][$turn] = false;
    $g['turn'] = ($turn === 1) ? 2 : 1;
    header('Location: game.php?event=' . urlencode('⏭ Turn skipped!') . '&evtype=bonus');
    exit();
}

// ════════════════════════════════════════════════════════════
//  AI NARRATOR — Dynamic Cell Events (PHP-only, no JS)
//  Generates story-driven text per event. Fulfils §4 AI
//  Enhancement: "Dynamic Cell Events" for board narration.
// ════════════════════════════════════════════════════════════
function aiNarrate(string $type, string $player, int $from, int $to, int $roll): string {
    $turnNo = $_SESSION['game']['turns_taken'][$_SESSION['game']['turn']];
    switch ($type) {
        case 'snake':
            $lines = [
                "The cobra strikes! {$player} is dragged from {$from} down to {$to}!",
                "Hissss! {$player} rolled a {$roll} straight into a snake — back to {$to}!",
                "The serpent coils around {$player} and drops them to cell {$to}.",
            ];
            break;
        case 'ladder':
            $lines = [
                "Fortune smiles! {$player} found a ladder and soars from {$from} to {$to}!",
                "{$player} grabs the ladder and rockets to {$to} — what a move!",
                "Incredible! {$player} climbs from {$from} to {$to} in one lucky step!",
            ];
            break;
        case 'bonus_roll':
            $lines = [
                "{$player} lands the bonus tile at {$to} — roll again!",
                "Double turn! The gift tile rewards {$player}. Roll again!",
            ];
            break;
        case 'skip':
            $lines = [
                "The skull tile at {$to} costs {$player} their next move. Ouch!",
                "Bad luck! {$player} steps on the penalty tile and loses a turn.",
            ];
            break;
        case 'warp':
            $lines = [
                "Warp zone! {$player} is teleported to cell {$to} — chaos reigns!",
                "The lightning tile zaps {$player} across the board to cell {$to}!",
            ];
            break;
        default:
            if ($roll === 6)      $lines = ["Six! {$player} blazes forward to cell {$to}!"];
            elseif ($roll === 1)  $lines = ["A one... {$player} inches to {$to}. Keep going!"];
            else                  $lines = ["{$player} rolls a {$roll} and moves to cell {$to}."];
    }
    return $lines[$turnNo % count($lines)];
}

// 1. Roll dice (server-side PHP)
$roll = rand(1, 6);

// 2. Calculate new position
$currentPos = $g['positions'][$turn];
$newPos     = $currentPos + $roll;
if ($newPos > 100) { $newPos = 100 - ($newPos - 100); }

// 3. Increment turn & deduct score
$g['turns_taken'][$turn]++;
$g['scores'][$turn] += SCORE_PER_TURN;

// 4. Event tracking
$eventMsg  = '';
$eventType = 'normal';
$extraRoll = false;
$narrate   = '';

// 5. Snakes — use session board arrays
if (isset($activeSnakes[$newPos])) {
    $tail      = $activeSnakes[$newPos];
    $eventMsg  = "🐍 Cobra! Slid from cell $newPos → $tail!";
    $eventType = 'snake';
    $g['snake_hits'][$turn]++;
    $g['scores'][$turn] += SCORE_SNAKE_PENALTY;
    $narrate   = aiNarrate('snake', $g['players'][$turn], $newPos, $tail, $roll);
    $newPos    = $tail;
}
// 6. Ladders — use session board arrays
elseif (isset($activeLadders[$newPos])) {
    $top       = $activeLadders[$newPos];
    $eventMsg  = "🪜 Ladder! Climbed from cell $newPos → $top!";
    $eventType = 'ladder';
    $g['ladder_climbs'][$turn]++;
    $g['scores'][$turn] += SCORE_LADDER_BONUS;
    $narrate   = aiNarrate('ladder', $g['players'][$turn], $newPos, $top, $roll);
    $newPos    = $top;
}
// 7. Bonus tiles
elseif (in_array($newPos, BONUS_EXTRA_ROLL)) {
    $eventMsg  = "🎁 Bonus! Roll again!";
    $eventType = 'bonus';
    $extraRoll = true;
    $narrate   = aiNarrate('bonus_roll', $g['players'][$turn], $currentPos, $newPos, $roll);
}
elseif (in_array($newPos, BONUS_SKIP_TURN)) {
    $eventMsg  = "💀 You lose your next turn!";
    $eventType = 'bonus';
    $g['skip_next'][$turn] = true;
    $narrate   = aiNarrate('skip', $g['players'][$turn], $currentPos, $newPos, $roll);
}
elseif (in_array($newPos, BONUS_WARP)) {
    $warpOptions = array_diff(range(40, 60), [50]);
    $warpDest    = $warpOptions[array_rand($warpOptions)];
    $eventMsg    = "⚡ Warp! Teleported to cell $warpDest!";
    $eventType   = 'bonus';
    $narrate     = aiNarrate('warp', $g['players'][$turn], $newPos, $warpDest, $roll);
    $newPos      = $warpDest;
}
else {
    $narrate = aiNarrate('normal', $g['players'][$turn], $currentPos, $newPos, $roll);
}

// 8. Update position
$g['positions'][$turn] = $newPos;

// 9. Score floor
$g['scores'][$turn] = max(SCORE_MINIMUM, $g['scores'][$turn]);

// 10. Log roll with narrator
$playerName = $g['players'][$turn];
$g['roll_history'][] = [
    'player'   => $playerName,
    'roll'     => $roll,
    'from'     => $currentPos,
    'to'       => $newPos,
    'event'    => $eventMsg,
    'type'     => $eventType,
    'turn'     => $g['turns_taken'][$turn],
    'narrate'  => $narrate,
];
if (count($g['roll_history']) > 30) {
    $g['roll_history'] = array_slice($g['roll_history'], -30);
}

// 11. Win condition
if ($newPos >= 100) {
    $finalScore         = calcFinalScore($turn);
    $g['scores'][$turn] = $finalScore;
    $g['status']        = 'won';
    $g['winner']        = $turn;
    $g['end_time']      = time();

    if (!isset($_SESSION['leaderboard'])) { $_SESSION['leaderboard'] = []; }
    $_SESSION['leaderboard'][] = [
        'username'   => $playerName,
        'score'      => $finalScore,
        'turns'      => $g['turns_taken'][$turn],
        'snakes'     => $g['snake_hits'][$turn],
        'ladders'    => $g['ladder_climbs'][$turn],
        'duration'   => $g['end_time'] - $g['start_time'],
        'date'       => date('M d, Y g:i A'),
        'player_no'  => $turn,
        'difficulty' => $g['difficulty'],
    ];

    $top10 = $_SESSION['leaderboard'];
    usort($top10, fn($a, $b) => $b['score'] <=> $a['score']);
    $top10 = array_slice($top10, 0, 10);
    setcookie('cobra_leaderboard', base64_encode(serialize($top10)),
              time() + (7 * 24 * 3600), '/', '', false, true);

    header('Location: leaderboard.php?winner=' . urlencode($playerName) .
           '&score=' . $finalScore . '&turns=' . $g['turns_taken'][$turn]);
    exit();
}

// 12. Advance turn unless extra roll
if (!$extraRoll) { $g['turn'] = ($turn === 1) ? 2 : 1; }

// 13. Redirect with event + narrator
$redirect = 'game.php?roll=' . $roll;
if ($eventMsg) $redirect .= '&event='   . urlencode($eventMsg);
if ($eventType) $redirect .= '&evtype=' . urlencode($eventType);
if ($narrate)   $redirect .= '&narrate='. urlencode($narrate);

header('Location: ' . $redirect);
exit();
