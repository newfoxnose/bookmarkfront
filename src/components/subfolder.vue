<script setup>
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
//import bookmarkitem from './bookmarkitem.vue';     //不能再导入组件了，netlify不支持3层，本地可以
import { EyeInvisibleTwoTone } from '@ant-design/icons-vue';
export default {
  components: {
    EyeInvisibleTwoTone,
    //bookmarkitem
  },
  emits: ['fatherMethod'],
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
    <div class="item" v-show="isShow(bookmarkitem.title, bookmarkitem.url)" v-for="bookmarkitem in folder_bookmark">
      <img v-if="editable == 'yes'" :src="bookmarkitem.icon_display" style="width:16px;height:16px;margin-right:3px;" @click="fatherMethod('编辑书签', bookmarkitem.id, bookmarkitem.url, bookmarkitem.title, bookmarkitem.folder_id, bookmarkitem.is_private)">
      <img v-else :src="bookmarkitem.icon_display" style="width:16px;height:16px;" @click="fatherMethod('编辑书签', bookmarkitem.id, bookmarkitem.url, bookmarkitem.title, bookmarkitem.folder_id, bookmarkitem.is_private)">
      <a :href="bookmarkitem.url" :title="bookmarkitem.title" target="_blank">
        {{ bookmarkitem.short_title }}
      </a>
      <eye-invisible-two-tone v-if="bookmarkitem.is_private == '1'" style="margin-left:3px;" />
    </div>

  <div v-for="item in subfolder">
    <subfolder :folder_name="item.folder_name" :folder_bookmark="item.bookmarks" :subfolder="item.subfolder"
      :search="search" :editable="editable"  :fatherMethod="fatherMethod">
    </subfolder>
  </div>
</template>


<style scoped>
.item {
  margin-top: 2rem;
  width: 300px;
  display: inline-block;
}


@media (min-width: 1024px) {
  .item {
    margin-top: 0;
    padding: 0.4rem 0 1rem calc(var(--section-gap) / 2);
  }
}
</style>
