import { ref } from 'vue'

/**
 * Manages a single pending-confirmation state for use with ConfirmDialog / AdminConfirmDialog.
 *
 * Usage:
 *   const { pending, request, cancel, confirm } = useConfirmAction()
 *
 *   // Open dialog with arbitrary data payload
 *   function onDeleteClick(item) { request(item) }
 *
 *   // In template: <ConfirmDialog :show="!!pending" @confirm="confirm(doDelete)" @cancel="cancel" />
 *
 *   async function doDelete(item) { await api.delete(item.id) }
 */
export function useConfirmAction() {
  const pending = ref(null)

  function request(data = true) {
    pending.value = data
  }

  function cancel() {
    pending.value = null
  }

  async function confirm(fn) {
    const data = pending.value
    pending.value = null
    if (typeof fn === 'function') {
      await fn(data)
    }
  }

  return { pending, request, cancel, confirm }
}
