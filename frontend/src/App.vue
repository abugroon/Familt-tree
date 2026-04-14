<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700/60 shadow-sm">
      <div class="max-w-screen-2xl mx-auto px-6 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-md">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
          <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white leading-none">{{ $t('app.title') }}</h1>
            <p class="text-xs text-gray-400 dark:text-slate-500 leading-none mt-0.5">{{ $t('app.subtitle') }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Members count -->
          <div v-if="store.allPeople.length" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-slate-700">
            <svg class="w-3.5 h-3.5 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
            </svg>
            <span class="text-xs text-gray-600 dark:text-slate-300 font-medium">{{ $t('app.members', { n: store.allPeople.length }) }}</span>
          </div>

          <!-- Theme toggle -->
          <button
            @click="themeToggle"
            class="w-9 h-9 rounded-xl flex items-center justify-center border transition-all active:scale-95
              bg-gray-100 dark:bg-slate-800 border-gray-200 dark:border-slate-700
              text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white
              hover:bg-gray-200 dark:hover:bg-slate-700"
            :title="isDark ? $t('app.lightMode') : $t('app.darkMode')"
          >
            <!-- Sun (shown in dark mode) -->
            <svg v-if="isDark" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <!-- Moon (shown in light mode) -->
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Language toggle -->
          <button
            @click="toggleLocale"
            class="w-9 h-9 rounded-xl flex items-center justify-center border transition-all active:scale-95
              bg-gray-100 dark:bg-slate-800 border-gray-200 dark:border-slate-700
              text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700
              text-xs font-bold"
            :title="locale === 'ar' ? 'English' : 'عربي'"
          >
            {{ locale === 'ar' ? 'EN' : 'ع' }}
          </button>

          <!-- Add Person -->
          <button
            @click="showAddModal = true"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-semibold shadow-md shadow-violet-200 dark:shadow-violet-900/40 hover:shadow-violet-300 active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            {{ $t('actions.addPersonTitle') }}
          </button>
        </div>
      </div>
    </header>

    <!-- Main -->
    <main class="relative">
      <!-- Loading -->
      <div v-if="store.loading" class="flex flex-col items-center justify-center h-[80vh] gap-4">
        <div class="w-12 h-12 rounded-full border-4 border-violet-200 dark:border-violet-800 border-t-violet-500 animate-spin"></div>
        <p class="text-gray-400 dark:text-slate-500 text-sm">{{ $t('app.loading') }}</p>
      </div>

      <!-- Error -->
      <div v-else-if="store.error" class="flex flex-col items-center justify-center h-[80vh] gap-4">
        <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 flex items-center justify-center">
          <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.072 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <p class="text-red-500 font-medium">{{ $t('app.loadError') }}</p>
        <button @click="store.fetchTrees()" class="px-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 text-sm shadow-sm">
          {{ $t('app.tryAgain') }}
        </button>
      </div>

      <!-- Empty state -->
      <div v-else-if="!store.trees.length" class="flex flex-col items-center justify-center h-[80vh] gap-6">
        <div class="w-24 h-24 rounded-3xl bg-white dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700/50 flex items-center justify-center shadow-sm">
          <svg class="w-12 h-12 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <div class="text-center">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">{{ $t('app.noMembers') }}</h2>
          <p class="text-gray-400 dark:text-slate-500 text-sm">{{ $t('app.noMembersDesc') }}</p>
        </div>
        <button
          @click="showAddModal = true"
          class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold shadow-lg shadow-violet-200 dark:shadow-violet-900/40"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          {{ $t('app.addFirstMember') }}
        </button>
      </div>

      <!-- Tree View -->
      <div v-else style="height: calc(100vh - 65px);">
        <FamilyTree
          :trees="store.trees"
          @person-click="openPersonDetails"
        />
      </div>
    </main>

    <!-- Floating Add Button (mobile) -->
    <button
      v-if="store.trees.length"
      @click="showAddModal = true"
      class="fixed bottom-6 right-6 w-14 h-14 rounded-full bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white shadow-xl shadow-violet-300 flex items-center justify-center sm:hidden active:scale-90"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
      </svg>
    </button>

    <!-- Modals -->
    <AddPersonModal
      v-if="showAddModal"
      :people="store.allPeople"
      @close="showAddModal = false"
      @saved="onPersonSaved"
    />

    <PersonDetailsModal
      v-if="selectedPerson"
      :person="selectedPerson"
      :people="store.allPeople"
      @close="selectedPerson = null"
      @edit="onEditPerson"
      @deleted="onPersonDeleted"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePeopleStore } from '@/stores/people'
import { useTheme } from '@/composables/useTheme'
import { applyLocaleDir } from '@/i18n/index.js'
import FamilyTree from '@/components/FamilyTree.vue'
import AddPersonModal from '@/components/AddPersonModal.vue'
import PersonDetailsModal from '@/components/PersonDetailsModal.vue'

const store = usePeopleStore()
const showAddModal = ref(false)
const selectedPerson = ref(null)
const { isDark, toggle: themeToggle, init: initTheme } = useTheme()
const { locale } = useI18n()

function toggleLocale() {
  locale.value = locale.value === 'ar' ? 'en' : 'ar'
  localStorage.setItem('locale', locale.value)
  applyLocaleDir(locale.value)
}

onMounted(async () => {
  initTheme()
  await Promise.all([store.fetchTrees(), store.fetchAllPeople()])
})

function openPersonDetails(person) {
  selectedPerson.value = person
}

async function onPersonSaved() {
  showAddModal.value = false
}

function onEditPerson(person) {
  selectedPerson.value = null
  // Re-open add modal pre-filled — handled inside PersonDetailsModal
}

async function onPersonDeleted() {
  selectedPerson.value = null
}
</script>
