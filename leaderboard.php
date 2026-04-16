<?php
// ============================================================
// leaderboard.php — CobraClimb Leaderboard
// CSC 4370/6370 · Spring 2026
// ============================================================
require 'includes/auth_check.php';
require 'includes/config.php';

// ── Load leaderboard: merge session scores + cookie-persisted scores ──
$scores = $_SESSION['leaderboard'] ?? [];

// Restore cookie-persisted scores from previous sessions
if (empty($scores) && isset($_COOKIE['cobra_leaderboard'])) {
    $raw    = $_COOKIE['cobra_leaderboard'];
    $cookie = is_string($raw) ? @unserialize(base64_decode($raw)) : false;
    if (is_array($cookie)) {
        $scores = $cookie;
    }
}

// Sort by score descending, then by turns ascending (tiebreaker)
usort($scores, function ($a, $b) {
    if ($b['score'] !== $a['score']) return $b['score'] <=> $a['score'];
    return $a['turns'] <=> $b['turns'];
});

// Keep only top 10
$scores = array_slice($scores, 0, 10);

// ── Win parameters from roll.php redirect ──
$winner     = filter_input(INPUT_GET, 'winner', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$winScore   = filter_input(INPUT_GET, 'score',  FILTER_SANITIZE_NUMBER_INT)    ?? 0;
$winTurns   = filter_input(INPUT_GET, 'turns',  FILTER_SANITIZE_NUMBER_INT)    ?? 0;

// Game data for win stats
$g          = $_SESSION['game'] ?? null;
$winPlayer  = $g['winner']   ?? 1;
$timePlayed = $g ? ($g['end_time'] - $g['start_time']) : 0;
$minutes    = floor($timePlayed / 60);
$seconds    = $timePlayed % 60;

// Rank medals
$medals = ['🥇', '🥈', '🥉'];

require 'includes/header.php';
?>

<?php if (isset($_GET['winner']) && $winner): ?>
<!-- ── Confetti overlay ── -->
<div class="confetti-wrap" id="confettiWrap"></div>
<?php endif; ?>

<div class="lb-container">

    <?php if ($winner): ?>
    <!-- ── Win Screen ── -->
    <div class="win-screen">
        <h1>🎉 Victory!</h1>
        <div class="winner-name">
            <?= htmlspecialchars($winner) ?> reached cell 100!
        </div>

        <div class="win-stats">
            <div class="win-stat">
                <div class="stat-val"><?= number_format((int)$winScore) ?></div>
                <div class="stat-lbl">Final Score</div>
            </div>
            <div class="win-stat">
                <div class="stat-val"><?= (int)$winTurns ?></div>
                <div class="stat-lbl">Turns Taken</div>
            </div>
            <?php if ($g): ?>
            <div class="win-stat">
                <div class="stat-val"><?= $g['snake_hits'][$winPlayer] ?></div>
                <div class="stat-lbl">🐍 Snake Hits</div>
            </div>
            <div class="win-stat">
                <div class="stat-val"><?= $g['ladder_climbs'][$winPlayer] ?></div>
                <div class="stat-lbl">🪜 Ladders Climbed</div>
            </div>
            <div class="win-stat">
                <div class="stat-val"><?= sprintf('%d:%02d', $minutes, $seconds) ?></div>
                <div class="stat-lbl">Time Played</div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ((int)$winTurns < 20): ?>
            <div class="alert alert-success" style="max-width:340px;margin:0 auto 1rem;">
                ⚡ Speed Bonus! Finished in under 20 turns — +200 pts!
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Leaderboard Table ── -->
    <div class="lb-header">
        <h1>🏆 Leaderboard</h1>
        <p>Top 10 CobraClimb champions — ranked by score</p>
    </div>

    <div class="lb-table-wrap">
        <?php if (empty($scores)): ?>
            <div class="lb-empty">
                <span class="emoji">🎲</span>
                <p>No scores yet — be the first to reach cell 100!</p>
            </div>
        <?php else: ?>
        <table class="lb-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Player</th>
                    <th>Score</th>
                    <th>Turns</th>
                    <th>🐍 Snakes</th>
                    <th>🪜 Ladders</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $i => $row): ?>
                <tr>
                    <td class="lb-rank <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>">
                        <?= $i < 3 ? $medals[$i] : ($i + 1) ?>
                    </td>
                    <td>
                        <div class="lb-user">
                            <span class="token <?= $row['player_no'] === 1 ? 't1' : 't2' ?>"
                                  style="width:22px;height:22px;font-size:0.6rem;">
                                P<?= (int)$row['player_no'] ?>
                            </span>
                            <?= htmlspecialchars($row['username']) ?>
                        </div>
                    </td>
                    <td class="lb-score"><?= number_format((int)$row['score']) ?></td>
                    <td><?= (int)$row['turns'] ?></td>
                    <td><?= (int)($row['snakes']  ?? 0) ?></td>
                    <td><?= (int)($row['ladders'] ?? 0) ?></td>
                    <td style="color:var(--text-muted);font-size:0.82rem;">
                        <?= htmlspecialchars($row['date'] ?? '') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ── Action Buttons ── -->
    <div class="lb-actions">
        <a href="game.php?new=1" class="btn btn-primary btn-lg">
            🎲 Play Again
        </a>
        <a href="game.php" class="btn btn-blue btn-lg">
            📋 Back to Board
        </a>
        <a href="logout.php" class="btn btn-ghost btn-lg">
            🚪 Logout
        </a>
    </div>

</div><!-- /.lb-container -->

<?php require 'includes/footer.php'; ?>
