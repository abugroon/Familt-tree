import { ref } from 'vue'

const pendingFocusId = ref(null)

export function useFocusNode() {
  function focusNode(id) {
    pendingFocusId.value = id
  }
  function clearFocus() {
    pendingFocusId.value = null
  }
  return { pendingFocusId, focusNode, clearFocus }
}
