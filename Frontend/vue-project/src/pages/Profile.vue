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

const deleteDialog  = ref(false)
const deleteConfirm = ref('')
const deleting      = ref(false)

const openDeleteDialog = () => {
  deleteConfirm.value = ''
  deleteDialog.value  = true
}

const deleteAccount = async () => {
  if (deleteConfirm.value !== 'DZĒST') return
  deleting.value = true
  try {
    await api.delete('/account')
    auth.token = null
    auth.user  = null
    localStorage.removeItem('gm_token')
    window.location.href = '/'
  } catch {
    error.value = 'Konta dzēšana neizdevās. Mēģini vēlreiz.'
    deleteDialog.value = false
  } finally {
    deleting.value = false
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
    saving.value = false
  }
}
</script>

<template>
  <div class="profile-page">

    <!-- Page header -->
    <div class="page-head">
      <div class="ph-avatar-wrap">
        <img v-if="avatarPreview" :src="avatarPreview" class="ph-avatar" />
        <div v-else class="ph-avatar-circle" :style="{ background: form.avatar_color || '#7c3aed' }">
          {{ form.gamertag?.[0]?.toUpperCase() || '?' }}
        </div>
      </div>
      <div>
        <h1 class="ph-title">Mans profils</h1>
        <p class="ph-sub">{{ form.gamertag || 'Iestatī savu GameMate profilu' }}</p>
      </div>
    </div>

    <div v-if="saved" class="c-alert c-success">✓ Profils veiksmīgi saglabāts!</div>
    <div v-if="error" class="c-alert c-error">⚠ {{ error }}</div>

    <!-- Avatar section -->
    <div class="sc">
      <div class="sc-head">Profila attēls</div>
      <div class="avatar-section">
        <div class="avatar-wrap" @click="pickAvatar">
          <img v-if="avatarPreview" :src="avatarPreview" class="avatar-img" />
          <div v-else class="avatar-placeholder" :style="{ background: form.avatar_color || '#7c3aed' }">
            {{ form.gamertag?.[0]?.toUpperCase() || '?' }}
          </div>
          <div class="avatar-overlay">Mainīt</div>
        </div>
        <div class="avatar-meta">
          <button class="ghost-btn" @click="pickAvatar">Augšupielādēt foto</button>
          <p class="hint">Maks. 4 MB · JPG / PNG / WebP</p>
          <p v-if="avatarFile" class="hint green">{{ avatarFile.name }} (tiks saglabāts)</p>
          <div class="color-row">
            <span class="hint">Krāsa:</span>
            <input type="color" v-model="form.avatar_color" class="color-pick" />
            <code class="color-hex">{{ form.avatar_color }}</code>
          </div>
        </div>
      </div>
      <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="onAvatarChange" />
    </div>

    <!-- Basic info -->
    <div class="sc">
      <div class="sc-head">Pamatinformācija</div>
      <v-row>
        <v-col cols="12" sm="6">
          <v-text-field v-model="form.gamertag" label="Gamertag *" variant="outlined" density="comfortable" color="deep-purple-lighten-2" />
        </v-col>
        <v-col cols="12" sm="6">
          <v-select v-model="form.platform" :items="platforms" label="Galvenā platforma" variant="outlined" density="comfortable" color="deep-purple-lighten-2" />
        </v-col>
        <v-col cols="12" sm="6">
          <v-select v-model="form.region" :items="regions" label="Reģions" variant="outlined" density="comfortable" color="deep-purple-lighten-2" />
        </v-col>
        <v-col cols="12" sm="6">
          <v-select v-model="form.playstyle" :items="styles" label="Spēles stils" variant="outlined" density="comfortable" color="deep-purple-lighten-2" />
        </v-col>
        <v-col cols="12">
          <v-textarea v-model="form.bio" label="Par mani" rows="3" counter="1000" variant="outlined" color="deep-purple-lighten-2" />
        </v-col>
      </v-row>
    </div>

    <!-- Games -->
    <div class="sc">
      <div class="sc-head">Spēles, ko spēlēju</div>
      <div class="game-chips">
        <span v-for="(g, i) in form.games" :key="i" class="g-chip">
          {{ g }}<button class="g-remove" @click="removeGame(i)">×</button>
        </span>
        <span v-if="!form.games.length" class="hint">Vēl nav pievienotu spēļu</span>
      </div>
      <div class="position-relative mt-3">
        <v-text-field
          v-model="gameSearch"
          label="Meklēt vai pievienot spēli"
          placeholder="piem. Valorant"
          variant="outlined"
          density="comfortable"
          hide-details
          color="deep-purple-lighten-2"
          @input="searchGames"
          @keyup.enter="addCustomGame"
        >
          <template #append-inner>
            <button class="add-inline-btn" @click="addCustomGame">+ Pievienot</button>
          </template>
        </v-text-field>
        <v-list v-if="gameSuggestions.length" class="suggestion-list elevation-4">
          <v-list-item v-for="g in gameSuggestions" :key="g" @click="addGame(g)" :title="g" density="compact" />
        </v-list>
      </div>
    </div>

    <!-- Gaming Accounts -->
    <div class="sc">
      <div class="sc-head">
        Spēļu konti un rangi
        <button class="ghost-btn ghost-sm ml-auto" @click="addGamingAccount">+ Pievienot kontu</button>
      </div>
      <p v-if="!form.gaming_accounts.length" class="hint mb-2">
        Pievieno Steam, Valorant, LoL un citus kontus, lai komandas biedri varētu redzēt tavu rangu.
      </p>
      <div v-for="(acc, i) in form.gaming_accounts" :key="i" class="acc-row">
        <v-row dense align="center">
          <v-col cols="12" sm="3">
            <v-select v-model="acc.platform" :items="GAMING_PLATFORMS.map(p => ({ title: p.icon + ' ' + p.label, value: p.key }))" label="Platforma" density="compact" hide-details variant="outlined" color="deep-purple-lighten-2" />
          </v-col>
          <v-col cols="12" sm="3">
            <v-text-field v-model="acc.username" :label="acc.platform === 'valorant' || acc.platform === 'riotid' ? 'Lietotājvārds#TAG' : 'Lietotājvārds / ID'" density="compact" hide-details variant="outlined" color="deep-purple-lighten-2" />
          </v-col>
          <v-col cols="12" sm="3">
            <v-select v-if="getRankOptions(acc.platform)" v-model="acc.rank" :items="getRankOptions(acc.platform)" label="Rangs" density="compact" hide-details clearable variant="outlined" color="deep-purple-lighten-2" />
            <v-text-field v-else v-model="acc.rank" label="Rangs (neobligāts)" density="compact" hide-details variant="outlined" color="deep-purple-lighten-2" />
          </v-col>
          <v-col cols="12" sm="2">
            <v-text-field v-if="['lol','cs2'].includes(acc.platform)" v-model="acc.server" label="Serveris" density="compact" hide-details placeholder="EUW, NA…" variant="outlined" color="deep-purple-lighten-2" />
          </v-col>
          <v-col cols="auto" class="d-flex align-center gap-1">
            <v-btn v-if="TRACKER_PLATFORMS.includes(acc.platform) && acc.username" size="small" variant="tonal" color="cyan" :loading="fetchingRank[i]" @click="fetchRank(acc, i)" title="Iegūt rangu no Tracker.gg">
              <span style="font-size:13px">📡</span>
            </v-btn>
            <v-btn icon size="small" color="red" variant="text" @click="removeGamingAccount(i)">✕</v-btn>
          </v-col>
        </v-row>
      </div>
    </div>

    <button class="save-btn" :disabled="saving" @click="save">
      {{ saving ? 'Saglabā…' : 'Saglabāt profilu' }}
    </button>

    <!-- Danger zone -->
    <div class="sc danger-zone">
      <div class="sc-head" style="color:#f87171;border-color:rgba(239,68,68,.15)">⚠ Bīstamā zona</div>
      <p class="hint" style="margin-bottom:14px;">Konta dzēšana ir neatgriezeniska. Visi tavi dati, profils un sakritības tiks neatgriezeniski dzēsti.</p>
      <button class="delete-btn" @click="openDeleteDialog">Dzēst manu kontu</button>
    </div>

    <!-- Confirmation dialog -->
    <v-dialog v-model="deleteDialog" max-width="440" persistent>
      <v-card style="background:#13131f;border:1px solid rgba(239,68,68,.3);border-radius:16px;">
        <v-card-title style="color:#f87171;padding:24px 24px 8px;font-size:1.1rem;font-weight:800;">
          ⚠ Dzēst kontu?
        </v-card-title>
        <v-card-text style="color:#9ca3af;font-size:.875rem;">
          <p style="margin-bottom:16px;">Šī darbība ir <strong style="color:#f87171">neatgriezeniska</strong>. Visi tavi dati tiks neatgriezeniski dzēsti.</p>
          <p style="margin-bottom:10px;">Lai apstiprinātu, ieraksti <strong style="color:#fff">DZĒST</strong>:</p>
          <input
            v-model="deleteConfirm"
            type="text"
            placeholder="DZĒST"
            class="confirm-input"
            @keyup.enter="deleteAccount"
          />
        </v-card-text>
        <v-card-actions style="padding:0 24px 24px;gap:10px;">
          <button class="ghost-btn" style="flex:1" @click="deleteDialog = false">Atcelt</button>
          <button
            class="delete-btn"
            style="flex:1"
            :disabled="deleteConfirm !== 'DZĒST' || deleting"
            @click="deleteAccount"
          >
            {{ deleting ? 'Dzēš…' : 'Dzēst kontu' }}
          </button>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </div>
