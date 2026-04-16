# CobraClimb — Development Journal 📓

**CSC 4370/6370 · Spring 2026 · Georgia State University**  
Maintained by: Madhu Sudhan Reddy Konda (Scrum Master)

---

## Sprint 1 — Days 1–3 (Apr 1–3)

### Apr 1, 2026
- Held first team standup on Discord
- Decided on Topic 03 — Adventures of the Dice (Snakes & Ladders)
- Named project "CobraClimb" — snake theme + climbing mechanic
- Assigned roles: Madhu = Scrum Master / PHP logic, Harika = Product Owner / HTML+CSS
- Created GitHub repo: `madkonda/CobraClimb`
- Drafted initial wireframes for all 7 screens on Draw.io

### Apr 2, 2026
- Completed Group Proposal document (all 7 sections)
- Defined snake positions (9 snakes), ladder positions (4 ladders), bonus tiles
- Designed scoring system: base 1000 − (turns × 12) + bonuses
- Committed initial README.md with team info and file structure

### Apr 3, 2026
- Submitted proposal to i-College Dropbox (both members uploaded individually)
- Set up GitHub Projects Kanban board for sprint tracking
- Finalized wireframe sketches and attached to proposal docx

---

## Sprint 2 — Days 4–6 (Apr 4–6)

### Apr 4, 2026
- Set up XAMPP local dev environment — Apache + PHP 8.1 running
- Created full folder structure: includes/, data/, css/, js/
- Built index.php entry point redirect
- Created includes/header.php and includes/footer.php shared layout

### Apr 5, 2026
- Built semantic HTML5 structure for all .php files (empty shells)
- Navigation links working across all pages
- Created auth_check.php session guard — redirects to login if no session
- Confirmed PHP include/require pattern works with XAMPP

### Apr 6, 2026
- Created includes/config.php with all game constants
  - SNAKES array: 9 snake positions (head → tail)
  - LADDERS array: 4 ladder positions (base → top)
  - BONUS_EXTRA_ROLL, BONUS_SKIP_TURN, BONUS_WARP arrays
  - Helper functions: loadUsers(), saveUsers(), findUser(), initGame(), calcFinalScore()
- All PHP files exist and navigate correctly

---

## Sprint 3 — Days 7–9 (Apr 7–9)

### Apr 7, 2026
- Built register.php with full POST form validation
  - password_hash(PASSWORD_DEFAULT) for secure storage
  - filter_input() + htmlspecialchars() for sanitization
  - Duplicate username check against users.json
  - Sticky form values on validation failure
- Built login.php with password_verify() credential check
  - session_regenerate_id(true) to prevent session fixation
  - $_SESSION['user'] and $_SESSION['logged_in'] set on success

### Apr 8, 2026
- Built roll.php dice roll processor
  - rand(1,6) server-side dice
  - Snake head → tail detection via SNAKES array
  - Ladder base → top detection via LADDERS array
  - Bonus tile detection: extra roll, skip turn, warp
  - Win condition: position >= 100 → redirect to leaderboard
  - calcFinalScore() computes final score on win
  - setcookie() persists top 10 leaderboard for 7 days
- Tested: snake hits, ladder climbs, bonus tiles all working

### Apr 9, 2026
- Built game.php main board
  - buildBoardOrder() generates 100 cells in snake pattern (top-left = 100)
  - PHP for loop renders all cells with correct CSS classes
  - Player tokens shown at correct cell positions from $_SESSION
  - Roll history sidebar logs last 30 events
  - Turn indicator and player stats panel
- Session-protected: auth_check.php included at top
- Tested: two-player turn alternation working correctly

---

## Sprint 4 — Days 10–12 (Apr 10–12)

### Apr 10, 2026
- Built full css/style.css
  - CSS custom properties (variables) for theming
  - Dark theme: bg #0d1117, panels #161b22
  - CSS Grid: 3-column game layout (panels + board)
  - Board: 10×10 CSS Grid with aspect-ratio:1 cells
  - Checkerboard pattern via nth-child selector
- Cell type overlays: snake-head (red), ladder-base (green), bonus (gold)

### Apr 11, 2026
- CSS animations added:
  - Dice roll: rotate + scale keyframe animation
  - Player token: tokenPop scale animation on landing
  - Win screen: winGlow pulse animation
  - Confetti: confettiFall keyframe for win celebration
- Responsive breakpoints: 1100px, 860px, 500px
- Mobile: single column layout, board scales with min()

### Apr 12, 2026
- Cross-browser testing: Chrome, Firefox, Safari — all passing
- Fixed: board aspect-ratio on Safari (added explicit height)
- Added utility CSS classes: .text-center, .mt-1, .d-flex etc.
- Polished: hover states on cells, buttons, nav links

---

## Sprint 5 — Days 13–15 (Apr 13–15)

### Apr 13, 2026
- Built leaderboard.php
  - usort() sorts scores descending, turns ascending (tiebreaker)
  - Cookie restoration: unserialize base64_decode from cobra_leaderboard
  - Medal display: 🥇🥈🥉 for top 3
  - Empty state: "No scores yet" message when array empty
- Win screen stats: final score, turns, snake hits, ladder climbs, time

### Apr 14, 2026
- Full integration test — complete game flow:
  - Register → Login → Game → Roll × N → Win → Leaderboard → Logout → Login again
  - Leaderboard persists across sessions via cookie
  - All 5 rubric requirements verified and checked off
- Bug fix: bounce-back mechanic when roll exceeds cell 100
- Bug fix: skip_next flag not clearing properly — fixed in roll.php

### Apr 15, 2026
- Added session timeout (45 min) to auth_check.php
- Added .htaccess to protect data/ directory from direct browser access
- Added data/.htaccess to deny access to users.json
- js/game.js: dice animation, confetti, keyboard shortcut (Space = roll)

---

## Sprint 6 — Days 16–18 (Apr 16–19)

### Apr 16, 2026
- Final code review pass — all files reviewed by both team members
- Added PHP comments to every major function in config.php
- Code cleanup: removed debug echo statements, standardized indentation

### Apr 17, 2026
- Recorded 15–20 min presentation video on Zoom
- All team members on camera — each walked through their own code
- VS Code walkthrough: PHP files, session logic, form validation
- Showed responsive design on Chrome DevTools mobile view

### Apr 18, 2026
- Video uploaded to YouTube (unlisted)
- Tested video link in incognito — accessible without login
- Final GitHub commit history reviewed — 20+ commits from both members

### Apr 19, 2026
- Submitted .txt file to i-College Dropbox (both members uploaded individually)
- .txt contains: GitHub URL + Video URL
- Deadline: 11:59 PM — submitted before deadline ✅

---

*This journal was maintained daily throughout the 18-day sprint window.*  
*CSC 4370/6370 · Georgia State University · Spring 2026*
