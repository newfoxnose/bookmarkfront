<script>
import { EyeInvisibleTwoTone,AppstoreTwoTone,LikeTwoTone ,ApiTwoTone, BorderOutlined} from '@ant-design/icons-vue';
export default {
  components: {
    EyeInvisibleTwoTone,AppstoreTwoTone,LikeTwoTone ,ApiTwoTone,BorderOutlined
  },
  props: ['title','pinyin', 'icon', 'id', 'url', 'short_title', 'search', 'editable', 'folder_id', 'is_private', 'http_code', 'is_published', 'is_recommend', 'is_friendlink'],
  emits: ['editbookmark'],
  methods: {
    isShow: function (str,pinyin, url) {
      if (str.toUpperCase().includes(this.search.toUpperCase()) || pinyin.toUpperCase().includes(this.search.toUpperCase()) ||url.toUpperCase().includes(this.search.toUpperCase())) {
        return true
      } else {
        return false
      }
    }
  },
  data() {
    return {
      go_url: this.$remoteDomain + "/go/"
    };
  }
}
</script>
<template>
  <div class="item" v-show="isShow(title,pinyin, url)" :itemid=id>
    <img v-if="editable == 'yes'" :src="icon" style="width:16px;height:16px;margin-right:3px;"
      @click="$emit('editbookmark', '编辑书签', id, url, title, folder_id, is_private, is_published,is_recommend,is_friendlink)">
    <img v-else :src="icon" style="width:16px;height:16px;">
    <a :href=" go_url+id+'/'+url" :title="title" target="_blank">
      {{ short_title }}
    </a>
    <a :href="'/frame/'+id"><border-outlined style="margin-left:3px;" /></a>
    <eye-invisible-two-tone v-if="is_private == '1'" style="margin-left:3px;" />
    <RouterLink :to="'/editpost/'+id" v-if="is_published == '1'"><appstore-two-tone style="margin-left:3px;" /></RouterLink>
    <like-two-tone  v-if="is_recommend == '1'" style="margin-left:3px;" />
    <api-two-tone  v-if="is_friendlink == '1'" style="margin-left:3px;" />
    <span class="http_code" v-if="http_code != 200 && http_code != ''">{{ http_code }}</span>
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
    padding-bottom:1rem;
  }
}
</style>