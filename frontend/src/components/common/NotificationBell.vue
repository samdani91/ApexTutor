<script setup>
import { ref, onMounted } from 'vue'
import { notificationApi } from '@/api/notifications.js'

const unread = ref(0)

onMounted(async () => {
  try {
    const { data } = await notificationApi.getAll()
    unread.value = data.unread
  } catch {}
})

defineExpose({ unread })
</script>
