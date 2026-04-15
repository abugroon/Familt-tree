import { ref } from 'vue'

// Shared singleton state — same refs reused across all components
const showAddModal     = ref(false)
const addChildParentId = ref(null)

export function useAddModal() {
  function openAddModal(parentId = null) {
    addChildParentId.value = parentId ?? null
    showAddModal.value = true
  }

  function closeAddModal() {
    showAddModal.value = false
    addChildParentId.value = null
  }

  return { showAddModal, addChildParentId, openAddModal, closeAddModal }
}
