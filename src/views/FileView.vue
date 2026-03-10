<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <!--这个文件不能用自动格式化，否则:data={token:qiniu_token,key:file_key}这部分会异常-->
  <div>
    <a-upload-dragger v-model:fileList="fileList" name="file" :multiple="false" :action=upload_url
      :data={token:aaa,timestamp:bbb,key:file_key} @change=" handleChange " :before-upload=" handleBeforeUpload "
      @drop=" handleDrop ">
      <p class="ant-upload-drag-icon">
        <inbox-outlined></inbox-outlined>
      </p>
      <p class="ant-upload-text">点击或拖放文件到此处进行上传</p>
      <p class="ant-upload-hint">
        每次只能上传一个文件，且文件大小不能超过20M，支持如下格式pdf|xls|xlsx|doc|docx|ppt|pptx|zip|rar|7z|txt|jpg|png|gif|webp|bmp|jpeg|mp3|mp4|wav|epub，用户总空间容量100MB。
      </p>
    </a-upload-dragger>
    <div class="space-info">用户空间：<strong>{{ space }}</strong> / 100MB（已使用/总空间）</div>
  </div>
  <br>
  <!-- 文件列表：卡片式布局 -->
  <div class="file-list">
    <a-row :gutter="[16, 16]">
      <a-col v-for="item in fileitems" :key="item.key" :xs="24" :sm="12" :md="12" :lg="12">
        <div class="file-item">
          <!-- 左侧：扩展名标签 / 图片预览 -->
          <div class="file-item-preview">
            <a-image
              v-if="['jpg','jpeg','png','gif','bmp','webp'].indexOf((item.key.split('.').pop()||'').toLowerCase()) !== -1"
              :href="item.path + item.key"
              class="file-thumb"
              :src="item.path + item.key"
            />
            <span v-else class="file-ext" :class="'ext-' + ((item.key.split('.').pop()||'').toLowerCase())">
              {{ (item.key.split('.').pop() || 'file').toLowerCase() }}
            </span>
          </div>
          <!-- 右侧：文件名与操作 -->
          <div class="file-item-body">
            <div class="file-item-name">
              <a target="_blank" :href="item.path + item.key" class="file-link">{{ item.displayname }}</a>
            </div>
            <div class="file-item-meta">
              {{ $func.formatterSizeUnit(item.fsize) }} · {{ $func.timeFormat(item.putTime) }}
            </div>
            <div class="file-item-actions">
              <div class="file-item-actions-left">
                <a v-if="['xls','xlsx','doc','docx','ppt','pptx'].indexOf((item.key.split('.').pop()||'').toLowerCase()) !== -1"
                  target="_blank"
                  :href="'https://view.officeapps.live.com/op/view.aspx?src=' + encodeURIComponent(item.path + item.key)"
                  class="file-action"
                >在线查看</a>
                <a v-else-if="['txt'].indexOf((item.key.split('.').pop()||'').toLowerCase()) !== -1"
                  @click="readBook(item.displayname, item.key, (item.key.split('.').pop()||'').toLowerCase())"
                  class="file-action"
                >在线阅读</a>
                <a v-else-if="['epub'].indexOf((item.key.split('.').pop()||'').toLowerCase()) !== -1"
                  @click="readEpubBook(item.displayname, item.path + item.key, (item.key.split('.').pop()||'').toLowerCase())"
                  class="file-action"
                >在线阅读</a>
                <a target="_blank" :href="item.path + item.key" class="file-action">下载</a>
              </div>
              <a class="file-action file-action-danger file-action-delete" @click="showConfirmDelete(item.key, item.displayname)">删除</a>
            </div>
          </div>
        </div>
      </a-col>
    </a-row>
    <!-- 空状态 -->
    <a-empty v-if="fileitems.length === 0 && loadingdone" description="暂无文件，上传后即可在此查看" />
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
/* 已使用空间提示 */
.space-info {
  margin-top: 12px;
  font-size: 13px;
  color: #64748b;
}

.space-info strong {
  color: #1e293b;
}

.content-dark-theme .space-info {
  color: #94a3b8;
}

.content-dark-theme .space-info strong {
  color: #e2e8f0;
}

/* 文件列表区域 */
.file-list {
}

/* 单个文件卡片 */
.file-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
  position: relative;
  overflow: hidden;
}

.file-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, #3b82f6 0%, #60a5fa 100%);
  border-radius: 4px 0 0 4px;
}

.file-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  border-color: #cbd5e1;
}

