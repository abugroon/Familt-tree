<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Modal -->
      <div
          class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-2xl shadow-black/30 overflow-hidden"
          :class="isDeceased
          ? 'ring-1 ring-gray-200 dark:ring-slate-700'
          : isMale
            ? 'ring-1 ring-blue-200 dark:ring-blue-700/40'
            : 'ring-1 ring-pink-200 dark:ring-pink-700/40'"
      >
        <!-- Top gradient banner -->
        <div class="relative h-28"
             :class="isDeceased
            ? 'bg-gradient-to-br from-slate-300 to-gray-400 dark:from-slate-700 dark:to-slate-600'
            : isMale
              ? 'bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600'
              : 'bg-gradient-to-br from-pink-400 via-rose-400 to-pink-600'"
        >
          <!-- Decorative circles -->
          <div class="absolute -top-6 -end-6 w-32 h-32 rounded-full opacity-20"
               :class="isDeceased ? 'bg-white' : 'bg-white'"></div>
          <div class="absolute top-4 -start-4 w-20 h-20 rounded-full opacity-10"
               :class="isDeceased ? 'bg-white' : 'bg-white'"></div>

          <!-- Close button -->
          <button @click="$emit('close')"
                  class="absolute top-3 end-3 w-8 h-8 rounded-xl bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>

          <!-- Deceased badge -->
          <div v-if="isDeceased" class="absolute top-3 start-3">
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/20 text-white text-xs font-semibold backdrop-blur-sm">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
              {{ $t('person.deceased') }}
            </span>
          </div>

          <!-- Gender badge -->
          <div v-else class="absolute top-3 start-3">
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-sm">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
              {{ isMale ? $t('person.male') : $t('person.female') }}
            </span>
          </div>
        </div>
        <!-- Scrollable content -->
        <div class="px-6 pb-6 max-h-[55vh] overflow-y-auto space-y-4">
          <!-- Avatar — overlapping banner -->
          <div class="flex justify-between items-end mt-5">
            <div
                class="w-24 h-24 rounded-2xl overflow-hidden ring-4 ring-white dark:ring-slate-900 shadow-xl flex-shrink-0"
                :class="isDeceased ? 'ring-gray-100 dark:ring-slate-800' : isMale ? 'ring-blue-50 dark:ring-slate-900' : 'ring-pink-50 dark:ring-slate-900'"
            >
              <img v-if="person.photo_url" :src="person.photo_url" :alt="person.name" class="w-full h-full object-cover"/>
              <div v-else class="w-full h-full flex items-center justify-center text-3xl font-bold"
                   :class="isDeceased
                ? 'bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-slate-500'
                : isMale
                  ? 'bg-blue-50 dark:bg-blue-900/40 text-blue-500 dark:text-blue-300'
                  : 'bg-pink-50 dark:bg-pink-900/40 text-pink-500 dark:text-pink-300'"
              >{{ initials }}
              </div>
            </div>

            <!-- Age pill (top right of content area) -->
            <div v-if="age !== null" class="mb-2"
                 :class="isDeceased
              ? 'text-gray-500 dark:text-slate-400'
              : isMale ? 'text-blue-600 dark:text-blue-400' : 'text-pink-600 dark:text-pink-400'"
            >
              <div class="flex flex-col items-end">
                <span class="text-3xl font-black leading-none">{{ age }}</span>
                <span class="text-xs font-semibold opacity-70 leading-none mt-0.5">{{
                    isDeceased ? $t('person.ageAtDeath') : $t('person.currentAge')
                  }}</span>
              </div>
            </div>
          </div>

          <!-- Name -->
          <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight">{{ person.name }}</h2>
            <p class="text-sm text-gray-400 dark:text-slate-500 mt-0.5">
              {{ person.gender === 'male' ? '👨 ' + $t('person.male') : '👩 ' + $t('person.female') }}
            </p>
          </div>

          <!-- Description -->
          <div v-if="person.description">
            <p class="text-sm text-gray-400 dark:text-slate-500 mt-0.5">
              وصف او ملاحظات
            </p>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight">{{ person.description }}</h2>
          </div>

          <!-- Date stats -->
          <div class="grid grid-cols-2 gap-3">
            <!-- Born -->
            <div class="flex items-start gap-3 p-3 rounded-2xl"
                 :class="isDeceased
                ? 'bg-gray-50 dark:bg-slate-800/50'
                : isMale ? 'bg-blue-50/60 dark:bg-blue-900/20' : 'bg-pink-50/60 dark:bg-pink-900/20'"
            >
              <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                   :class="isDeceased ? 'bg-gray-200 dark:bg-slate-700' : isMale ? 'bg-blue-100 dark:bg-blue-800/60' : 'bg-pink-100 dark:bg-pink-800/60'"
              >
                <svg class="w-4 h-4"
                     :class="isDeceased ? 'text-gray-500 dark:text-slate-400' : isMale ? 'text-blue-600 dark:text-blue-300' : 'text-pink-600 dark:text-pink-300'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <div class="text-xs text-gray-400 dark:text-slate-500 font-medium mb-0.5">{{ $t('person.born') }}</div>
                <div class="text-sm font-bold text-gray-800 dark:text-slate-200">
                  {{ person.birth_date ? formatFullDate(person.birth_date) : '—' }}
                </div>
              </div>
            </div>

            <!-- Died / Status -->
            <div class="flex items-start gap-3 p-3 rounded-2xl"
                 :class="isDeceased ? 'bg-gray-50 dark:bg-slate-800/50' : 'bg-emerald-50/60 dark:bg-emerald-900/10'"
            >
              <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                   :class="isDeceased ? 'bg-gray-200 dark:bg-slate-700' : 'bg-emerald-100 dark:bg-emerald-800/40'"
              >
                <svg v-if="isDeceased" class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg v-else class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
              </div>
              <div>
                <div class="text-xs text-gray-400 dark:text-slate-500 font-medium mb-0.5">
                  {{ isDeceased ? $t('person.died') : $t('person.status') }}
                </div>
                <div v-if="isDeceased" class="text-sm font-bold text-gray-700 dark:text-slate-300">
                  {{ formatFullDate(person.death_date) }}
                </div>
                <div v-else class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                  <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $t('person.alive') }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Parent -->
          <div v-if="person.parent"
               class="flex items-center gap-3 p-3 rounded-2xl bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800/30"
          >
            <div
                class="w-8 h-8 rounded-xl bg-violet-100 dark:bg-violet-800/40 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor"
                   viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <div>
              <div class="text-xs text-violet-400 dark:text-violet-500 font-medium mb-0.5">{{
                  $t('person.parent')
                }}
              </div>
              <div class="text-sm font-bold text-violet-700 dark:text-violet-300">{{ person.parent.name }}</div>
            </div>
          </div>

          <!-- Children -->
          <div v-if="person.children && person.children.length">
            <div class="flex items-center justify-between mb-2.5">
              <span class="text-sm font-bold text-gray-700 dark:text-slate-300">{{ $t('person.children') }}</span>
              <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                    :class="isMale ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300' : 'bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-300'"
              >{{ person.children.length }}</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div
                  v-for="child in person.children" :key="child.id"
                  class="flex items-center gap-2 px-3 py-2 rounded-xl border text-sm font-medium"
                  :class="child.gender === 'male'
                  ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800/40 text-blue-700 dark:text-blue-300'
                  : 'bg-pink-50 dark:bg-pink-900/20 border-pink-100 dark:border-pink-800/40 text-pink-700 dark:text-pink-300'"
              >
                <span class="text-base leading-none">{{ child.gender === 'male' ? '👨' : '👩' }}</span>
                <span class="truncate">{{ child.name.split(' ')[0] }}</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Footer actions -->
        <div
            class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-700/50 flex gap-3">
          <button
              @click="openEdit"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white text-sm font-semibold transition-all active:scale-95 shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ $t('actions.edit') }}
          </button>
          <button
              @click="confirmDelete"
              class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800/50 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-semibold transition-all active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            {{ $t('actions.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete confirmation dialog -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 z-60 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteConfirm = false"></div>
      <div
          class="relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 p-6 max-w-xs w-full shadow-2xl">
        <div class="text-center mb-5">
          <div
              class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.072 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <h3 class="text-gray-900 dark:text-white font-bold text-lg mb-1">
            {{ $t('actions.confirmDelete', {name: person.name}) }}</h3>
          <p class="text-gray-400 dark:text-slate-500 text-sm">{{ $t('actions.undoWarning') }}</p>
        </div>
        <div class="flex gap-3">
          <button @click="showDeleteConfirm = false"
                  class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 text-sm font-semibold hover:bg-gray-200 dark:hover:bg-slate-700">
            {{ $t('actions.cancel') }}
          </button>
          <button @click="deletePerson" :disabled="deleting"
                  class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-semibold disabled:opacity-50 transition-colors">
            {{ deleting ? $t('actions.deleting') : $t('actions.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Edit modal -->
    <AddPersonModal
        v-if="showEditModal"
        :people="people"
        :edit-person="person"
        @close="showEditModal = false"
        @saved="onEditSaved"
    />
  </Teleport>
</template>

<script setup>
import {ref, computed} from 'vue'
import {usePeopleStore} from '@/stores/people'
import AddPersonModal from './AddPersonModal.vue'

const props = defineProps({
  person: {type: Object, required: true},
  people: {type: Array, default: () => []},
})

const emit = defineEmits(['close', 'deleted', 'edit', 'view-person'])
const store = usePeopleStore()
const showDeleteConfirm = ref(false)
const showEditModal = ref(false)
const deleting = ref(false)

const isMale = computed(() => props.person.gender === 'male')
const isDeceased = computed(() => !!props.person.death_date)

const initials = computed(() =>
    props.person.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
)

const age = computed(() => {
  if (!props.person.birth_date) return null
  const birth = new Date(props.person.birth_date)
  const end = props.person.death_date ? new Date(props.person.death_date) : new Date()
  return Math.floor((end - birth) / (365.25 * 24 * 60 * 60 * 1000))
})

function formatFullDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})
}

function openEdit() {
  showEditModal.value = true
}

function onEditSaved() {
  showEditModal.value = false
  emit('close')
}

function confirmDelete() {
  showDeleteConfirm.value = true
}

async function deletePerson() {
  deleting.value = true
  try {
    await store.deletePerson(props.person.id)
    showDeleteConfirm.value = false
    emit('deleted')
  } finally {
    deleting.value = false
  }
}
</script>
