<template>
  <h3 style="margin-top:15px;">文件</h3>
<div>
  <a-upload-dragger
    v-model:fileList="fileList"
    name="file"
    :multiple="false"
    action="http://up-cn-east-2.qiniup.com"
    :data={token:qiniu_token,key:file_key}
    @change="handleChange"
    :before-upload="handleBeforeUpload"
    @drop="handleDrop"
  >
    <p class="ant-upload-drag-icon">
      <inbox-outlined></inbox-outlined>
    </p>
    <p class="ant-upload-text">Click or drag file to this area to upload</p>
    <p class="ant-upload-hint">
      Support for a single or bulk upload. Strictly prohibit from uploading company data or other
      band files
    </p>
  </a-upload-dragger>
  </div>
</template>
<script>
import { message } from 'ant-design-vue';
import { InboxOutlined } from '@ant-design/icons-vue';
import { onMounted,getCurrentInstance,defineComponent, ref } from 'vue'
import * as qiniu from 'qiniu-js'
import { keydown } from 'dom7';


export default {
  components: {
    InboxOutlined,
  },
  setup() {
    const { proxy } = getCurrentInstance()
    const qiniu_token = ref('')
    const file_key = ref('')
    // 获取token和列表
    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      proxy.$http.post('/ajax/qiniu_token_ajax/', params).then(res => {
        //console.log(res.data)
        qiniu_token.value = res.data.data.qiniu_token
      });
    })
    const handleBeforeUpload = (file) => {
      console.log(file.name)
      file_key.value=file.name;
  }
    const handleChange = info => {
      const status = info.file.status;
      //console.log(status)
      if (status !== 'uploading') {
        //console.log(info.file, info.fileList);
      }
      if (status === 'done') {
        message.success(`${info.file.name} file uploaded successfully.`);
      } else if (status === 'error') {
        message.error(`${info.file.name} file upload failed.`);
      }
    };
    return {
      qiniu_token,
      file_key,
      handleBeforeUpload,
      handleChange,
      fileList: ref([]),
      handleDrop: e => {
        console.log(e);
      },
    };
  },
}
</script>
