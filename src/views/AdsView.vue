<template>
  <div class="ads-container">
    <pre>{{ adsContent }}</pre>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'AdsView',
  setup() {
    const adsContent = ref('')

    onMounted(async () => {
      try {
        const response = await fetch('/ads.txt')
        adsContent.value = await response.text()
      } catch (error) {
        console.error('Error loading ads.txt:', error)
        adsContent.value = 'Error loading ads.txt'
      }
    })

    return {
      adsContent
    }
  }
}
</script>

<style scoped>
.ads-container {
  padding: 20px;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style> 