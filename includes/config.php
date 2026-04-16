<?php
// ============================================================
// includes/config.php — CobraClimb Game Constants & Helpers
// CSC 4370/6370 · Spring 2026 · Georgia State University
// Madhu Sudhan Reddy Konda & Harika Kakarala
// ============================================================

// ── Snake positions: head cell => tail cell (Expert: 9 snakes) ──
define('SNAKES', [
    99 => 41,   // Mega cobra near the finish — most punishing
    95 => 56,   // Big drop from near-finish
    87 => 24,   // Long mid-to-bottom slither
    62 => 19,   // Mid-board danger zone
    54 => 34,   // Short penalty drop
    46 => 25,   // Side-board slide
    40 =>  3,   // Brutal early-game drop
    32 => 12,   // Early board trap
    17 =>  7,   // Starter cobra (small but annoying)
]);

// ── Ladder positions: base cell => top cell (Expert: 4 ladders) ──
define('LADDERS', [
     4 => 14,   // Short early climb
     9 => 31,   // Decent first-turn boost
    20 => 38,   // Mid-tier lift
    28 => 84,   // Big lucky climb — game changer
]);

// ── Bonus tile positions ──
define('BONUS_EXTRA_ROLL', [6, 23]);    // 🎁 Land here = roll again immediately
define('BONUS_SKIP_TURN',  [35, 67]);   // 💀 Land here = lose your next turn
define('BONUS_WARP',       [50]);       // ⚡ Land here = teleport to random cell 40–60

// ── Scoring constants ──
define('SCORE_BASE',         1000);    // Starting score for every player
define('SCORE_PER_TURN',      -12);   // Deduction per turn taken
define('SCORE_SNAKE_PENALTY', -60);   // Penalty each time you hit a snake
define('SCORE_LADDER_BONUS',  +30);   // Bonus each time you climb a ladder
define('SCORE_WIN_UNDER_20', +200);   // Speed bonus: win in under 20 turns
define('SCORE_MINIMUM',       100);   // Floor: score never drops below this

// ── Flat-file data store path ──
define('USERS_FILE', __DIR__ . '/../data/users.json');

// ============================================================
//  HELPER FUNCTIONS
// ============================================================

/**
 * Load all registered users from the flat JSON file.
 * Returns an empty array if the file does not exist yet.
 */
function loadUsers(): array {
    if (!file_exists(USERS_FILE)) return [];
    $raw = file_get_contents(USERS_FILE);
    return json_decode($raw, true) ?? [];
}

/**
 * Persist the users array back to the JSON flat file.
 */
function saveUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

/**
 * Find a single user by username (case-insensitive match).
 * Returns the user array or null if not found.
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
 * Resets all positions, scores, turn order, and timestamps.
 *
 * @param string $p1 Player 1 username (logged-in user)
 * @param string $p2 Player 2 name (pass-and-play or guest)
 */
function initGame(string $p1, string $p2): void {
    $_SESSION['game'] = [
        'players'       => [1 => $p1, 2 => $p2],
        'positions'     => [1 => 0, 2 => 0],       // 0 = off-board starting position
        'turn'          => 1,                        // Player 1 goes first
        'scores'        => [1 => SCORE_BASE, 2 => SCORE_BASE],
        'turns_taken'   => [1 => 0, 2 => 0],
        'snake_hits'    => [1 => 0, 2 => 0],
        'ladder_climbs' => [1 => 0, 2 => 0],
        'skip_next'     => [1 => false, 2 => false], // Bonus skip-turn flag
        'roll_history'  => [],                       // Last 30 roll events
        'start_time'    => time(),
        'status'        => 'active',                 // active | won
        'winner'        => null,
        'end_time'      => null,
    ];
}

/**
 * Calculate a player's final score at game end.
 * Applies turn deductions, snake penalties, ladder bonuses, and speed bonus.
 *
 * @param int $player Player number (1 or 2)
 * @return int Final score (minimum SCORE_MINIMUM)
 */
function calcFinalScore(int $player): int {
    $g      = $_SESSION['game'];
    $score  = SCORE_BASE;
    $score += $g['turns_taken'][$player]   * SCORE_PER_TURN;
    $score += $g['snake_hits'][$player]    * SCORE_SNAKE_PENALTY;
    $score += $g['ladder_climbs'][$player] * SCORE_LADDER_BONUS;
    if ($g['turns_taken'][$player] < 20)   $score += SCORE_WIN_UNDER_20;
    return max(SCORE_MINIMUM, $score);
}

/**
 * Return the CSS class name for a given board cell number.
 * Used to apply visual styling (snake-head, ladder-base, bonus, etc.).
 */
function cellType(int $cell): string {
    if (isset(SNAKES[$cell]))              return 'snake-head';
    if (in_array($cell, SNAKES))          return 'snake-tail';
    if (isset(LADDERS[$cell]))            return 'ladder-base';
    if (in_array($cell, LADDERS))         return 'ladder-top';
    if (in_array($cell, BONUS_EXTRA_ROLL)) return 'bonus-roll';
    if (in_array($cell, BONUS_SKIP_TURN))  return 'bonus-skip';
    if (in_array($cell, BONUS_WARP))       return 'bonus-warp';
    return '';
}

/**
 * Return the emoji icon for a given board cell number.
 * Displayed inside each cell on the game board.
 */
function cellIcon(int $cell): string {
    if (isset(SNAKES[$cell]))              return '🐍';
    if (in_array($cell, SNAKES))           return '💀';
    if (isset(LADDERS[$cell]))             return '🪜';
    if (in_array($cell, LADDERS))          return '⬆';
    if (in_array($cell, BONUS_EXTRA_ROLL)) return '🎁';
    if (in_array($cell, BONUS_SKIP_TURN))  return '⛔';
    if (in_array($cell, BONUS_WARP))       return '⚡';
    return '';
}

/**
 * Build the ordered list of 100 cell numbers for visual board rendering.
 * Top-left = cell 100, snake zigzag pattern, bottom-left = cell 1.
 *
 * Row 9 (top): 100,99,...,91   (reversed — odd row)
 * Row 8:        81,82,...,90   (left-to-right — even row)
 * ...
 * Row 0 (bottom): 1,2,...,10  (left-to-right — even row)
 *
 * @return int[] Array of 100 cell numbers in display order
 */
function buildBoardOrder(): array {
    $cells = [];
    for ($row = 9; $row >= 0; $row--) {
        $start    = $row * 10 + 1;
        $rowCells = range($start, $start + 9);
        // Odd-indexed rows (from bottom) go right-to-left
        if ($row % 2 === 1) {
            $rowCells = array_reverse($rowCells);
        }
        $cells = array_merge($cells, $rowCells);
    }
    return $cells;
}
