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
    $diff = isset($_GET['diff']) && in_array($_GET['diff'], ['easy','standard','expert'])
        ? $_GET['diff'] : 'expert';
    initGame($_SESSION['user'], $p2Name, $diff);
}

$g       = &$_SESSION['game'];
$turn    = $g['turn'];
$p1      = $g['players'][1];
$p2      = $g['players'][2];
$pos1    = $g['positions'][1];
$pos2    = $g['positions'][2];

// Retrieve any event message + AI narrator line from roll.php redirect
$event    = filter_input(INPUT_GET, 'event',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$evType   = filter_input(INPUT_GET, 'evtype',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$lastRoll = filter_input(INPUT_GET, 'roll',    FILTER_SANITIZE_NUMBER_INT)    ?? '';
$narrate  = filter_input(INPUT_GET, 'narrate', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

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

            <!-- AI Narrator — dynamic story text per event (pure PHP) -->
            <?php if ($narrate): ?>
                <div class="ai-narrator">
                    🤖 <?= htmlspecialchars($narrate) ?>
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

        <!-- Difficulty + New Game -->
        <div class="mt-2">
            <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:0.35rem;text-align:center;">
                🎚 Difficulty:
                <strong style="color:var(--text);">
                    <?= $g['difficulty'] === 'easy' ? '🟢 Easy' : ($g['difficulty'] === 'standard' ? '🟡 Standard' : '🔴 Expert') ?>
                </strong>
            </div>
            <div class="diff-selector">
                <a href="game.php?new=1&diff=easy"
                   class="diff-btn diff-easy <?= $g['difficulty']==='easy' ? 'diff-active' : '' ?>"
                   onclick="return confirm('Start new Easy game?')">🟢 Easy</a>
                <a href="game.php?new=1&diff=standard"
                   class="diff-btn diff-standard <?= $g['difficulty']==='standard' ? 'diff-active' : '' ?>"
                   onclick="return confirm('Start new Standard game?')">🟡 Standard</a>
                <a href="game.php?new=1&diff=expert"
                   class="diff-btn diff-expert <?= $g['difficulty']==='expert' ? 'diff-active' : '' ?>"
                   onclick="return confirm('Start new Expert game?')">🔴 Expert</a>
            </div>
        </div>

    </aside>

    <!-- ── CENTER: Game Board ── -->
    <section class="board-wrapper">

        <div class="board-title">🐍 CobraClimb Board 🪜</div>

        <!-- Live game timer -->
        <?php
            $elapsed  = time() - $g['start_time'];
            $minutes  = floor($elapsed / 60);
            $seconds  = $elapsed % 60;
        ?>
        <div class="text-muted" style="font-size:0.8rem;text-align:center;">
            ⏱ Game time: <?= sprintf('%d:%02d', $minutes, $seconds) ?>
        </div>

        <!-- Off-board start zone (players at position 0) -->
        <?php if ($pos1 === 0 || $pos2 === 0): ?>
        <div class="start-zone">
            <span>🏁 Starting line:</span>
            <?php if ($pos1 === 0): ?><span class="token t1" style="width:22px;height:22px;">P1</span><?php endif; ?>
            <?php if ($pos2 === 0): ?><span class="token t2" style="width:22px;height:22px;">P2</span><?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 10×10 Board Grid -->
        <?php $activeSnakes = $g['snakes']; $activeLadders = $g['ladders']; ?>
        <div class="board" role="grid" aria-label="CobraClimb Snakes and Ladders game board, <?= $g['difficulty'] ?> difficulty, cell 100 top left to cell 1 bottom left">
            <?php foreach ($boardCells as $cell):
                // Use session-stored board data for active difficulty
                $isSnakeHead = isset($activeSnakes[$cell]);
                $isSnakeTail = in_array($cell, $activeSnakes);
                $isLadderBase = isset($activeLadders[$cell]);
                $isLadderTop  = in_array($cell, $activeLadders);
                $type = '';
                if ($isSnakeHead)  $type = 'snake-head';
                elseif ($isSnakeTail)  $type = 'snake-tail';
                elseif ($isLadderBase) $type = 'ladder-base';
                elseif ($isLadderTop)  $type = 'ladder-top';
                elseif (in_array($cell, BONUS_EXTRA_ROLL)) $type = 'bonus-roll';
                elseif (in_array($cell, BONUS_SKIP_TURN))  $type = 'bonus-skip';
                elseif (in_array($cell, BONUS_WARP))       $type = 'bonus-warp';

                $icon = '';
                if ($isSnakeHead)  $icon = '🐍';
                elseif ($isSnakeTail)  $icon = '💀';
                elseif ($isLadderBase) $icon = '🪜';
                elseif ($isLadderTop)  $icon = '⬆';
                elseif (in_array($cell, BONUS_EXTRA_ROLL)) $icon = '🎁';
                elseif (in_array($cell, BONUS_SKIP_TURN))  $icon = '⛔';
                elseif (in_array($cell, BONUS_WARP))       $icon = '⚡';

                $isFinish = ($cell === 100);
                $classes  = 'cell';
                if ($type)     $classes .= ' ' . $type;
                if ($isFinish) $classes .= ' cell-finish';
            ?>
            <?php
                $dest = '';
                if ($isSnakeHead)   $dest = ' data-dest="' . $activeSnakes[$cell]  . '"';
                elseif ($isLadderBase) $dest = ' data-dest="' . $activeLadders[$cell] . '"';
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
