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
//import bookmarkitem from './bookmarkitem.vue';
import { EyeInvisibleTwoTone } from '@ant-design/icons-vue';
export default {
  components: {
    EyeInvisibleTwoTone,
    //bookmarkitem
  },
  methods: {
    isShow: function (str, url) {
      if (str.toUpperCase().includes(this.search.toUpperCase()) || url.toUpperCase().includes(this.search.toUpperCase())) {
        return true
      } else {
        return false
      }
    }
  },
};
</script>
<template>
  <h3>{{ folder_name }}</h3>
  <div v-for="bookmarkitem in folder_bookmark">
    <div class="item" v-show="isShow(bookmarkitem.title, bookmarkitem.url)">
      <img v-if="bookmarkitem.editable == 'yes'" :src="bookmarkitem.icon" style="width:16px;height:16px;margin-right:3px;"
        @click="$emit('editbookmark', '编辑书签', id, url, title, folder_id, is_private)">
      <img v-else :src="bookmarkitem.icon" style="width:16px;height:16px;">
      <a :href="bookmarkitem.url" :title="bookmarkitem.title" target="_blank">
        {{ bookmarkitem.short_title }}
      </a>
      <eye-invisible-two-tone v-if="bookmarkitem.is_private == '1'" style="margin-left:3px;" />
    </div>
  </div>

  <div v-for="item in subfolder">
    <subfolder :folder_name="item.folder_name" :folder_bookmark="item.bookmarks" :subfolder="item.subfolder"
      :search="search" :editable="editable" :fatherMethod="fatherMethod">
    </subfolder>
  </div>
</template>
