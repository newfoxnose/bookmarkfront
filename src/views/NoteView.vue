<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>

  <h3 style="margin-top:15px;">随手记</h3>
  <a-button type="primary" @click="save" :loading="iconLoading">保存</a-button>
  <span style="float:right" v-if="auto_save_count_down < 10">距离自动保存还有<i style="color:red">{{ auto_save_count_down
  }}</i>秒</span>

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

import { onBeforeUnmount, ref, onMounted, getCurrentInstance } from 'vue'

export default {
  setup() {

    const iconLoading = ref(false);

    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const { proxy } = getCurrentInstance()
    const auto_save_count_down = ref(60)
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    // ajax 异步获取内容
    onMounted(() => {
      const interval = setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/note_ajax/', params).then(res => {
        valueHtml.value = res.data.data.note
        defaultPercent.value = 100;
        loadingdone.value = true
      });
      setTimeout(() => {
        //valueHtml.value = '<p>模拟 Ajax 异步设置内容</p>'
      }, 1500)
      /*
                //停用自动保存，在一个用户多地登陆时会导致内容保存错误
            setInterval(() => {
              auto_save_count_down.value = auto_save_count_down.value - 1;
              if (auto_save_count_down.value == 0) {
                
                params.append("content", valueHtml.value);
                proxy.$http.post('/ajax/save_note_ajax/', params).then(res => {
                  //这个提示不会生效，但不要去掉，否则会重复多次请求
                  if (res.data.message == "保存成功") {
                    message.info("已自动保存");
                  }
                });
                
                auto_save_count_down.value = 60;
              }
            }, 1000)
      */
    })

    const save = () => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("content", valueHtml.value);
      proxy.$http.post('/ajax/save_note_ajax/', params).then(res => {
        message.info("已保存");
        iconLoading.value = false;
      });
    }

    return {
      valueHtml,
      save,
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