</template>

<style scoped>
.profile-page {
  max-width: 860px; margin: 0 auto;
  padding: 40px 24px 60px;
}

/* Page header */
.page-head {
  display: flex; align-items: center; gap: 16px;
  margin-bottom: 28px;
}
.ph-avatar-wrap { flex-shrink: 0; }
.ph-avatar, .ph-avatar-circle {
  width: 60px; height: 60px; border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(124,58,237,.5);
  box-shadow: 0 0 24px rgba(124,58,237,.25);
}
.ph-avatar-circle {
  display: flex; align-items: center; justify-content: center;
  font-size: 26px; font-weight: 800; color: #fff;
}
.ph-title { font-size: 1.6rem; font-weight: 800; color: #fff; margin: 0 0 2px; }
.ph-sub   { color: #6b7280; font-size: .85rem; margin: 0; }

/* Alerts */
.c-alert {
  border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;
  font-size: .875rem; font-weight: 600;
}
.c-success { background: rgba(74,222,128,.1); border: 1px solid rgba(74,222,128,.25); color: #4ade80; }
.c-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.25);  color: #f87171; }

/* Section card */
.sc {
  background: #13131f;
  border: 1px solid rgba(124,58,237,.18);
  border-radius: 16px; padding: 22px 24px;
  margin-bottom: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,.3);
}
.sc-head {
  display: flex; align-items: center; gap: 8px;
  font-weight: 700; font-size: .95rem; color: #e5e7eb;
  margin-bottom: 18px; padding-bottom: 14px;
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.sc-head > span:first-child { font-size: 16px; }
.ml-auto { margin-left: auto; }

/* Avatar */
.avatar-section { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.avatar-wrap {
  position: relative; width: 90px; height: 90px;
  border-radius: 50%; overflow: hidden; flex-shrink: 0;
  cursor: pointer;
  box-shadow: 0 0 0 3px rgba(124,58,237,.3), 0 4px 20px rgba(0,0,0,.4);
}
.avatar-img { width: 90px; height: 90px; object-fit: cover; border-radius: 50%; }
.avatar-placeholder {
  width: 90px; height: 90px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 36px; font-weight: 700; color: #fff;
}
.avatar-overlay {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: rgba(0,0,0,.6); color: #fff;
  font-size: 11px; text-align: center; padding: 4px 0;
  opacity: 0; transition: opacity .2s;
}
.avatar-wrap:hover .avatar-overlay { opacity: 1; }
.avatar-meta { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: 8px; }

.color-row   { display: flex; align-items: center; gap: 8px; }
.color-pick  { width: 30px; height: 30px; border: none; border-radius: 6px; cursor: pointer; padding: 2px; background: transparent; }
.color-hex   { font-family: monospace; font-size: .75rem; color: #9ca3af; }

/* Buttons */
.ghost-btn {
  background: rgba(124,58,237,.12); border: 1px solid rgba(124,58,237,.28);
  color: #a78bfa; padding: 8px 16px; border-radius: 8px;
  font-size: .82rem; font-weight: 600; cursor: pointer;
  transition: background .2s;
  white-space: nowrap;
}
.ghost-btn:hover { background: rgba(124,58,237,.22); }
.ghost-sm { padding: 5px 12px; font-size: .75rem; }

.add-inline-btn {
  background: rgba(124,58,237,.15); border: none; color: #a78bfa;
  padding: 4px 10px; border-radius: 6px; font-size: .75rem; font-weight: 600;
  cursor: pointer; transition: background .2s; white-space: nowrap;
}
.add-inline-btn:hover { background: rgba(124,58,237,.3); }

/* Hints */
.hint     { color: #6b7280; font-size: .78rem; margin: 0; }
.hint.green { color: #4ade80; }
.mb-2     { margin-bottom: 8px; }
.mt-3     { margin-top: 12px; }

/* Game chips */
.game-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.g-chip {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(124,58,237,.2); color: #a78bfa;
  padding: 5px 6px 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
}
.g-remove {
  background: rgba(255,255,255,.1); border: none; color: #a78bfa;
  width: 18px; height: 18px; border-radius: 50%; cursor: pointer;
  font-size: 15px; display: flex; align-items: center; justify-content: center;
  line-height: 1; padding: 0; transition: background .2s, color .2s;
}
.g-remove:hover { background: rgba(239,68,68,.3); color: #f87171; }

/* Account row */
.acc-row {
  background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06);
  border-radius: 10px; padding: 10px; margin-bottom: 10px;
}

/* Suggestion list */
.suggestion-list {
  position: absolute; top: 100%; left: 0; right: 0; z-index: 10;
  max-height: 220px; overflow-y: auto;
  background: #1e1e2e; border: 1px solid rgba(124,58,237,.2);
  border-radius: 8px; margin-top: 4px;
}

/* Save button */
.save-btn {
  width: 100%;
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: #fff; border: none; border-radius: 12px;
  padding: 15px; font-size: 1rem; font-weight: 700;
  cursor: pointer; margin-top: 8px;
  transition: opacity .2s, transform .15s;
  box-shadow: 0 4px 24px rgba(124,58,237,.4);
}
.save-btn:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
.save-btn:active:not(:disabled) { transform: translateY(0); }
.save-btn:disabled { opacity: .45; cursor: not-allowed; }

.danger-zone {
  border-color: rgba(239,68,68,.2);
  margin-top: 24px;
}
.delete-btn {
  background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.35);
  color: #f87171; padding: 10px 20px; border-radius: 8px;
  font-size: .875rem; font-weight: 600; cursor: pointer;
  transition: background .2s;
  white-space: nowrap;
}
.delete-btn:hover:not(:disabled) { background: rgba(239,68,68,.25); }
.delete-btn:disabled { opacity: .4; cursor: not-allowed; }
.confirm-input {
  width: 100%; background: rgba(255,255,255,.05);
  border: 1px solid rgba(239,68,68,.4); border-radius: 10px;
  color: #fff; font-size: .95rem; padding: 11px 14px; outline: none;
  transition: border-color .2s;
}
.confirm-input:focus { border-color: #f87171; }
.confirm-input::placeholder { color: #4b5563; }

@media (max-width: 600px) {
  .profile-page { padding: 24px 16px 40px; }
  .sc { padding: 18px 16px; }
}
</style>
