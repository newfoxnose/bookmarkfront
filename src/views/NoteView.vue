<template>
  <h3 style="margin-top:15px;">随手记</h3>
  <a-button type="primary" @click="save">保存</a-button>
  <div style="border: 1px solid #ccc">
    <Toolbar style="border-bottom: 1px solid #ccc" :editor="editorRef" :defaultConfig="toolbarConfig" :mode="mode" />
    <Editor style="height: 500px; overflow-y: hidden;" v-model="valueHtml" :defaultConfig="editorConfig" :mode="mode"
      @onCreated="handleCreated" />
  </div>
</template>

<script>
import { message } from 'ant-design-vue'; import '@wangeditor/editor/dist/css/style.css' // 引入 css

import { onBeforeUnmount, ref, shallowRef, onMounted, getCurrentInstance } from 'vue'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
export default {
  components: { Editor, Toolbar },
  setup() {
    // 编辑器实例，必须用 shallowRef
    const editorRef = shallowRef()

    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const { proxy } = getCurrentInstance()
    // ajax 异步获取内容
    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      proxy.$http.post('/ajax/note_ajax/', params).then(res => {
        valueHtml.value = res.data.data.note
      });
      setTimeout(() => {
        //valueHtml.value = '<p>模拟 Ajax 异步设置内容</p>'
      }, 1500)
      setInterval(() => {
        params.append("content", valueHtml.value);
        proxy.$http.post('/ajax/save_note_ajax/', params).then(res => {
          //valueHtml.value = res.data.data.note
        });
      }, 30000)   //每30秒自动保存一次
    })

    const toolbarConfig = {}
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
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      params.append("content", valueHtml.value);
        proxy.$http.post('/ajax/save_note_ajax/', params).then(res => {
          message.info("已保存");
        });
    }

    return {
      editorRef,
      valueHtml,
      mode: 'default', // 或 'simple'
      toolbarConfig,
      editorConfig,
      handleCreated,
      save
    };
  },
}
</script>
