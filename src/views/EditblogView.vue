<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>


  <h3 style="margin-top:15px;">编辑笔记</h3>
  <a-button type="primary" @click="save" :loading="iconLoading">保存</a-button>
  <a-form :model="formState">
    <a-form-item label="标题" name="title" :rules="[{ required: true, message: '标题不能为空' }]">
      <a-input v-model:value="formState.title" />
    </a-form-item>
    <p>
      <a-select style="width: 100%" v-model:value="formState.folder_id" v-if="folder_list">
        <a-select-option v-for="item in folder_list" :value="item.value" :lv="item.lv"> {{ item.name }}</a-select-option>
      </a-select>
    </p>
    <p>
      <a-checkbox v-model:checked="formState.is_recommend">推荐</a-checkbox>
    </p>
  </a-form>

  <vue-ueditor-wrap v-model="valueHtml" :config="editorConfig" editor-id="editor-demo-01"></vue-ueditor-wrap>
</template>
<style scoped>
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

import { ref, onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
export default {
  setup() {
    const formState = ref([])
    const router = useRouter()
    const iconLoading = ref(false);
    const folder_list = ref([])
    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const { proxy } = getCurrentInstance()
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    // ajax 异步获取内容
    onMounted(() => {
      const interval = setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random() * 7 + 2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      params.append("blog_id", router.currentRoute.value.params.id);
      proxy.$http.post('/ajax/get_folder_ajax/', params).then(res => {
        if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/";
    }
        folder_list.value = res.data.data.data
      })
      proxy.$http.post('/ajax/get_blog_ajax/', params).then(res => {
        //console.log(res.data.data.blog)
        valueHtml.value = res.data.data.blog.content
        defaultPercent.value = 100;
        loadingdone.value = true
        formState.value.title = res.data.data.blog.title
        formState.value.folder_id = res.data.data.blog.folder_id
        if (res.data.data.blog.is_private == 1) {
          formState.value.is_private = true;
        }
        else {
          formState.value.is_private = false
        }
        if (res.data.data.blog.is_recommend == 1) {
          formState.value.is_recommend = true;
        }
        else {
          formState.value.is_recommend = false
        }
      });
      setTimeout(() => {
        //valueHtml.value = '<p>模拟 Ajax 异步设置内容</p>'
      }, 1500)
    })

    const save = () => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      params.append("content", valueHtml.value);
      params.append("title", formState.value.title);
      params.append("folder_id", formState.value.folder_id);
      params.append("post_id", router.currentRoute.value.params.id);
      params.append("is_private", 0);   //博客都为公开
      if (formState.value.is_recommend == true) {
        params.append("is_recommend", 1);
      }
      else {
        params.append("is_recommend", 0);
      }
      proxy.$http.post('/ajax/save_blog_ajax/', params).then(res => {
        //console.log(res.data)
        message.info(res.data.msg);
        iconLoading.value = false;
      }).catch(error => {
        message.info("无法正常保存");
        iconLoading.value = false;
        console.log(error);
      });
    }
    return {
      formState,
      valueHtml,
      save,
      folder_list,
      iconLoading,
      defaultPercent,
      loadingdone
    };

  },
  created() {
    // 更多 UEditor 配置，参考 http://fex.baidu.com/ueditor/#start-config
    this.editorConfig = {
      /*
      toolbars: [
        ['source', 'undo', 'redo'],
        ['bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc','simpleupload']
      ],
      */
      UEDITOR_HOME_URL: '/UEditor/', // 访问 UEditor 静态资源的根路径，可参考常见问题1
      lang: 'zh-cn',
      // 初始容器高度
      initialFrameHeight: 360,
      serverUrl: this.$remoteDomain+'/ueditor/controller.php?token='+$cookies.get('token')+'&timestamp='+(new Date().getTime()), // 服务端接口
    };
  },
}

</script>
