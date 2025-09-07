<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <h3 class="content-title">上传词典文件</h3>
  <!--这个文件不能用自动格式化，否则:data={token:qiniu_token,key:file_key}这部分会异常-->
  <div>
    <a-upload-dragger v-model:fileList="fileList" name="file" :multiple="false" :action=upload_url
      :data={token:aaa,timestamp:bbb,key:file_key} @change=" handleChange " :before-upload=" handleBeforeUpload "
      @drop=" handleDrop ">
      <p class="ant-upload-drag-icon">
        <inbox-outlined></inbox-outlined>
      </p>
      <p class="ant-upload-text">点击或拖放excel文件到此处进行上传</p>
      <p class="ant-upload-hint">
        每次只能上传一个文件，仅支持xls、xlsx格式，且文件大小不能超过5M，空间容量30MB，上传的文件会被自动分割。
      </p>
    </a-upload-dragger>
    已使用空间：{{ space }}
  </div>
  <br>
  <div>
    <div v-for=" item  in  fileitems " style="margin-bottom:5px;">
      <span style="margin-left:5px;">
        {{ item.dict_name }}
      </span>
      <span style="margin-left:20px;">(记录数：{{ item.words_amount }} , 创建时间：{{ item.createtime }})</span>
      <a style="margin-left:20px;" @click="deletefile(item.id)">删除</a>
    </div>
  </div>

  <!-- 使用TXT阅读器组件 -->
  <txt-reader
    v-if="!isEpub"
    :visible="visible"
    :title="articletitle"
    :content="articlecontent"
    @close="onClose"
  />

  <!-- 使用EPUB阅读器组件 -->
  <epub-reader
    v-if="isEpub"
    :file-url="epubFileUrl"
    :title="articletitle"
    @close="closeEpubReader"
  />
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

.epub-reader-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #fff;
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.epub-reader-header {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
}

.epub-reader-title {
  flex: 1;
  margin-right: 20px;
}

.epub-reader-controls {
  display: flex;
  align-items: center;
}

.epub-viewer {
  flex: 1;
  width: 100%;
  height: calc(100% - 64px);
  overflow: hidden;
}

.txt-reader {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.reader-toolbar {
  padding: 10px;
  border-bottom: 1px solid #f0f0f0;
  background: #fff;
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.page-jump {
  margin-left: 10px;
  display: flex;
  align-items: center;
}

.page-jump input {
  width: 60px;
  margin: 0 5px;
}

.reader-content {
  flex: 1;
  padding: 20px;
  overflow: hidden;
  line-height: 1.6;
  background: #fff;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  margin: 20px;
  white-space: pre-wrap;
  word-wrap: break-word;
  height: calc(100% - 40px);
  position: relative;
  font-size: v-bind(fontSize + 'px');
}

.reader-content > div {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 20px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  box-sizing: border-box;
  height: 100%;
}

.drawer-light-theme {
  background: #fff;
  color: #000;
}

.drawer-dark-theme {
  background: #1f1f1f;
  color: #fff;
}

.drawer-light-theme .reader-content {
  background: #fff;
  color: #000;
}

.drawer-dark-theme .reader-content {
  background: #1f1f1f;
  color: #fff;
}

.drawer-light-theme .reader-toolbar {
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
}

.drawer-dark-theme .reader-toolbar {
  background: #1f1f1f;
  border-bottom: 1px solid #303030;
}

.dark-theme .epub-reader-container {
  background: #1f1f1f;
  color: #fff;
}

.dark-theme .epub-reader-header {
  background: #1f1f1f;
  border-bottom-color: #303030;
}

.epub-reader-container :deep(.ant-drawer-content-wrapper) {
  background: #fff;
}

.dark-theme .epub-reader-container :deep(.ant-drawer-content-wrapper) {
  background: #1f1f1f;
}

.epub-reader-container :deep(.ant-menu) {
  border-right: none;
}

.dark-theme .epub-reader-container :deep(.ant-menu) {
  background: #1f1f1f;
  color: #fff;
}

.dark-theme .epub-reader-container :deep(.ant-menu-item) {
  color: #fff;
}

.dark-theme .epub-reader-container :deep(.ant-menu-item-selected) {
  background: #1890ff;
  color: #fff;
}

.dark-theme .epub-reader-container :deep(.ant-menu-item:hover) {
  color: #1890ff;
}
</style>
<script>
import { message } from 'ant-design-vue';
import { InboxOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';
import { Base64 } from "js-base64";
import TxtReader from '../components/TxtReader.vue';
import EpubReader from '../components/EpubReader.vue';

export default {
  components: {
    InboxOutlined,
    TxtReader,
    EpubReader
  },
  setup() {
    $cookies.set('selectedkey','21',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()
    const aaa=ref($cookies.get('token'));
    const bbb=ref();
    const file_key = ref('')
    const fileitems = ref([])
    const space = ref('');
    const upload_url = ref(proxy.$remoteDomain+"/ajax/import_dict_ajax")

    const visible = ref(false);
    const articletitle = ref('');
    const articlecontent = ref('');
    const isEpub = ref(false);
    const epubFileUrl = ref('');

    const readBook = (displayname,file, ext) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));
      
      isEpub.value = false;
      visible.value = true;
      articletitle.value = displayname;
      
      proxy.$http.post('/ajax/read_txt_ajax/', params).then(res => {
        if (res.data.code == '200') {
          articlecontent.value = res.data.data;
        } else {
          message.error(res.data.msg || '加载失败');
          visible.value = false;
        }
      }).catch(error => {
        console.error('请求错误:', error);
        message.error('加载失败，请重试');
        visible.value = false;
      });
    };

    const readEpubBook = (displayname,fileUrl, ext) => {
      isEpub.value = true;
      visible.value = false;
      articletitle.value = displayname;
      epubFileUrl.value = fileUrl;
    };

    const onClose = () => {
      visible.value = false;
      articlecontent.value = '';
    };

    const closeEpubReader = () => {
      isEpub.value = false;
      epubFileUrl.value = '';
    };

    onMounted(() => {
      const interval=setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/list_dict_ajax/', params).then(res => {
        if (res.data.code=='401'){
          window.location.href ="/";
        }
        if (res.data.code=="200"){
          fileitems.value = res.data.data.documents
          space.value = res.data.data.space
        } 
        else{
          message.error(res.data.msg);
        }
        defaultPercent.value = 100;
        loadingdone.value = true
      });
    });

    const handleBeforeUpload = (file) => {
      bbb.value=new Date().getTime();
      file_key.value = "dict/"+file.name;
    }

    const handleChange = info => {
      const status = info.file.status;    
      if (status === 'done') {
        if (info.file.response.code=="200"){
          message.success(`${info.file.name} 上传成功.`);
          fileitems.value = info.file.response.data.documents;
          space.value = info.file.response.data.space;
        }
        else{
          message.error(info.file.response.msg);
        }
      } else if (status === 'error') {
        message.error(`${info.file.name} 上传失败.`);
      }
    };

    const deletefile = (id) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("id", id);
      proxy.$http.post('/ajax/delete_dict_ajax/', params).then(res => {
        fileitems.value = res.data.data.documents
      });
    };

    return {
      aaa,
      bbb,
      upload_url,
      file_key,
      fileitems,
      space,
      handleBeforeUpload,
      handleChange,
      deletefile,
      fileList: ref([]),
      handleDrop: e => {
        console.log(e);
      },
      defaultPercent,
      loadingdone,
      readBook,
      readEpubBook,
      visible,
      onClose,
      articletitle,
      articlecontent,
      isEpub,
      epubFileUrl,
      closeEpubReader
    };
  },
}
</script>
