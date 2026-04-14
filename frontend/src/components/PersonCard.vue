<template>
  <div
      style="width: 150px"
    class="person-card group relative cursor-pointer select-none"
    :class="isDeceased ? 'opacity-70' : ''"
    @mousedown.stop
    @click="$emit('click', person)"
  >
    <!-- Hover shadow glow -->
    <div
      class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl scale-95"
      :class="isDeceased ? 'bg-gray-300' : isMale ? 'bg-blue-200' : 'bg-pink-200'"
    ></div>

    <!-- Card -->
    <div
      class="relative rounded-2xl border p-4 flex flex-col items-center gap-2.5 w-44 bg-white dark:bg-slate-800/80 shadow-sm group-hover:shadow-lg transition-shadow duration-300"
      style="width: 225px"
      :class="isDeceased
        ? 'border-gray-200 dark:border-slate-600/50'
        : isMale
          ? 'border-blue-200 dark:border-blue-700/50 group-hover:border-blue-300 dark:group-hover:border-blue-600'
          : 'border-pink-200 dark:border-pink-700/50 group-hover:border-pink-300 dark:group-hover:border-pink-600'"
    >
      <!-- Top color bar -->
      <div
        class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl"
        :class="isDeceased ? 'bg-gray-300 dark:bg-slate-600' : isMale ? 'bg-blue-400' : 'bg-pink-400'"
      ></div>

      <!-- Avatar -->
      <div class="relative mt-1">
        <div
          class="w-16 h-16 rounded-full overflow-hidden ring-2 ring-offset-2"
          :class="isDeceased
            ? 'ring-gray-300 dark:ring-slate-600 ring-offset-white dark:ring-offset-slate-800'
            : isMale
              ? 'ring-blue-300 dark:ring-blue-600 ring-offset-white dark:ring-offset-slate-800'
              : 'ring-pink-300 dark:ring-pink-600 ring-offset-white dark:ring-offset-slate-800'"
        >
          <img v-if="person.photo_url" :src="person.photo_url" :alt="person.name" class="w-full h-full object-cover" />
          <div
            v-else
            class="w-full h-full flex items-center justify-center text-lg font-bold"
            :class="isDeceased
              ? 'bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-slate-500'
              : isMale
                ? 'bg-blue-50 dark:bg-blue-900/60 text-blue-500 dark:text-blue-300'
                : 'bg-pink-50 dark:bg-pink-900/60 text-pink-500 dark:text-pink-300'"
          >
            {{ initials }}
          </div>
        </div>
      </div>

      <!-- Name + gender -->
      <div class="text-center w-full">
        <div class="flex items-center justify-center gap-1 mb-1">
          <span
            class="font-semibold leading-tight line-clamp-1"
            :class="isDeceased ? 'text-gray-500 dark:text-slate-500' : 'text-gray-800 dark:text-white'"
          >{{ person.name }}</span>
<!--          <span class="text-sm flex-shrink-0">{{ person.gender === 'male' ? '👨' : '👩' }}</span>-->
        </div>

        <!-- Age -->
        <div v-if="age !== null" class="mb-1">
          <span
            class="text-xs font-semibold px-2 py-0.5 rounded-full"
            :class="isDeceased
              ? 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'
              : isMale
                ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300'
                : 'bg-pink-50 dark:bg-pink-900/50 text-pink-600 dark:text-pink-300'"
          >{{ age }}{{ $t('person.ageShort') }}</span>
        </div>

        <!-- Dates -->
        <div class="text-xs space-y-0.5">
          <div v-if="person.birth_date" class="flex items-center justify-center gap-1">
            <svg class="w-3 h-3 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-gray-400 dark:text-slate-500">{{ formatDate(person.birth_date) }}</span>
            <template v-if="person.death_date">
              <span class="text-gray-300 dark:text-slate-600">—</span>
              <span class="text-gray-400 dark:text-slate-500">{{ formatDate(person.death_date) }}</span>
            </template>
          </div>

          <template v-if="person.death_date ||person.birth_date ">
            <div v-if="!person.death_date" class="flex items-center justify-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
              <span class="text-emerald-500 dark:text-emerald-400 font-medium">{{ $t('person.alive') }}</span>
            </div>
            <div v-else class="text-gray-400 dark:text-slate-500 text-xs">{{ $t('person.deceased') }}</div>
          </template>

        </div>
      </div>

      <!-- Children badge -->
      <div
        v-if="person.children && person.children.length"
        class="absolute -top-2 -right-2 w-5 h-5 rounded-full text-xs font-bold flex items-center justify-center text-white shadow-sm"
        :class="isDeceased ? 'bg-gray-400' : isMale ? 'bg-blue-500' : 'bg-pink-500'"
      >
        {{ person.children.length }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  person: { type: Object, required: true },
})

defineEmits(['click'])

const isMale     = computed(() => props.person.gender === 'male')
const isDeceased = computed(() => !!props.person.death_date)

const initials = computed(() =>
  props.person.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
)

const age = computed(() => {
  if (!props.person.birth_date) return null
  const birth = new Date(props.person.birth_date)
  const end   = props.person.death_date ? new Date(props.person.death_date) : new Date()
  return Math.floor((end - birth) / (365.25 * 24 * 60 * 60 * 1000))
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).getFullYear()
}
</script>
