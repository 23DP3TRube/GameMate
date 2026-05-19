<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api'

const exporting = ref('')

const exportFile = async (type) => {
  exporting.value = type
  try {
    const res = await api.get(`/admin/export/${type}`, { responseType: 'blob' })
    const ext  = type === 'pdf' ? 'pdf' : 'csv'
    const mime = type === 'pdf' ? 'application/pdf' : 'text/csv'
    const url  = URL.createObjectURL(new Blob([res.data], { type: mime }))
    const a    = document.createElement('a')
    a.href     = url
    a.download = `gamemate_lietotaji_${new Date().toISOString().slice(0,10)}.${ext}`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    alert('Eksports neizdevās.')
  } finally {
    exporting.value = ''
  }
}

const users    = ref([])
const stats    = ref(null)
const loading  = ref(true)
const tab      = ref('users')
const search   = ref('')
const sortBy   = ref('name')
const sortDir  = ref('asc')
const deleting = ref({})
const toggling = ref({})

const error = ref('')

onMounted(async () => {
  try {
    await Promise.all([loadUsers(), loadStats()])
  } catch (e) {
    error.value = e.response?.data?.message || 'Neizdevās ielādēt datus.'
  } finally {
    loading.value = false
  }
})

const loadUsers = async () => {
  const { data } = await api.get('/admin/users')
  users.value = data
}

const loadStats = async () => {
  const { data } = await api.get('/admin/stats')
  stats.value = data
}

const filtered = computed(() => {
  let list = [...users.value]
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(u =>
      u.name?.toLowerCase().includes(q) ||
      u.email?.toLowerCase().includes(q) ||
      u.gamertag?.toLowerCase().includes(q)
    )
  }
  list.sort((a, b) => {
    const va = (a[sortBy.value] || '').toString().toLowerCase()
    const vb = (b[sortBy.value] || '').toString().toLowerCase()
    return sortDir.value === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va)
  })
  return list
})

const toggleSort = (field) => {
  if (sortBy.value === field) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  else { sortBy.value = field; sortDir.value = 'asc' }
}

const sortIcon = (field) => {
  if (sortBy.value !== field) return '↕'
  return sortDir.value === 'asc' ? '↑' : '↓'
}

const deleteUser = async (u) => {
  if (!confirm(`Dzēst lietotāju "${u.name}"?`)) return
  deleting.value[u.id] = true
  try {
    await api.delete(`/admin/users/${u.id}`)
    users.value = users.value.filter(x => x.id !== u.id)
  } catch (e) {
    alert(e.response?.data?.error || 'Kļūda')
  } finally {
    deleting.value[u.id] = false
  }
}

const toggleAdmin = async (u) => {
  toggling.value[u.id] = true
  try {
    const { data } = await api.post(`/admin/users/${u.id}/toggle-admin`)
    u.is_admin = data.is_admin
  } catch (e) {
    alert(e.response?.data?.error || 'Kļūda')
  } finally {
    toggling.value[u.id] = false
  }
}
</script>

