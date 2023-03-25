<script setup>
defineEmits(['fatherMethod'])
defineProps({
  search: {
    type: String,
    required: false
  },
  folder_name: {
    type: String,
    required: false
  },
  folder_bookmark: {
    type: Array,
    required: false
  },
  subfolder: {
    type: Array,
    required: false
  },
  editable: {
    type: String,
    required: true
  },
  fatherMethod: {
    type: Function,
    required: false
  }
})
</script >
<script>
import bookmarkitem from './bookmarkitem.vue';

export default {
  components: {
    bookmarkitem
  }
};
</script>
<template>
  <h3>{{ folder_name }}</h3>
  <div>
    <bookmarkitem v-for="bookmarkitem in folder_bookmark" :id="bookmarkitem.id" :folder_id="bookmarkitem.folder_id"
      :url="bookmarkitem.url" :title="bookmarkitem.title" :icon="bookmarkitem.icon_display"
      :short_title="bookmarkitem.short_title" :is_private="bookmarkitem.is_private" :search="search" :editable="editable"
      @editbookmark="fatherMethod">
    </bookmarkitem>

  </div>
  <div v-for="item in subfolder">
    <subfolder :folder_name="item.folder_name" :folder_bookmark="item.bookmarks" :subfolder="item.subfolder"
      :search="search" :editable="editable" :fatherMethod="fatherMethod">
    </subfolder>
  </div></template>
