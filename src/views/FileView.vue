<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <h3 class="content-title">文件</h3>
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
        每次只能上传一个文件，且文件大小不能超过20M，支持如下格式pdf|xls|xlsx|doc|docx|ppt|pptx|zip|rar|7z|txt|jpg|png|gif|webp|bmp|jpeg|mp3|mp4|wav|epub，最多上传15个文件。
      </p>
    </a-upload-dragger>
  </div>
  <br>
  <div>
    <div v-for=" item  in  fileitems " style="margin-bottom:5px;">
      <span class="ext">{{ lcase_ext= item.key.split('.').pop().toLowerCase() }}</span>
      <a-image v-if="['jpg','jpeg','png','gif','bmp','webp'].indexOf(lcase_ext) !== -1" :href=" item.path  + item.key " style="max-height:50px;margin-left:5px;"
        :src=" item.path + item.key "
      />
      <a v-else target="_blank" :href=" item.path + item.key " style="margin-left:5px;">
        {{ item.key.substring(11) }}
      </a>
      <span style="margin-left:20px;">({{ $func.formatterSizeUnit(item.fsize) }} , {{ $func.timeFormat(item.putTime)
        }})</span>
      <a v-if="['xls','xlsx','doc','docx','ppt','pptx'].indexOf(lcase_ext) !== -1" target="_blank" :href="'https://view.officeapps.live.com/op/view.aspx?src='+ encodeURIComponent(item.path  + item.key) " style="margin-left:5px;">
        在线查看
      </a>
      <a v-else-if="['txt'].indexOf(lcase_ext) !== -1" @click="readBook(item.key, lcase_ext)" style="margin-left:5px; cursor: pointer;">
        在线阅读
      </a>
      <a v-else-if="['epub'].indexOf(lcase_ext) !== -1" @click="readEpubBook(item.path + item.key, lcase_ext)" style="margin-left:5px; cursor: pointer;">
        在线阅读
      </a>
      <a style="margin-left:20px;" @click="deletefile(item.key)">删除</a>
    </div>
  </div>

  <!-- EPUB阅读器 -->
  <div v-if="isEpub" class="epub-reader-container">
    <div class="epub-reader-header">
      <div class="epub-reader-title">
        <h3>{{ articletitle }}</h3>
      </div>
      <div class="epub-reader-controls">
        <a-button-group>
          <a-button @click="prevPage" title="上一页">
            <template #icon><left-outlined /></template>
          </a-button>
          <a-button @click="nextPage" title="下一页">
            <template #icon><right-outlined /></template>
          </a-button>
        </a-button-group>
        <div class="page-jump" style="margin-left: 10px;">
          <span>跳转到</span>
          <a-input-number 
            v-model:value="currentPage" 
            :min="1" 
            :max="totalPages"
            @change="handlePageJump"
            style="width: 80px; margin: 0 5px;"
          />
          <span>页</span>
        </div>
        <span style="margin-left: 10px;">第 {{ currentPage }}/{{ totalPages }} 页</span>
        <a-button-group style="margin-left: 10px;">
          <a-button @click="toggleBookmark" :type="hasBookmark ? 'primary' : 'default'" title="添加/删除书签">
            <template #icon><book-outlined /></template>
          </a-button>
        </a-button-group>
        <a-button @click="showChapters" style="margin-left: 10px;" title="章节列表">
          <template #icon><menu-outlined /></template>
        </a-button>
        <a-button @click="closeEpubReader" type="primary" style="margin-left: 10px;">关闭</a-button>
      </div>
    </div>
    <div id="epub-viewer" class="epub-viewer"></div>

    <!-- 章节列表抽屉 -->
    <a-drawer
      title="章节列表"
      placement="left"
      :visible="chaptersVisible"
      @close="chaptersVisible = false"
      width="300"
    >
      <a-menu
        mode="inline"
        :selectedKeys="[currentChapter]"
        @select="handleChapterSelect"
      >
        <template v-for="chapter in chapters" :key="chapter.href">
          <a-menu-item>
            {{ chapter.label }}
          </a-menu-item>
        </template>
      </a-menu>
    </a-drawer>
  </div>

  <!-- TXT阅读器 -->
  <a-drawer
    v-else
    :visible="visible"
    :width="1000"
    :title="articletitle"
    :class="'drawer-'+$cookies.get('theme')+'-theme'"
    placement="right"
    @close="onClose"
  >
    <div class="txt-reader">
      <div class="reader-toolbar">
        <a-button-group>
          <a-button @click="prevPage" :disabled="currentPage === 1">
            <template #icon><left-outlined /></template>
          </a-button>
          <a-button @click="nextPage" :disabled="currentPage === totalPages">
            <template #icon><right-outlined /></template>
          </a-button>
        </a-button-group>
        <div class="page-jump">
          <span>跳转到</span>
          <a-input-number 
            v-model:value="jumpPage" 
            :min="1" 
            :max="totalPages"
            @pressEnter="handlePageJump"
          />
          <span>页</span>
          <a-button type="primary" @click="handlePageJump" style="margin-left: 5px;">确定</a-button>
        </div>
        <a-button-group style="margin-left: 10px;">
          <a-button @click="toggleBookmark" :type="hasBookmark ? 'primary' : 'default'">
            <template #icon><book-outlined /></template>
          </a-button>
        </a-button-group>
        <span style="margin-left: 10px;">第 {{ currentPage }}/{{ totalPages }} 页</span>
      </div>
      <div class="reader-content" ref="readerContent">
        <div v-if="currentPageContent" v-html="currentPageContent"></div>
        <div v-else>加载中...</div>
      </div>
    </div>
  </a-drawer>
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
import { InboxOutlined, LeftOutlined, RightOutlined, BookOutlined, MenuOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref, computed, onUnmounted, nextTick } from 'vue';
import { Base64 } from "js-base64";
import ePub from 'epubjs';

