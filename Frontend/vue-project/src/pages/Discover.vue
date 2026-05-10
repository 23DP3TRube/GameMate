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
    <v-card v-else-if="current" color="grey-darken-4" class="pa-5">
      <!-- Avatar + identity -->
      <div class="d-flex align-center mb-3">
        <div class="avatar-wrap mr-4">
          <img v-if="current.avatar_url" :src="current.avatar_url" class="avatar-img" />
          <div v-else class="avatar-circle" :style="{ background: current.avatar_color || '#7c3aed' }">
            {{ current.gamertag?.[0]?.toUpperCase() }}
          </div>
        </div>
        <div>
          <div class="text-h6 font-weight-bold">{{ current.gamertag }}</div>
          <div class="text-grey-lighten-1 text-caption">
            {{ current.user?.name }}
            <span v-if="current.platform"> · {{ current.platform }}</span>
            <span v-if="current.region"> · {{ current.region }}</span>
          </div>
          <v-chip v-if="current.playstyle" size="x-small" class="mt-1" color="purple" variant="tonal">
            {{ current.playstyle }}
          </v-chip>
        </div>
      </div>

      <!-- Bio -->
      <p v-if="current.bio" class="mb-3 text-body-2">{{ current.bio }}</p>

      <!-- Games -->
      <div v-if="current.games?.length" class="mb-3">
        <div class="text-caption text-grey mb-1">SPĒLĒ</div>
        <div class="d-flex flex-wrap">
          <v-chip v-for="(g, i) in current.games" :key="i" class="ma-1" color="primary" variant="tonal" size="small">{{ g }}</v-chip>
        </div>
      </div>

      <!-- Gaming accounts -->
      <div v-if="current.gaming_accounts?.length" class="mb-3">
        <div class="text-caption text-grey mb-1">KONTI</div>
        <div class="d-flex flex-wrap gap-2">
          <div
            v-for="(acc, i) in current.gaming_accounts" :key="i"
            class="account-badge"
          >
            <span class="acc-icon">{{ PLATFORM_ICONS[acc.platform] || '🎮' }}</span>
            <span class="acc-label">{{ PLATFORM_LABELS[acc.platform] || acc.platform }}</span>
            <span class="acc-name">{{ acc.username }}</span>
            <v-chip v-if="acc.rank" size="x-small" color="amber" variant="tonal" class="ml-1">{{ acc.rank }}</v-chip>
            <span v-if="acc.server" class="text-grey text-caption ml-1">{{ acc.server }}</span>
          </div>
        </div>
      </div>

      <!-- Swipe buttons -->
      <div class="swipe-btns mt-5">
        <v-btn size="x-large" color="red" variant="tonal" rounded @click="swipe(false)" class="swipe-btn">
          <span style="font-size:22px">✕</span>
          <span class="ml-2">Izlaist</span>
        </v-btn>
        <v-btn size="x-large" color="green" variant="tonal" rounded @click="swipe(true)" class="swipe-btn">
          <span style="font-size:22px">♥</span>
          <span class="ml-2">Patīk</span>
        </v-btn>
      </div>

      <!-- Progress -->
      <div class="text-center text-caption text-grey mt-3">
        {{ idx + 1 }} / {{ profiles.length }} spēlētāji
      </div>
    </v-card>

    <!-- Empty state -->
    <v-card v-else color="grey-darken-4" class="pa-6 text-center">
      <v-icon size="48" class="mb-3 text-grey">mdi-account-search</v-icon>
      <p class="mb-1">Vairāk spēlētāju nav.</p>
      <p class="text-caption text-grey mb-4">Mēģini mainīt filtrus vai pārbaudi vēlāk.</p>
      <v-btn @click="load">Ielādēt vēlreiz</v-btn>
    </v-card>

  </v-container>
</template>

<style scoped>
.avatar-wrap  { flex-shrink: 0; }
.avatar-img   { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; }
.avatar-circle {
  width: 72px; height: 72px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 30px; font-weight: 700; color: #fff;
}
.account-badge {
  display: inline-flex; align-items: center; gap: 4px;
  background: rgba(255,255,255,.06); border-radius: 20px;
  padding: 3px 10px; font-size: 12px; margin: 2px;
}
.acc-icon  { font-size: 14px; }
.acc-label { color: #aaa; }
.acc-name  { font-weight: 600; }
.swipe-btns { display: flex; justify-content: space-around; gap: 12px; }
.swipe-btn  { flex: 1; max-width: 180px; }
@media (max-width: 480px) {
  .swipe-btn { max-width: 50%; font-size: 0.9rem; }
}
</style>
