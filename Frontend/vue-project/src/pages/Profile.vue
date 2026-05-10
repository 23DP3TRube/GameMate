<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api'
import { auth } from '../auth'

const form = ref({
  gamertag: '', bio: '', platform: 'PC', region: 'EU',
  playstyle: 'Casual', games: [], avatar_color: '#7c3aed',
  gaming_accounts: [],
})

const saving     = ref(false)
const saved      = ref(false)
const error      = ref('')
const gameSearch = ref('')
const gameSuggestions = ref([])
const avatarPreview   = ref(null)
const avatarFile      = ref(null)
const uploadingAvatar = ref(false)
const fileInput       = ref(null)

const platforms  = ['PC', 'PS5', 'PS4', 'Xbox Series X/S', 'Xbox One', 'Switch', 'Mobile']
const regions    = ['EU', 'NA', 'SA', 'Asia', 'Oceania', 'Middle East', 'Africa']
const styles     = ['Casual', 'Competitive', 'Hardcore', 'Semi-competitive']

const GAMING_PLATFORMS = [
  { key: 'steam',      label: 'Steam',            icon: '🎮', rankOptions: null },
  { key: 'valorant',   label: 'Valorant',          icon: '🔫', rankOptions: ['Iron','Bronze','Silver','Gold','Platinum','Diamond','Ascendant','Immortal','Radiant'] },
  { key: 'lol',        label: 'League of Legends', icon: '⚔️', rankOptions: ['Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'] },
  { key: 'cs2',        label: 'CS2',               icon: '💣', rankOptions: ['Silver I','Silver II','Silver III','Silver IV','Silver Elite','Silver Elite Master','Gold Nova I','Gold Nova II','Gold Nova III','Gold Nova Master','Master Guardian I','Master Guardian II','Master Guardian Elite','Distinguished Master Guardian','Legendary Eagle','Legendary Eagle Master','Supreme Master First Class','Global Elite'] },
  { key: 'apex',       label: 'Apex Legends',      icon: '🦅', rankOptions: ['Bronze','Silver','Gold','Platinum','Diamond','Master','Predator'] },
  { key: 'overwatch2', label: 'Overwatch 2',        icon: '🌟', rankOptions: ['Bronze','Silver','Gold','Platinum','Diamond','Master','Grandmaster','Top 500'] },
  { key: 'psn',        label: 'PlayStation Network',icon: '🎯', rankOptions: null },
  { key: 'xbox',       label: 'Xbox Gamertag',      icon: '🟩', rankOptions: null },
  { key: 'epic',       label: 'Epic Games',         icon: '🔷', rankOptions: null },
  { key: 'battlenet',  label: 'Battle.net',         icon: '🔵', rankOptions: null },
  { key: 'riotid',     label: 'Riot ID',            icon: '⚡', rankOptions: null },
]

const platformMap = computed(() => Object.fromEntries(GAMING_PLATFORMS.map(p => [p.key, p])))

onMounted(async () => {
  const p = auth.user?.profile
  if (p) {
    form.value = {
      ...form.value, ...p,
      games: p.games || [],
      gaming_accounts: p.gaming_accounts || [],
    }
    avatarPreview.value = p.avatar_url || null
  }
})

const searchGames = async () => {
  if (!gameSearch.value.trim()) { gameSuggestions.value = []; return }
  const { data } = await api.get('/games', { params: { q: gameSearch.value } })
  gameSuggestions.value = data.filter(g => !form.value.games.includes(g))
}

const addGame = (game) => {
  if (!form.value.games.includes(game)) form.value.games.push(game)
  gameSearch.value = ''
  gameSuggestions.value = []
}

const addCustomGame = () => {
  const g = gameSearch.value.trim()
  if (g && !form.value.games.includes(g)) { form.value.games.push(g) }
  gameSearch.value = ''
  gameSuggestions.value = []
}

const removeGame = (i) => form.value.games.splice(i, 1)

const addGamingAccount = () => {
  form.value.gaming_accounts.push({ platform: 'steam', username: '', rank: '', server: '' })
}
const removeGamingAccount = (i) => form.value.gaming_accounts.splice(i, 1)

const getRankOptions = (platform) => platformMap.value[platform]?.rankOptions || null

const TRACKER_PLATFORMS = ['valorant', 'riotid', 'apex', 'cs2', 'overwatch2', 'lol']
const fetchingRank = ref({})

