<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <!--这个文件不能用自动格式化，否则:data={token:qiniu_token,key:file_key}这部分会异常-->
  <div class="url-form">
    <a-input-search
      v-model:value="urlInput"
      placeholder="粘贴网页 URL，将正文转为 txt 电子书"
      enter-button="转码"
      :loading="urlSubmitting"
      @search="submitUrl"
    />
  </div>
  <div>
    <a-upload-dragger v-model:fileList="fileList" name="file" :multiple="false" :action=upload_url
      :data={token:aaa,timestamp:bbb,key:file_key} @change=" handleChange " :before-upload=" handleBeforeUpload "
      @drop=" handleDrop ">
      <p class="ant-upload-drag-icon">
        <inbox-outlined></inbox-outlined>
      </p>
      <p class="ant-upload-text">点击或拖放txt文件到此处进行上传</p>
      <p class="ant-upload-hint">
        每次只能上传一个文件，仅支持txt格式，且文件大小不能超过5M，用户总空间容量100MB，上传的文件会被自动分割。
      </p>
    </a-upload-dragger>
    <div class="space-info">用户空间：<strong>{{ space }}</strong> / 100MB（已使用/总空间）</div>
  </div>
  <br>
  <!-- 文件列表：卡片式布局 -->
  <div class="file-list">
    <a-row :gutter="[16, 16]">
      <a-col v-for="item in fileitems" :key="item.key" :xs="24" :sm="24" :md="12" :lg="12">
        <div class="file-item">
          <!-- 左侧：TXT 扩展名标签 -->
          <div class="file-item-preview">
            <span class="file-ext ext-txt">txt</span>
          </div>
          <!-- 右侧：文件名与操作 -->
          <div class="file-item-body">
            <div class="file-item-name">{{ item.displayname }}</div>
            <div class="file-item-meta">
              {{ $func.formatterSizeUnit(item.fsize) }} · {{ $func.timeFormat(item.putTime) }}
            </div>
            <div class="file-item-actions">
              <a class="file-action" @click="toggleChunkList(item)">
                {{ expandedChunkKey === item.key ? '收起分片' : '查看分片' }}
              </a>
              <a class="file-action file-action-danger" @click="deletefile(item.key)">删除</a>
            </div>
          </div>
          <!-- 分片列表：展开时显示，占满卡片宽度 -->
          <div v-if="expandedChunkKey === item.key" class="chunk-list">
            <span
              v-for="(chunk, idx) in getChunkList(item)"
              :key="chunk.name"
              @click.stop="readChunk(item.displayname, chunk.path, chunk.displayName)"
              :class="['chunk-item', { 'chunk-item--last-read': chunk.path === lastReadChunkPath }]"
            >
              {{ chunk.displayName }}
            </span>
          </div>
        </div>
      </a-col>
    </a-row>
    <!-- 空状态 -->
    <a-empty v-if="fileitems.length === 0 && loadingdone" description="暂无 txt 文件，上传后即可在此查看" />
  </div>

  <!-- 使用TXT阅读器组件 -->
  <txt-reader
    v-if="!isEpub"
    :visible="visible"
    :title="articletitle"
    :content="articlecontent"
    :chunk-path="currentChunkPath"
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
/* URL转码表单 */
.url-form {
  margin-bottom: 16px;
}
/* 已使用空间提示 */
.space-info {
  margin-top: 12px;
  font-size: 13px;
  color: var(--text-tertiary);
}
.space-info strong {
  color: var(--text-primary);
}

/* 文件列表区域 */
.file-list {
}

/* 单个文件卡片 */
.file-item {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: var(--card-bg);
  border-radius: 12px;
  border: 1px solid var(--text-primary);
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
  border-color: var(--border-color);
}

