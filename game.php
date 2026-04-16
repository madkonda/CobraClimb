<?php
// ============================================================
// game.php — CobraClimb Main Game Board
// CSC 4370/6370 · Spring 2026
// ============================================================
require 'includes/auth_check.php';
require 'includes/config.php';

// ── Initialize game if not active or user requests new game ──
if (!isset($_SESSION['game']) || isset($_GET['new'])) {
    $p2Name = isset($_GET['p2']) && trim($_GET['p2']) !== ''
        ? htmlspecialchars(trim($_GET['p2']))
        : 'Player 2';
    initGame($_SESSION['user'], $p2Name);
}

$g       = &$_SESSION['game'];
$turn    = $g['turn'];
$p1      = $g['players'][1];
$p2      = $g['players'][2];
$pos1    = $g['positions'][1];
$pos2    = $g['positions'][2];

// Retrieve any event message from roll.php redirect
$event   = filter_input(INPUT_GET, 'event',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$evType  = filter_input(INPUT_GET, 'evtype',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$lastRoll = filter_input(INPUT_GET, 'roll',   FILTER_SANITIZE_NUMBER_INT)    ?? '';

// ── Build the board cell order (top-left=100, snake pattern) ──
$boardCells = buildBoardOrder();

// ── Dice face emoji map ──
$diceFaces = ['', '⚀', '⚁', '⚂', '⚃', '⚄', '⚅'];

require 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     GAME LAYOUT: Left Panel | Board | Right Panel
     ══════════════════════════════════════════════════════════ -->
<div class="game-layout">

    <!-- ── LEFT PANEL: Players & Dice ── -->
    <aside class="panel">

        <h3>Players</h3>

        <!-- Player 1 card -->
        <div class="player-card p1 <?= $turn === 1 ? 'active-turn' : '' ?>">
            <div class="player-name">
                <span class="token">P1</span>
                <?= htmlspecialchars($p1) ?>
                <?php if ($turn === 1): ?> 🎯<?php endif; ?>
            </div>
            <div class="player-stat"><span>Position</span><span>Cell <?= $pos1 === 0 ? 'Start' : $pos1 ?></span></div>
            <div class="player-stat"><span>Score</span>    <span><?= $g['scores'][1] ?></span></div>
            <div class="player-stat"><span>Turns</span>    <span><?= $g['turns_taken'][1] ?></span></div>
            <div class="player-stat"><span>🐍 Hits</span>  <span><?= $g['snake_hits'][1] ?></span></div>
            <div class="player-stat"><span>🪜 Climbs</span><span><?= $g['ladder_climbs'][1] ?></span></div>
        </div>

        <!-- Player 2 card -->
        <div class="player-card p2 <?= $turn === 2 ? 'active-turn' : '' ?>">
            <div class="player-name">
                <span class="token" style="background:var(--p2-color)">P2</span>
                <?= htmlspecialchars($p2) ?>
                <?php if ($turn === 2): ?> 🎯<?php endif; ?>
            </div>
            <div class="player-stat"><span>Position</span><span>Cell <?= $pos2 === 0 ? 'Start' : $pos2 ?></span></div>
            <div class="player-stat"><span>Score</span>    <span><?= $g['scores'][2] ?></span></div>
            <div class="player-stat"><span>Turns</span>    <span><?= $g['turns_taken'][2] ?></span></div>
            <div class="player-stat"><span>🐍 Hits</span>  <span><?= $g['snake_hits'][2] ?></span></div>
            <div class="player-stat"><span>🪜 Climbs</span><span><?= $g['ladder_climbs'][2] ?></span></div>
        </div>

        <!-- Turn banner -->
        <div class="turn-banner">
            🎲 <?= htmlspecialchars($turn === 1 ? $p1 : $p2) ?>'s Turn
        </div>

        <!-- Dice section -->
        <div class="dice-section">
            <?php if ($lastRoll): ?>
                <span class="dice-display" id="diceDisplay">
                    <?= $diceFaces[(int)$lastRoll] ?? '🎲' ?>
                </span>
                <div class="dice-result">Rolled a <strong><?= (int)$lastRoll ?></strong></div>
            <?php else: ?>
                <span class="dice-display" id="diceDisplay">🎲</span>
                <div class="dice-result">Press Roll to start!</div>
            <?php endif; ?>

            <!-- Event message feedback -->
            <?php if ($event): ?>
                <div class="event-msg event-<?= htmlspecialchars($evType) ?> mb-1">
                    <?= htmlspecialchars($event) ?>
                </div>
            <?php endif; ?>

            <!-- Skip notice -->
            <?php if ($g['skip_next'][$turn]): ?>
                <div class="skip-notice">💀 <?= htmlspecialchars($turn === 1 ? $p1 : $p2) ?> loses this turn!</div>
                <form method="POST" action="roll.php" class="roll-form">
                    <input type="hidden" name="action" value="skip">
                    <button type="submit" class="btn-roll">⏭ Skip Turn</button>
                </form>
            <?php else: ?>
                <form method="POST" action="roll.php" class="roll-form" id="rollForm">
                    <input type="hidden" name="action" value="roll">
                    <button type="submit" class="btn-roll" id="rollBtn">🎲 Roll Dice</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- New game link -->
        <div class="mt-2 text-center">
            <a href="game.php?new=1" class="btn btn-ghost" style="width:100%;font-size:0.8rem;"
               onclick="return confirm('Start a new game? Current progress will be lost.')">
                🔄 New Game
            </a>
        </div>

    </aside>

    <!-- ── CENTER: Game Board ── -->
    <section class="board-wrapper">

        <div class="board-title">🐍 CobraClimb Board 🪜</div>

        <!-- Off-board start zone (players at position 0) -->
        <?php if ($pos1 === 0 || $pos2 === 0): ?>
        <div class="start-zone">
            <span>🏁 Starting line:</span>
            <?php if ($pos1 === 0): ?><span class="token t1" style="width:22px;height:22px;">P1</span><?php endif; ?>
            <?php if ($pos2 === 0): ?><span class="token t2" style="width:22px;height:22px;">P2</span><?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 10×10 Board Grid -->
        <div class="board" role="grid" aria-label="CobraClimb game board">
            <?php foreach ($boardCells as $cell):
                $type    = cellType($cell);
                $icon    = cellIcon($cell);
                $isFinish = ($cell === 100);
                $classes  = 'cell';
                if ($type)     $classes .= ' ' . $type;
                if ($isFinish) $classes .= ' cell-finish';
            ?>
            <?php
                $dest = '';
                if (isset(SNAKES[$cell]))        $dest = ' data-dest="' . SNAKES[$cell] . '"';
                elseif (isset(LADDERS[$cell]))   $dest = ' data-dest="' . LADDERS[$cell] . '"';
            ?>
            <div class="<?= $classes ?>" title="Cell <?= $cell ?>"<?= $dest ?>>
                <span class="cell-num"><?= $cell ?></span>

                <?php if ($isFinish): ?>
                    <span class="cell-icon">🏆</span>
                <?php elseif ($icon): ?>
                    <span class="cell-icon"><?= $icon ?></span>
                <?php endif; ?>

                <!-- Player tokens -->
                <?php
                $tokens = '';
                if ($pos1 === $cell) $tokens .= '<span class="token t1" title="' . htmlspecialchars($p1) . '">P1</span>';
                if ($pos2 === $cell) $tokens .= '<span class="token t2" title="' . htmlspecialchars($p2) . '">P2</span>';
                if ($tokens) echo '<div class="cell-tokens">' . $tokens . '</div>';
                ?>
            </div>
            <?php endforeach; ?>
        </div>

    </section>

    <!-- ── RIGHT PANEL: Roll History & Legend ── -->
    <aside class="panel">

        <h3>Roll History</h3>
        <div class="roll-log" id="rollLog">
            <?php if (empty($g['roll_history'])): ?>
                <div class="log-entry text-center text-muted" style="font-size:0.8rem;">
                    No rolls yet — start playing!
                </div>
            <?php else: ?>
                <?php foreach (array_reverse($g['roll_history']) as $entry): ?>
                <div class="log-entry log-<?= htmlspecialchars($entry['type']) ?>">
                    <strong><?= htmlspecialchars($entry['player']) ?></strong>
                    rolled <?= (int)$entry['roll'] ?> →
                    cell <?= (int)$entry['to'] ?>
                    <?php if ($entry['event']): ?>
                        · <?= htmlspecialchars($entry['event']) ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h3 class="mt-2">Board Legend</h3>
        <div class="legend">
            <div class="legend-item"><span class="legend-dot ld-snake"></span>🐍 Snake Head (slide down)</div>
            <div class="legend-item"><span class="legend-dot ld-ladder"></span>🪜 Ladder Base (climb up)</div>
            <div class="legend-item"><span class="legend-dot ld-roll"></span>🎁 Roll Again bonus</div>
            <div class="legend-item"><span class="legend-dot ld-skip"></span>💀 Skip Turn penalty</div>
            <div class="legend-item"><span class="legend-dot ld-warp"></span>⚡ Warp tile</div>
            <div class="legend-item"><span class="token t1" style="width:18px;height:18px;font-size:0.6rem;">P1</span>
                <?= htmlspecialchars($p1) ?></div>
            <div class="legend-item"><span class="token t2" style="width:18px;height:18px;font-size:0.6rem;">P2</span>
                <?= htmlspecialchars($p2) ?></div>
        </div>

        <h3 class="mt-2">Scoring</h3>
        <div class="legend">
            <div class="legend-item" style="flex-direction:column;align-items:flex-start;">
                <span>Base: 1000 pts</span>
                <span>Per turn: −12 pts</span>
                <span>Snake hit: −60 pts</span>
                <span>Ladder climb: +30 pts</span>
                <span>Win &lt;20 turns: +200 pts</span>
            </div>
        </div>

        <div class="mt-2">
            <a href="leaderboard.php" class="btn btn-ghost btn-block" style="font-size:0.85rem;">
                🏆 Leaderboard
            </a>
        </div>

    </aside>

</div><!-- /.game-layout -->

<?php require 'includes/footer.php'; ?>