const fetchRank = async (acc, i) => {
  if (!acc.username.trim()) return
  fetchingRank.value[i] = true
  try {
    const { data } = await api.get('/tracker/rank', {
      params: { platform: acc.platform, username: acc.username.trim() }
    })
    if (data.rank) {
      acc.rank = data.rank
    } else {
      acc.rank = ''
      alert('Rangs netika atrasts šim kontam Tracker.gg.')
    }
  } catch (e) {
    const msg = e.response?.data?.error || 'Neizdevās iegūt rangu.'
    alert(msg)
  } finally {
    fetchingRank.value[i] = false
  }
}

const pickAvatar = () => fileInput.value?.click()

const onAvatarChange = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  avatarFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
}

const uploadAvatar = async () => {
  if (!avatarFile.value) return
  uploadingAvatar.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', avatarFile.value)
    const { data } = await api.post('/profile/avatar', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    if (auth.user?.profile) auth.user.profile.avatar_url = data.avatar_url
    avatarFile.value = null
  } finally {
    uploadingAvatar.value = false
  }
}

const save = async () => {
  saving.value = true; saved.value = false; error.value = ''
  try {
    if (avatarFile.value) await uploadAvatar()
    const payload = { ...form.value }
    payload.gaming_accounts = payload.gaming_accounts.filter(a => a.username.trim())
    const { data } = await api.put('/profile', payload)
    if (auth.user) auth.user.profile = { ...auth.user.profile, ...data }
    saved.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Saglabāšana neizdevās. Lūdzu, mēģini vēlreiz.'
  } finally {
    saving.value = false }
}
</script>

