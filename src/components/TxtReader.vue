<template>
  <a-drawer
    :visible="visible"
    :width="500"
    :title="title"
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
        <div v-if="isContentReady && currentPageContent" v-html="currentPageContent"></div>
        <div v-else class="loading-text">加载中...</div>
      </div>
    </div>
  </a-drawer>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { message } from 'ant-design-vue';
import { LeftOutlined, RightOutlined, BookOutlined } from '@ant-design/icons-vue';

export default {
  name: 'TxtReader',
  components: {
    LeftOutlined,
    RightOutlined,
    BookOutlined
  },
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    content: {
      type: String,
      required: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const currentPage = ref(1);
    const totalPages = ref(1);
    const pageContent = ref([]);
    const hasBookmark = ref(false);
    const readerContent = ref(null);
    const jumpPage = ref(1);
    const resizeObserver = ref(null);
    const fontSize = ref(16);
    const isContentReady = ref(false);

    const currentPageContent = computed(() => {
      return pageContent.value[currentPage.value - 1] || '';
    });

    // 监听content变化
    watch(() => props.content, (newContent) => {
      if (newContent && readerContent.value) {
        updateContent();
      }
    });

    // 监听visible变化
    watch(() => props.visible, (newVisible) => {
      if (newVisible) {
        nextTick(() => {
          updateContent();
        });
      }
    });

    const splitContentIntoPages = (content, containerHeight, containerWidth) => {
      if (!content || !containerHeight || !containerWidth) {
        return [];
      }

      const lineHeight = 1.6;
      const currentFontSize = fontSize.value;
      const padding = 40;
      const availableWidth = containerWidth - padding;
      const availableHeight = containerHeight - padding;
      
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

      content = content.replace(/\n{3,}/g, '\n\n');
      const paragraphs = content.split(/\r?\n/);
      const pages = [];
      let currentPage = [];
      let currentHeight = 0;

      paragraphs.forEach((paragraph, index) => {
        if (paragraph.trim() === '') {
          currentPage.push(paragraph);
          currentHeight += currentFontSize * lineHeight;
          return;
        }

        tempDiv.textContent = paragraph;
        const paragraphHeight = tempDiv.offsetHeight;

        if (currentHeight + paragraphHeight > availableHeight && currentPage.length > 0) {
          pages.push(currentPage.join('\n'));
          currentPage = [];
          currentHeight = 0;
        }

        if (paragraphHeight > availableHeight) {
          let remainingText = paragraph;
          while (remainingText.length > 0) {
            let splitIndex = Math.floor(remainingText.length / 2);
            let testText = remainingText.slice(0, splitIndex);
            
            while (splitIndex > 0) {
              tempDiv.textContent = testText;
              if (tempDiv.offsetHeight <= availableHeight) {
                break;
              }
              splitIndex = Math.floor(splitIndex / 2);
              testText = remainingText.slice(0, splitIndex);
            }

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
      isContentReady.value = true;
      return pages;
    };

    const updateContent = () => {
      if (readerContent.value && props.content) {
        const containerHeight = readerContent.value.clientHeight;
        const containerWidth = readerContent.value.clientWidth;
        if (containerHeight && containerWidth) {
          pageContent.value = splitContentIntoPages(props.content, containerHeight, containerWidth);
          totalPages.value = pageContent.value.length;
          if (currentPage.value > totalPages.value) {
            currentPage.value = totalPages.value;
          }
          jumpPage.value = Math.min(jumpPage.value, totalPages.value);
        }
      }
    };

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--;
        jumpPage.value = currentPage.value;
      }
    };

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++;
        jumpPage.value = currentPage.value;
      }
    };

    const handlePageJump = () => {
      if (jumpPage.value >= 1 && jumpPage.value <= totalPages.value) {
        currentPage.value = jumpPage.value;
      }
    };

    const toggleBookmark = () => {
      hasBookmark.value = !hasBookmark.value;
      if (hasBookmark.value) {
        saveBookmark();
        message.success('已添加书签');
      } else {
        removeBookmark();
        message.success('已删除书签');
      }
    };

    const saveBookmark = () => {
      const bookmark = {
        page: currentPage.value,
        totalPages: totalPages.value,
        title: props.title
      };
      localStorage.setItem(`bookmark_${props.title}`, JSON.stringify(bookmark));
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
          currentPage.value = bookmark.page;
          jumpPage.value = bookmark.page;
        } catch (error) {
          console.error('加载书签失败:', error);
        }
      }
    };

    const onClose = () => {
      emit('close');
    };

    onMounted(() => {
      if (readerContent.value) {
        resizeObserver.value = new ResizeObserver(() => {
          updateContent();
        });
        resizeObserver.value.observe(readerContent.value);
      }

      window.addEventListener('resize', updateContent);
      nextTick(() => {
        updateContent();
        loadBookmark();
      });
    });

    onUnmounted(() => {
      if (resizeObserver.value) {
        resizeObserver.value.disconnect();
      }
      window.removeEventListener('resize', updateContent);
    });

    return {
      currentPage,
      totalPages,
      currentPageContent,
      readerContent,
      hasBookmark,
      jumpPage,
      isContentReady,
      prevPage,
      nextPage,
      handlePageJump,
      toggleBookmark,
      onClose
    };
  }
};
</script>

<style scoped>
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

.loading-text {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  font-size: 16px;
  color: #999;
}
</style> 