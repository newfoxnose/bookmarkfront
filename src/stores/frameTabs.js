/**
 * Frame 多标签页状态管理
 * 存储已打开的 frame 页面，支持添加、关闭、切换
 */
import { reactive } from "vue";

/** 书签页标签的固定 id，显示在最左侧 */
export const BOOKMARKS_TAB_ID = "__bookmarks__";

export const frameTabsStore = reactive({
  /** 标签列表：{ id, title } */
  tabs: [],
  /** 当前激活的标签 id */
  activeId: null,

  /**
   * 确保书签页标签存在于列表最左侧
   */
  ensureBookmarksTab() {
    const exists = this.tabs.find((t) => t.id === BOOKMARKS_TAB_ID);
    if (!exists) {
      this.tabs.unshift({ id: BOOKMARKS_TAB_ID, title: "书签" });
    }
  },

  /** 判断是否为书签页标签 */
  isBookmarksTab(id) {
    return String(id) === BOOKMARKS_TAB_ID;
  },

  /**
   * 添加或激活标签
   * @param {string} id - 书签 id，或 BOOKMARKS_TAB_ID
   * @param {string} [title] - 标签标题，默认 "链接 {id}"
   */
  addTab(id, title) {
    const strId = String(id);
    // 书签页标签始终在最左侧
    this.ensureBookmarksTab();
    if (strId === BOOKMARKS_TAB_ID) {
      this.activeId = BOOKMARKS_TAB_ID;
      return;
    }
    const displayTitle = title || `链接 ${strId}`;
    const exists = this.tabs.find((t) => t.id === strId);
    if (!exists) {
      this.tabs.push({ id: strId, title: displayTitle });
    } else {
      if (title) {
        exists.title = displayTitle;
      }
    }
    this.activeId = strId;
  },

  /**
   * 关闭标签
   * @param {string} id - 要关闭的标签 id
   * @returns {string|null} 关闭后应跳转的 id，若无则 null（将导航到 /home）
   */
  removeTab(id) {
    const strId = String(id);
    const idx = this.tabs.findIndex((t) => t.id === strId);
    if (idx === -1) return this.activeId;
    this.tabs.splice(idx, 1);
    if (this.tabs.length === 0) {
      this.activeId = null;
      return null;
    }
    if (this.activeId === strId) {
      const nextIdx = Math.min(idx, this.tabs.length - 1);
      this.activeId = this.tabs[nextIdx].id;
      return this.activeId;
    }
    return this.activeId;
  },

  /**
   * 设置当前激活标签
   * @param {string} id - 标签 id
   */
  setActive(id) {
    if (this.tabs.some((t) => t.id === String(id))) {
      this.activeId = String(id);
    }
  },
});