<template>
  <div class="admin-wrap">
    <div class="admin-header">
      <h1 class="admin-title">⚙️ Administrācija</h1>
      <div class="admin-tabs">
        <button :class="['tab-btn', { active: tab === 'users' }]" @click="tab = 'users'">Lietotāji</button>
        <button :class="['tab-btn', { active: tab === 'stats' }]" @click="tab = 'stats'">Statistika</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-10"><v-progress-circular indeterminate /></div>
    <div v-else-if="error" class="text-center py-10" style="color:#f87171;">{{ error }}</div>

    <!-- ── Users tab ── -->
    <div v-else-if="tab === 'users'">
      <div class="toolbar">
        <input v-model="search" class="search-input" placeholder="Meklēt pēc vārda, e-pasta, gamertag..." />
        <div class="user-count">{{ filtered.length }} lietotāji</div>
        <button class="btn-export" :disabled="exporting === 'pdf'" @click="exportFile('pdf')">
          {{ exporting === 'pdf' ? '⏳' : '📄' }} PDF
        </button>
        <button class="btn-export btn-export--csv" :disabled="exporting === 'csv'" @click="exportFile('csv')">
          {{ exporting === 'csv' ? '⏳' : '📊' }} CSV
        </button>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th @click="toggleSort('name')" class="sortable">Vārds {{ sortIcon('name') }}</th>
              <th @click="toggleSort('email')" class="sortable">E-pasts {{ sortIcon('email') }}</th>
              <th @click="toggleSort('gamertag')" class="sortable">Gamertag {{ sortIcon('gamertag') }}</th>
              <th @click="toggleSort('platform')" class="sortable">Platforma {{ sortIcon('platform') }}</th>
              <th @click="toggleSort('region')" class="sortable">Reģions {{ sortIcon('region') }}</th>
              <th @click="toggleSort('created_at')" class="sortable">Reģistrēts {{ sortIcon('created_at') }}</th>
              <th>Loma</th>
              <th>Darbības</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in filtered" :key="u.id" :class="{ 'admin-row': u.is_admin }">
              <td class="fw-bold">{{ u.name }}</td>
              <td class="text-muted">{{ u.email }}</td>
              <td>{{ u.gamertag || '—' }}</td>
              <td>{{ u.platform || '—' }}</td>
              <td>{{ u.region || '—' }}</td>
              <td class="text-muted">{{ u.created_at }}</td>
              <td>
                <span :class="['role-badge', u.is_admin ? 'role-admin' : 'role-user']">
                  {{ u.is_admin ? 'Admins' : 'Lietotājs' }}
                </span>
              </td>
              <td class="actions">
                <button
                  class="btn-sm btn-toggle"
                  :disabled="toggling[u.id]"
                  @click="toggleAdmin(u)"
                  :title="u.is_admin ? 'Noņemt admina tiesības' : 'Piešķirt admina tiesības'"
                >{{ u.is_admin ? '👤' : '🛡️' }}</button>
                <button
                  class="btn-sm btn-delete"
                  :disabled="deleting[u.id]"
                  @click="deleteUser(u)"
                  title="Dzēst"
                >✕</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Stats tab ── -->
    <div v-else-if="tab === 'stats' && stats">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-num">{{ stats.total_users }}</div>
          <div class="stat-label">Reģistrēti lietotāji</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">{{ stats.total_matches }}</div>
          <div class="stat-label">Kopā sakritības</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">{{ stats.total_swipes }}</div>
          <div class="stat-label">Kopā nobalsojumi</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">{{ stats.total_messages }}</div>
          <div class="stat-label">Nosūtīti ziņojumi</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">{{ stats.avg_matches_per_user }}</div>
          <div class="stat-label">Vidēji sakritības / lietotājs</div>
        </div>
      </div>

      <div class="dist-grid">
        <div class="dist-card">
          <h3 class="dist-title">Platformas sadalījums</h3>
          <div v-for="p in stats.platforms" :key="p.platform" class="dist-row">
            <span class="dist-label">{{ p.platform }}</span>
            <div class="dist-bar-wrap">
              <div class="dist-bar" :style="{ width: (p.total / stats.total_users * 100) + '%' }" />
            </div>
            <span class="dist-count">{{ p.total }}</span>
          </div>
          <div v-if="!stats.platforms.length" class="text-muted">Nav datu</div>
        </div>

        <div class="dist-card">
          <h3 class="dist-title">Reģionu sadalījums</h3>
          <div v-for="r in stats.regions" :key="r.region" class="dist-row">
            <span class="dist-label">{{ r.region }}</span>
            <div class="dist-bar-wrap">
              <div class="dist-bar" :style="{ width: (r.total / stats.total_users * 100) + '%' }" />
            </div>
            <span class="dist-count">{{ r.total }}</span>
          </div>
          <div v-if="!stats.regions.length" class="text-muted">Nav datu</div>
        </div>

        <div class="dist-card">
          <h3 class="dist-title">Spēles stila sadalījums</h3>
          <div v-for="s in stats.playstyles" :key="s.playstyle" class="dist-row">
            <span class="dist-label">{{ s.playstyle }}</span>
            <div class="dist-bar-wrap">
              <div class="dist-bar" :style="{ width: (s.total / stats.total_users * 100) + '%' }" />
            </div>
            <span class="dist-count">{{ s.total }}</span>
          </div>
          <div v-if="!stats.playstyles.length" class="text-muted">Nav datu</div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-wrap   { padding: 40px 48px; max-width: 1400px; margin: 0 auto; }