<template>
  <v-container class="py-8 pa-4" style="max-width: 860px; margin: 0 auto;">
    <h2 class="mb-6 text-h5 font-weight-bold">Mans profils</h2>

    <v-alert v-if="saved" type="success" class="mb-4" closable>Profils saglabāts!</v-alert>
    <v-alert v-if="error" type="error" class="mb-4" closable>{{ error }}</v-alert>

    <!-- Avatar -->
    <v-card color="grey-darken-4" class="pa-5 mb-4">
      <div class="text-subtitle-1 font-weight-bold mb-3">Profila attēls</div>
      <div class="avatar-section">
        <div class="avatar-wrap" @click="pickAvatar" style="cursor:pointer;">
          <img v-if="avatarPreview" :src="avatarPreview" class="avatar-img" />
          <div v-else class="avatar-placeholder" :style="{ background: form.avatar_color }">
            {{ form.gamertag?.[0]?.toUpperCase() || '?' }}
          </div>
          <div class="avatar-overlay">Mainīt</div>
        </div>
        <div class="avatar-meta">
          <v-btn size="small" variant="outlined" @click="pickAvatar">Augšupielādēt foto</v-btn>
          <div class="text-caption text-grey mt-1">Maks. 4 MB · JPG / PNG / WebP</div>
          <div v-if="avatarFile" class="text-caption text-green mt-1">{{ avatarFile.name }} (tiks saglabāts ar profilu)</div>
          <v-text-field
            v-model="form.avatar_color"
            label="Avatāra krāsa (hex rezerves)"
            density="compact"
            hide-details
            class="mt-3"
            style="max-width:200px"
          />
        </div>
      </div>
      <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="onAvatarChange" />
    </v-card>

    <!-- Basic info -->
    <v-card color="grey-darken-4" class="pa-5 mb-4">
      <div class="text-subtitle-1 font-weight-bold mb-3">Pamatinformācija</div>
      <v-row>
        <v-col cols="12" sm="6">
          <v-text-field v-model="form.gamertag" label="Gamertag *" />
        </v-col>
        <v-col cols="12" sm="6">
          <v-select v-model="form.platform" :items="platforms" label="Galvenā platforma" />
        </v-col>
        <v-col cols="12" sm="6">
          <v-select v-model="form.region" :items="regions" label="Reģions" />
        </v-col>
        <v-col cols="12" sm="6">
          <v-select v-model="form.playstyle" :items="styles" label="Spēles stils" />
        </v-col>
        <v-col cols="12">
          <v-textarea v-model="form.bio" label="Par mani" rows="3" counter="1000" />
        </v-col>
      </v-row>
    </v-card>

    <!-- Games -->
    <v-card color="grey-darken-4" class="pa-5 mb-4">
      <div class="text-subtitle-1 font-weight-bold mb-3">Spēles, ko spēlēju</div>
      <div class="d-flex flex-wrap mb-3">
        <v-chip
          v-for="(g, i) in form.games" :key="i"
          closable @click:close="removeGame(i)"
          class="ma-1" color="primary" variant="tonal"
        >{{ g }}</v-chip>
        <span v-if="!form.games.length" class="text-grey text-caption mt-1">Vēl nav pievienotu spēļu</span>
      </div>
      <div class="position-relative">
        <v-text-field
          v-model="gameSearch"
          label="Meklēt vai pievienot spēli"
          placeholder="piem. Valorant"
          @input="searchGames"
          @keyup.enter="addCustomGame"
          hide-details
        >
          <template #append-inner>
            <v-btn size="small" variant="text" @click="addCustomGame">Pievienot</v-btn>
          </template>
        </v-text-field>
        <v-list v-if="gameSuggestions.length" class="suggestion-list elevation-4">
          <v-list-item
            v-for="g in gameSuggestions" :key="g"
            @click="addGame(g)"
            :title="g"
            density="compact"
          />
        </v-list>
      </div>
    </v-card>

    <!-- Gaming Accounts -->
    <v-card color="grey-darken-4" class="pa-5 mb-4">
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="text-subtitle-1 font-weight-bold">Spēļu konti un rangi</div>
        <v-btn size="small" color="primary" variant="tonal" @click="addGamingAccount">+ Pievienot kontu</v-btn>
      </div>
      <div v-if="!form.gaming_accounts.length" class="text-grey text-caption mb-2">
        Pievieno Steam, Valorant, LoL un citus kontus, lai potenciālie komandas biedri varētu redzēt tavu rangu.
      </div>
      <div v-for="(acc, i) in form.gaming_accounts" :key="i" class="account-row mb-3">
        <v-row dense align="center">
          <v-col cols="12" sm="3">
            <v-select
              v-model="acc.platform"
              :items="GAMING_PLATFORMS.map(p => ({ title: p.icon + ' ' + p.label, value: p.key }))"
              label="Platforma"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-text-field
              v-model="acc.username"
              :label="acc.platform === 'valorant' || acc.platform === 'riotid' ? 'Lietotājvārds#TAG' : 'Lietotājvārds / ID'"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-select
              v-if="getRankOptions(acc.platform)"
              v-model="acc.rank"
              :items="getRankOptions(acc.platform)"
              label="Rangs"
              density="compact"
              hide-details
              clearable
            />
            <v-text-field
              v-else
              v-model="acc.rank"
              label="Rangs (neobligāts)"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" sm="2">
            <v-text-field
              v-if="['lol','cs2'].includes(acc.platform)"
              v-model="acc.server"
              label="Serveris"
              density="compact"
              hide-details
              placeholder="EUW, NA…"
            />
          </v-col>
          <v-col cols="auto" class="d-flex align-center gap-1">
            <v-btn
              v-if="TRACKER_PLATFORMS.includes(acc.platform) && acc.username"
              size="small" variant="tonal" color="cyan"
              :loading="fetchingRank[i]"
              @click="fetchRank(acc, i)"
              title="Iegūt rangu no Tracker.gg"
            >
              <span style="font-size:13px">📡</span>
            </v-btn>
            <v-btn icon size="small" color="red" variant="text" @click="removeGamingAccount(i)">✕</v-btn>
          </v-col>
        </v-row>
      </div>
    </v-card>

    <v-btn color="primary" :loading="saving" @click="save" block size="large">Saglabāt profilu</v-btn>
  </v-container>
</template>

<style scoped>
.avatar-section { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.avatar-meta    { flex: 1; min-width: 160px; }
.avatar-wrap { position: relative; width: 90px; height: 90px; border-radius: 50%; overflow: hidden; flex-shrink: 0; }
.avatar-img  { width: 90px; height: 90px; object-fit: cover; border-radius: 50%; }
.avatar-placeholder {
  width: 90px; height: 90px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 36px; font-weight: 700; color: #fff;
}
.avatar-overlay {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: rgba(0,0,0,.55); color: #fff;
  font-size: 11px; text-align: center; padding: 3px 0;
  opacity: 0; transition: opacity .2s;
}
.avatar-wrap:hover .avatar-overlay { opacity: 1; }
.suggestion-list {
  position: absolute; top: 100%; left: 0; right: 0; z-index: 10;
  max-height: 220px; overflow-y: auto;
  background: #2a2a2a; border-radius: 6px;
}
.account-row { background: rgba(255,255,255,.04); border-radius: 8px; padding: 8px; }
@media (max-width: 600px) {
  .avatar-section { gap: 16px; }
}
</style>
