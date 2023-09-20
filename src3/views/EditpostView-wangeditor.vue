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

  <br><br>
  <a-form :model="formState">
  <a-form-item label="标题" name="title" :rules="[{ required: true, message: '标题不能为空' }]">
      <a-input v-model:value="formState.title"/>
    </a-form-item>
  </a-form>
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

import {  onBeforeUnmount, ref, shallowRef, onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
export default {
  components: { Editor, Toolbar },
  setup() {
    const formState = ref([])
    const router = useRouter()
    console.log(router.currentRoute.value.params.id)
    const iconLoading = ref(false);
    // 编辑器实例，必须用 shallowRef
    const editorRef = shallowRef()
    const title = ref('')
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
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      params.append("post_id", router.currentRoute.value.params.id);
      proxy.$http.post('/ajax/get_post_ajax/', params).then(res => {
        console.log(res.data)
        //valueHtml.value = res.data.data.content
        const editor = editorRef.value
        editor.setHtml(res.data.data.content)
        defaultPercent.value = 100;
        loadingdone.value = true
        formState.value.title=res.data.data.title
      });
      setTimeout(() => {
        //valueHtml.value = '<p>模拟 Ajax 异步设置内容</p>'
      }, 1500)
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
      params.append("title", formState.value.title);
      params.append("post_id", router.currentRoute.value.params.id);
      proxy.$http.post('/ajax/save_post_ajax/', params).then(res => {
        message.info("已保存");
        iconLoading.value = false;
      });
    }

    return {
      formState,
      editorRef,
      title,
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
