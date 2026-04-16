# CobraClimb 🐍🪜

**CSC 4370/6370 — Web Programming · Spring 2026 · Georgia State University**

A fully PHP-driven Snakes & Ladders board game with user authentication, session management, and a live leaderboard.

---

## 👥 Team Members

| Name | Role | Scrum Role |
|------|------|------------|
| Madhu Sudhan Reddy Konda | PHP Logic, Sessions, Game Engine | Scrum Master |
| Harika Kakarala | HTML/CSS, Forms, Testing | Product Owner |

---

## 🎮 Project Overview

CobraClimb is a server-side PHP board game where 2 registered players race across a 100-cell grid. Roll the dice, climb ladders, dodge cobras, and hit the leaderboard!

**Tech Stack:** PHP · HTML5 · CSS3 · Scrum Agile

---

## 🗂️ File Structure

```
CobraClimb/
├── index.php           → Redirects to login
├── login.php           → User login + session start
├── register.php        → New user registration
├── game.php            → Main game board (session-protected)
├── roll.php            → Dice roll processor (POST handler)
├── leaderboard.php     → Top scores display
├── logout.php          → Session destroy + redirect
├── includes/
│   ├── header.php      → Shared HTML header
│   ├── footer.php      → Shared HTML footer
│   └── auth_check.php  → Session guard (redirects if not logged in)
├── data/
│   └── users.json      → Flat-file user store (no database)
├── css/
│   └── style.css       → Full CSS3 responsive styling
└── js/
    └── game.js         → Minor UI helpers (animations only)
```

---

## ⚙️ Setup Instructions (Local — XAMPP)

1. Clone this repo into your XAMPP `htdocs` folder
2. Start Apache in XAMPP
3. Visit `http://localhost/CobraClimb/`
4. Register an account and start playing!

**PHP Version Required:** 7.4 or higher  
**No database required** — uses flat-file JSON storage

---

## 📋 5 Core Requirements Implemented

| # | Requirement | Implementation |
|---|-------------|----------------|
| 1 | Sessions & Cookies / Leaderboard | `$_SESSION` tracks positions, turns, scores. Cookie persists leaderboard. |
| 2 | Form Processing | POST forms for dice roll, login, register — all validated + sanitized |
| 3 | Login & Registration | Flat-file user store, session-protected routes, `session_destroy()` logout |
| 4 | PHP Game Logic | `rand(1,6)` dice, snake/ladder arrays, win condition — all server-side |
| 5 | Rubric Alignment | Responsive CSS3, Scrum docs, Development Journal, 15–20 min video |

---

## 🔀 Commit Conventions

All commits follow the format: `[SprintN] filename — description`

| Sprint | Focus |
|--------|-------|
| S1 | Project scaffold, auth pages, flat-file user store |
| S2 | Game board, config constants, session game state |
| S3 | Roll logic, bonus tiles, leaderboard, cookie persistence |
| S4 | Security hardening, accessibility, edge-case fixes |
| S5 | CSS polish, animations, responsive breakpoints |
| S6 | Testing, DEVLOG, final documentation |

Individual contribution can be traced by author on each commit (`git log --oneline --author="Name"`).

---

## 📅 Project Deadlines

- **Proposal Due:** April 5, 2026 @ 11:59 PM
- **Project Due:** April 19, 2026 @ 11:59 PM

---

*CSC 4370/6370 · Georgia State University · Spring 2026*
