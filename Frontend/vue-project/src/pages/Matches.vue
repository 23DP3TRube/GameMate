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
  <v-container class="py-8 pa-4" style="max-width: 900px; margin: 0 auto;">
    <h2 class="text-h5 font-weight-bold mb-6">Manas Sakritības</h2>

    <div v-if="loading" class="text-center py-10">
      <v-progress-circular indeterminate />
    </div>

    <v-card v-else-if="matches.length === 0" color="grey-darken-4" class="pa-6 text-center">
      <v-icon size="48" class="mb-3 text-grey">mdi-heart-outline</v-icon>
      <p>Vēl nav sakritību.</p>
      <p class="text-caption text-grey mb-4">Atklāj jaunus spēlētājus!</p>
      <v-btn color="primary" to="/discover">Atklāt spēlētājus</v-btn>
    </v-card>

    <v-card
      v-for="m in matches" :key="m.match_id"
      color="grey-darken-4"
      class="pa-4 mb-3 match-card"
    >
      <div class="match-inner">
        <!-- Avatar -->
        <div class="avatar-wrap mr-4">
          <img v-if="m.profile?.avatar_url" :src="m.profile.avatar_url" class="avatar-img" />
          <div v-else class="avatar-circle" :style="{ background: m.profile?.avatar_color || '#7c3aed' }">
            {{ m.profile?.gamertag?.[0]?.toUpperCase() }}
          </div>
        </div>

        <!-- Info -->
        <div class="flex-grow-1 match-info">
          <div class="font-weight-bold">{{ m.profile?.gamertag }}</div>
          <div class="text-caption text-grey">
            <span v-if="m.profile?.platform">{{ m.profile.platform }}</span>
            <span v-if="m.profile?.region"> · {{ m.profile.region }}</span>
            <span v-if="m.profile?.playstyle"> · {{ m.profile.playstyle }}</span>
          </div>
          <div v-if="m.profile?.games?.length" class="mt-1">
            <v-chip
              v-for="(g, i) in m.profile.games.slice(0, 3)" :key="i"
              size="x-small" color="primary" variant="tonal" class="mr-1"
            >{{ g }}</v-chip>
            <span v-if="m.profile.games.length > 3" class="text-caption text-grey">+{{ m.profile.games.length - 3 }} more</span>
          </div>
        </div>

        <v-btn color="primary" :to="`/matches/${m.match_id}`" class="chat-btn flex-shrink-0">Tērzēt</v-btn>
      </div>
    </v-card>
  </v-container>
</template>

<style scoped>
.match-inner {
  display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
}
.match-info  { min-width: 0; }
.avatar-wrap { flex-shrink: 0; }
.avatar-img  { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; }
.avatar-circle {
  width: 56px; height: 56px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 700; color: #fff;
}
@media (max-width: 400px) {
  .chat-btn { width: 100%; margin-top: 8px; }
}
</style>
