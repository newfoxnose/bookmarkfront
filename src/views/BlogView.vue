<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <h3 style="margin-top:15px;">文章</h3>
  <div>
    <div v-for=" item  in  fileitems " style="margin-bottom:5px;">

      <span class="ext">{{ item.id }}</span>
      <a :href=" '/editblog/' + item.id " style="margin-left:5px;">
        {{ item.title }}
      </a>
      <eye-invisible-two-tone v-if="item.is_private == '1'" style="margin-left:3px;" />
      <like-two-tone  v-if="item.is_recommend == '1'" style="margin-left:3px;" />
      <span style="margin-left:20px;">( {{ item.createtime}})</span>
      <a style="margin-left:20px;" @click="deletefile(item.id)">删除</a>

    </div>
  </div>
</template>
<style scoped>
.ext {
  text-align: center;
  display: inline-block;
  width: 40px;
  margin-right: 3px;
  color: coral;
  font-weight: bold;
  border-style: solid;
  border-width: thin;
  border-color: crimson;
  padding: 2px;
  margin: 3px;
}

.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}
</style>
<script>
import { message } from 'ant-design-vue';

import { InboxOutlined,EyeInvisibleTwoTone,LikeTwoTone } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';
import * as qiniu from 'qiniu-js';
import { Base64 } from "js-base64";


export default {
  components: {
    InboxOutlined,EyeInvisibleTwoTone,LikeTwoTone
  },
  setup() {
    const defaultPercent = ref(10);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()

    const file_key = ref('')
    const fileitems = ref([])

    onMounted(() => {
      const interval=setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      proxy.$http.post('/ajax/list_blog_ajax/', params).then(res => {
        fileitems.value = res.data.data.blog
        defaultPercent.value = 100;
        loadingdone.value = true
      });
    })
    const deletefile = (id) => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      params.append("id_b64", proxy.$func.urlsafe_b64encode(Base64.encode(id)));
      proxy.$http.post('/ajax/delete_blog_ajax/', params).then(res => {
        fileitems.value = res.data.data.blog
      });
    };
    return {
      file_key,
      fileitems,
      deletefile,
      fileList: ref([]),
      defaultPercent,
      loadingdone
    };
  },
}
</script>
