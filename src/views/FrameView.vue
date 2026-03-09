<script>
import { getCurrentInstance, ref, watch, nextTick, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { CloseOutlined } from "@ant-design/icons-vue";
import { frameTabsStore, BOOKMARKS_TAB_ID } from "@/stores/frameTabs";
import HomeView from "@/views/HomeView.vue";

/** 无法获取内容高度时的 fallback 高度（px） */
const FALLBACK_HEIGHT = 600;

export default {
  components: {
    CloseOutlined,
    HomeView,
  },
  setup() {
    $cookies.set("selectedkey", "1", "720h");
    $cookies.set("openkey", "");
    const { proxy } = getCurrentInstance();
    const router = useRouter();
    const route = useRoute();

    /** 当前激活标签对应的 iframe src，书签页时为 null */
    const activeFrameSrc = ref("");
    /** iframe 元素引用 */
    const iframeRef = ref(null);
    /** 书签内容区 ref，用于测量高度 */
    const bookmarksContentRef = ref(null);
    /** iframe 内容高度（px），null 表示无法获取，用 fallback */
    const iframeHeight = ref(null);
    /** 书签页内容高度（px），null 表示无法获取，用 fallback */
    const bookmarksContentHeight = ref(null);

    /** 测量书签页内容高度（同源可直接测量） */
    const measureBookmarksContent = () => {
      const el = bookmarksContentRef.value;
      if (!el) return;
      const height = el.scrollHeight || el.offsetHeight || 0;
      bookmarksContentHeight.value = height > 0 ? height : null;
    };

    /** ResizeObserver 监听书签内容变化，内容增删时重新测量 */
    let resizeObserver = null;
    const setupBookmarksResizeObserver = () => {
      resizeObserver?.disconnect();
      const el = bookmarksContentRef.value;
      if (!el) return;
      resizeObserver = new ResizeObserver(() => {
        measureBookmarksContent();
      });
      resizeObserver.observe(el);
    };

    /** 根据 route 同步 store 并更新 iframe src（/home 与 /frame/__bookmarks__ 均视为书签页） */
    const syncFromRoute = () => {
      const id = route.params.id ?? (route.path === "/home" ? BOOKMARKS_TAB_ID : null);
      if (!id) return;
      const title = route.query.title || null;
      frameTabsStore.addTab(id, title);
      if (id === BOOKMARKS_TAB_ID) {
        activeFrameSrc.value = "";
        bookmarksContentHeight.value = null; // 切换回书签页时重新测量
      } else {
        activeFrameSrc.value = proxy.$remoteDomain + "/go/" + id;
        iframeHeight.value = null;
      }
    };

    /** 根据 iframe 内部文档高度自适应（仅同源有效） */
    const resizeIframeToContent = () => {
      const el = iframeRef.value;
      if (!el || !el.contentDocument) return;
      try {
        const doc = el.contentDocument;
        const html = doc.documentElement;
        const body = doc.body;
        const height = Math.max(
          body?.scrollHeight ?? 0,
          body?.offsetHeight ?? 0,
          html?.scrollHeight ?? 0,
          html?.offsetHeight ?? 0
        );
        if (height > 0) {
          iframeHeight.value = height;
        }
      } catch {
        // 跨域无法访问，保持 null，使用 fallback
      }
    };

    /** iframe 加载完成后尝试获取内容高度 */
    const onIframeLoad = () => {
      resizeIframeToContent();
      setTimeout(resizeIframeToContent, 500);
      setTimeout(resizeIframeToContent, 1500);
    };

    syncFromRoute();
    watch(
      () => [route.path, route.params.id, route.query.title],
      () => syncFromRoute(),
      { immediate: false }
    );

    /** 书签页激活时测量内容高度，延迟重试以适配异步渲染 */
    const scheduleBookmarksMeasure = () => {
      nextTick(measureBookmarksContent);
      setTimeout(measureBookmarksContent, 300);
      setTimeout(measureBookmarksContent, 1000);
    };

    watch(
      () => frameTabsStore.activeId === BOOKMARKS_TAB_ID,
      (isBookmarks) => {
        if (isBookmarks) {
          nextTick(() => {
            scheduleBookmarksMeasure();
            setupBookmarksResizeObserver();
          });
        } else {
          resizeObserver?.disconnect();
          resizeObserver = null;
        }
      },
      { immediate: true }
    );

    onMounted(() => {
      if (frameTabsStore.activeId === BOOKMARKS_TAB_ID) {
        nextTick(() => {
          scheduleBookmarksMeasure();
          setupBookmarksResizeObserver();
        });
      }
    });

    onUnmounted(() => {
      resizeObserver?.disconnect();
    });

    /** 切换到指定标签 */
    const switchTab = (id) => {
      frameTabsStore.setActive(id);
      router.push(id === BOOKMARKS_TAB_ID ? { path: "/home" } : { path: "/frame/" + id });
    };

    /** 关闭标签 */
    const closeTab = (id, e) => {
      e?.stopPropagation?.();
      const nextId = frameTabsStore.removeTab(id);
      if (nextId) {
        router.push(nextId === BOOKMARKS_TAB_ID ? { path: "/home" } : { path: "/frame/" + nextId });
      } else {
        router.push({ path: "/home" });
      }
    };

    return {
      frameTabsStore,
      BOOKMARKS_TAB_ID,
      activeFrameSrc,
      iframeRef,
      iframeHeight,
      bookmarksContentRef,
      bookmarksContentHeight,
      FALLBACK_HEIGHT,
      onIframeLoad,
      switchTab,
      closeTab,
    };
  },
};
</script>

<template>
  <div class="frame-view-root">
    <!-- 顶部标签栏：最左侧为书签页，其余为 frame 页面 -->
    <div class="frame-tabs-bar">
      <div
        v-for="tab in frameTabsStore.tabs"
        :key="tab.id"
        class="frame-tab-item"
        :class="{ active: frameTabsStore.activeId === tab.id }"
        @click="switchTab(tab.id)"
      >
        <span class="frame-tab-title">{{ tab.title }}</span>
        <span
          v-if="tab.id !== BOOKMARKS_TAB_ID"
          class="frame-tab-close"
          title="关闭"
          @click="closeTab(tab.id, $event)"
        >
          <CloseOutlined />
        </span>
      </div>
    </div>
    <!-- 内容区：书签页或 iframe，有内容高度则完整显示（无滚动条），否则 600px（显示滚动条） -->
    <div
      class="frame-content"
      :class="{
        'frame-content-grows': frameTabsStore.activeId === BOOKMARKS_TAB_ID && bookmarksContentHeight
      }"
    >
      <!-- 书签页 -->
      <div
        v-if="frameTabsStore.activeId === BOOKMARKS_TAB_ID"
        ref="bookmarksContentRef"
        class="frame-content-inner frame-content-bookmarks"
        :style="{
          height: bookmarksContentHeight ? bookmarksContentHeight + 'px' : FALLBACK_HEIGHT + 'px',
          overflow: bookmarksContentHeight ? 'visible' : 'auto'
        }"
      >
        <HomeView />
      </div>
      <!-- iframe -->
      <div
        v-else-if="activeFrameSrc"
        class="frame-content-inner frame-content-iframe"
        :style="{
          height: iframeHeight ? iframeHeight + 'px' : FALLBACK_HEIGHT + 'px',
          overflow: iframeHeight ? 'visible' : 'auto'
        }"
      >
        <iframe
          ref="iframeRef"
          :src="activeFrameSrc"
          :style="iframeHeight ? { height: iframeHeight + 'px' } : { height: FALLBACK_HEIGHT + 'px' }"
          title="External Content"
          frameborder="0"
          allowfullscreen
          @load="onIframeLoad"
        >
        </iframe>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 整体容器：iframe 时固定高度填满视口，书签有测量高度时允许延伸 */
