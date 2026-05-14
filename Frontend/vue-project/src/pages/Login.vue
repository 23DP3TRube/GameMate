<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { auth } from '../auth'

const email    = ref('')
const password = ref('')
const error    = ref('')
const loading  = ref(false)
const router   = useRouter()

async function submit() {
  if (!email.value.trim() || !password.value.trim()) {
    error.value = 'Lūdzu ievadi e-pastu un paroli.'
    return
  }
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', {
      email:    email.value.trim(),
      password: password.value,
    })
    auth.setSession(data.token, data.user)
    router.push('/discover')
  } catch (e) {
    error.value = e.response?.data?.message || `Error ${e.response?.status ?? 'unknown'}: nevarēja pieslēgties.`
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="card">
      <div class="logo">🎮</div>
      <h2>Laipni atgriezies</h2>
      <p class="sub">Pieslēdzies, lai atrastu komandas biedrus</p>

      <div v-if="error" class="error-box">{{ error }}</div>

      <label>E-pasts
        <input v-model="email" type="email" placeholder="tu@epasts.lv" autocomplete="email" />
      </label>

      <label>Parole
        <input v-model="password" type="password" placeholder="Tava parole" @keyup.enter="submit" />
      </label>

      <button class="btn-primary" :disabled="loading" @click="submit">
        {{ loading ? 'Pieslēdzas…' : 'Pieslēgties' }}
      </button>

      <p class="demo-hint">Demo konts: <strong>alex@demo.com</strong> / <strong>password</strong></p>

      <p class="footer-text">
        Nav konta?
        <router-link to="/register">Reģistrēties</router-link>
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
.demo-hint { text-align: center; color: #4b5563; font-size: 0.78rem; margin: 0; }
.demo-hint strong { color: #6b7280; }
.footer-text { text-align: center; color: #6b7280; font-size: 0.875rem; margin: 0; }
.footer-text a { color: #a78bfa; text-decoration: none; font-weight: 600; }
.footer-text a:hover { color: #c4b5fd; }
@media (max-width: 480px) {
  .card { padding: 32px 24px; }
}
</style>
