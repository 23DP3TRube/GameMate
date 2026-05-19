<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route  = useRoute()
const router = useRouter()

const email    = ref('')
const token    = ref('')
const password = ref('')
const confirm  = ref('')
const error    = ref('')
const loading  = ref(false)

const reqs = {
  length:  { label: 'Vismaz 8 rakstzīmes',    ok: false },
  upper:   { label: 'Viens lielais burts',      ok: false },
  lower:   { label: 'Viens mazais burts',       ok: false },
  digit:   { label: 'Viens cipars',            ok: false },
  special: { label: 'Viena speciālrakstzīme',  ok: false },
}

function checkReqs(val) {
  reqs.length.ok  = val.length >= 8
  reqs.upper.ok   = /[A-Z]/.test(val)
  reqs.lower.ok   = /[a-z]/.test(val)
  reqs.digit.ok   = /[0-9]/.test(val)
  reqs.special.ok = /[^A-Za-z0-9]/.test(val)
}

onMounted(() => {
  token.value = route.params.token ?? ''
  email.value = route.query.email  ?? ''
})

async function submit() {
  error.value = ''
  if (!Object.values(reqs).every(r => r.ok)) {
    error.value = 'Parole neatbilst prasībām.'
    return
  }
  if (password.value !== confirm.value) {
    error.value = 'Paroles nesakrīt.'
    return
  }
  loading.value = true
  try {
    await api.post('/reset-password', {
      email:    email.value,
      token:    token.value,
      password: password.value,
    })
    router.push('/login?reset=1')
  } catch (e) {
    error.value = e.response?.data?.message || 'Radās kļūda. Mēģini vēlreiz.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="card">
      <div class="logo">🔒</div>
      <h2>Jauna parole</h2>
      <p class="sub">Ievadi jaunu paroli savam kontam.</p>

      <div v-if="error" class="error-box">{{ error }}</div>

      <label>Jaunā parole
        <input
          v-model="password"
          type="password"
          placeholder="Jaunā parole"
          @input="checkReqs(password)"
        />
      </label>

      <ul class="req-list">
        <li v-for="(r, key) in reqs" :key="key" :class="{ ok: r.ok }">
          <span>{{ r.ok ? '✓' : '○' }}</span> {{ r.label }}
        </li>
      </ul>

      <label>Apstiprini paroli
        <input v-model="confirm" type="password" placeholder="Atkārtoti ievadi paroli" @keyup.enter="submit" />
      </label>

      <button class="btn-primary" :disabled="loading" @click="submit">
        {{ loading ? 'Saglabā…' : 'Saglabāt paroli' }}
      </button>

      <p class="footer-text">
        <router-link to="/login">← Atpakaļ uz pieslēgšanos</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 80vh;
  display: flex; align-items: center; justify-content: center;
  padding: 24px;
  background: radial-gradient(ellipse at 50% 0%, rgba(124,58,237,.12) 0%, transparent 60%);
}
.card {
  background: #13131f;
  border: 1px solid rgba(124,58,237,.25);
  border-radius: 20px;
  padding: 44px 40px;
  width: 100%; max-width: 420px;
  display: flex; flex-direction: column; gap: 14px;
  box-shadow: 0 0 60px rgba(124,58,237,.1), 0 24px 48px rgba(0,0,0,.5);
}
.logo { font-size: 44px; text-align: center; margin-bottom: 4px; }
h2 { text-align: center; margin: 0; font-size: 1.6rem; font-weight: 800; color: #fff; }
.sub { text-align: center; color: #6b7280; font-size: 0.875rem; margin: 0; }
.error-box {
  background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3);
  color: #f87171; padding: 11px 14px; border-radius: 10px; font-size: 0.875rem;
}
label {
  display: flex; flex-direction: column; gap: 6px;
  color: #9ca3af; font-size: 0.8rem; font-weight: 600; letter-spacing: .3px;
}
input {
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
  border-radius: 12px; color: #fff; font-size: 0.95rem;
  padding: 12px 16px; outline: none; transition: border-color .2s, background .2s;
}
input:focus { border-color: rgba(124,58,237,.7); background: rgba(124,58,237,.06); }
input::placeholder { color: #4b5563; }
.req-list {
  list-style: none; margin: 0; padding: 0;
  display: flex; flex-direction: column; gap: 4px;
}
.req-list li {
  font-size: 0.78rem; color: #4b5563;
  display: flex; align-items: center; gap: 6px;
  transition: color .2s;
}
.req-list li.ok { color: #4ade80; }
.btn-primary {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: #fff; border: none; border-radius: 12px;
  padding: 13px; font-size: 1rem; font-weight: 700;
  cursor: pointer; margin-top: 4px;
  transition: opacity .2s, transform .15s;
  box-shadow: 0 4px 20px rgba(124,58,237,.4);
}
.btn-primary:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
.btn-primary:active:not(:disabled) { transform: translateY(0); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.footer-text { text-align: center; color: #6b7280; font-size: 0.875rem; margin: 0; }
.footer-text a { color: #a78bfa; text-decoration: none; font-weight: 600; }
.footer-text a:hover { color: #c4b5fd; }
@media (max-width: 480px) {
  .card { padding: 32px 24px; }
}
</style>
