<template>
  <div class="min-h-screen bg-gradient-to-br from-violet-50 via-indigo-50 to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 flex items-center justify-center p-4">
    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-violet-400/10 dark:bg-violet-500/5 blur-3xl"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-indigo-400/10 dark:bg-indigo-500/5 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 shadow-xl shadow-violet-200 dark:shadow-violet-900/40 mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('app.title') }}</h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ $t('app.subtitle') }}</p>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-gray-200/60 dark:shadow-slate-900/60 border border-gray-100 dark:border-slate-700/60 overflow-hidden">
        <!-- Tabs -->
        <div class="flex border-b border-gray-100 dark:border-slate-700/60">
          <button
            @click="activeTab = 'login'"
            class="flex-1 py-4 text-sm font-semibold transition-colors"
            :class="activeTab === 'login'
              ? 'text-violet-600 dark:text-violet-400 border-b-2 border-violet-500 bg-violet-50/50 dark:bg-violet-900/10'
              : 'text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300'"
          >
            {{ $t('auth.login') }}
          </button>
          <button
            @click="activeTab = 'register'"
            class="flex-1 py-4 text-sm font-semibold transition-colors"
            :class="activeTab === 'register'
              ? 'text-violet-600 dark:text-violet-400 border-b-2 border-violet-500 bg-violet-50/50 dark:bg-violet-900/10'
              : 'text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300'"
          >
            {{ $t('auth.register') }}
          </button>
        </div>

        <div class="p-6">
          <!-- Error message -->
          <div v-if="error" class="mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 text-red-600 dark:text-red-400 text-sm">
            {{ error }}
          </div>

          <!-- Login form -->
          <form v-if="activeTab === 'login'" @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.email') }}</label>
              <input
                v-model="loginForm.email"
                type="email"
                required
                autocomplete="email"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
                :placeholder="$t('auth.email')"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.password') }}</label>
              <input
                v-model="loginForm.password"
                type="password"
                required
                autocomplete="current-password"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
                :placeholder="$t('auth.password')"
              />
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-md shadow-violet-200 dark:shadow-violet-900/40 active:scale-[0.98] transition-all disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ $t('actions.saving') }}
              </span>
              <span v-else>{{ $t('auth.login') }}</span>
            </button>
            <p class="text-center text-xs text-gray-500 dark:text-slate-400">
              {{ $t('auth.noAccount') }}
              <button type="button" @click="activeTab = 'register'" class="text-violet-600 dark:text-violet-400 font-medium hover:underline">
                {{ $t('auth.register') }}
              </button>
            </p>
          </form>

          <!-- Register form -->
          <form v-else @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.fullName') }}</label>
              <input
                v-model="registerForm.name"
                type="text"
                required
                autocomplete="name"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
                :placeholder="$t('auth.fullName')"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.email') }}</label>
              <input
                v-model="registerForm.email"
                type="email"
                required
                autocomplete="email"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
                :placeholder="$t('auth.email')"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.password') }}</label>
              <input
                v-model="registerForm.password"
                type="password"
                required
                autocomplete="new-password"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
                :placeholder="$t('auth.password')"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.confirmPassword') }}</label>
              <input
                v-model="registerForm.passwordConfirmation"
                type="password"
                required
                autocomplete="new-password"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
                :placeholder="$t('auth.confirmPassword')"
              />
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-md shadow-violet-200 dark:shadow-violet-900/40 active:scale-[0.98] transition-all disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ $t('actions.saving') }}
              </span>
              <span v-else>{{ $t('auth.register') }}</span>
            </button>
            <p class="text-center text-xs text-gray-500 dark:text-slate-400">
              {{ $t('auth.hasAccount') }}
              <button type="button" @click="activeTab = 'login'" class="text-violet-600 dark:text-violet-400 font-medium hover:underline">
                {{ $t('auth.login') }}
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useTheme } from '@/composables/useTheme.js'

const router    = useRouter()
const authStore = useAuthStore()
const { init: initTheme } = useTheme()

initTheme()

const activeTab = ref('login')
const loading   = ref(false)
const error     = ref('')

const loginForm = reactive({ email: '', password: '' })
const registerForm = reactive({ name: '', email: '', password: '', passwordConfirmation: '' })

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await authStore.login(loginForm.email, loginForm.password)
    router.push({ name: 'app' })
  } catch (e) {
    error.value = e.response?.data?.message || e.response?.data?.errors?.email?.[0] || 'Login failed'
  } finally {
    loading.value = false
  }
}

async function handleRegister() {
  error.value = ''
  if (registerForm.password !== registerForm.passwordConfirmation) {
    error.value = 'Passwords do not match'
    return
  }
  loading.value = true
  try {
    await authStore.register(
      registerForm.name,
      registerForm.email,
      registerForm.password,
      registerForm.passwordConfirmation
    )
    router.push({ name: 'app' })
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = e.response?.data?.message || 'Registration failed'
    }
  } finally {
    loading.value = false
  }
}
</script>
