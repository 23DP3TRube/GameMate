<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api'

const profiles   = ref([])
const idx        = ref(0)
const loading    = ref(true)
const matchMsg   = ref('')
const showFilters = ref(false)

const filters = ref({ platform: '', region: '', playstyle: '', game: '' })
const sortBy  = ref('gamertag')
const sortDir = ref('asc')

const platforms  = ['', 'PC', 'PS5', 'PS4', 'Xbox Series X/S', 'Xbox One', 'Switch', 'Mobile']
const regions    = ['', 'EU', 'NA', 'SA', 'Asia', 'Oceania', 'Middle East', 'Africa']
const styles     = ['', 'Casual', 'Competitive', 'Hardcore', 'Semi-competitive']

const PLATFORM_ICONS = {
  steam: '🎮', valorant: '🔫', lol: '⚔️', cs2: '💣',
  apex: '🦅', overwatch2: '🌟', psn: '🎯', xbox: '🟩',
  epic: '🔷', battlenet: '🔵', riotid: '⚡',
}
const PLATFORM_LABELS = {
  steam: 'Steam', valorant: 'Valorant', lol: 'LoL', cs2: 'CS2',
  apex: 'Apex', overwatch2: 'OW2', psn: 'PSN', xbox: 'Xbox',
  epic: 'Epic', battlenet: 'Battle.net', riotid: 'Riot ID',
}

const current = computed(() => profiles.value[idx.value])
const activeFilterCount = computed(() =>
  Object.values(filters.value).filter(v => v).length
)

const load = async () => {
  loading.value = true
  const params = { sort_by: sortBy.value, sort_dir: sortDir.value }
  if (filters.value.platform)  params.platform  = filters.value.platform
  if (filters.value.region)    params.region    = filters.value.region
  if (filters.value.playstyle) params.playstyle = filters.value.playstyle
  if (filters.value.game)      params.game      = filters.value.game
  const { data } = await api.get('/discover', { params })
  profiles.value = data
  idx.value = 0
  loading.value = false
}

const applyFilters = () => { showFilters.value = false; load() }
const resetFilters = () => { filters.value = { platform: '', region: '', playstyle: '', game: '' }; load() }

const swipe = async (liked) => {
  if (!current.value) return
  const target = current.value.user_id
  const { data } = await api.post('/swipe', { target_id: target, liked })
  if (data.matched) {
    matchMsg.value = `🎉 Sakritība ar ${current.value.gamertag}!`
    setTimeout(() => (matchMsg.value = ''), 3500)
  }
  idx.value++
}

onMounted(load)
</script>

