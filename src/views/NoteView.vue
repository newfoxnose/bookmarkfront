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
  <div style="border: 1px solid #ccc">
    <Toolbar style="border-bottom: 1px solid #ccc" :editor="editorRef" :defaultConfig="toolbarConfig" :mode="mode" />
    <Editor style="height: 500px; overflow-y: hidden;" v-model="valueHtml" :defaultConfig="editorConfig" :mode="mode"
      @onCreated="handleCreated" />
  </div>
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
import '@wangeditor/editor/dist/css/style.css' // 引入 css

import { onBeforeUnmount, ref, shallowRef, onMounted, getCurrentInstance } from 'vue'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
export default {
  components: { Editor, Toolbar },
  setup() {

    const iconLoading = ref(false);
    // 编辑器实例，必须用 shallowRef
    const editorRef = shallowRef()

    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const { proxy } = getCurrentInstance()
    const auto_save_count_down = ref(60)
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    // ajax 异步获取内容
    onMounted(() => {
      const interval = setInterval(() => {
        const percent = defaultPercent.value + 10;
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      proxy.$http.post('/ajax/note_ajax/', params).then(res => {
        valueHtml.value = res.data.data.note
        defaultPercent.value = 95;
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
    //编辑器配置
    const toolbarConfig = {
      excludeKeys: [
        'emotion', 'uploadImage', 'uploadVideo'
      ]
    }
    const editorConfig = {
      placeholder: '请输入内容...'
    }

    // 组件销毁时，也及时销毁编辑器
    onBeforeUnmount(() => {
      const editor = editorRef.value
      if (editor == null) return
      editor.destroy()
    })

    const handleCreated = (editor) => {
      editorRef.value = editor // 记录 editor 实例，重要！
    }

    const save = () => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      params.append("content", valueHtml.value);
      proxy.$http.post('/ajax/save_note_ajax/', params).then(res => {
        message.info("已保存");
        iconLoading.value = false;
      });
    }

    return {
      editorRef,
      valueHtml,
      mode: 'default', // 或 'simple'
      toolbarConfig,
      editorConfig,
      handleCreated,
      save,
      auto_save_count_down,
      iconLoading,
      defaultPercent,
      loadingdone
    };
  },
}
</script>
