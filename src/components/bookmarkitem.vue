<script>
import {  EyeInvisibleTwoTone } from '@ant-design/icons-vue';
export default {
  components: {
    EyeInvisibleTwoTone
  },
  props: ['title', 'icon', 'id','url', 'short_title', 'search', 'editable','folder_id','is_private'],
  emits: ['editbookmark'],
  methods: {
    isShow: function (str, url) {
      if (str.toUpperCase().includes(this.search.toUpperCase()) || url.toUpperCase().includes(this.search.toUpperCase())) {
        return true
      } else {
        return false
      }
    }
  },
}
</script>
<template>
  <!--<div class="item" v-show="isShow(title, url)">-->
    <hr>
  <div class="item">
    <img v-if="editable == 'yes'" :src="icon" style="width:16px;height:16px;margin-right:3px;" @click="$emit('editbookmark','编辑书签',id,url,title,folder_id,is_private)">
    <img v-else :src="icon" style="width:16px;height:16px;">
    <a :href="url" :title="title" target="_blank">
      {{ short_title }}
    </a>
    <eye-invisible-two-tone v-if="is_private == '1'" style="margin-left:3px;"/>
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
