<template>
  <div class="relative bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700/60 shadow-sm z-30">
    <div class="flex items-center gap-1 px-4 py-2 overflow-x-auto no-scrollbar">

      <!-- All Families chip -->
      <button
        @click="$emit('select', null)"
        class="flex-shrink-0 flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all border"
        :class="selected === null
          ? 'bg-violet-600 text-white border-violet-600 shadow-md shadow-violet-200 dark:shadow-violet-900/40'
          : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'"
      >
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
        </svg>
        {{ $t('nav.allFamilies') }}
      </button>

      <!-- Divider -->
      <div class="w-px h-6 bg-gray-200 dark:bg-slate-700 flex-shrink-0 mx-1"></div>

      <!-- Root person chips -->
      <button
        v-for="root in roots"
        :key="root.id"
        @click="$emit('select', root.id)"
        class="flex-shrink-0 flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium transition-all border group"
        :class="selected === root.id
          ? root.gender === 'male'
            ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200 dark:shadow-blue-900/40'
            : 'bg-pink-600 text-white border-pink-600 shadow-md shadow-pink-200 dark:shadow-pink-900/40'
          : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'"
      >
        <!-- Avatar circle -->
        <div
          class="w-6 h-6 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center text-xs font-bold"
          :class="selected === root.id
            ? 'bg-white/20 text-white'
            : root.gender === 'male'
              ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300'
              : 'bg-pink-100 dark:bg-pink-900/50 text-pink-600 dark:text-pink-300'"
        >
          <img v-if="root.photo_url" :src="root.photo_url" class="w-full h-full object-cover" />
          <span v-else>{{ root.name.charAt(0) }}</span>
        </div>

        <!-- Name -->
        <span class="max-w-[120px] truncate">{{ root.name }}</span>

        <!-- Children count -->
        <span
          v-if="root.children && root.children.length"
          class="text-xs px-1.5 py-0.5 rounded-full"
          :class="selected === root.id
            ? 'bg-white/20 text-white'
            : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'"
        >{{ root.children.length }}</span>
      </button>

    </div>

    <!-- Scroll fade edges -->
    <div class="pointer-events-none absolute top-0 end-0 h-full w-8 bg-gradient-to-s from-white dark:from-slate-900"></div>
  </div>
</template>

<script setup>
defineProps({
  roots:    { type: Array,  default: () => [] },
  selected: { type: Number, default: null },
})
defineEmits(['select'])
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
