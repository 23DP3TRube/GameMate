<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const matches = ref([])
const loading = ref(true)

onMounted(async () => {
  const { data } = await api.get('/matches')
  matches.value = data
  loading.value = false
})
</script>

<template>
  <div class="matches-page">
    <div class="matches-inner">
      <div class="page-header">
        <h2 class="page-title">Manas Sakritības</h2>
        <span v-if="matches.length" class="match-count">{{ matches.length }}</span>
      </div>

      <div v-if="loading" class="loading-wrap">
        <v-progress-circular indeterminate color="purple" />
      </div>

      <div v-else-if="matches.length === 0" class="empty-state">
        <div class="empty-icon">💜</div>
        <p class="empty-title">Vēl nav sakritību</p>
        <p class="empty-sub">Atklāj jaunus spēlētājus un sakriti!</p>
        <router-link to="/discover" class="cta-btn">Atklāt spēlētājus</router-link>
      </div>

      <div v-else class="match-list">
        <router-link
          v-for="m in matches" :key="m.match_id"
          :to="`/matches/${m.match_id}`"
          class="match-card"
        >
          <div class="mc-avatar">
            <img v-if="m.profile?.avatar_url" :src="m.profile.avatar_url" class="mc-img" />
            <div v-else class="mc-circle" :style="{ background: m.profile?.avatar_color || '#7c3aed' }">
              {{ m.profile?.gamertag?.[0]?.toUpperCase() }}
            </div>
            <div class="mc-online" />
          </div>

          <div class="mc-info">
            <div class="mc-name">{{ m.profile?.gamertag }}</div>
            <div class="mc-meta">
              <span v-if="m.profile?.platform">{{ m.profile.platform }}</span>
              <span v-if="m.profile?.region"> · {{ m.profile.region }}</span>
              <span v-if="m.profile?.playstyle"> · {{ m.profile.playstyle }}</span>
            </div>
            <div v-if="m.profile?.games?.length" class="mc-chips">
              <span v-for="(g, i) in m.profile.games.slice(0, 3)" :key="i" class="mc-chip">{{ g }}</span>
              <span v-if="m.profile.games.length > 3" class="mc-more">+{{ m.profile.games.length - 3 }}</span>
            </div>
          </div>

          <div class="mc-arrow">›</div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.matches-page  {
  padding: 40px 24px; width: 100%;
  background: radial-gradient(ellipse at 50% 0%, rgba(124,58,237,.08) 0%, transparent 55%);
  min-height: 80vh;
}
.matches-inner { max-width: 720px; margin: 0 auto; }

.page-header {
  display: flex; align-items: center; gap: 12px; margin-bottom: 28px;
}
.page-title  { font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0; }
.match-count {
  background: #7c3aed; color: #fff;
  font-size: 12px; font-weight: 700;
  padding: 2px 9px; border-radius: 20px;
}

.loading-wrap { text-align: center; padding: 60px 0; }

.empty-state  { text-align: center; padding: 64px 24px; }
.empty-icon   { font-size: 56px; margin-bottom: 16px; }
.empty-title  { font-size: 1.15rem; font-weight: 700; color: #e5e7eb; margin-bottom: 8px; }
.empty-sub    { color: #6b7280; font-size: .875rem; margin-bottom: 24px; }
.cta-btn {
  display: inline-block; background: #7c3aed; color: #fff;
  padding: 11px 28px; border-radius: 10px; font-weight: 700;
  text-decoration: none; transition: background .2s;
}
.cta-btn:hover { background: #6d28d9; }

.match-list   { display: flex; flex-direction: column; gap: 10px; }

.match-card {
  display: flex; align-items: center; gap: 16px;
  background: #13131f; border: 1px solid rgba(124,58,237,.12);
  border-radius: 16px; padding: 16px 20px;
  text-decoration: none; color: inherit;
  transition: border-color .25s, transform .15s, box-shadow .25s, background .25s;
  cursor: pointer;
}
.match-card:hover {
  border-color: rgba(124,58,237,.45);
  transform: translateY(-2px);
  background: #16162a;
  box-shadow: 0 8px 32px rgba(0,0,0,.4), 0 0 0 1px rgba(124,58,237,.1);
}

.mc-avatar { position: relative; flex-shrink: 0; }
.mc-img, .mc-circle {
  width: 58px; height: 58px; border-radius: 50%;
  object-fit: cover; border: 2px solid rgba(255,255,255,.1);
}
.mc-circle {
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; font-weight: 800; color: #fff;
}
.mc-online {
  position: absolute; bottom: 2px; right: 2px;
  width: 12px; height: 12px; border-radius: 50%;
  background: #4ade80; border: 2px solid #1a1a2e;
}

.mc-info  { flex: 1; min-width: 0; }
.mc-name  { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 3px; }
.mc-meta  { color: #6b7280; font-size: .8rem; margin-bottom: 6px; }
.mc-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.mc-chip  {
  background: rgba(124,58,237,.2); color: #a78bfa;
  padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.mc-more  { color: #6b7280; font-size: 11px; align-self: center; }

.mc-arrow {
  color: #4b5563; font-size: 24px; font-weight: 300;
  flex-shrink: 0; transition: color .2s, transform .2s;
}
.match-card:hover .mc-arrow { color: #a78bfa; transform: translateX(3px); }

@media (max-width: 480px) {
  .matches-page { padding: 24px 16px; }
  .mc-img, .mc-circle { width: 50px; height: 50px; }
}
</style>
