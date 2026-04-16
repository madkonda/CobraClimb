// ============================================================
// js/game.js — CobraClimb UI Enhancements
// CSC 4370/6370 · Spring 2026
// NOTE: All GAME LOGIC is PHP server-side. This file handles
//       UI animations only — no game state or decisions here.
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // ── Dice Roll Animation ──
  const rollForm    = document.getElementById('rollForm');
  const rollBtn     = document.getElementById('rollBtn');
  const diceDisplay = document.getElementById('diceDisplay');

  if (rollForm && rollBtn && diceDisplay) {
    rollForm.addEventListener('submit', () => {
      rollBtn.disabled = true;
      rollBtn.textContent = '🎲 Rolling...';
      diceDisplay.classList.add('rolling');
    });
  }

  // ── Auto-scroll roll log to top (newest first) ──
  const rollLog = document.getElementById('rollLog');
  if (rollLog) rollLog.scrollTop = 0;

  // ── Confetti on Win Screen ──
  const confettiWrap = document.getElementById('confettiWrap');
  if (confettiWrap) {
    const colors = ['#d29922','#2ea043','#58a6ff','#f85149','#bc8cff','#f0883e'];
    for (let i = 0; i < 80; i++) {
      const piece = document.createElement('div');
      piece.className = 'confetti-piece';
      piece.style.cssText = [
        `left:${Math.random()*100}%`,
        `width:${6+Math.random()*8}px`,
        `height:${6+Math.random()*8}px`,
        `background:${colors[Math.floor(Math.random()*colors.length)]}`,
        `animation-duration:${2+Math.random()*3}s`,
        `animation-delay:${Math.random()*2}s`,
        `border-radius:${Math.random()>0.5?'50%':'2px'}`
      ].join(';');
      confettiWrap.appendChild(piece);
    }
    setTimeout(() => confettiWrap.remove(), 6000);
  }

  // ── Tooltip: show snake/ladder destination using data attributes ──
  document.querySelectorAll('.cell[data-dest]').forEach(cell => {
    const dest = cell.getAttribute('data-dest');
    const type = cell.classList.contains('snake-head') ? '🐍 Snake! Slides' : '🪜 Ladder! Climbs';
    cell.title = `${type} to cell ${dest}`;
  });

  // ── Highlight cells with players ──
  document.querySelectorAll('.cell-tokens').forEach(wrap => {
    wrap.closest('.cell')?.style.setProperty('box-shadow','0 0 8px rgba(255,255,255,0.25)');
  });

});