export default {
  components: {
    InboxOutlined,
    LeftOutlined,
    RightOutlined,
    BookOutlined,
    MenuOutlined
  },
  setup() {
    $cookies.set('selectedkey','3',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const fontSize = ref(16);

    const { proxy } = getCurrentInstance()
    const aaa=ref($cookies.get('token'));
    const bbb=ref();
    const file_key = ref('')
    const fileitems = ref([])
    const upload_url = ref(proxy.$remoteDomain+"/ajax/upload_file_ajax")

    const visible = ref(false);
    const articletitle = ref('');
    const articlecontent = ref('');
    const isEpub = ref(false);
    let book = null;

    const currentPage = ref(1);
    const totalPages = ref(1);
    const pageContent = ref([]);
    const hasBookmark = ref(false);
    const readerContent = ref(null);
    const jumpPage = ref(1);
    const resizeObserver = ref(null);
    const currentBlobUrl = ref(null);
    const isDarkTheme = ref(false);
    let rendition = null;

    const currentPageContent = computed(() => {
      return pageContent.value[currentPage.value - 1] || '';
    });

    const chaptersVisible = ref(false);
    const chapters = ref([]);
    const currentChapter = ref('');

    const splitContentIntoPages = (content, containerHeight, containerWidth) => {
      const lineHeight = 1.6;
      const currentFontSize = fontSize.value;
      const padding = 40;
      const availableWidth = containerWidth - padding;
      const availableHeight = containerHeight - padding;
      
      // 创建临时div来计算实际文本高度
      const tempDiv = document.createElement('div');
      tempDiv.style.cssText = `
        position: absolute;
        visibility: hidden;
        width: ${availableWidth}px;
        font-size: ${currentFontSize}px;
        line-height: ${lineHeight};
        white-space: pre-wrap;
        word-wrap: break-word;
      `;
      document.body.appendChild(tempDiv);

      // 处理连续换行，将多个连续换行替换为单个换行
      content = content.replace(/\n{3,}/g, '\n\n');
      const paragraphs = content.split(/\r?\n/);
      const pages = [];
      let currentPage = [];
      let currentHeight = 0;

      paragraphs.forEach((paragraph, index) => {
        // 如果是空行，直接添加到当前页
        if (paragraph.trim() === '') {
          currentPage.push(paragraph);
          currentHeight += currentFontSize * lineHeight;
          return;
        }

        tempDiv.textContent = paragraph;
        const paragraphHeight = tempDiv.offsetHeight;

        // 如果当前段落加上已有内容超过页面高度，且当前页已有内容，则创建新页
        if (currentHeight + paragraphHeight > availableHeight && currentPage.length > 0) {
          pages.push(currentPage.join('\n'));
          currentPage = [];
          currentHeight = 0;
        }

        // 如果单个段落超过一页高度，需要分割
        if (paragraphHeight > availableHeight) {
          let remainingText = paragraph;
          while (remainingText.length > 0) {
            let splitIndex = Math.floor(remainingText.length / 2);
            let testText = remainingText.slice(0, splitIndex);
            
            // 二分查找合适的分割点
            while (splitIndex > 0) {
              tempDiv.textContent = testText;
              if (tempDiv.offsetHeight <= availableHeight) {
                break;
              }
              splitIndex = Math.floor(splitIndex / 2);
              testText = remainingText.slice(0, splitIndex);
            }

            // 尝试在句子或段落结束处分割
            const lastPeriod = remainingText.lastIndexOf('。', splitIndex);
            const lastComma = remainingText.lastIndexOf('，', splitIndex);
            const lastSpace = remainingText.lastIndexOf(' ', splitIndex);
            const splitPoint = Math.max(lastPeriod, lastComma, lastSpace);
            
            if (splitPoint > 0) {
              splitIndex = splitPoint + 1;
            }

            const chunk = remainingText.slice(0, splitIndex);
            if (currentPage.length > 0) {
              pages.push(currentPage.join('\n'));
              currentPage = [];
              currentHeight = 0;
            }
            pages.push(chunk);
            remainingText = remainingText.slice(splitIndex);
          }
        } else {
          currentPage.push(paragraph);
          currentHeight += paragraphHeight;
        }
      });

      if (currentPage.length > 0) {
        pages.push(currentPage.join('\n'));
      }

      document.body.removeChild(tempDiv);
      return pages;
    };

    const prevPage = () => {
      if (rendition) {
        rendition.prev();
        updatePageInfo();
      }
    };

    const nextPage = () => {
      if (rendition) {
        rendition.next();
        updatePageInfo();
      }
    };

    const toggleTheme = () => {
      isDarkTheme.value = !isDarkTheme.value;
      if (rendition) {
        if (isDarkTheme.value) {
          rendition.themes.select('dark');
          document.documentElement.classList.add('dark-theme');
        } else {
          rendition.themes.default();
          document.documentElement.classList.remove('dark-theme');
        }
      }
    };

    const toggleBookmark = () => {
      if (rendition) {
        hasBookmark.value = !hasBookmark.value;
        if (hasBookmark.value) {
          saveBookmark();
          message.success('已添加书签');
        } else {
          removeBookmark();
          message.success('已删除书签');
        }
      }
    };

    const saveBookmark = () => {
      if (rendition) {
        const location = rendition.currentLocation();
        if (location) {
          const bookmark = {
            page: currentPage.value,
            totalPages: totalPages.value,
            location: location.start.cfi,
            title: articletitle.value
          };
          localStorage.setItem(`bookmark_${articletitle.value}`, JSON.stringify(bookmark));
        }
      }
    };

    const removeBookmark = () => {
      localStorage.removeItem(`bookmark_${articletitle.value}`);
    };

    const loadBookmark = () => {
      const bookmarkStr = localStorage.getItem(`bookmark_${articletitle.value}`);
      if (bookmarkStr) {
        try {
          const bookmark = JSON.parse(bookmarkStr);
          hasBookmark.value = true;
          if (rendition) {
            rendition.display(bookmark.location);
            updatePageInfo();
          }
        } catch (error) {
          console.error('加载书签失败:', error);
        }
      }
    };

    const handlePageJump = (page) => {
      if (rendition && page >= 1 && page <= totalPages.value) {
        rendition.display(page);
        updatePageInfo();
      }
    };

    const updatePageInfo = () => {
      if (rendition) {
        const location = rendition.currentLocation();
        if (location) {
          currentPage.value = location.start.displayed.page;
          totalPages.value = location.start.displayed.total;
        }
      }
    };

    const updateContent = () => {
      if (readerContent.value && articlecontent.value) {
        const containerHeight = readerContent.value.clientHeight;
        const containerWidth = readerContent.value.clientWidth;
        pageContent.value = splitContentIntoPages(articlecontent.value, containerHeight, containerWidth);
        totalPages.value = pageContent.value.length;
        if (currentPage.value > totalPages.value) {
          currentPage.value = totalPages.value;
        }
        jumpPage.value = Math.min(jumpPage.value, totalPages.value);
      }
    };

    const readBook = (file, ext) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));
      
        isEpub.value = false;
        proxy.$http.post('/ajax/read_file_ajax/', params).then(res => {
          if (res.data.code == '200') {
            visible.value = true;
            articletitle.value = file.replace("attachment/", "");
            articlecontent.value = res.data.data;
            nextTick(() => {
              updateContent();
              jumpPage.value = 1;
              loadBookmark();
            });
          } else {
            message.error(res.data.msg || '加载失败');
          }
        }).catch(error => {
          console.error('请求错误:', error);
          message.error('加载失败，请重试');
        });
    };

    const showChapters = () => {
      chaptersVisible.value = true;
    };

    const handleChapterSelect = ({ key }) => {
      if (rendition) {
        rendition.display(key);
        updatePageInfo();
        chaptersVisible.value = false;
      }
    };

    const loadChapters = async () => {
      if (book) {
        try {
          const navigation = await book.loaded.navigation;
          chapters.value = navigation.toc.map(item => ({
            label: item.label,
            href: item.href
          }));
        } catch (error) {
          console.error('加载章节失败:', error);
          message.error('加载章节列表失败');
        }
      }
    };

    const readEpubBook = (fileUrl, ext) => {
      isEpub.value = true;
      visible.value = false;
      articletitle.value = fileUrl.replace("attachment/", "");
      currentPage.value = 1;
      totalPages.value = 1;
      hasBookmark.value = false;
      chapters.value = [];
      currentChapter.value = '';
      
      nextTick(async () => {
        if (book) {
          book.destroy();
        }
        try {
          console.log('开始创建EPUB实例...');
          console.log('EPUB文件URL:', fileUrl);
          
          book = ePub(fileUrl, {
            restore: true,
            reload: true
          });

          // 添加EPUB加载事件监听
          book.on('loaded', () => {
            console.log('EPUB loaded事件触发');
          });

          book.on('error', (error) => {
            console.error('EPUB error事件触发:', error);
            message.error('电子书加载失败：' + error.message);
          });

          // 添加超时处理
          const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => {
              reject(new Error('EPUB加载超时'));
            }, 10000); // 10秒超时
          });

          // 等待EPUB加载完成
          console.log('等待EPUB加载...');
          try {
            await Promise.race([book.ready, timeoutPromise]);
            console.log('EPUB加载完成');
          } catch (error) {
            console.error('EPUB加载失败:', error);
            message.error('电子书加载超时，请重试');
            throw error;
          }
          
          console.log('开始创建rendition...');
          rendition = book.renderTo("epub-viewer", {
            width: "100%",
            height: "100%",
            spread: "none",
            flow: "paginated",
            minSpreadWidth: 1000,
            manager: "continuous",
            allowScriptedContent: true,
            allowPopups: true
          });

          // 设置初始样式
          rendition.themes.default({
            body: {
              font: '16px "PingFang SC", "Microsoft YaHei", sans-serif',
              lineHeight: '1.6',
              padding: '20px',
              margin: '0',
              background: '#fff',
              color: '#000'
            }
          });

          // 注册深色主题
          rendition.themes.register('dark', {
            body: {
              background: '#1f1f1f',
              color: '#fff'
            }
          });

          // 初始化主题状态
          isDarkTheme.value = document.documentElement.classList.contains('dark-theme');
          if (isDarkTheme.value) {
            rendition.themes.select('dark');
          }

          // 添加加载事件监听
          rendition.on('rendered', (section) => {
            console.log('EPUB渲染完成:', section);
            updatePageInfo();
            currentChapter.value = section.href;
            // 检查是否有书签
            loadBookmark();
          });

          rendition.on('error', (error) => {
            console.error('EPUB渲染错误:', error);
            message.error('电子书渲染失败：' + error.message);
          });

          // 显示内容
          console.log('开始显示内容...');
          try {
            // 获取目录
            const toc = await book.loaded.navigation;
            console.log('目录加载完成:', toc);
            
            // 获取第一个章节
            const firstChapter = await book.spine.get(0);
            console.log('第一个章节:', firstChapter);
            
            // 显示第一个章节
            await rendition.display(firstChapter.href);
            console.log('EPUB显示完成');
            
            // 检查渲染状态
            const currentLocation = rendition.currentLocation();
            console.log('当前位置:', currentLocation);

            // 添加导航事件
            rendition.on('keyup', (e) => {
              if (e.key === 'ArrowLeft') {
                rendition.prev();
                updatePageInfo();
              }
              if (e.key === 'ArrowRight') {
                rendition.next();
                updatePageInfo();
              }
            });

            // 添加点击导航
            rendition.on('click', (e) => {
              const { clientX } = e;
              const { width } = rendition.getBounds();
              if (clientX < width / 2) {
                rendition.prev();
                updatePageInfo();
              } else {
                rendition.next();
                updatePageInfo();
              }
            });

            // 强制重新渲染一次
            setTimeout(() => {
              rendition.resize();
              rendition.display();
            }, 100);

            // 加载章节列表
            await loadChapters();
          } catch (error) {
            console.error('EPUB显示失败:', error);
            message.error('电子书显示失败：' + error.message);
            throw error;
          }
        } catch (error) {
          console.error('EPUB加载错误:', error);
          console.error('错误详情:', error.stack);
          message.error('电子书加载失败，请重试');
          if (book) {
            book.destroy();
            book = null;
          }
        }
      });
    };

    const closeEpubReader = () => {
      isEpub.value = false;
      visible.value = false;
      chaptersVisible.value = false;
      if (book) {
        book.destroy();
        book = null;
      }
      rendition = null;
      articletitle.value = '';
      hasBookmark.value = false;
      chapters.value = [];
      currentChapter.value = '';
    };

    const onClose = () => {
      visible.value = false;
      currentPage.value = 1;
      totalPages.value = 1;
      pageContent.value = [];
      hasBookmark.value = false;
      articlecontent.value = '';
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
        } 
        else{
          message.error(res.data.msg);
        }
        defaultPercent.value = 100;
        loadingdone.value = true
      });
      if (readerContent.value) {
        resizeObserver.value = new ResizeObserver(() => {
          updateContent();
        });
        resizeObserver.value.observe(readerContent.value);
      }

      window.addEventListener('resize', () => {
        updateContent();
      });
    })
    onUnmounted(() => {
      if (resizeObserver.value) {
        resizeObserver.value.disconnect();
      }
      window.removeEventListener('resize', updateContent);
      if (currentBlobUrl.value) {
        URL.revokeObjectURL(currentBlobUrl.value);
      }
      if (book) {
        book.destroy();
      }
    });
    const handleBeforeUpload = (file) => {
      bbb.value=new Date().getTime();
      file_key.value = "attachment/"+file.name;
    }
    const handleChange = info => {
      const status = info.file.status;    
      if (status !== 'uploading') {
      }
      if (status === 'done') {
        if (info.file.response.code=="200"){
          message.success(`${info.file.name} 上传成功.`);
          fileitems.value = info.file.response.data.documents;
        }
        else{
          message.error(info.file.response.msg);
        }
      } else if (status === 'error') {
        message.error(`${info.file.name} 上传失败.`);
      }
    };
    const deletefile = (file) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("file_b64", proxy.$func.urlsafe_b64encode(Base64.encode(file)));
      proxy.$http.post('/ajax/delete_file_ajax/', params).then(res => {
        fileitems.value = res.data.data.documents
      });
    };
    return {
      aaa,
      bbb,
      upload_url,
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
      loadingdone,
      readBook,
      readEpubBook,
      visible,
      onClose,
      articletitle,
      articlecontent,
      isEpub,
      fontSize,
      currentPage,
      totalPages,
      currentPageContent,
      readerContent,
      hasBookmark,
      prevPage,
      nextPage,
      toggleBookmark,
      jumpPage,
      handlePageJump,
      updateContent,
      closeEpubReader,
      isDarkTheme,
      toggleTheme,
      chaptersVisible,
      chapters,
      currentChapter,
      showChapters,
      handleChapterSelect,
    };
  },
}
</script>
