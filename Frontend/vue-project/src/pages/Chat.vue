<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'
import { auth } from '../auth'

const route   = useRoute()
const router  = useRouter()
const messages = ref([])
const otherProfile = ref(null)
const text    = ref('')
const scroller = ref(null)
const sending  = ref(false)
let   timer    = null

const scroll = async () => {
  await nextTick()
  if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight
}

const load = async () => {
  const { data } = await api.get(`/matches/${route.params.id}/messages`)
  messages.value = data
  await scroll()
}

const loadMatchInfo = async () => {
  const { data } = await api.get('/matches')
  const match = data.find(m => m.match_id == route.params.id)
  if (match) otherProfile.value = match.profile
}

const send = async () => {
  if (!text.value.trim() || sending.value) return
  sending.value = true
  try {
    await api.post(`/matches/${route.params.id}/messages`, { body: text.value })
    text.value = ''
    await load()
  } finally {
    sending.value = false
  }
}

onMounted(() => {
  loadMatchInfo()
  load()
  timer = setInterval(load, 4000)
})
onUnmounted(() => clearInterval(timer))
</script>

<template>
  <v-container class="py-6 px-3 chat-container" style="max-width: 780px; margin: 0 auto;">

    <!-- Chat header -->
    <div class="d-flex align-center mb-4">
      <v-btn icon variant="text" @click="router.back()" class="mr-2">
        <v-icon>mdi-arrow-left</v-icon>
      </v-btn>
      <div v-if="otherProfile" class="d-flex align-center">
        <div class="avatar-wrap mr-3">
          <img v-if="otherProfile.avatar_url" :src="otherProfile.avatar_url" class="avatar-img" />
          <div v-else class="avatar-circle" :style="{ background: otherProfile.avatar_color || '#7c3aed' }">
            {{ otherProfile.gamertag?.[0]?.toUpperCase() }}
          </div>
        </div>
        <div>
          <div class="font-weight-bold">{{ otherProfile.gamertag }}</div>
          <div class="text-caption text-grey">
            {{ otherProfile.platform }} · {{ otherProfile.region }}
          </div>
        </div>
      </div>
      <div v-else class="font-weight-bold">Tērzēšana</div>
    </div>

    <!-- Messages -->
    <v-card color="grey-darken-4" class="pa-3">
      <div ref="scroller" class="msgs">
        <div
          v-for="m in messages" :key="m.id"
          :class="['msg', m.sender_id === auth.user?.id ? 'mine' : 'theirs']"
        >
          <div class="msg-body">{{ m.body }}</div>
          <div class="msg-time">{{ new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</div>
        </div>
        <div v-if="messages.length === 0" class="text-grey text-center py-6">
          Sāc sarunu! Sveiki 👋
        </div>
      </div>

      <v-form @submit.prevent="send" class="d-flex mt-3 gap-2">
        <v-text-field
          v-model="text"
          placeholder="Raksti ziņojumu..."
          hide-details
          @keyup.enter="send"
        />
        <v-btn color="primary" type="submit" :loading="sending" icon>
          <v-icon>mdi-send</v-icon>
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<style scoped>
.msgs { height: 60vh; overflow-y: auto; padding: 8px; display: flex; flex-direction: column; }
@media (max-width: 600px) {
  .chat-container { padding-top: 12px !important; padding-bottom: 12px !important; }
  .msgs { height: calc(100dvh - 240px); min-height: 280px; }
}
.msg  { margin: 4px 0; max-width: 72%; word-wrap: break-word; }
.msg-body { padding: 8px 12px; border-radius: 16px; line-height: 1.4; }
.msg-time { font-size: 10px; color: #888; margin-top: 2px; padding: 0 4px; }
.mine        { align-self: flex-end; }
.mine .msg-body   { background: #7c3aed; color: #fff; border-bottom-right-radius: 4px; }
.mine .msg-time   { text-align: right; }
.theirs      { align-self: flex-start; }
.theirs .msg-body { background: #374151; color: #fff; border-bottom-left-radius: 4px; }
.avatar-wrap { flex-shrink: 0; }
.avatar-img  { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.avatar-circle {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; font-weight: 700; color: #fff;
}
</style>