/* 左侧预览区：扩展名标签 / 图片缩略图 */
.file-item-preview {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

.file-thumb {
  max-width: 64px !important;
  max-height: 64px !important;
  border-radius: 8px;
  object-fit: cover;
}

.file-ext {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 6px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* 按扩展名区分颜色 */
.file-ext.ext-pdf { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
.file-ext.ext-doc, .file-ext.ext-docx { color: #2563eb; border-color: #bfdbfe; background: #eff6ff; }
.file-ext.ext-xls, .file-ext.ext-xlsx { color: #16a34a; border-color: #bbf7d0; background: #f0fdf4; }
.file-ext.ext-ppt, .file-ext.ext-pptx { color: #ea580c; border-color: #fed7aa; background: #fff7ed; }
.file-ext.ext-txt { color: #475569; border-color: #cbd5e1; background: #f8fafc; }
.file-ext.ext-epub { color: #7c3aed; border-color: #ddd6fe; background: #f5f3ff; }
.file-ext.ext-zip, .file-ext.ext-rar, .file-ext.ext-7z { color: #0891b2; border-color: #a5f3fc; background: #ecfeff; }
.file-ext.ext-mp3, .file-ext.ext-mp4, .file-ext.ext-wav { color: #be185d; border-color: #fbcfe8; background: #fdf2f8; }

/* 右侧内容区 */
.file-item-body {
  flex: 1;
  min-width: 0;
}

.file-item-name {
  margin-bottom: 4px;
}

.file-link {
  font-size: 15px;
  font-weight: 500;
  color: #1e293b;
  text-decoration: none;
  transition: color 0.2s;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-link:hover {
  color: #3b82f6;
}

.file-item-meta {
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 10px;
}

/* 操作按钮组：左侧查看/下载，右侧删除 */
.file-item-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  justify-content: space-between;
}

.file-item-actions-left {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.file-action-delete {
  margin-left: auto;
}

.file-action {
  font-size: 12px;
  color: #3b82f6;
  cursor: pointer;
  transition: color 0.2s;
}

.file-action:hover {
  color: #2563eb;
  text-decoration: underline;
}

.file-action-danger {
  color: #dc2626;
}

.file-action-danger:hover {
  color: #b91c1c;
}

/* 深色主题下文件列表样式 */
.content-dark-theme .file-item {
  background: #1f1f1f;
  border-color: #303030;
}

.content-dark-theme .file-item:hover {
  border-color: #404040;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.content-dark-theme .file-item-preview {
  background: linear-gradient(135deg, #2d2d2d 0%, #252525 100%);
}

.content-dark-theme .file-ext {
  color: #94a3b8;
  border-color: #404040;
  background: #252525;
}

.content-dark-theme .file-link {
  color: #e2e8f0;
}

.content-dark-theme .file-link:hover {
  color: #60a5fa;
}

.content-dark-theme .file-item-meta {
  color: #64748b;
}

.content-dark-theme .file-action {
  color: #60a5fa;
}

.content-dark-theme .file-action:hover {
  color: #93c5fd;
}

.content-dark-theme .file-action-danger {
  color: #f87171;
}

.content-dark-theme .file-action-danger:hover {
  color: #fca5a5;
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
import { message, Modal } from 'ant-design-vue';
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
    $cookies.set('selectedkey','3',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()
    const aaa=ref($cookies.get('token'));
    const bbb=ref();
    const file_key = ref('')
    const fileitems = ref([])
    const space = ref('');
    const upload_url = ref(proxy.$remoteDomain+"/ajax/upload_file_ajax")

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
      
      proxy.$http.post('/ajax/read_file_ajax/', params).then(res => {
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
      proxy.$http.post('/ajax/list_file_ajax/', params).then(res => {
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
      file_key.value = "attachment/"+file.name;
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

    /** 执行删除文件的请求 */
    const deletefile = (file) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));
      proxy.$http.post('/ajax/delete_file_ajax/', params).then(res => {
        fileitems.value = res.data.data.documents
        space.value = res.data.data.space
      });
    };

    /** 显示删除确认弹窗，确认后再调用 deletefile */
    const showConfirmDelete = (fileKey, displayName) => {
      Modal.confirm({
        title: '确认删除',
        content: `确定要删除文件「${displayName}」吗？删除后无法恢复。`,
        okText: '确定',
        cancelText: '取消',
        okType: 'danger',
        onOk() {
          deletefile(fileKey);
        },
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
      showConfirmDelete,
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
