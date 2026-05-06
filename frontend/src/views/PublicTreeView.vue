<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700/60 shadow-sm">
      <div class="max-w-screen-2xl mx-auto px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-md">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        </div>
        <div>
          <h1 class="text-base font-bold text-gray-900 dark:text-white">{{ $t('app.title') }}</h1>
          <p v-if="owner" class="text-xs text-gray-400 dark:text-slate-500">{{ owner }}</p>
        </div>
        <div class="ms-auto">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800/40 text-violet-600 dark:text-violet-400 text-xs font-medium">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            عرض للقراءة فقط
          </span>
        </div>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center h-[80vh] gap-4">
      <div class="w-12 h-12 rounded-full border-4 border-violet-200 dark:border-violet-800 border-t-violet-500 animate-spin"></div>
      <p class="text-gray-400 dark:text-slate-500 text-sm">{{ $t('app.loading') }}</p>
    </div>

    <!-- Not found -->
    <div v-else-if="notFound" class="flex flex-col items-center justify-center h-[80vh] gap-4">
      <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 flex items-center justify-center">
        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.072 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
      </div>
      <p class="text-red-500 font-medium">رابط المشاركة غير صالح</p>
    </div>

    <!-- Tree -->
    <div v-else-if="trees.length" class="tree-canvas-height">
      <FamilyTree :trees="trees" @person-click="openDetails" />
    </div>

    <!-- Person details (read-only) -->
    <PersonDetailsModal
      v-if="selectedPerson"
      :person="selectedPerson"
      :people="allPeople"
      @close="selectedPerson = null"
      @marriage-changed="() => {}"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import api from '@/api.js'
import FamilyTree from '@/components/FamilyTree.vue'
import PersonDetailsModal from '@/components/PersonDetailsModal.vue'

const route = useRoute()
const { init: initTheme } = useTheme()

const loading        = ref(true)
const notFound       = ref(false)
const owner          = ref('')
const trees          = ref([])
const selectedPerson = ref(null)

const allPeople = computed(() => {
  const flat = []
  function walk(node) {
    flat.push(node)
    if (node.spouse) flat.push(node.spouse)
    node.children?.forEach(walk)
  }
  trees.value.forEach(walk)
  return flat
})

function openDetails(person) {
  selectedPerson.value = person
}

onMounted(async () => {
  initTheme()
  try {
    const res    = await api.get(`/share/${route.params.token}/tree`)
    owner.value  = res.data.owner
    trees.value  = res.data.trees
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.tree-canvas-height { height: calc(100dvh - 57px); }
</style>
