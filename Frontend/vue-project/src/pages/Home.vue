<script setup>
import { auth } from '../auth'

const steps = [
  { title: 'Izveido profilu', desc: 'Pievieno spēles, platformu, reģionu un spēļu kontus ar rangiem.' },
  { title: 'Pārlūko spēlētājus', desc: 'Atklāj citus spēlētājus, kas atbilst tavām interesēm un stilam.' },
  { title: 'Sakrities un tērzē', desc: 'Kad abiem patīkat viens otrs, sāciet tērzēt un spēlēt kopā.' },
]

const features = [
  { icon: '🎮', title: 'Vairākas platformas', desc: 'PC, PlayStation, Xbox, Switch un vēl vairāk.' },
  { icon: '🏆', title: 'Ranga attēlošana', desc: 'Automātiski iegūsti rangu no Tracker.gg — Valorant, LoL, CS2, Apex un citās spēlēs.' },
  { icon: '🌍', title: 'Reģiona filtrs', desc: 'Atrodi spēlētājus savā reģionā, lai nodrošinātu zemu ping.' },
  { icon: '💬', title: 'Iebūvēta tērzēšana', desc: 'Raksti savām sakritībām tieši GameMate platformā.' },
]
</script>

<template>
  <!-- ── Hero ── -->
  <section class="hero-section">
    <div class="hero-glow" />
    <div class="hero-inner">

      <div class="hero-left">
        <p class="eyebrow">Spēļu savienošana</p>
        <h1 class="hero-title">
          Atrodi savu nākamo<br>
          <span class="grad">spēļu komandas biedru.</span>
        </h1>
        <p class="hero-sub">
          GameMate savieno spēlētājus pēc spēles, platformas, reģiona un spēles stila.
          Spied, sakriti un sāc spēlēt kopā.
        </p>
        <div class="hero-actions">
          <v-btn v-if="!auth.token" color="primary" size="large" to="/register" class="hero-cta">Sākt bez maksas</v-btn>
          <v-btn v-else color="primary" size="large" to="/discover" class="hero-cta">Atklāt spēlētājus</v-btn>
          <v-btn v-if="!auth.token" variant="outlined" size="large" to="/login">Pieslēgties</v-btn>
        </div>
      </div>

      <div class="hero-right">
        <p class="demo-label">Piemērs</p>
        <div class="demo-stack">
          <div class="demo-card demo-card--back" />
          <div class="demo-card demo-card--front">
            <div class="dc-top">
              <div class="dc-avatar">⚡</div>
              <div class="dc-info">
                <div class="dc-name">NightOwl<span class="dc-tag">#EU1</span></div>
                <div class="dc-meta">PC · EU · Competitive</div>
              </div>
              <div class="dc-rank-label">Radiant</div>
            </div>
            <div class="dc-games">
              <span class="dc-chip purple">Valorant</span>
              <span class="dc-chip blue">CS2</span>
              <span class="dc-chip orange">Apex</span>
            </div>
            <div class="dc-accounts">
              <div class="dc-acc">🔫 <span class="acc-name">NightOwl#0001</span><span class="acc-badge">Radiant</span></div>
              <div class="dc-acc">💣 <span class="acc-name">NightOwl</span><span class="acc-badge gold">Global Elite</span></div>
            </div>
            <div class="dc-btns">
              <button class="dc-pass">✕ Pass</button>
              <button class="dc-like">♥ Like</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ── Stats strip ── -->
  <div class="stats-strip">
    <div class="stat-item">
      <span class="stat-num">500+</span>
      <span class="stat-lbl">Aktīvi spēlētāji</span>
    </div>
    <div class="stat-div" />
    <div class="stat-item">
      <span class="stat-num">50+</span>
      <span class="stat-lbl">Atbalstītas spēles</span>
    </div>
    <div class="stat-div" />
    <div class="stat-item">
      <span class="stat-num">200+</span>
      <span class="stat-lbl">Sakritības</span>
    </div>
    <div class="stat-div" />
    <div class="stat-item">
      <span class="stat-num">5</span>
      <span class="stat-lbl">Platformas</span>
    </div>
  </div>

  <!-- ── How it works + Features ── -->
  <section class="body-section">
    <div class="body-inner">

      <div class="steps-col">
        <p class="section-label">Vienkāršs process</p>
        <h2 class="section-title">Kā tas darbojas</h2>
        <div v-for="(step, i) in steps" :key="i" class="step-item">
          <div class="step-num">{{ i + 1 }}</div>
          <div>
            <div class="step-name">{{ step.title }}</div>
            <div class="step-desc">{{ step.desc }}</div>
          </div>
        </div>
      </div>

      <div class="feats-col">
        <p class="section-label">Ko tu iegūsi</p>
        <h2 class="section-title">Funkcijas</h2>
        <div class="feats-grid">
          <div v-for="f in features" :key="f.icon" class="feat-card">
            <div class="feat-icon">{{ f.icon }}</div>
            <div>
              <div class="feat-title">{{ f.title }}</div>
              <div class="feat-desc">{{ f.desc }}</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ── Bottom CTA ── -->
  <section class="cta-section">
    <div class="cta-glow" />
    <div class="cta-inner">
      <p class="cta-eyebrow">Gatavs sākt?</p>
      <h2 class="cta-title">Atrodi savu spēļu komandu šodien</h2>
      <p class="cta-sub">Pievienojies simtiem spēlētāju, kas jau ir atraduši savus komandas biedrus GameMate.</p>
      <div class="cta-actions">
        <router-link v-if="!auth.token" to="/register" class="cta-btn-primary">Sākt bez maksas →</router-link>
        <router-link v-else to="/discover" class="cta-btn-primary">Atklāt spēlētājus →</router-link>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* ── Shared ── */
