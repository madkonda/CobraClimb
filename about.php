<?php
// ============================================================
// about.php — CobraClimb Team Introduction Page
// CSC 4370/6370 · Spring 2026 · Georgia State University
// Required submission document: Team Intro Page
// ============================================================
require 'includes/auth_check.php';
require 'includes/config.php';
require 'includes/header.php';
?>

<div class="lb-container" style="max-width:780px;">

    <div class="lb-header">
        <h1>🐍 CobraClimb</h1>
        <p style="font-size:1.05rem;color:var(--text);">
            A fully PHP-driven Snakes &amp; Ladders board game built for<br>
            <strong>CSC 4370/6370 — Web Programming · Spring 2026</strong><br>
            Georgia State University
        </p>
    </div>

    <!-- Project Description -->
    <div class="win-screen" style="margin-bottom:1.5rem;">
        <h2 style="font-size:1.2rem;margin-bottom:0.75rem;">🎮 Project Description</h2>
        <p style="color:var(--text-muted);line-height:1.8;">
            CobraClimb is a server-side PHP board game where two registered players race across a
            100-cell grid. Roll the dice, climb ladders, dodge cobras, trigger bonus tiles, and
            race your opponent to cell 100. All game logic — dice rolls, snake/ladder detection,
            score calculation, and leaderboard persistence — runs entirely server-side in PHP with
            no JavaScript. Three difficulty levels (Easy / Standard / Expert) change the board
            layout and hazard density. An AI Narrator generates story-driven text for every board
            event using pure PHP string logic.
        </p>
    </div>

    <!-- Team Members -->
    <h2 style="font-size:1.1rem;margin-bottom:1rem;">👥 Team Members</h2>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">

        <!-- Member 1 -->
        <div class="player-card p1" style="padding:1.25rem;">
            <div class="player-name" style="font-size:1rem;margin-bottom:0.75rem;">
                <span class="token">P1</span>
                <strong>Madhu Sudhan Reddy Konda</strong>
            </div>
            <div class="player-stat"><span>Scrum Role</span><span>🧭 Scrum Master</span></div>
            <div class="player-stat"><span>Project Role</span><span>Project Leader</span></div>
            <div class="player-stat" style="flex-direction:column;align-items:flex-start;gap:0.25rem;">
                <span style="color:var(--text-muted);font-size:0.8rem;">Primary Contributions</span>
                <span style="font-size:0.83rem;">PHP game engine, session management,
                roll logic, leaderboard, cookie persistence,
                AI narrator, difficulty system, auth flow</span>
            </div>
        </div>

        <!-- Member 2 -->
        <div class="player-card p2" style="padding:1.25rem;">
            <div class="player-name" style="font-size:1rem;margin-bottom:0.75rem;">
                <span class="token" style="background:var(--p2-color)">P2</span>
                <strong>Harika Kakarala</strong>
            </div>
            <div class="player-stat"><span>Scrum Role</span><span>📦 Product Owner</span></div>
            <div class="player-stat"><span>Project Role</span><span>Frontend Lead</span></div>
            <div class="player-stat" style="flex-direction:column;align-items:flex-start;gap:0.25rem;">
                <span style="color:var(--text-muted);font-size:0.8rem;">Primary Contributions</span>
                <span style="font-size:0.83rem;">HTML5 structure, CSS3 responsive design,
                board layout, register/login forms,
                sprint documentation, DEVLOG entries</span>
            </div>
        </div>

    </div>

    <!-- Tech Stack -->
    <h2 style="font-size:1.1rem;margin-bottom:0.75rem;">⚙️ Tech Stack</h2>
    <div class="legend" style="margin-bottom:1.5rem;">
        <div class="legend-item">🐘 <strong>PHP 7.4+</strong> — All game logic, sessions, cookies, form handling</div>
        <div class="legend-item">🌐 <strong>HTML5</strong> — Semantic structure, accessible forms, dynamic PHP output</div>
        <div class="legend-item">🎨 <strong>CSS3</strong> — Flexbox/Grid, animations, dark theme, responsive design</div>
        <div class="legend-item">🔄 <strong>Scrum</strong> — 6 sprints, daily standups, sprint logs, DEVLOG</div>
        <div class="legend-item">🗂 <strong>Flat-file</strong> — JSON user store (no database required)</div>
    </div>

    <!-- 5 Core Requirements -->
    <h2 style="font-size:1.1rem;margin-bottom:0.75rem;">📋 5 Core Requirements</h2>
    <table class="lb-table" style="margin-bottom:1.5rem;">
        <thead>
            <tr><th>#</th><th>Requirement</th><th>Implementation</th></tr>
        </thead>
        <tbody>
            <tr><td>1</td><td>Sessions &amp; Cookies / Leaderboard</td>
                <td><code>$_SESSION</code> tracks positions, scores, turns. Cookie persists top 10.</td></tr>
            <tr><td>2</td><td>Form Processing</td>
                <td>POST forms for dice roll, login, register — validated with <code>filter_input()</code></td></tr>
            <tr><td>3</td><td>Login &amp; Registration</td>
                <td>Flat-file JSON store, <code>password_hash()</code>, session-protected routes</td></tr>
            <tr><td>4</td><td>PHP Game Logic</td>
                <td><code>rand(1,6)</code> dice, snake/ladder arrays, AI narrator, win condition — all server-side</td></tr>
            <tr><td>5</td><td>Rubric Alignment</td>
                <td>Responsive CSS3, Scrum docs, DEVLOG, 3 difficulty levels, AI enhancement</td></tr>
        </tbody>
    </table>

    <div class="lb-actions">
        <a href="game.php" class="btn btn-primary btn-lg">🎲 Play CobraClimb</a>
        <a href="leaderboard.php" class="btn btn-blue btn-lg">🏆 Leaderboard</a>
    </div>

</div>

<?php require 'includes/footer.php'; ?>
