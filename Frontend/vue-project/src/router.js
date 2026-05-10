import { createRouter, createWebHistory } from 'vue-router'
import { auth } from './auth'

const routes = [
  { path: '/', component: () => import('./pages/Home.vue') },
  { path: '/login', component: () => import('./pages/Login.vue') },
  { path: '/register', component: () => import('./pages/Register.vue') },
  { path: '/discover', component: () => import('./pages/Discover.vue'), meta: { auth: true } },
  { path: '/matches', component: () => import('./pages/Matches.vue'), meta: { auth: true } },
  { path: '/matches/:id', component: () => import('./pages/Chat.vue'), meta: { auth: true } },
  { path: '/profile', component: () => import('./pages/Profile.vue'), meta: { auth: true } },
  { path: '/admin', component: () => import('./pages/Admin.vue'), meta: { auth: true } },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach(async (to) => {
  if (!auth.ready) await auth.hydrate()
  if (to.meta.auth && !auth.token) return '/login'
})

export default router
