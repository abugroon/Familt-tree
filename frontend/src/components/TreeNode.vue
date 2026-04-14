<template>
  <div class="tree-node flex flex-col items-center">
    <!-- Person card wrapper — data-person-id used by FamilyTree to find card positions -->
    <div ref="cardRef" :data-person-id="node.id" class="relative">
      <PersonCard
        :person="node"
        @click="$emit('person-click', node)"
      />

      <!-- Expand / collapse toggle -->
      <button
        v-if="node.children && node.children.length"
        @click.stop="toggleCollapse"
        class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full border-2 border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center justify-center z-10 shadow-md"
        :title="collapsed ? $t('actions.expand') : $t('actions.collapse')"
      >
        <svg
          class="w-3 h-3 text-gray-500 dark:text-slate-400 transition-transform duration-300"
          :class="{ 'rotate-180': !collapsed }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Children -->
    <Transition name="tree-expand">
      <div
        v-if="!collapsed && node.children && node.children.length"
        class="flex items-start gap-8 mt-12 pt-4"
      >
        <TreeNode
          v-for="child in node.children"
          :key="child.id"
          :node="child"
          :depth="depth + 1"
          @person-click="$emit('person-click', $event)"
          @collapse-toggle="$emit('collapse-toggle')"
        />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PersonCard from './PersonCard.vue'

const props = defineProps({
  node: { type: Object, required: true },
  depth: { type: Number, default: 0 },
})

defineEmits(['person-click', 'collapse-toggle'])

const cardRef = ref(null)
const collapsed = ref(false)

function toggleCollapse() {
  collapsed.value = !collapsed.value
}
</script>

<style scoped>
.tree-expand-enter-active,
.tree-expand-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}
.tree-expand-enter-from,
.tree-expand-leave-to {
  opacity: 0;
  transform: scaleY(0.8) translateY(-16px);
  transform-origin: top center;
}
</style>
