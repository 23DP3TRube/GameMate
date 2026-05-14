<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { auth } from '../auth'

const name     = ref('')
const gamertag = ref('')
const email    = ref('')
const password = ref('')
const error    = ref('')
const loading  = ref(false)
const router   = useRouter()

function validatePassword(pw) {
  if (pw.length < 8)               return 'Parolei jābūt vismaz 8 rakstzīmēm.'
  if (!/[A-Z]/.test(pw))           return 'Parolei jāsatur vismaz viens lielais burts.'
  if (!/[a-z]/.test(pw))           return 'Parolei jāsatur vismaz viens mazais burts.'
  if (!/[0-9]/.test(pw))           return 'Parolei jāsatur vismaz viens cipars.'
  if (!/[^A-Za-z0-9]/.test(pw))   return 'Parolei jāsatur vismaz viena speciālā zīme (piem. !@#$%).'
  return ''
}

async function submit() {
  if (!name.value.trim() || !gamertag.value.trim() || !email.value.trim() || !password.value.trim()) {
    error.value = 'Lūdzu aizpildi visus laukus.'
    return
  }
  const pwErr = validatePassword(password.value)
  if (pwErr) { error.value = pwErr; return }
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/register', {
      name:     name.value.trim(),
      gamertag: gamertag.value.trim(),
      email:    email.value.trim(),
      password: password.value,
    })
    auth.setSession(data.token, data.user)
    router.push('/profile')
  } catch (e) {
    const errs = e.response?.data?.errors
    error.value = errs
      ? Object.values(errs).flat().join(' ')
      : (e.response?.data?.message || `Error ${e.response?.status ?? 'unknown'}: nevarēja reģistrēties.`)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="card">
      <div class="logo">🎮</div>
      <h2>Izveido savu kontu</h2>
      <p class="sub">Atrodi savus spēļu komandas biedrus GameMate</p>

      <div v-if="error" class="error-box">{{ error }}</div>

      <label>Pilns vārds
        <input v-model="name" type="text" placeholder="Jānis Bērziņš" autocomplete="name" />
      </label>

      <label>Gamertag
        <input v-model="gamertag" type="text" placeholder="Tavs unikālais spēlētāja vārds" />
      </label>

      <label>E-pasts
        <input v-model="email" type="email" placeholder="tu@epasts.lv" autocomplete="email" />
      </label>

      <label>Parole
        <input v-model="password" type="password" placeholder="Vismaz 8 rakstzīmes" @keyup.enter="submit" />
      </label>
      <div v-if="password" class="pw-checks">
        <span :class="password.length >= 8       ? 'ok' : 'no'">{{ password.length >= 8       ? '✓' : '✗' }} 8+ rakstzīmes</span>
        <span :class="/[A-Z]/.test(password)     ? 'ok' : 'no'">{{ /[A-Z]/.test(password)     ? '✓' : '✗' }} Lielais burts</span>
        <span :class="/[a-z]/.test(password)     ? 'ok' : 'no'">{{ /[a-z]/.test(password)     ? '✓' : '✗' }} Mazais burts</span>
        <span :class="/[0-9]/.test(password)     ? 'ok' : 'no'">{{ /[0-9]/.test(password)     ? '✓' : '✗' }} Cipars</span>
        <span :class="/[^A-Za-z0-9]/.test(password) ? 'ok' : 'no'">{{ /[^A-Za-z0-9]/.test(password) ? '✓' : '✗' }} Speciālā zīme</span>
      </div>

      <button class="btn-primary" :disabled="loading" @click="submit">
        {{ loading ? 'Izveido kontu…' : 'Izveidot kontu' }}
      </button>

      <p class="footer-text">
        Jau ir konts?
        <router-link to="/login">Pieslēgties</router-link>
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
.pw-checks {
  display: flex; flex-wrap: wrap; gap: 6px; margin-top: -6px;
}
.pw-checks span {
  font-size: 0.75rem; font-weight: 600; padding: 3px 10px;
  border-radius: 20px; transition: all .2s;
}
.pw-checks .ok { background: rgba(74,222,128,.12); color: #4ade80; }
.pw-checks .no { background: rgba(255,255,255,.05); color: #4b5563; }
@media (max-width: 480px) {
  .card { padding: 32px 24px; }
}
</style>