.admin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
.admin-title  { font-size: 1.6rem; font-weight: 800; margin: 0; }
.admin-tabs   { display: flex; gap: 8px; }
.tab-btn      { padding: 8px 20px; border-radius: 8px; border: 1px solid #333; background: #1e1e1e; color: #aaa; cursor: pointer; font-weight: 600; transition: all .2s; }
.tab-btn.active { background: #7c3aed; color: #fff; border-color: #7c3aed; }

.toolbar      { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
.search-input { flex: 1; min-width: 200px; padding: 10px 14px; background: #1e1e1e; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: .9rem; outline: none; }
.search-input:focus { border-color: #7c3aed; }
.user-count   { color: #6b7280; font-size: .85rem; white-space: nowrap; }
.btn-export   { padding: 8px 14px; border-radius: 8px; border: 1px solid #333; background: #1e1e1e; color: #a78bfa; cursor: pointer; font-size: .8rem; font-weight: 600; white-space: nowrap; transition: background .2s; }
.btn-export:hover:not(:disabled) { background: rgba(124,58,237,.2); }
.btn-export:disabled { opacity: .5; cursor: not-allowed; }
.btn-export--csv { color: #34d399; }
.btn-export--csv:hover:not(:disabled) { background: rgba(52,211,153,.1); }

.table-wrap   { overflow-x: auto; border-radius: 12px; border: 1px solid #2a2a2a; }
.data-table   { width: 100%; border-collapse: collapse; font-size: .85rem; }
.data-table th { padding: 12px 16px; background: #1a1a1a; color: #9ca3af; font-weight: 600; text-align: left; white-space: nowrap; border-bottom: 1px solid #2a2a2a; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #1f1f1f; color: #e5e7eb; }
.data-table tbody tr:hover { background: rgba(255,255,255,.03); }
.data-table tbody tr:last-child td { border-bottom: none; }
.sortable { cursor: pointer; user-select: none; }
.sortable:hover { color: #a78bfa; }
.admin-row td { background: rgba(124,58,237,.05); }
.fw-bold { font-weight: 600; }
.text-muted { color: #6b7280; }

.role-badge   { padding: 3px 10px; border-radius: 20px; font-size: .75rem; font-weight: 700; }
.role-admin   { background: rgba(124,58,237,.25); color: #a78bfa; }
.role-user    { background: rgba(255,255,255,.07); color: #9ca3af; }

.actions      { display: flex; gap: 6px; }
.btn-sm       { padding: 5px 10px; border-radius: 6px; border: none; cursor: pointer; font-size: .8rem; font-weight: 600; transition: opacity .2s; }
.btn-sm:disabled { opacity: .4; cursor: not-allowed; }
.btn-toggle   { background: rgba(124,58,237,.2); color: #a78bfa; }
.btn-toggle:hover:not(:disabled) { background: rgba(124,58,237,.4); }
.btn-delete   { background: rgba(239,68,68,.15); color: #f87171; }
.btn-delete:hover:not(:disabled) { background: rgba(239,68,68,.3); }

/* Stats */
.stats-grid   { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px; }
.stat-card    { background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 14px; padding: 24px; text-align: center; }
.stat-num     { font-size: 2rem; font-weight: 800; color: #a78bfa; }
.stat-label   { color: #9ca3af; font-size: .8rem; margin-top: 6px; }

.dist-grid    { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.dist-card    { background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 14px; padding: 20px; }
.dist-title   { font-size: .95rem; font-weight: 700; margin-bottom: 16px; color: #e5e7eb; }
.dist-row     { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.dist-label   { width: 90px; font-size: .8rem; color: #9ca3af; flex-shrink: 0; }
.dist-bar-wrap { flex: 1; height: 8px; background: #2a2a2a; border-radius: 4px; overflow: hidden; }
.dist-bar     { height: 100%; background: linear-gradient(90deg, #7c3aed, #a78bfa); border-radius: 4px; transition: width .5s; }
.dist-count   { width: 28px; text-align: right; font-size: .8rem; color: #6b7280; }

@media (max-width: 768px) { .admin-wrap { padding: 24px 16px; } }
</style>