.hero-section,
.body-section {
  width: 100%;
  box-sizing: border-box;
}

/* ── Hero ── */
.hero-section {
  position: relative;
  padding: 80px 64px 64px;
  overflow: hidden;
}
.hero-glow {
  position: absolute;
  top: -200px; left: 40%;
  width: 900px; height: 900px;
  background: radial-gradient(circle, rgba(124,58,237,.14) 0%, transparent 65%);
  pointer-events: none; z-index: 0;
}
.hero-inner {
  position: relative; z-index: 1;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}
.hero-left  { min-width: 0; }
.hero-right { display: flex; flex-direction: column; align-items: center; }

.eyebrow {
  font-size: 0.72rem; font-weight: 700; letter-spacing: 3px;
  text-transform: uppercase; color: #a78bfa; margin-bottom: 18px;
}
.hero-title {
  font-size: clamp(2rem, 3vw, 3.5rem);
  font-weight: 900; line-height: 1.08; margin-bottom: 20px;
}
.grad {
  background: linear-gradient(135deg, #a78bfa 0%, #60a5fa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-sub {
  color: #9ca3af; font-size: 1.05rem; line-height: 1.7;
  margin-bottom: 36px;
}
.hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }
.hero-cta     { min-width: 160px; }

/* ── Demo card ── */
.demo-label {
  font-size: 0.68rem; letter-spacing: 3px; text-transform: uppercase;
  color: #6b7280; margin-bottom: 16px; text-align: center;
}
.demo-stack { position: relative; width: 340px; }
.demo-card {
  background: #1a1a2e;
  border: 1px solid rgba(167,139,250,.25);
  border-radius: 18px;
  padding: 24px;
}
.demo-card--front {
  position: relative; z-index: 2;
  box-shadow: 0 0 60px rgba(124,58,237,.18), 0 20px 40px rgba(0,0,0,.45);
}
.demo-card--back {
  position: absolute;
  top: 14px; left: -14px; right: 14px; bottom: -14px;
  z-index: 1;
  background: #13132a;
  border-color: rgba(167,139,250,.1);
}

