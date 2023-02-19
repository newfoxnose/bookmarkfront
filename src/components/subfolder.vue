<script setup>

import bookmarkitem from './bookmarkitem.vue'

defineProps({
  sub_folder_name: {
    type: String,
    required: false
  },
  sub_folder_bookmark: {
    type: Array,
    required: false
  }
})


</script>


<template>
  
    <h3>{{ sub_folder_name }}</h3>
    <bookmarkitem v-for="bookmarkitem in sub_folder_bookmark" :url="bookmarkitem.url" :title="bookmarkitem.title"
      :icon="bookmarkitem.icon" />

</template>
