<template>
  <div class="epub-reader-container" :class="'content-'+$cookies.get('contenttheme')+'-theme'">
    <div class="epub-reader-header">
      <div class="epub-reader-title">
        <h3>{{ title }}</h3>
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
        <a-button @click="closeReader" type="primary" style="margin-left: 10px;">关闭</a-button>
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
      :class="'content-'+$cookies.get('contenttheme')+'-theme'"
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
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { message } from 'ant-design-vue';
import { LeftOutlined, RightOutlined, BookOutlined, MenuOutlined } from '@ant-design/icons-vue';
import ePub from 'epubjs';

export default {
  name: 'EpubReader',
  components: {
    LeftOutlined,
    RightOutlined,
    BookOutlined,
    MenuOutlined
  },
  props: {
    fileUrl: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const currentPage = ref(1);
    const totalPages = ref(1);
    const hasBookmark = ref(false);
    const chaptersVisible = ref(false);
    const chapters = ref([]);
    const currentChapter = ref('');
    const isDarkTheme = ref(false);
    let book = null;
    let rendition = null;

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
            title: props.title
          };
          localStorage.setItem(`bookmark_${props.title}`, JSON.stringify(bookmark));
        }
      }
    };

    const removeBookmark = () => {
      localStorage.removeItem(`bookmark_${props.title}`);
    };

    const loadBookmark = () => {
      const bookmarkStr = localStorage.getItem(`bookmark_${props.title}`);
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

    const initEpub = async () => {
      try {
        console.log('开始创建EPUB实例...');
        console.log('EPUB文件URL:', props.fileUrl);
        
        book = ePub(props.fileUrl, {
          restore: true,
          reload: true
        });

        book.on('loaded', () => {
          console.log('EPUB loaded事件触发');
        });

        book.on('error', (error) => {
          console.error('EPUB error事件触发:', error);
          message.error('电子书加载失败：' + error.message);
        });

        const timeoutPromise = new Promise((_, reject) => {
          setTimeout(() => {
            reject(new Error('EPUB加载超时'));
          }, 10000);
        });

        await Promise.race([book.ready, timeoutPromise]);
        console.log('EPUB加载完成');
        
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

        const isDarkTheme = $cookies.get('contenttheme') === 'dark';
        
        rendition.themes.default({
          body: {
            font: '16px "PingFang SC", "Microsoft YaHei", sans-serif',
            lineHeight: '1.6',
            padding: '20px',
            margin: '0',
            background: isDarkTheme ? '#141414' : '#fff',
            color: isDarkTheme ? '#e6e6e6' : '#000'
          }
        });

        rendition.themes.register('dark', {
          body: {
            background: '#141414',
            color: '#e6e6e6'
          },
          '::selection': {
            background: '#1890ff',
            color: '#fff'
          },
          'a': {
            color: '#1890ff'
          },
          'h1, h2, h3, h4, h5, h6': {
            color: '#fff'
          }
        });

        if (isDarkTheme) {
          rendition.themes.select('dark');
        }

        rendition.on('rendered', (section) => {
          console.log('EPUB渲染完成:', section);
          updatePageInfo();
          currentChapter.value = section.href;
          loadBookmark();
        });

        rendition.on('error', (error) => {
          console.error('EPUB渲染错误:', error);
          message.error('电子书渲染失败：' + error.message);
        });

        const toc = await book.loaded.navigation;
        const firstChapter = await book.spine.get(0);
        await rendition.display(firstChapter.href);

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

        setTimeout(() => {
          rendition.resize();
          rendition.display();
        }, 100);

        await loadChapters();
      } catch (error) {
        console.error('EPUB加载错误:', error);
        message.error('电子书加载失败，请重试');
        if (book) {
          book.destroy();
          book = null;
        }
      }
    };

    const closeReader = () => {
      if (book) {
        book.destroy();
        book = null;
      }
      rendition = null;
      emit('close');
    };

    onMounted(() => {
      initEpub();
    });

    onUnmounted(() => {
      if (book) {
        book.destroy();
      }
    });

    return {
      currentPage,
      totalPages,
      hasBookmark,
      chaptersVisible,
      chapters,
      currentChapter,
      prevPage,
      nextPage,
      toggleBookmark,
      handlePageJump,
      showChapters,
      handleChapterSelect,
      closeReader
    };
  }
};
</script>

<style scoped>
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

.content-dark-theme {
  background: #141414;
  color: #e6e6e6;
}

.content-dark-theme .epub-reader-header {
  background: #141414;
  border-bottom-color: #303030;
}

.content-dark-theme .epub-reader-container {
  background: #141414;
}

.content-dark-theme :deep(.ant-drawer-content-wrapper) {
  background: #141414;
}

.content-dark-theme :deep(.ant-menu) {
  background: #141414;
  color: #e6e6e6;
}

.content-dark-theme :deep(.ant-menu-item) {
  color: #e6e6e6;
}

.content-dark-theme :deep(.ant-menu-item-selected) {
  background: #1890ff;
  color: #fff;
}

.content-dark-theme :deep(.ant-menu-item:hover) {
  color: #1890ff;
}

.content-dark-theme :deep(.ant-input-number) {
  background: #1f1f1f;
  border-color: #434343;
  color: #e6e6e6;
}

.content-dark-theme :deep(.ant-input-number-input) {
  color: #e6e6e6;
}

.content-dark-theme :deep(.ant-btn) {
  background: #1f1f1f;
  border-color: #434343;
  color: #e6e6e6;
}

.content-dark-theme :deep(.ant-btn:hover) {
  border-color: #1890ff;
  color: #1890ff;
}

.content-dark-theme :deep(.ant-btn-primary) {
  background: #1890ff;
  border-color: #1890ff;
  color: #fff;
}

.content-dark-theme :deep(.ant-btn-primary:hover) {
  background: #40a9ff;
  border-color: #40a9ff;
  color: #fff;
}

.content-light-theme {
  background: #fff;
  color: #000;
}

.content-light-theme .epub-reader-header {
  background: #fff;
  border-bottom-color: #f0f0f0;
}

.content-light-theme .epub-reader-container {
  background: #fff;
}

.content-light-theme :deep(.ant-drawer-content-wrapper) {
  background: #fff;
}

.content-light-theme :deep(.ant-menu) {
  background: #fff;
  color: #000;
}

.content-light-theme :deep(.ant-menu-item) {
  color: #000;
}

.content-light-theme :deep(.ant-menu-item-selected) {
  background: #e6f7ff;
  color: #1890ff;
}

.content-light-theme :deep(.ant-menu-item:hover) {
  color: #1890ff;
}
</style> 