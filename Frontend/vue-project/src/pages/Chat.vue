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
  <div class="chat-page">
    <!-- Header -->
    <div class="chat-header">
      <button class="back-btn" @click="router.back()">‹</button>
      <div v-if="otherProfile" class="header-profile">
        <div class="h-avatar">
          <img v-if="otherProfile.avatar_url" :src="otherProfile.avatar_url" class="h-img" />
          <div v-else class="h-circle" :style="{ background: otherProfile.avatar_color || '#7c3aed' }">
            {{ otherProfile.gamertag?.[0]?.toUpperCase() }}
          </div>
          <div class="h-online" />
        </div>
        <div>
          <div class="h-name">{{ otherProfile.gamertag }}</div>
          <div class="h-meta">{{ otherProfile.platform }} · {{ otherProfile.region }}</div>
        </div>
      </div>
      <div v-else class="h-name">Tērzēšana</div>
    </div>

    <!-- Messages -->
    <div ref="scroller" class="msgs">
      <div v-if="messages.length === 0" class="empty-chat">
        <div class="empty-emoji">👋</div>
        <p>Sāc sarunu!</p>
      </div>
      <div
        v-for="m in messages" :key="m.id"
        :class="['msg', m.sender_id === auth.user?.id ? 'mine' : 'theirs']"
      >
        <div class="msg-body">{{ m.body }}</div>
        <div class="msg-time">{{ new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</div>
      </div>
    </div>

    <!-- Input -->
    <div class="chat-input-wrap">
      <input
        v-model="text"
        class="chat-input"
        placeholder="Raksti ziņojumu..."
        @keyup.enter="send"
      />
      <button class="send-btn" :disabled="sending || !text.trim()" @click="send">
        <span v-if="sending">⏳</span>
        <span v-else>➤</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.chat-page {
  display: flex; flex-direction: column;
  height: calc(100vh - 60px);
  max-width: 780px; margin: 0 auto;
  padding: 0 16px 16px;
}

/* Header */
.chat-header {
  display: flex; align-items: center; gap: 12px;
  padding: 16px 0 12px;
  border-bottom: 1px solid rgba(255,255,255,.07);
  flex-shrink: 0;
}
.back-btn {
  width: 38px; height: 38px; border-radius: 50%;
  background: rgba(255,255,255,.07); border: none;
  color: #fff; font-size: 22px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: background .2s;
}
.back-btn:hover { background: rgba(255,255,255,.12); }
.header-profile { display: flex; align-items: center; gap: 12px; }
.h-avatar { position: relative; flex-shrink: 0; }
.h-img, .h-circle {
  width: 42px; height: 42px; border-radius: 50%;
  object-fit: cover; border: 2px solid rgba(255,255,255,.1);
}
.h-circle {
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; font-weight: 700; color: #fff;
}
.h-online {
  position: absolute; bottom: 1px; right: 1px;
  width: 10px; height: 10px; border-radius: 50%;
  background: #4ade80; border: 2px solid #121212;
}
.h-name { font-weight: 700; font-size: 1rem; color: #fff; }
.h-meta { color: #6b7280; font-size: .75rem; margin-top: 1px; }

/* Messages */
.msgs {
  flex: 1; overflow-y: auto; padding: 20px 0 12px;
  display: flex; flex-direction: column; gap: 4px;
}
.msgs::-webkit-scrollbar { width: 4px; }
.msgs::-webkit-scrollbar-track { background: transparent; }
.msgs::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

.empty-chat { text-align: center; margin: auto; color: #4b5563; }
.empty-emoji { font-size: 48px; margin-bottom: 8px; }

.msg { max-width: 72%; word-wrap: break-word; }
.msg-body {
  padding: 10px 14px; border-radius: 18px; line-height: 1.5;
  font-size: .9rem;
}
.msg-time { font-size: 10px; color: #6b7280; margin-top: 3px; padding: 0 6px; }

.mine { align-self: flex-end; }
.mine .msg-body {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: #fff; border-bottom-right-radius: 4px;
  box-shadow: 0 2px 12px rgba(124,58,237,.35);
}
.mine .msg-time { text-align: right; }

.theirs { align-self: flex-start; }
.theirs .msg-body {
  background: #1e293b; color: #e5e7eb;
  border-bottom-left-radius: 4px;
  border: 1px solid rgba(255,255,255,.06);
}

/* Input */
.chat-input-wrap {
  display: flex; gap: 10px; align-items: center;
  padding-top: 12px; border-top: 1px solid rgba(255,255,255,.07);
  flex-shrink: 0;
}
.chat-input {
  flex: 1; background: #1e293b; border: 1px solid rgba(255,255,255,.1);
  border-radius: 24px; color: #fff; font-size: .95rem;
  padding: 12px 18px; outline: none; transition: border-color .2s;
}
.chat-input:focus { border-color: rgba(124,58,237,.6); }
.chat-input::placeholder { color: #4b5563; }
.send-btn {
  width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
  background: #7c3aed; border: none; color: #fff;
  font-size: 18px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s, transform .15s;
}
.send-btn:hover:not(:disabled) { background: #6d28d9; transform: scale(1.05); }
.send-btn:disabled { opacity: .4; cursor: not-allowed; }
</style>
