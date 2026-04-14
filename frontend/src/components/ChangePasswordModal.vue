<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Modal -->
      <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-slate-700/60 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700/60">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $t('auth.changePassword') }}</h2>
          <button
            @click="$emit('close')"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-6">
          <!-- Success message -->
          <div v-if="success" class="mb-4 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/40 text-green-700 dark:text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $t('auth.passwordChanged') }}
          </div>

          <!-- Error message -->
          <div v-if="error" class="mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 text-red-600 dark:text-red-400 text-sm">
            {{ error }}
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.currentPassword') }}</label>
              <input
                v-model="form.currentPassword"
                type="password"
                required
                autocomplete="current-password"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.newPassword') }}</label>
              <input
                v-model="form.newPassword"
                type="password"
                required
                autocomplete="new-password"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">{{ $t('auth.confirmPassword') }}</label>
              <input
                v-model="form.confirmPassword"
                type="password"
                required
                autocomplete="new-password"
                class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-400 dark:focus:border-violet-500 transition-colors text-sm"
              />
            </div>

            <div class="flex gap-3 pt-1">
              <button
                type="button"
                @click="$emit('close')"
                class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 font-medium text-sm transition-colors"
              >
                {{ $t('actions.cancel') }}
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-md shadow-violet-200 dark:shadow-violet-900/40 active:scale-[0.98] transition-all disabled:opacity-60"
              >
                <span v-if="loading" class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  {{ $t('actions.saving') }}
                </span>
                <span v-else>{{ $t('actions.saveChanges') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth.js'

defineEmits(['close'])

const authStore = useAuthStore()
const loading   = ref(false)
const error     = ref('')
const success   = ref(false)

const form = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

async function handleSubmit() {
  error.value   = ''
  success.value = false

  if (form.newPassword !== form.confirmPassword) {
    error.value = 'New passwords do not match'
    return
  }

  loading.value = true
  try {
    await authStore.changePassword(form.currentPassword, form.newPassword, form.confirmPassword)
    success.value = true
    form.currentPassword = ''
    form.newPassword     = ''
    form.confirmPassword = ''
    setTimeout(() => {
      success.value = false
    }, 2000)
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = e.response?.data?.message || 'Failed to change password'
    }
  } finally {
    loading.value = false
  }
}
</script>
