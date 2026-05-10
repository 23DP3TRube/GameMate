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
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.card {
  background: #1e1e1e;
  border: 1px solid #333;
  border-radius: 12px;
  padding: 40px;
  width: 100%;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.logo { font-size: 40px; text-align: center; }
h2 { text-align: center; margin: 0; font-size: 1.5rem; color: #fff; }
.sub { text-align: center; color: #888; font-size: 0.85rem; margin: 0; }
.error-box {
  background: #3b1212;
  border: 1px solid #c0392b;
  color: #ff6b6b;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.9rem;
}
label {
  display: flex;
  flex-direction: column;
  gap: 5px;
  color: #ccc;
  font-size: 0.85rem;
}
input {
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 8px;
  color: #fff;
  font-size: 1rem;
  padding: 10px 14px;
  outline: none;
  transition: border-color 0.2s;
}
input:focus { border-color: #7c3aed; }
.btn-primary {
  background: #7c3aed;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 4px;
  transition: background 0.2s;
}
.btn-primary:hover:not(:disabled) { background: #6d28d9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.demo-hint { text-align: center; color: #666; font-size: 0.8rem; margin: 0; }
.footer-text { text-align: center; color: #888; font-size: 0.9rem; margin: 0; }
.footer-text a { color: #a78bfa; }
</style>