<template>
  <v-container class="py-8 pa-4" style="max-width: 760px; margin: 0 auto;">

    <!-- Header row -->
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
      <h2 class="text-h5 font-weight-bold">Atklāt spēlētājus</h2>
      <div class="d-flex gap-2 align-center flex-wrap">
        <v-select
          v-model="sortBy"
          :items="[
            { title: 'Gamertag', value: 'gamertag' },
            { title: 'Platforma', value: 'platform' },
            { title: 'Reģions', value: 'region' },
          ]"
          density="compact"
          hide-details
          style="min-width:130px"
          @update:model-value="load"
        />
        <v-btn
          icon density="compact" variant="tonal"
          :title="sortDir === 'asc' ? 'Augošā secībā' : 'Dilstošā secībā'"
          @click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'; load()"
        >
          <v-icon>{{ sortDir === 'asc' ? 'mdi-sort-ascending' : 'mdi-sort-descending' }}</v-icon>
        </v-btn>
        <v-btn
          variant="tonal"
          :color="activeFilterCount ? 'primary' : 'grey'"
          @click="showFilters = !showFilters"
          prepend-icon="mdi-filter-variant"
        >
          Filtri
          <v-badge v-if="activeFilterCount" :content="activeFilterCount" color="red" floating />
        </v-btn>
      </div>
    </div>

    <!-- Filter panel -->
    <v-expand-transition>
      <v-card v-if="showFilters" color="grey-darken-4" class="pa-4 mb-4">
        <v-row dense>
          <v-col cols="12" sm="6">
            <v-select v-model="filters.platform" :items="platforms" label="Platforma" density="compact" clearable hide-details />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select v-model="filters.region" :items="regions" label="Reģions" density="compact" clearable hide-details />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select v-model="filters.playstyle" :items="styles" label="Spēles stils" density="compact" clearable hide-details />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field v-model="filters.game" label="Spēle" density="compact" hide-details placeholder="piem. Valorant" />
          </v-col>
        </v-row>
        <div class="d-flex gap-2 mt-3">
          <v-btn color="primary" @click="applyFilters">Lietot</v-btn>
          <v-btn variant="text" @click="resetFilters">Atiestatīt</v-btn>
        </div>
      </v-card>
    </v-expand-transition>

    <v-alert v-if="matchMsg" type="success" class="mb-3">{{ matchMsg }}</v-alert>

    <div v-if="loading" class="text-center py-10"><v-progress-circular indeterminate /></div>

    <!-- Profile card -->
    <div v-else-if="current" class="player-card">
      <!-- Card header with avatar -->
      <div class="card-hero" :style="{ background: `linear-gradient(135deg, ${current.avatar_color || '#7c3aed'}33 0%, #1a1a2e 100%)` }">
        <div class="hero-avatar">
          <img v-if="current.avatar_url" :src="current.avatar_url" class="hero-img" />
          <div v-else class="hero-circle" :style="{ background: current.avatar_color || '#7c3aed' }">
            {{ current.gamertag?.[0]?.toUpperCase() }}
          </div>
        </div>
        <div class="hero-identity">
          <div class="hero-name">{{ current.gamertag }}</div>
          <div class="hero-meta">
            <span v-if="current.platform" class="meta-chip">{{ current.platform }}</span>
            <span v-if="current.region"   class="meta-chip">{{ current.region }}</span>
            <span v-if="current.playstyle" class="meta-chip purple">{{ current.playstyle }}</span>
          </div>
        </div>
      </div>

      <!-- Card body -->
      <div class="card-body">
        <p v-if="current.bio" class="bio-text">{{ current.bio }}</p>

        <div v-if="current.games?.length" class="section">
          <div class="section-label">SPĒLĒ</div>
          <div class="chips-row">
            <span v-for="(g, i) in current.games" :key="i" class="game-chip">{{ g }}</span>
          </div>
        </div>

        <div v-if="current.gaming_accounts?.length" class="section">
          <div class="section-label">KONTI</div>
          <div class="accounts-row">
            <div v-for="(acc, i) in current.gaming_accounts" :key="i" class="account-badge">
              <span class="acc-icon">{{ PLATFORM_ICONS[acc.platform] || '🎮' }}</span>
              <span class="acc-label">{{ PLATFORM_LABELS[acc.platform] || acc.platform }}</span>
              <span class="acc-name">{{ acc.username }}</span>
              <span v-if="acc.rank" class="rank-badge">{{ acc.rank }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Swipe buttons -->
      <div class="swipe-row">
        <button class="swipe-btn pass" @click="swipe(false)">
          <span class="swipe-icon">✕</span>
          <span class="swipe-label">Izlaist</span>
        </button>
        <div class="progress-dot">{{ idx + 1 }}/{{ profiles.length }}</div>
        <button class="swipe-btn like" @click="swipe(true)">
          <span class="swipe-icon">♥</span>
          <span class="swipe-label">Patīk</span>
        </button>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="empty-icon">🔍</div>
      <p class="empty-title">Vairāk spēlētāju nav</p>
      <p class="empty-sub">Mēģini mainīt filtrus vai pārbaudi vēlāk.</p>
      <button class="reload-btn" @click="load">Ielādēt vēlreiz</button>
    </div>

  </v-container>
</template>

<style scoped>
/* ── Player card ── */
.player-card {
  background: #1a1a2e;
  border: 1px solid rgba(124,58,237,.2);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 40px rgba(0,0,0,.5), 0 0 0 1px rgba(124,58,237,.1);
}

/* Hero */
.card-hero {
  padding: 32px 24px 24px;
  display: flex; align-items: center; gap: 20px;
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.hero-avatar { flex-shrink: 0; }
.hero-img, .hero-circle {
  width: 80px; height: 80px; border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 4px 20px rgba(0,0,0,.4);
  border: 3px solid rgba(255,255,255,.12);
}
.hero-circle {
  display: flex; align-items: center; justify-content: center;
  font-size: 34px; font-weight: 800; color: #fff;
}
.hero-identity { min-width: 0; }
.hero-name {
  font-size: 1.35rem; font-weight: 800; color: #fff;
  margin-bottom: 8px; line-height: 1.2;
}
.hero-meta { display: flex; flex-wrap: wrap; gap: 6px; }
.meta-chip {
  background: rgba(255,255,255,.08); color: #ccc;
  padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.meta-chip.purple { background: rgba(124,58,237,.25); color: #a78bfa; }

/* Body */
.card-body { padding: 20px 24px; }
.bio-text { color: #9ca3af; font-size: .9rem; line-height: 1.6; margin-bottom: 16px; }
.section { margin-bottom: 16px; }
.section-label {
  font-size: 10px; letter-spacing: 2px; font-weight: 700;
  color: #6b7280; margin-bottom: 8px;
}
.chips-row { display: flex; flex-wrap: wrap; gap: 6px; }
.game-chip {
  background: rgba(124,58,237,.2); color: #a78bfa;
  padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
}
.accounts-row { display: flex; flex-wrap: wrap; gap: 6px; }
.account-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
  border-radius: 20px; padding: 4px 12px; font-size: 12px;
}
.acc-icon  { font-size: 13px; }
.acc-label { color: #6b7280; font-size: 11px; }
.acc-name  { font-weight: 600; color: #e5e7eb; }
.rank-badge {
  background: rgba(251,191,36,.15); color: #fbbf24;
  border-radius: 10px; padding: 1px 7px; font-size: 10px; font-weight: 700;
}

/* Swipe row */
.swipe-row {
  display: flex; align-items: center; justify-content: center;
  gap: 24px; padding: 20px 24px;
  border-top: 1px solid rgba(255,255,255,.06);
  background: rgba(0,0,0,.2);
}
.swipe-btn {
  display: flex; flex-direction: column; align-items: center; gap: 4px;
  width: 72px; height: 72px; border-radius: 50%; border: none; cursor: pointer;
  font-weight: 700; transition: transform .15s, box-shadow .15s;
}
.swipe-btn:hover { transform: scale(1.08); }
.swipe-btn:active { transform: scale(.95); }
.swipe-btn.pass {
  background: rgba(239,68,68,.15); color: #f87171;
  box-shadow: 0 0 0 2px rgba(239,68,68,.2);
}
.swipe-btn.pass:hover { box-shadow: 0 0 0 2px rgba(239,68,68,.5), 0 4px 20px rgba(239,68,68,.2); }
.swipe-btn.like {
  background: rgba(34,197,94,.15); color: #4ade80;
  box-shadow: 0 0 0 2px rgba(34,197,94,.2);
}
.swipe-btn.like:hover { box-shadow: 0 0 0 2px rgba(34,197,94,.5), 0 4px 20px rgba(34,197,94,.2); }
.swipe-icon  { font-size: 22px; line-height: 1; }
.swipe-label { font-size: 10px; letter-spacing: .5px; }
.progress-dot {
  color: #6b7280; font-size: 12px; font-weight: 600;
  background: rgba(255,255,255,.05); padding: 6px 12px;
  border-radius: 20px;
}

/* Empty state */
.empty-state {
  text-align: center; padding: 64px 24px;
  background: #1a1a2e; border: 1px solid rgba(255,255,255,.06);
  border-radius: 20px;
}
.empty-icon  { font-size: 56px; margin-bottom: 16px; }
.empty-title { font-size: 1.1rem; font-weight: 700; color: #e5e7eb; margin-bottom: 8px; }
.empty-sub   { color: #6b7280; font-size: .875rem; margin-bottom: 24px; }
.reload-btn  {
  background: rgba(124,58,237,.2); color: #a78bfa; border: 1px solid rgba(124,58,237,.3);
  padding: 10px 24px; border-radius: 10px; cursor: pointer; font-weight: 600;
  transition: background .2s;
}
.reload-btn:hover { background: rgba(124,58,237,.35); }

@media (max-width: 480px) {
  .card-hero { padding: 24px 16px 20px; }
  .card-body { padding: 16px; }
  .swipe-row { padding: 16px; gap: 16px; }
  .swipe-btn { width: 64px; height: 64px; }
}
</style>
