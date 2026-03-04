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
      <div class="reader-content" ref="readerContent" :style="{ fontSize: fontSize + 'px', lineHeight: lineHeight }">
        <div v-if="isContentReady && currentPageContent" v-html="currentPageContent"></div>
        <div v-else class="loading-text">加载中...</div>
      </div>
      <div class="reader-toolbar">
        <div class="reader-settings">
          <span>字体</span>
          <a-select v-model:value="fontSize" size="small" style="width: 70px;" @change="onSettingChange">
            <a-select-option v-for="s in fontSizeOptions" :key="s" :value="s">{{ s }}px</a-select-option>
          </a-select>
          <span style="margin-left: 8px;">行距</span>
          <a-select v-model:value="lineHeight" size="small" style="width: 70px;" @change="onSettingChange">
            <a-select-option v-for="h in lineHeightOptions" :key="h" :value="h">{{ h }}</a-select-option>
          </a-select>
        </div>
        <div class="reader-toolbar-row">
         
          <div class="page-jump">
            <span>跳转到</span>
            <a-input-number 
              v-model:value="jumpPage" 
              :min="1" 
              :max="totalPages"
              @pressEnter="handlePageJump"
              :controls="false"
            />
            <span>页/{{ totalPages }} 页</span>
            <a-button type="primary" @click="handlePageJump" style="margin-left: 5px;">确定</a-button>
          </div>
          <a-button-group>
            <a-button @click="goToFirstPage" :disabled="currentPage === 1" title="第一页">
              <template #icon><double-left-outlined /></template>
            </a-button>
            <a-button @click="goToLastPage" :disabled="currentPage === totalPages" title="最后一页">
              <template #icon><double-right-outlined /></template>
            </a-button>
            <a-button @click="prevPage" :disabled="currentPage === 1" class="btn-prev-spacing">
              <template #icon><left-outlined /></template>
            </a-button>
            <a-button @click="nextPage" :disabled="currentPage === totalPages">
              <template #icon><right-outlined /></template>
            </a-button>
          </a-button-group>
        </div>
     
      </div>
    </div>
  </a-drawer>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { DoubleLeftOutlined, LeftOutlined, RightOutlined, DoubleRightOutlined } from '@ant-design/icons-vue';