.dc-top  { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.dc-avatar {
  width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg,#7c3aed,#4f46e5);
  display: flex; align-items: center; justify-content: center; font-size: 22px;
}
.dc-info { flex: 1; min-width: 0; }
.dc-name { font-weight: 700; font-size: 0.95rem; }
.dc-tag  { color: #6b7280; font-weight: 400; font-size: 0.8rem; }
.dc-meta { color: #9ca3af; font-size: 0.75rem; margin-top: 2px; }
.dc-rank-label {
  background: linear-gradient(135deg,#fbbf24,#f59e0b);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800; font-size: 0.85rem; white-space: nowrap;
}

.dc-games { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
.dc-chip  { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.dc-chip.purple { background: rgba(124,58,237,.25); color: #a78bfa; }
.dc-chip.blue   { background: rgba(59,130,246,.2);  color: #93c5fd; }
.dc-chip.orange { background: rgba(249,115,22,.2);  color: #fdba74; }

.dc-accounts { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.dc-acc {
  display: flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.04);
  border-radius: 8px; padding: 7px 10px; font-size: 0.78rem; color: #d1d5db;
}
.acc-name  { flex: 1; }
.acc-badge {
  background: rgba(167,139,250,.2); color: #a78bfa;
  border-radius: 12px; padding: 2px 8px; font-size: 0.7rem; font-weight: 700;
}
.acc-badge.gold { background: rgba(251,191,36,.15); color: #fbbf24; }

.dc-btns { display: flex; gap: 8px; }
.dc-pass, .dc-like {
  flex: 1; padding: 10px; border-radius: 10px;
  font-size: 0.85rem; font-weight: 700; cursor: pointer; border: none;
  transition: transform .15s, filter .15s;
}
.dc-pass:hover, .dc-like:hover { transform: translateY(-2px); filter: brightness(1.15); }
.dc-pass { background: rgba(239,68,68,.15);  color: #f87171; }
.dc-like { background: rgba(34,197,94,.15);  color: #4ade80; }

/* ── Body ── */
.body-section { padding: 72px 64px 88px; }
.body-inner {
  display: grid;
  grid-template-columns: 5fr 7fr;
  gap: 80px;
  max-width: 1400px;
  margin: 0 auto;
}
.steps-col, .feats-col { min-width: 0; }

.section-label {
  font-size: 0.72rem; font-weight: 700; letter-spacing: 3px;
  text-transform: uppercase; color: #a78bfa; margin-bottom: 6px;
}
.section-title { font-size: 1.65rem; font-weight: 800; margin-bottom: 32px; }

.step-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px; }
.step-num  {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg,#7c3aed,#4f46e5);
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 14px;
}
.step-name { font-weight: 700; font-size: 0.95rem; margin-bottom: 4px; }
.step-desc { color: #9ca3af; font-size: 0.85rem; line-height: 1.55; }

.feats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.feat-card {
  display: flex; align-items: flex-start; gap: 14px;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px; padding: 20px 18px;
  transition: border-color .25s, background .25s;
}
.feat-card:hover {
  border-color: rgba(167,139,250,.35);
  background: rgba(124,58,237,.09);
}
.feat-icon  { font-size: 26px; flex-shrink: 0; line-height: 1; margin-top: 2px; }
.feat-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 4px; }
.feat-desc  { color: #9ca3af; font-size: 0.8rem; line-height: 1.5; }

/* ── Stats strip ── */
.stats-strip {
  display: flex; align-items: center; justify-content: center;
  flex-wrap: wrap; gap: 0;
  background: rgba(124,58,237,.06);
  border-top: 1px solid rgba(124,58,237,.12);
  border-bottom: 1px solid rgba(124,58,237,.12);
  padding: 20px 32px;
}
.stat-item {
  display: flex; flex-direction: column; align-items: center;
  padding: 4px 40px; gap: 3px;
}
.stat-num  { font-size: 1.5rem; font-weight: 900; color: #c4b5fd; line-height: 1; }
.stat-lbl  { font-size: .72rem; font-weight: 600; color: #6b7280; letter-spacing: .5px; text-transform: uppercase; }
.stat-div  { width: 1px; height: 36px; background: rgba(255,255,255,.08); }

/* ── CTA section ── */
.cta-section {
  position: relative; overflow: hidden;
  padding: 80px 32px 88px;
  text-align: center;
}
.cta-glow {
  position: absolute;
  top: -120px; left: 50%; transform: translateX(-50%);
  width: 700px; height: 500px;
  background: radial-gradient(circle, rgba(124,58,237,.18) 0%, transparent 65%);
  pointer-events: none;
}
.cta-inner {
  position: relative; z-index: 1;
  max-width: 640px; margin: 0 auto;
}
.cta-eyebrow {
  font-size: .72rem; font-weight: 700; letter-spacing: 3px;
  text-transform: uppercase; color: #a78bfa; margin-bottom: 14px;
}
.cta-title {
  font-size: clamp(1.6rem, 3vw, 2.4rem);
  font-weight: 900; line-height: 1.15; margin-bottom: 16px; color: #fff;
}
.cta-sub { color: #9ca3af; font-size: 1rem; line-height: 1.65; margin-bottom: 32px; }
.cta-actions { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
.cta-btn-primary {
  display: inline-block;
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: #fff; text-decoration: none;
  padding: 14px 32px; border-radius: 12px; font-size: 1rem; font-weight: 700;
  box-shadow: 0 4px 24px rgba(124,58,237,.45);
  transition: opacity .2s, transform .15s;
}
.cta-btn-primary:hover { opacity: .9; transform: translateY(-2px); }

/* ── Mobile ── */
@media (max-width: 900px) {
  .hero-section { padding: 52px 24px 40px; }
  .hero-inner   { grid-template-columns: 1fr; gap: 40px; }
  .hero-right   { display: none; }
  .body-section { padding: 48px 24px 56px; }
  .body-inner   { grid-template-columns: 1fr; gap: 48px; }
  .feats-grid   { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
  .hero-title { font-size: 2rem; }
  .feats-grid { grid-template-columns: 1fr 1fr; }
  .stat-item  { padding: 4px 20px; }
  .cta-section { padding: 56px 24px 64px; }
}
</style>
