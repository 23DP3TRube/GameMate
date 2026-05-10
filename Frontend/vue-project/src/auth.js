import { reactive } from 'vue'
import api from './api'

export const auth = reactive({
  user: null,
  token: localStorage.getItem('gm_token') || null,
  ready: false,

  async hydrate() {
    if (!this.token) { this.ready = true; return }
    try {
      const { data } = await api.get('/me')
      this.user = data
    } catch {
      this.token = null
      localStorage.removeItem('gm_token')
    }
    this.ready = true
  },
  setSession(token, user) {
    this.token = token
    this.user = user
    localStorage.setItem('gm_token', token)
  },
  async logout() {
    try { await api.post('/logout') } catch {}
    this.token = null
    this.user = null
    localStorage.removeItem('gm_token')
  }
})
