<!--
  页面标题栏组件：显示标题和刷新图标
  标题在左，刷新图标在右，点击刷新可强制重新加载当前页面（绕过 keep-alive 缓存）
-->
<template>
  <div class="page-header-wrap">
    <h3 class="content-title page-header-title">{{ title }}</h3>
    <SyncOutlined class="page-header-refresh" title="强制刷新本页" @click="onRefresh" />
  </div>
</template>

<script>
import { SyncOutlined } from "@ant-design/icons-vue";
import { inject } from "vue";

export default {
  name: "PageHeader",
  components: { SyncOutlined },
  props: {
    title: { type: String, default: "" },
  },
  setup() {
    const refreshPage = inject("refreshPage");
    const onRefresh = () => {
      if (typeof refreshPage === "function") refreshPage();
    };
    return { onRefresh };
  },
};
</script>

<style scoped>
.page-header-wrap {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 15px;
  margin-left: 30px;
  margin-right: 30px;
  margin-bottom: 15px;
}

.page-header-title {
  margin: 0;
  flex: 1;
}

.page-header-refresh {
  font-size: 18px;
  cursor: pointer;
  color: #1890ff;
  padding: 4px;
}

.page-header-refresh:hover {
  color: #40a9ff;
}
</style>
