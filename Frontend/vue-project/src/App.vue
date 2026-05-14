<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { ref } from 'vue'
import { auth } from './auth'
import api from './api'

const resending       = ref(false)
const resendDone      = ref(false)

const resendVerification = async () => {
  resending.value = true
  try {
    await api.post('/resend-verification')
    resendDone.value = true
  } finally {
    resending.value = false
  }
}

const router   = useRouter()
const menuOpen = ref(false)

const onLogout = async () => {
  menuOpen.value = false
  await auth.logout()
  router.push('/')
}
const closeMenu = () => { menuOpen.value = false }
</script>

<template>
  <v-app>
    <!-- ── Nav bar — plain <header>, guaranteed 100% wide ── -->
    <header class="app-header">
      <RouterLink to="/" class="brand" @click="closeMenu">🎮 GameMate</RouterLink>

      <nav class="desktop-nav">
        <RouterLink to="/" class="nav-link">Sākums</RouterLink>
        <template v-if="auth.token">
          <RouterLink to="/discover" class="nav-link">Atklāt</RouterLink>
          <RouterLink to="/matches"  class="nav-link">Sakritības</RouterLink>
          <RouterLink to="/profile"  class="nav-link">Profils</RouterLink>
          <RouterLink v-if="auth.user?.is_admin" to="/admin" class="nav-link nav-admin">Admin</RouterLink>
          <button class="nav-link nav-logout" @click="onLogout">Iziet</button>
        </template>
        <template v-else>
          <RouterLink to="/login"    class="nav-link">Pieslēgties</RouterLink>
          <RouterLink to="/register" class="nav-cta">Reģistrēties</RouterLink>
        </template>
      </nav>

      <button class="hamburger-btn" @click="menuOpen = !menuOpen" aria-label="Menu">
        <span class="bar" :class="{ open: menuOpen }">
          <i /><i /><i />
        </span>
      </button>
    </header>

    <!-- ── Mobile overlay ── -->
    <div class="mobile-overlay" :class="{ open: menuOpen }" @click.self="closeMenu">
      <div class="mobile-panel">
        <RouterLink to="/" class="mm-link" @click="closeMenu">Sākums</RouterLink>
        <template v-if="auth.token">
          <RouterLink to="/discover" class="mm-link" @click="closeMenu">Atklāt</RouterLink>
          <RouterLink to="/matches"  class="mm-link" @click="closeMenu">Sakritības</RouterLink>
          <RouterLink to="/profile"  class="mm-link" @click="closeMenu">Profils</RouterLink>
          <RouterLink v-if="auth.user?.is_admin" to="/admin" class="mm-link mm-admin" @click="closeMenu">Admin</RouterLink>
          <button class="mm-link mm-logout" @click="onLogout">Iziet</button>
        </template>
        <template v-else>
          <RouterLink to="/login"    class="mm-link" @click="closeMenu">Pieslēgties</RouterLink>
          <RouterLink to="/register" class="mm-link mm-cta" @click="closeMenu">Reģistrēties</RouterLink>
        </template>
      </div>
    </div>

    <!-- ── Email verification banner ── -->
    <div
      v-if="auth.token && auth.user && !auth.user.email_verified_at"
      class="verify-banner"
    >
      <span>Apstipriniet savu e-pastu, lai aktivizētu kontu.</span>
      <button v-if="!resendDone" class="verify-resend" :disabled="resending" @click="resendVerification">
        {{ resending ? 'Sūta…' : 'Nosūtīt vēlreiz' }}
      </button>
      <span v-else class="verify-sent">✓ Nosūtīts!</span>
    </div>

    <!-- ── Content — plain <div>, NOT v-main ── -->
    <div class="app-content">
      <RouterView />
    </div>
  </v-app>
</template>

<style>
/* ── Global resets ── */
*, *::before, *::after { box-sizing: border-box; }
html, body { margin: 0; padding: 0; overflow-x: hidden; width: 100%; }
img { max-width: 100%; }

/* Make v-app behave as a full-height flex column */
.v-application {
  display:        flex        !important;
  flex-direction: column      !important;
  min-height:     100vh       !important;
  width:          100%        !important;
  max-width:      100%        !important;
}
</style>

