<template>
  <h3 style="margin-top:15px;">写作</h3>
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
      <a-checkbox v-model:checked="formState.is_private">私有</a-checkbox>
      <a-checkbox v-model:checked="formState.is_recommend">推荐</a-checkbox>
    </p>
  </a-form>
  <vue-ueditor-wrap v-model="valueHtml" :config="editorConfig" editor-id="editor-demo-01"></vue-ueditor-wrap>

  <div>
    <a-modal v-model:visible="visible" title="新建文章成功" @ok="handleOk" ok-text="确认" cancel-text="取消">
      <p>跳转到文章列表页请点击“确定”</p>
      <p>再写一篇请点击“取消”</p>
    </a-modal>
  </div>
</template>

<script>


import { message } from 'ant-design-vue';

import { defineComponent, ref, onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
export default defineComponent({
  setup() {
    const formState = ref([])
    formState.value.title = '';
    formState.value.is_private = false;
    formState.value.is_recommend = false;
    const folder_list = ref([])
    const router = useRouter()
    const iconLoading = ref(false);

    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const { proxy } = getCurrentInstance()

    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/get_folder_ajax/', params).then(res => {
        folder_list.value = res.data.data.data
        formState.value.folder_id = res.data.data.data[0].value
      })
    })

    const save = () => {
      iconLoading.value = true;
      if (proxy.$func.getVarType(formState.value.title) == "undefined" || formState.value.title == '') {
        message.info("标题不能为空");
        iconLoading.value = false;
      }
      else {
        let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get('token'));
        params.append("timestamp",new Date().getTime());
        params.append("content", valueHtml.value);
        params.append("title", formState.value.title);
        params.append("folder_id", formState.value.folder_id);
        if (formState.value.is_private == true) {
          params.append("is_private", 1);
        }
        else {
          params.append("is_private", 0);
        }
        if (formState.value.is_recommend == true) {
          params.append("is_recommend", 1);
        }
        else {
          params.append("is_recommend", 0);
        }
        proxy.$http.post('/ajax/save_blog_ajax/', params).then(res => {
          //console.log(res.data.msg);
          if (res.data.msg == "新建成功") {    //这条提示如果改的话要和后端一起改
            visible.value = true;
            //message.info("已保存，即将跳转到列表页面",10);
            iconLoading.value = false;
            //window.location.href = "/blog";
          }
          else{
            message.info(res.data.msg);
            iconLoading.value = false;
          }
        }).catch(error => {
          message.info("无法正常保存");
          iconLoading.value = false;
          console.log(error);
        });
      }
    }
    const visible = ref(false);
    const handleOk = e => {
      visible.value = false;
      window.location.href = "/blog";
    };
    return {
      visible,
      handleOk,
      formState,
      folder_list,
      valueHtml,
      save,
      iconLoading
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
  }
})

</script>
