<script setup>
import { ref } from 'vue'
import api from '../api'

const email   = ref('')
const error   = ref('')
const success = ref('')
const loading = ref(false)

async function submit() {
  if (!email.value.trim()) {
    error.value = 'Lūdzu ievadi savu e-pastu.'
    return
  }
  error.value   = ''
  success.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/forgot-password', { email: email.value.trim() })
    success.value = data.message
    email.value   = ''
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
      <div class="logo">🔑</div>
      <h2>Aizmirsi paroli?</h2>
      <p class="sub">Ievadi savu e-pastu un mēs nosūtīsim paroles atjaunošanas saiti.</p>

      <div v-if="error" class="error-box">{{ error }}</div>
      <div v-if="success" class="success-box">{{ success }}</div>

      <label>E-pasts
        <input v-model="email" type="email" placeholder="tu@epasts.lv" autocomplete="email" @keyup.enter="submit" />
      </label>

      <button class="btn-primary" :disabled="loading" @click="submit">
        {{ loading ? 'Sūta…' : 'Nosūtīt saiti' }}
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
.success-box {
  background: rgba(74,222,128,.1); border: 1px solid rgba(74,222,128,.3);
  color: #4ade80; padding: 11px 14px; border-radius: 10px; font-size: 0.875rem;
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
@media (max-width: 480px) {
  .card { padding: 32px 24px; }
}
</style>
