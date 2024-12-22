<script setup>
//import bookmarkitem from './bookmarkitem.vue';     //不能再导入组件了，netlify不支持3层，本地可以
import {
  EyeInvisibleTwoTone,
  AppstoreTwoTone,
  LikeTwoTone,
  ApiTwoTone,BorderOutlined,
} from "@ant-design/icons-vue";
import { onMounted, ref } from "vue";
const subFolderRefs = ref([]);
defineProps({
  search: {
    type: String,
    required: false,
  },
  folder_name: {
    type: String,
    required: false,
  },
  display_offset: {
    type: Number,
    required: false,
  },
  father_id: {
    type: String,
    required: false,
  },
  folder_id: {
    type: String,
    required: true,
  },
  folder_bookmark: {
    type: Array,
    required: false,
  },
  //这里的subfolderx不能使用子组件同名的名字subfolder，否则会无法显示子目录书签
  subfolderx: {
    type: Array,
    required: false,
  },
  editable: {
    type: String,
    required: true,
  },
  fatherMethod: {
    type: Function,
    required: false,
  },
});
onMounted(() => {
  //console.log(subFolderRefs.value);
});
</script>
<script>
export default {
  components: {
    EyeInvisibleTwoTone,
    AppstoreTwoTone,
    LikeTwoTone,
    ApiTwoTone,BorderOutlined,
    //bookmarkitem
  },
  emits: ["fatherMethod"],
  methods: {
    isShow: function (str,pinyin, url) {
      var result = false;
      if (str !== null) {
        if (str.toUpperCase().includes(this.search.toUpperCase())) {
          result = true;
        }
      }
      if (pinyin !== null) {
        if (pinyin.toUpperCase().includes(this.search.toUpperCase())) {
          result = true;
        }
      }
      if (url !== null) {
        if (url.toUpperCase().includes(this.search.toUpperCase())) {
          result = true;
        }
      }
      return result;
    },
    showTitle: function (refs) {
      if (this.$func.getVarType(refs) == "HTMLDivElement") {
        let str = refs.innerHTML;
        if (str.toUpperCase().includes(this.search.toUpperCase())) {
          return true;
        } else {
          return false;
        }
      }
    },
  },
  data() {
    return {
      go_url: this.$remoteDomain + "/go/"
    };
  }
};
</script>
<template>
  <div
    ref="subFolderRefs"
    v-show="showTitle(subFolderRefs)"
    :folderid="folder_id"
    :display_offset="display_offset"
    :style="{ 'margin-left': display_offset * 20 + 'px' }"
  >
    <h3 v-if="father_id!=-1">{{ folder_name }}</h3>
    <div
      class="item"
      v-show="isShow(bookmarkitem.title, bookmarkitem.pinyin, bookmarkitem.url)"
      v-for="bookmarkitem in folder_bookmark"
      :itemid="bookmarkitem.id"
    >
    
    <span v-if="father_id!=-1" style="padding-left:20px;"></span>
      <img
        v-if="editable == 'yes'"
        :src="bookmarkitem.icon_display"
        style="width: 16px; height: 16px; margin-right: 3px"
        @click="
          fatherMethod(
            '编辑书签',
            bookmarkitem.id,
            bookmarkitem.url,
            bookmarkitem.title,
            bookmarkitem.folder_id,
            bookmarkitem.is_private,
            bookmarkitem.is_published,
            bookmarkitem.is_recommend,
            bookmarkitem.is_friendlink
          )
        "
      />
      <img
        v-else
        :src="bookmarkitem.icon_display"
        style="width: 16px; height: 16px"
        @click="
          fatherMethod(
            '编辑书签',
            bookmarkitem.id,
            bookmarkitem.url,
            bookmarkitem.title,
            bookmarkitem.folder_id,
            bookmarkitem.is_private,
            bookmarkitem.is_published,
            bookmarkitem.is_recommend,
            bookmarkitem.is_friendlink
          )
        "
      />
      <a :href="go_url+bookmarkitem.id" :title="bookmarkitem.title" :pinyin="bookmarkitem.pinyin" target="_blank">
        {{ bookmarkitem.short_title }}
      </a>
      <a :href="'/frame/'+bookmarkitem.id"><border-outlined style="margin-left:3px;" /></a>
      <eye-invisible-two-tone
        v-if="bookmarkitem.is_private == '1'"
        style="margin-left: 3px"
      />
      <RouterLink
        :to="'/editpost/' + bookmarkitem.id"
        v-if="bookmarkitem.is_published == '1'"
        ><appstore-two-tone style="margin-left: 3px"
      /></RouterLink>
      <like-two-tone
        v-if="bookmarkitem.is_recommend == '1'"
        style="margin-left: 3px"
      />
      <api-two-tone
        v-if="bookmarkitem.is_friendlink == '1'"
        style="margin-left: 3px"
      />
      <span
        class="http_code"
        v-if="bookmarkitem.http_code != 200 && bookmarkitem.http_code != ''"
        >{{ bookmarkitem.http_code }}</span
      >
    </div>
  </div>
  <div v-for="item in subfolderx">
    <subfolder
      :folder_name="item.folder_name"
      :folder_id="item.id"
      :folder_bookmark="item.bookmarks"
      :subfolderx="item.subfolder"
      :search="search"
      :editable="editable"
      :fatherMethod="fatherMethod"
      :display_offset="item.display_offset"
    >
    </subfolder>
  </div>
</template>

<style scoped>
.http_code {
  transform: rotate(-5deg);
  display: inline-block;
  margin-left: 5px;
  font-weight: bold;
  color: red;
  text-decoration: underline;
}
.item {
  margin-top: 0;
    padding-top:0.4rem;
    padding-bottom:1rem;
  width: 100%;
  display: inline-block;
  padding-left:0.2rem;
}

@media (min-width: 720px) {
  .item {
    width: 50%;
    margin-top: 0;
    padding-top:0.4rem;
    padding-bottom:1rem;
  }
}

@media (min-width: 1024px) {
  .item {
    width: 33%;
    margin-top: 0;
    padding-top:0.4rem;
    padding-left:0rem;
    padding-bottom:1rem;
  }
}
</style>