export default {
  name: 'TxtReader',
  components: {
    DoubleLeftOutlined,
    LeftOutlined,
    RightOutlined,
    DoubleRightOutlined
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
    },
    chunkPath: {
      type: String,
      default: ''
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const currentPage = ref(1);
    const totalPages = ref(1);
    const pageContent = ref([]);
    const readerContent = ref(null);
    const jumpPage = ref(1);
    const resizeObserver = ref(null);
    const STORAGE_KEY = 'txtreader_settings';
    const fontSizeOptions = [12, 14, 16, 18, 20, 22, 24];
    const lineHeightOptions = [1.2, 1.4, 1.6, 1.8, 2.0, 2.2, 2.5];
    const fontSize = ref(loadSetting('fontSize', 16));
    const lineHeight = ref(loadSetting('lineHeight', 1.6));
    const isContentReady = ref(false);

    function loadSetting(key, defaultVal) {
      try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
          const obj = JSON.parse(saved);
          const val = obj[key];
          if (key === 'fontSize' && fontSizeOptions.includes(val)) return val;
          if (key === 'lineHeight' && lineHeightOptions.includes(val)) return val;
        }
      } catch (e) {}
      return defaultVal;
    }
    function saveSettings() {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
          fontSize: fontSize.value,
          lineHeight: lineHeight.value
        }));
      } catch (e) {}
    }

    const POSITION_STORAGE_KEY = 'txtreader_positions';
    function loadPosition(chunkPath) {
      if (!chunkPath) return null;
      try {
        const saved = localStorage.getItem(POSITION_STORAGE_KEY);
        if (saved) {
          const obj = JSON.parse(saved);
          return obj[chunkPath] || null;
        }
      } catch (e) {}
      return null;
    }
    function savePosition() {
      const path = props.chunkPath;
      if (!path) return;
      try {
        const saved = localStorage.getItem(POSITION_STORAGE_KEY);
        const obj = saved ? JSON.parse(saved) : {};
        obj[path] = {
          page: currentPage.value,
          fontSize: fontSize.value,
          lineHeight: lineHeight.value
        };
        localStorage.setItem(POSITION_STORAGE_KEY, JSON.stringify(obj));
      } catch (e) {}
    }

    const currentPageContent = computed(() => {
      return pageContent.value[currentPage.value - 1] || '';
    });

    // 监听content变化：同分片且未调整字体/行距时恢复上次页码，否则从第一页
    watch(() => props.content, (newContent) => {
      if (newContent && readerContent.value) {
        updateContent();
        nextTick(() => {
          const path = props.chunkPath;
          if (path) {
            const saved = loadPosition(path);
            if (saved && saved.fontSize === fontSize.value && saved.lineHeight === lineHeight.value) {
              const page = Math.min(Math.max(1, saved.page), totalPages.value);
              if (page >= 1 && page <= totalPages.value) {
                currentPage.value = page;
                jumpPage.value = page;
              } else {
                currentPage.value = 1;
                jumpPage.value = 1;
              }
            } else {
              currentPage.value = 1;
              jumpPage.value = 1;
            }
          } else {
            currentPage.value = 1;
            jumpPage.value = 1;
          }
        });
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

      const currentLineHeight = lineHeight.value;
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
        line-height: ${currentLineHeight};
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
          currentHeight += currentFontSize * currentLineHeight;
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

    /** 跳转到第一页 */
    const goToFirstPage = () => {
      if (currentPage.value > 1) {
        currentPage.value = 1;
        jumpPage.value = 1;
      }
    };

    /** 跳转到最后一页 */
    const goToLastPage = () => {
      if (currentPage.value < totalPages.value && totalPages.value > 0) {
        currentPage.value = totalPages.value;
        jumpPage.value = totalPages.value;
      }
    };

    const onSettingChange = () => {
      saveSettings();
      nextTick(() => updateContent());
    };

    const onClose = () => {
      savePosition();
      emit('close');
    };

    /** 键盘翻页：左/上=上一页，右/下=下一页 */
    const handleKeydown = (e) => {
      if (!props.visible) return;
      const tag = (e.target?.tagName || '').toUpperCase();
      if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;
      if (['ArrowLeft', 'ArrowUp'].includes(e.key)) {
        e.preventDefault();
        prevPage();
      } else if (['ArrowRight', 'ArrowDown'].includes(e.key)) {
        e.preventDefault();
        nextPage();
      }
    };

    // 翻页时保存阅读位置
    watch(currentPage, () => {
      savePosition();
    });

    onMounted(() => {
      window.addEventListener('keydown', handleKeydown);
      if (readerContent.value) {
        resizeObserver.value = new ResizeObserver(() => {
          updateContent();
        });
        resizeObserver.value.observe(readerContent.value);
      }

      window.addEventListener('resize', updateContent);
      nextTick(() => {
        updateContent();
      });
    });

    onUnmounted(() => {
      window.removeEventListener('keydown', handleKeydown);
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
      jumpPage,
      isContentReady,
      fontSize,
      fontSizeOptions,
      lineHeight,
      lineHeightOptions,
      onSettingChange,
      prevPage,
      nextPage,
      goToFirstPage,
      goToLastPage,
      handlePageJump,
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
  border-top: 1px solid #f0f0f0;
  background: #fff;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.reader-toolbar-row {
  display: flex;
  align-items: center;
}

/* 最后一页与上一页按钮之间的间距 */
.reader-toolbar :deep(.btn-prev-spacing) {
  margin-left: 12px;
}

.reader-settings {
  display: flex;
  align-items: center;
  gap: 8px;
}

.page-jump {
  margin-right: 15px;
  display: flex;
  align-items: center;
}

/* 页码输入框缩小宽度 */
.page-jump :deep(.ant-input-number) {
  width: 48px;
  min-width: 48px;
}
.page-jump :deep(.ant-input-number-input) {
  width: 100%;
  padding: 0 4px;
  margin: 0 5px;
}

.reader-content {
  flex: 1;
  padding: 20px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  margin: 20px;
  white-space: pre-wrap;
  word-wrap: break-word;
  height: calc(100% - 40px);
  position: relative;
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
  border-top: 1px solid #f0f0f0;
}

.drawer-dark-theme .reader-toolbar {
  background: #1f1f1f;
  border-top: 1px solid #303030;
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