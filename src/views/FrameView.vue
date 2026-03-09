<script>
import { getCurrentInstance, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { CloseOutlined } from "@ant-design/icons-vue";
import { frameTabsStore, BOOKMARKS_TAB_ID } from "@/stores/frameTabs";
import HomeView from "@/views/HomeView.vue";

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
    /** iframe 元素引用，用于自适应高度 */
    const iframeRef = ref(null);
    /** iframe 动态高度（px），null 时使用 100% 填满容器 */
    const iframeHeight = ref(null);

    /** 根据 route 同步 store 并更新 iframe src（/home 与 /frame/__bookmarks__ 均视为书签页） */
    const syncFromRoute = () => {
      const id = route.params.id ?? (route.path === "/home" ? BOOKMARKS_TAB_ID : null);
      if (!id) return;
      const title = route.query.title || null;
      frameTabsStore.addTab(id, title);
      if (id === BOOKMARKS_TAB_ID) {
        activeFrameSrc.value = "";
      } else {
        activeFrameSrc.value = proxy.$remoteDomain + "/go/" + id;
        iframeHeight.value = null; // 重置高度，等待 onload 重新计算
      }
    };

    /** 根据 iframe 内部文档高度自适应，消除 iframe 内部滚动条（仅同源有效） */
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
        // 跨域无法访问 contentDocument，保持默认高度
      }
    };

    /** iframe 加载完成后尝试自适应高度，并延迟重试以适配异步渲染 */
    const onIframeLoad = () => {
      resizeIframeToContent();
      setTimeout(resizeIframeToContent, 500);
      setTimeout(resizeIframeToContent, 1500);
    };

    // 首次挂载及路由变化时同步（/home 与 /frame/:id 均需处理）
    syncFromRoute();
    watch(
      () => [route.path, route.params.id, route.query.title],
      () => syncFromRoute(),
      { immediate: false }
    );

    /** 切换到指定标签：书签页用 /home，frame 页用 /frame/:id */
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
    <!-- 内容区：书签页显示 HomeView，其余显示 iframe -->
    <div class="frame-content">
      <HomeView v-if="frameTabsStore.activeId === BOOKMARKS_TAB_ID" />
      <iframe
        v-else-if="activeFrameSrc"
        ref="iframeRef"
        :src="activeFrameSrc"
        :style="iframeHeight ? { height: iframeHeight + 'px' } : {}"
        title="External Content"
        frameborder="0"
        allowfullscreen
        @load="onIframeLoad"
      >
      </iframe>
    </div>
  </div>
</template>

<style scoped>
/* 整体容器 */
.frame-view-root {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: calc(100vh - 180px);
  min-height: 300px;
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

/* 内容区：书签页或 iframe */
.frame-content {
  flex: 1;
  min-height: 0;
  overflow: auto;
}

/* iframe：宽度 100%，高度由 JS 自适应或默认填满 */
.frame-content iframe {
  width: 100%;
  min-height: 100%;
  border: none;
  display: block;
}
</style>