.frame-view-root {
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: calc(100vh - 180px);
  overflow: visible;
}

/* 顶部标签栏：仅允许横向滚动，禁止纵向滚动条 */
.frame-tabs-bar {
  display: flex;
  flex-wrap: nowrap;
  gap: 2px;
  padding: 6px 0 0 0;
  background: rgba(0, 0, 0, 0.02);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  overflow-x: auto;
  overflow-y: hidden;
  flex-shrink: 0;
}

/* 单个标签 */
.frame-tab-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 6px 6px 0 0;
  cursor: pointer;
  white-space: nowrap;
  background: rgba(0, 0, 0, 0.04);
  transition: background 0.2s;
}

.frame-tab-item:hover {
  background: rgba(0, 0, 0, 0.08);
}

.frame-tab-item.active {
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-bottom-color: transparent;
  margin-bottom: -1px;
  cursor: default;
}

.frame-tab-title {
  font-size: 13px;
  color: rgba(0, 0, 0, 0.85);
}

.frame-tab-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 2px 4px;
  margin: -2px 0;
  font-size: 14px;
  min-width: 20px;
  min-height: 20px;
  color: rgba(0, 0, 0, 0.45);
  border-radius: 4px;
  cursor: pointer;
}

.frame-tab-close:hover {
  color: rgba(0, 0, 0, 0.85);
  background: rgba(0, 0, 0, 0.06);
}

/* 内容区：默认占满剩余空间 */
.frame-content {
  flex: 1;
  min-height: 0;
  overflow: visible;
  width: 100%;
}

/* 书签页有测量高度时，内容区随内容延伸，由页面滚动 */
.frame-content.frame-content-grows {
  flex: 0 0 auto;
  min-height: 0;
}

/* 内容区内层：书签或 iframe 容器 */
.frame-content-inner {
  width: 100%;
}

.frame-content-iframe iframe {
  width: 100%;
  border: none;
  display: block;
}
</style>