<style scoped>
/* ── Header ── */
.app-header {
  width: 100%;
  height: 62px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  background: rgba(8, 8, 18, 0.82);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(124,58,237,.28);
  box-shadow: 0 1px 32px rgba(0,0,0,.4), 0 0 0 0.5px rgba(124,58,237,.1);
  position: sticky;
  top: 0;
  z-index: 100;
  flex-shrink: 0;
}

.brand {
  background: linear-gradient(135deg, #c4b5fd 0%, #818cf8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-decoration: none;
  font-weight: 900; font-size: 1.1rem; letter-spacing: .2px;
}

/* Desktop nav */
.desktop-nav { display: flex; align-items: center; gap: 2px; }

.nav-link {
  color: #bbb; text-decoration: none;
  font-size: .875rem; font-weight: 500;
  padding: 6px 10px; border-radius: 6px;
  background: none; border: none; cursor: pointer;
  transition: color .2s, background .2s;
}
.nav-link:hover { color: #fff; background: rgba(255,255,255,.07); }
.router-link-active.nav-link {
  color: #c4b5fd;
  background: rgba(167,139,250,.1);
}

.nav-cta {
  color: #fff; text-decoration: none;
  font-size: .875rem; font-weight: 600;
  padding: 6px 14px; border-radius: 6px;
  background: #7c3aed; margin-left: 6px;
  transition: background .2s;
}
.nav-cta:hover { background: #6d28d9; }
.nav-admin  { color: #fbbf24; }
.nav-admin:hover { color: #fcd34d !important; background: rgba(251,191,36,.08) !important; }
.nav-logout { color: #f87171; }
.nav-logout:hover { color: #fca5a5 !important; background: rgba(239,68,68,.08) !important; }
.mm-admin   { color: #fbbf24; }

/* ── Verification banner ── */
.verify-banner {
  width: 100%; background: #92400e; color: #fde68a;
  padding: 10px 28px; display: flex; align-items: center;
  gap: 12px; font-size: .85rem; flex-shrink: 0;
}
.verify-resend {
  background: rgba(255,255,255,.15); border: none; color: #fde68a;
  padding: 4px 12px; border-radius: 6px; cursor: pointer; font-size: .8rem;
  font-weight: 600; transition: background .2s;
}
.verify-resend:hover:not(:disabled) { background: rgba(255,255,255,.25); }
.verify-resend:disabled { opacity: .5; cursor: not-allowed; }
.verify-sent { color: #bbf7d0; font-weight: 600; font-size: .8rem; }

/* ── Content area ── */
.app-content {
  width: 100%;
  flex: 1;
  min-height: 0;
  background: #121212;
}

/* ── Hamburger ── */
.hamburger-btn {
  display: none; background: none; border: none;
  cursor: pointer; padding: 8px;
}
.bar { display: flex; flex-direction: column; gap: 5px; }
.bar i {
  display: block; width: 22px; height: 2px;
  background: #fff; border-radius: 2px;
  transition: all .3s; font-style: normal;
}
.bar.open i:nth-child(1) { transform: translateY(7px)  rotate(45deg); }
.bar.open i:nth-child(2) { opacity: 0; }
.bar.open i:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ── Mobile overlay ── */
.mobile-overlay {
  display: none; position: fixed; inset: 0;
  z-index: 200; background: rgba(0,0,0,.6);
  backdrop-filter: blur(4px);
}
.mobile-overlay.open { display: flex; justify-content: flex-end; }
.mobile-panel {
  width: 240px; max-width: 80vw;
  background: #1c1c1c; border-left: 1px solid #333;
  padding: 80px 0 24px; display: flex; flex-direction: column;
}
.mm-link {
  display: block; padding: 16px 24px;
  color: #ddd; text-decoration: none;
  font-size: 1rem; font-weight: 600;
  border: none; background: none; text-align: left;
  cursor: pointer; transition: background .2s, color .2s;
}
.mm-link:hover { background: #2a2a2a; color: #fff; }
.mm-cta    { color: #a78bfa; }
.mm-logout { color: #f87171; }

@media (max-width: 768px) {
  .desktop-nav   { display: none; }
  .hamburger-btn { display: block; }
}
</style>
