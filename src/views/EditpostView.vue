<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>

  <h3 style="margin-top:15px;">编辑内容</h3>
  <a-button type="primary" @click="save" :loading="iconLoading">保存</a-button>
  <span style="float:right" v-if="auto_save_count_down < 10">距离自动保存还有<i style="color:red">{{ auto_save_count_down
  }}</i>秒</span>
<p>网址：
  <a :href="formState.url" target="_blank">{{ formState.url }}</a>
  &nbsp;&nbsp;
  <a-button type="primary" @click="reload_url(editId)" :loading="iconLoading">重新获取网页内容</a-button>
</p>
  <a-form :model="formState">
    <a-form-item label="标题" name="title" :rules="[{ required: true, message: '标题不能为空' }]">
      <a-input v-model:value="formState.title" />
    </a-form-item>
  </a-form>
<p>注：http协议的图片无法在编辑器里显示，不代表没有正常上传</p>
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

import { ref,  onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
export default {
  setup() {
    const formState = ref([])
    const router = useRouter()
    const iconLoading = ref(false);

    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const { proxy } = getCurrentInstance()
    const auto_save_count_down = ref(60)
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    // ajax 异步获取内容4
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
      params.append("post_id", router.currentRoute.value.params.id);
      proxy.$http.post('/ajax/get_post_ajax/', params).then(res => {
        if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/login";
    }
        console.log(res.data)
        valueHtml.value = res.data.data.content
        defaultPercent.value = 100;
        loadingdone.value = true
        formState.value.title = res.data.data.title
        formState.value.url = res.data.data.url
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
      params.append("post_id", router.currentRoute.value.params.id);
      proxy.$http.post('/ajax/save_post_ajax/', params).then(res => {
        message.info(res.data.msg);
        console.log(res.data);
        iconLoading.value = false;
      }).catch(error => {
        message.info("无法正常保存");
        iconLoading.value = false;
            console.log(error);
          });
    }
    
    const reload_url = () => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      params.append("url", formState.value.url);
      proxy.$http.post('/ajax/url_content/', params).then(res => {
        //console.log(res);
        message.info("已重新获取");
        formState.value.title = res.data.data.title
        valueHtml.value = res.data.data.content
        iconLoading.value = false;
      }).catch(error => {
        message.info("无法正常获取");
        iconLoading.value = false;
            console.log(error);
          });
    }
    return {
      formState,
      valueHtml,
      save,
      reload_url,
      auto_save_count_down,
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
      lang:'zh-cn',
      // 初始容器高度
      initialFrameHeight: 360,
      serverUrl: this.$remoteDomain+'/ueditor/controller.php?token='+$cookies.get('token')+'&timestamp='+(new Date().getTime()), // 服务端接口
    };
  },
}

</script>