/* 左侧预览区：TXT 扩展名标签 */
.file-item-preview {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: linear-gradient(135deg, #f1f5f9 0%, var(--text-primary) 100%);
}
.file-ext {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 8px;
  border-radius: 6px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.file-ext.ext-txt {
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
  background: var(--panel-bg);
}

/* 右侧内容区 */
.file-item-body {
  flex: 1;
  min-width: 0;
}
.file-item-name {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.file-item-meta {
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 10px;
}

/* 操作按钮组 */
.file-item-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  margin-bottom: 8px;
}
.file-action {
  font-size: 13px;
  color: #3b82f6;
  transition: color 0.2s ease;
  cursor: pointer;
}
.file-action:hover {
  color: #2563eb;
}
.file-action-danger {
  color: var(--text-secondary);
}
.file-action-danger:hover {
  color: #ef4444;
}

/* 分片列表区域：占满卡片宽度 */
.chunk-list {
  flex: 0 0 100%;
  width: 100%;
  margin-top: 4px;
  padding: 12px;
  background: linear-gradient(135deg, var(--panel-bg) 0%, #f1f5f9 100%);
  border: 1px solid var(--text-primary);
  border-radius: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* 分片项样式 */
.chunk-item {
  cursor: pointer;
  color: #3b82f6;
  font-size: 13px;
  padding: 6px 12px;
  background: var(--card-bg);
  border: 1px solid var(--text-primary);
  border-radius: 6px;
  transition: all 0.2s ease;
}
.chunk-item:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #2563eb;
}
.chunk-item--last-read {
  font-weight: 600;
  background: #dbeafe;
  border-color: #3b82f6;
  color: #1d4ed8;
}
.chunk-item--last-read:hover {
  background: #bfdbfe;
}

/* 深色主题下文件列表样式 */
.content-dark-theme .space-info {
  color: var(--text-secondary);
}
.content-dark-theme .space-info strong {
  color: var(--text-primary);
}
.content-dark-theme .file-item {
  background: var(--card-bg);
  border-color: var(--border-color);
}
.content-dark-theme .file-item:hover {
  border-color: var(--border-color);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.content-dark-theme .file-item-preview {
  background: linear-gradient(135deg, #2d2d2d 0%, var(--panel-bg) 100%);
}
.content-dark-theme .file-ext.ext-txt {
  color: var(--text-secondary);
  border-color: var(--border-color);
  background: var(--panel-bg);
}
.content-dark-theme .file-item-name {
  color: var(--text-primary);
}
.content-dark-theme .file-item-meta {
  color: var(--text-tertiary);
}
.content-dark-theme .file-action {
  color: #60a5fa;
}
.content-dark-theme .file-action:hover {
  color: #93c5fd;
}
.content-dark-theme .file-action-danger {
  color: var(--text-secondary);
}
.content-dark-theme .file-action-danger:hover {
  color: #f87171;
}
.content-dark-theme .chunk-list {
  background: linear-gradient(135deg, var(--panel-bg) 0%, var(--card-bg) 100%);
  border-color: var(--border-color);
}
.content-dark-theme .chunk-item {
  color: #60a5fa;
  background: var(--card-bg);
  border-color: var(--border-color);
}
.content-dark-theme .chunk-item:hover {
  background: #2d2d2d;
  border-color: #505050;
  color: #93c5fd;
}
.content-dark-theme .chunk-item--last-read {
  background: #1e3a5f;
  border-color: #3b82f6;
  color: #93c5fd;
}
.content-dark-theme .chunk-item--last-read:hover {
  background: #1e40af;
}

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
  background: var(--card-bg);
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.epub-reader-header {
  padding: 16px;
  border-bottom: 1px solid var(--border-color-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--card-bg);
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
  border-bottom: 1px solid var(--border-color-light);
  background: var(--card-bg);
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
  background: var(--card-bg);
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
  background: var(--card-bg);
  color: var(--text-primary);
}

.drawer-dark-theme {
  background: var(--card-bg);
  color: var(--text-primary);
}

.drawer-light-theme .reader-content {
  background: var(--card-bg);
  color: var(--text-primary);
}

.drawer-dark-theme .reader-content {
  background: var(--card-bg);
  color: var(--text-primary);
}

.drawer-light-theme .reader-toolbar {
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color-light);
}

.drawer-dark-theme .reader-toolbar {
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
}

.dark-theme .epub-reader-container {
  background: var(--card-bg);
  color: var(--text-primary);
}

.dark-theme .epub-reader-header {
  background: var(--card-bg);
  border-bottom-color: var(--border-color);
}

.epub-reader-container :deep(.ant-drawer-content-wrapper) {
  background: var(--card-bg);
}

.dark-theme .epub-reader-container :deep(.ant-drawer-content-wrapper) {
  background: var(--card-bg);
}

.epub-reader-container :deep(.ant-menu) {
  border-right: none;
}

.dark-theme .epub-reader-container :deep(.ant-menu) {
  background: var(--card-bg);
  color: var(--text-primary);
}

.dark-theme .epub-reader-container :deep(.ant-menu-item) {
  color: var(--text-primary);
}

.dark-theme .epub-reader-container :deep(.ant-menu-item-selected) {
  background: #1890ff;
  color: var(--text-primary);
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
    $cookies.set('selectedkey','20',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()
    const aaa=ref($cookies.get('token'));
    const bbb=ref();
    const file_key = ref('')
    const fileitems = ref([])
    const space = ref('');
    const upload_url = ref(proxy.$remoteDomain+"/ajax/upload_txt_ajax")

    const urlInput = ref('');
    const urlSubmitting = ref(false);

    const visible = ref(false);
    const articletitle = ref('');
    const articlecontent = ref('');
    const isEpub = ref(false);
    const epubFileUrl = ref('');
    // 当前展开分片列表的文件 key，用于折叠/展开分片列表
    const expandedChunkKey = ref('');
    // 上一次阅读的分片路径，用于高亮显示（刷新后从 localStorage 恢复）
    const LAST_CHUNK_STORAGE_KEY = 'txtreader_last_chunk_path';
    const lastReadChunkPath = ref(localStorage.getItem(LAST_CHUNK_STORAGE_KEY) || '');
    // 当前阅读的分片路径，用于阅读器记忆页码
    const currentChunkPath = ref('');

    /** 去掉分片名的 .txt 后缀用于显示 */
    const stripTxtExt = (name) => (name || '').replace(/\.txt$/i, '');

    /**
     * 获取分片列表，兼容 chunk_files 与 chunk_paths 两种格式，displayName 不含 .txt
     * @param {object} item - 文件项
     * @returns {Array<{name:string, path:string, displayName:string}>}
     */

    const getChunkList = (item) => {
      let list = [];
      if (item.chunk_paths && item.chunk_paths.length > 0) {
        list = item.chunk_paths.map((path) => ({
          path,
          name: path.split('/').pop() || path
        }));
      } else if (item.chunk_files && item.chunk_files.length > 0 && item.chunk_paths) {
        list = item.chunk_files.map((name, idx) => ({
          name,
          path: item.chunk_paths[idx] || `${item.chunk_dir || ''}/${name}`.replace(/^\//, '')
        }));
      } else if (item.chunk_files && item.chunk_files.length > 0 && item.chunk_dir) {
        list = item.chunk_files.map((name) => ({
          name,
          path: `${item.chunk_dir}/${name}`.replace(/\/\//g, '/')
        }));
      }
      return list.map((chunk) => ({ ...chunk, displayName: stripTxtExt(chunk.name) }));
    };

    /**
     * 切换分片列表的展开/收起
     * @param {object} item - 文件项，需包含 key、chunk_files
     */
    const toggleChunkList = (item) => {
      expandedChunkKey.value = expandedChunkKey.value === item.key ? '' : item.key;
    };

    /**
     * 加载并阅读单个分片内容
     * @param {string} displayname - 书籍显示名
     * @param {string} chunkPath - 分片完整路径，如 txtreader/xxx/000.txt
     * @param {string} chunkName - 分片文件名，如 000.txt
     */
    const readChunk = (displayname, chunkPath, chunkName) => {
      if (!chunkPath) {
        message.error('分片路径无效');
        return;
      }
      isEpub.value = false;
      articletitle.value = `${displayname} - ${chunkName}`;
      articlecontent.value = '';
      visible.value = true;

      const params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(chunkPath)));

      proxy.$http.post('/ajax/read_txt_ajax/', params).then(res => {
        // 兼容 code 为数字 200 或字符串 '200'
        if (String(res.data.code) === '200') {
          articlecontent.value = res.data.data || '';
          lastReadChunkPath.value = chunkPath;
          currentChunkPath.value = chunkPath;
          localStorage.setItem(LAST_CHUNK_STORAGE_KEY, chunkPath);
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

    /**
     * 阅读非分片电子书（使用 key 直接请求完整内容）
     */
    const readBook = (displayname, file, ext, item) => {
      isEpub.value = false;
      visible.value = true;
      articletitle.value = displayname;

      // 非分片：使用 key 直接请求完整内容
      const params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));

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
      currentChunkPath.value = '';
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
      proxy.$http.post('/ajax/list_txt_ajax/', params).then(res => {
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
      file_key.value = "txtreader/"+file.name;
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

    const submitUrl = () => {
      if (!urlInput.value || !urlInput.value.match(/^https?:\/\//i)) {
        message.error('请输入有效的 URL');
        return;
      }
      urlSubmitting.value = true;
      let params = new URLSearchParams();
      params.append('token', $cookies.get('token'));
      params.append('timestamp', new Date().getTime());
      params.append('url', urlInput.value);
      proxy.$http.post('/ajax/url_to_txt_ajax/', params).then(res => {
        if (res.data.code == '401') {
          window.location.href = '/';
          return;
        }
        if (res.data.code == '200') {
          message.success('转换成功');
          fileitems.value = res.data.data.documents;
          space.value = res.data.data.space;
          urlInput.value = '';
        } else {
          message.error(res.data.msg || '转换失败');
        }
      }).catch(() => {
        message.error('网络请求失败');
      }).finally(() => {
        urlSubmitting.value = false;
      });
    };

    const deletefile = (file) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));
      proxy.$http.post('/ajax/delete_txt_ajax/', params).then(res => {
        fileitems.value = res.data.data.documents
        space.value = res.data.data.space
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
      urlInput,
      urlSubmitting,
      submitUrl,
      deletefile,
      fileList: ref([]),
      handleDrop: e => {
        console.log(e);
      },
      defaultPercent,
      loadingdone,
      readBook,
      readChunk,
      getChunkList,
      toggleChunkList,
      expandedChunkKey,
      lastReadChunkPath,
      currentChunkPath,
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
