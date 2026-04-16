// ============================================================
// js/game.js — CobraClimb UI Enhancements
// CSC 4370/6370 · Spring 2026 · Georgia State University
// NOTE: All GAME LOGIC is PHP server-side. This file handles
//       UI animations and keyboard shortcuts only.
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // ── Dice Roll Animation + button state ──
  const rollForm    = document.getElementById('rollForm');
  const rollBtn     = document.getElementById('rollBtn');
  const diceDisplay = document.getElementById('diceDisplay');

  if (rollForm && rollBtn && diceDisplay) {
    rollForm.addEventListener('submit', () => {
      rollBtn.disabled = true;
      rollBtn.textContent = '🎲 Rolling...';
      diceDisplay.classList.add('rolling');
    });

    // ── Keyboard shortcut: Space bar = Roll Dice ──
    document.addEventListener('keydown', (e) => {
      if (e.code === 'Space' && !rollBtn.disabled
          && document.activeElement.tagName !== 'INPUT'
          && document.activeElement.tagName !== 'TEXTAREA') {
        e.preventDefault();
        rollForm.requestSubmit();
      }
    });
  }

  // ── Auto-scroll roll log to top (newest first) ──
  const rollLog = document.getElementById('rollLog');
  if (rollLog) rollLog.scrollTop = 0;

  // ── Confetti animation on Win Screen ──
  const confettiWrap = document.getElementById('confettiWrap');
  if (confettiWrap) {
    const colors = ['#d29922','#2ea043','#58a6ff','#f85149','#bc8cff','#f0883e'];
    for (let i = 0; i < 80; i++) {
      const piece = document.createElement('div');
      piece.className = 'confetti-piece';
      piece.style.cssText = [
        `left:${Math.random() * 100}%`,
        `width:${6 + Math.random() * 8}px`,
        `height:${6 + Math.random() * 8}px`,
        `background:${colors[Math.floor(Math.random() * colors.length)]}`,
        `animation-duration:${2 + Math.random() * 3}s`,
        `animation-delay:${Math.random() * 2}s`,
        `border-radius:${Math.random() > 0.5 ? '50%' : '2px'}`
      ].join(';');
      confettiWrap.appendChild(piece);
    }
    setTimeout(() => confettiWrap.remove(), 6000);
  }

  // ── Tooltip: show snake/ladder destination via data-dest attribute ──
  document.querySelectorAll('.cell[data-dest]').forEach(cell => {
    const dest = cell.getAttribute('data-dest');
    const isSnake  = cell.classList.contains('snake-head');
    const isLadder = cell.classList.contains('ladder-base');
    if (isSnake)  cell.title = `🐍 Snake! Slides down to cell ${dest}`;
    if (isLadder) cell.title = `🪜 Ladder! Climbs up to cell ${dest}`;
  });

  // ── Highlight cells that have player tokens ──
  document.querySelectorAll('.cell-tokens').forEach(wrap => {
    const parentCell = wrap.closest('.cell');
    if (parentCell) {
      parentCell.style.boxShadow = '0 0 10px rgba(255,255,255,0.3)';
      parentCell.style.zIndex    = '5';
    }
  });

  // ── Show spacebar hint only on game page ──
  if (rollBtn) {
    const hint = document.createElement('small');
    hint.textContent = 'Tip: Press Space to roll 🎲';
    hint.style.cssText = 'display:block;color:var(--text-muted);font-size:0.72rem;margin-top:4px;text-align:center;';
    rollBtn.insertAdjacentElement('afterend', hint);
  }

});
