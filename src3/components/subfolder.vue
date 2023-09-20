<script setup>
//import bookmarkitem from './bookmarkitem.vue';     //不能再导入组件了，netlify不支持3层，本地可以
import { EyeInvisibleTwoTone,AppstoreTwoTone,LikeTwoTone ,ApiTwoTone } from '@ant-design/icons-vue';
import { onMounted, ref } from "vue";
const subFolderRefs = ref([]);
defineProps({
  search: {
    type: String,
    required: false
  },
  folder_name: {
    type: String,
    required: false
  },
  display_offset: {
    type: Number,
    required: false
  },
  folder_id: {
    type: String,
    required: true
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
onMounted(() => {
  //console.log(subFolderRefs.value);
});
</script >
<script>

export default {
  components: {
    EyeInvisibleTwoTone,AppstoreTwoTone,LikeTwoTone ,ApiTwoTone
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
    },
    showTitle: function (refs) {
      if (this.$func.getVarType(refs) == "HTMLDivElement") {
      let str=refs.innerHTML
      if (str.toUpperCase().includes(this.search.toUpperCase())) {
        return true
      } else {
        return false
      }
    }
    },
  },
};

</script>
<template>
  <div ref="subFolderRefs" v-show="showTitle(subFolderRefs)" :folderid=folder_id :display_offset="display_offset" :style="{ 'margin-left': display_offset*15 + 'px' }">
    <h3>{{ folder_name }}</h3>
    <div class="item" v-show="isShow(bookmarkitem.title, bookmarkitem.url)" v-for="bookmarkitem in folder_bookmark" :itemid=bookmarkitem.id>
      <img v-if="editable == 'yes'" :src="bookmarkitem.icon_display" style="width:16px;height:16px;margin-right:3px;"
        @click="fatherMethod('编辑书签', bookmarkitem.id, bookmarkitem.url, bookmarkitem.title, bookmarkitem.folder_id, bookmarkitem.is_private,bookmarkitem.is_published,bookmarkitem.is_recommend,bookmarkitem.is_friendlink)">
      <img v-else :src="bookmarkitem.icon_display" style="width:16px;height:16px;"
        @click="fatherMethod('编辑书签', bookmarkitem.id, bookmarkitem.url, bookmarkitem.title, bookmarkitem.folder_id, bookmarkitem.is_private,bookmarkitem.is_published,bookmarkitem.is_recommend,bookmarkitem.is_friendlink)">
      <a :href="bookmarkitem.url" :title="bookmarkitem.title" target="_blank">
        {{ bookmarkitem.short_title }}
      </a>
      <eye-invisible-two-tone v-if="bookmarkitem.is_private == '1'" style="margin-left:3px;" />
      <RouterLink :to="'/editpost/'+bookmarkitem.id" v-if="bookmarkitem.is_published == '1'"><appstore-two-tone style="margin-left:3px;" /></RouterLink>
       <like-two-tone  v-if="bookmarkitem.is_recommend == '1'" style="margin-left:3px;" />
    <api-two-tone  v-if="bookmarkitem.is_friendlink == '1'" style="margin-left:3px;" />
      <span class="http_code" v-if="bookmarkitem.http_code != 200 && bookmarkitem.http_code != ''">{{ bookmarkitem.http_code }}</span>
    </div>
  </div>
  <div v-for="item in subfolder">
    <subfolder :folder_name="item.folder_name" :folder_id="item.id" :folder_bookmark="item.bookmarks" :subfolder="item.subfolder"
      :search="search" :editable="editable" :fatherMethod="fatherMethod" :display_offset="item.display_offset">
    </subfolder>
  </div>
</template>


<style scoped>
.http_code {
  transform: rotate(-5deg) ;
  display:inline-block;
  margin-left:5px;
  font-weight:bold;
  color:red;
  text-decoration: underline;
}
.item {
  margin-top: 2rem;
  width: 320px;
  display: inline-block;
}


@media (min-width: 1024px) {
  .item {
    margin-top: 0;
    padding: 0.4rem 0 1rem 2rem;
  }
}
</style>
