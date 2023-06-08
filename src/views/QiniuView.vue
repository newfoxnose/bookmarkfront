<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <h3 style="margin-top:15px;">文件</h3>
  <!--这个文件不能用自动格式化，否则:data={token:qiniu_token,key:file_key}这部分会异常-->
  <div v-if="fileitems != null">
    <a-upload-dragger v-model:fileList="fileList" name="file" :multiple="false" action="https://up-cn-east-2.qiniup.com"
      :data={token:qiniu_token,key:file_key} @change=" handleChange " :before-upload=" handleBeforeUpload "
      @drop=" handleDrop ">
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
  <div v-else>
    <h5>七牛云账号信息未填写或错误。</h5>
  </div>
  <div>
    <div v-for=" item  in  fileitems " style="margin-bottom:5px;">

      <span class="ext">{{ item.key.split(".").pop() }}</span>
      <a target="_blank" :href=" qiniu_domain + '/' + item.key " style="margin-left:5px;">
        {{ item.key }}
      </a>
      <span style="margin-left:20px;">({{ $func.formatterSizeUnit(item.fsize) }} , {{ $func.timeFormat(item.putTime)
        }})</span>
      <a style="margin-left:20px;" @click="deletefile(item.key)">删除</a>

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
import { InboxOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';
import * as qiniu from 'qiniu-js';
import { Base64 } from "js-base64";


export default {
  components: {
    InboxOutlined,
  },
  setup() {
    const defaultPercent = ref(5);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()
    const qiniu_token = ref('')
    const qiniu_domain = ref('')
    const file_key = ref('')
    const fileitems = ref([])

    // 获取token和列表
    onMounted(() => {
      const interval=setInterval(() => {
        const percent = defaultPercent.value + 10;
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 10)
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      proxy.$http.post('/ajax/qiniu_list_ajax/', params).then(res => {
        qiniu_token.value = res.data.data.qiniu_token
        qiniu_domain.value = res.data.data.qiniu_domain
        fileitems.value = res.data.data.documents
        defaultPercent.value = 95;
        loadingdone.value = true
      });
    })
    const handleBeforeUpload = (file) => {
      file_key.value = file.name;
    }
    const handleChange = info => {
      const status = info.file.status;
      //console.log(status)
      if (status !== 'uploading') {
        //console.log(info.file, info.fileList);
      }
      if (status === 'done') {
        message.success(`${info.file.name} 上传成功.`);
        let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
        params.append("teacher_id", $cookies.get('teacher_id'));
        params.append("login", $cookies.get('login'));
        params.append("level", $cookies.get('level'));
        proxy.$http.post('/ajax/qiniu_list_ajax/', params).then(res => {
          qiniu_token.value = res.data.data.qiniu_token
          qiniu_domain.value = res.data.data.qiniu_domain
          fileitems.value = res.data.data.documents
        });

      } else if (status === 'error') {
        message.error(`${info.file.name} 上传失败.`);
      }
    };
    const deletefile = (file) => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));
      proxy.$http.post('/ajax/delete_file_ajax/', params).then(res => {
        qiniu_token.value = res.data.data.qiniu_token
        qiniu_domain.value = res.data.data.qiniu_domain
        fileitems.value = res.data.data.documents
      });
    };
    return {
      qiniu_token,
      qiniu_domain,
      file_key,
      fileitems,
      handleBeforeUpload,
      handleChange,
      deletefile,
      fileList: ref([]),
      handleDrop: e => {
        console.log(e);
      },
      defaultPercent,
      loadingdone
    };
  },
}
</script>
