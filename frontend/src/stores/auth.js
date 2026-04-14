import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api.js'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || null)
  const user  = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  function setToken(t) {
    token.value = t
    if (t) {
      localStorage.setItem('auth_token', t)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  async function login(email, password) {
    const res = await api.post('/auth/login', { email, password })
    setToken(res.data.token)
    user.value = res.data.user
    return res.data
  }

  async function register(name, email, password, passwordConfirmation) {
    const res = await api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    })
    setToken(res.data.token)
    user.value = res.data.user
    return res.data
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // Ignore errors on logout
    }
    setToken(null)
    user.value = null
  }

  async function fetchMe() {
    try {
      const res = await api.get('/auth/me')
      user.value = res.data
      return res.data
    } catch {
      setToken(null)
      user.value = null
      return null
    }
  }

  async function changePassword(currentPassword, newPassword, newPasswordConfirmation) {
    const res = await api.post('/auth/change-password', {
      current_password:      currentPassword,
      password:              newPassword,
      password_confirmation: newPasswordConfirmation,
    })
    return res.data
  }

  async function regenerateShareToken() {
    const res = await api.post('/auth/regenerate-share-token')
    if (user.value) {
      user.value = { ...user.value, share_token: res.data.share_token }
    }
    return res.data.share_token
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    register,
    logout,
    fetchMe,
    changePassword,
    regenerateShareToken,
  }
})
