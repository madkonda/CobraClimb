<?php
// ============================================================
// includes/config.php — CobraClimb Game Constants
// CSC 4370/6370 · Spring 2026 · Georgia State University
// ============================================================

// ── Snake positions: head cell => tail cell (Expert: 9 snakes) ──
define('SNAKES', [
    99 => 41,   // Mega cobra near the finish line
    95 => 56,   // Big drop
    87 => 24,   // Long slither
    62 => 19,   // Mid-board danger
    54 => 34,   // Short drop
    46 => 25,   // Side slide
    40 =>  3,   // Brutal early drop
    32 => 12,   // Early board trap
    17 =>  7,   // Starter cobra
]);

// ── Ladder positions: base cell => top cell (Expert: 4 ladders) ──
define('LADDERS', [
     4 => 14,   // Short climb
     9 => 31,   // Decent boost
    20 => 38,   // Mid boost
    28 => 84,   // Big lucky climb
]);

// ── Bonus tile types ──
define('BONUS_EXTRA_ROLL', [6, 23]);    // 🎁 Roll again
define('BONUS_SKIP_TURN',  [35, 67]);   // 💀 Lose next turn
define('BONUS_WARP',       [50]);       // ⚡ Warp to random cell 40–60

// ── Scoring constants ──
define('SCORE_BASE',          1000);
define('SCORE_PER_TURN',       -12);   // Deduct per turn taken
define('SCORE_SNAKE_PENALTY',  -60);   // Penalty per snake hit
define('SCORE_LADDER_BONUS',   +30);   // Bonus per ladder climbed
define('SCORE_WIN_UNDER_20',  +200);   // Speed bonus: win in < 20 turns
define('SCORE_MINIMUM',        100);   // Floor score

// ── Data file path ──
define('USERS_FILE', __DIR__ . '/../data/users.json');

// ── Session helpers ──

/**
 * Load all users from flat JSON file.
 */
function loadUsers(): array {
    if (!file_exists(USERS_FILE)) return [];
    $raw = file_get_contents(USERS_FILE);
    return json_decode($raw, true) ?? [];
}

/**
 * Save users array back to JSON file.
 */
function saveUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

/**
 * Find a user by username (case-insensitive).
 */
function findUser(string $username): ?array {
    foreach (loadUsers() as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            return $user;
        }
    }
    return null;
}

/**
 * Initialize a fresh game session for two players.
 */
function initGame(string $p1, string $p2): void {
    $_SESSION['game'] = [
        'players'       => [1 => $p1, 2 => $p2],
        'positions'     => [1 => 0, 2 => 0],   // 0 = not on board
        'turn'          => 1,
        'scores'        => [1 => SCORE_BASE, 2 => SCORE_BASE],
        'turns_taken'   => [1 => 0, 2 => 0],
        'snake_hits'    => [1 => 0, 2 => 0],
        'ladder_climbs' => [1 => 0, 2 => 0],
        'skip_next'     => [1 => false, 2 => false],
        'roll_history'  => [],
        'start_time'    => time(),
        'status'        => 'active',   // active | won
        'winner'        => null,
    ];
}

/**
 * Compute final score for a player at game end.
 */
function calcFinalScore(int $player): int {
    $g = $_SESSION['game'];
    $score   = SCORE_BASE;
    $score  += $g['turns_taken'][$player]   * SCORE_PER_TURN;
    $score  += $g['snake_hits'][$player]    * SCORE_SNAKE_PENALTY;
    $score  += $g['ladder_climbs'][$player] * SCORE_LADDER_BONUS;
    if ($g['turns_taken'][$player] < 20)    $score += SCORE_WIN_UNDER_20;
    return max(SCORE_MINIMUM, $score);
}

/**
 * Return CSS class suffix for a cell (snake-head, snake-tail, ladder-base, ladder-top, bonus-*).
 */
function cellType(int $cell): string {
    if (isset(SNAKES[$cell]))                      return 'snake-head';
    if (in_array($cell, SNAKES))                   return 'snake-tail';
    if (isset(LADDERS[$cell]))                     return 'ladder-base';
    if (in_array($cell, LADDERS))                  return 'ladder-top';
    if (in_array($cell, BONUS_EXTRA_ROLL))         return 'bonus-roll';
    if (in_array($cell, BONUS_SKIP_TURN))          return 'bonus-skip';
    if (in_array($cell, BONUS_WARP))               return 'bonus-warp';
    return '';
}

/**
 * Return an emoji label for a cell type.
 */
function cellIcon(int $cell): string {
    if (isset(SNAKES[$cell]))              return '🐍';
    if (in_array($cell, SNAKES))           return '💀';
    if (isset(LADDERS[$cell]))             return '🪜';
    if (in_array($cell, LADDERS))          return '⬆';
    if (in_array($cell, BONUS_EXTRA_ROLL)) return '🎁';
    if (in_array($cell, BONUS_SKIP_TURN))  return '💀';
    if (in_array($cell, BONUS_WARP))       return '⚡';
    return '';
}

/**
 * Build the 100 cells in correct visual order for the board
 * (top-left = 100, snake pattern, bottom-left = 1).
 */
function buildBoardOrder(): array {
    $cells = [];
    for ($row = 9; $row >= 0; $row--) {
        $start    = $row * 10 + 1;
        $rowCells = range($start, $start + 9);
        if ($row % 2 === 1) $rowCells = array_reverse($rowCells);
        $cells = array_merge($cells, $rowCells);
    }
    return $cells;
}
