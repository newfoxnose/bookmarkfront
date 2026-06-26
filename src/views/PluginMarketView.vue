<template>
  <div class="plugin-market">
    <div class="market-header">
      <p>发现并添加您喜欢的功能到常用菜单</p>
    </div>
    
    <div class="plugin-grid">
      <div 
        v-for="plugin in plugins" 
        :key="plugin.key"
        class="plugin-card"
      >
        <div class="plugin-icon">
          <component :is="getIcon(plugin.icon)" />
        </div>
        <h3>{{ plugin.label }}</h3>
        <p>{{ plugin.description }}</p>
        <div class="plugin-actions">
          <a-button 
            type="primary" 
            size="small" 
            @click="navigateTo(plugin.path)"
          >
            <component :is="PlayCircleOutlined" />
            使用
          </a-button>
          <a-button 
            v-if="!isInNav(plugin.key)"
            size="small" 
            @click="addToNav(plugin)"
          >
            <component :is="PlusOutlined" />
            添加到常用
          </a-button>
          <a-button 
            v-else
            size="small" 
            danger
            @click="removeFromNav(plugin.key)"
          >
            <component :is="MinusOutlined" />
            移除
          </a-button>
        </div>
        <div v-if="isInNav(plugin.key)" class="favorite-badge">
          <StarOutlined />
          <span>已添加</span>
        </div>
      </div>
    </div>
    
    <div class="tips">
      <InfoCircleOutlined />
      <span>添加到常用的功能将显示在左侧导航栏中，方便快速访问</span>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import {
  GlobalOutlined,
  ApiOutlined,
  ReadOutlined,
  FireOutlined,
  GroupOutlined,
  WifiOutlined,
  CalendarOutlined,
  PlayCircleOutlined,
  PlusOutlined,
  MinusOutlined,
  StarOutlined,
  InfoCircleOutlined,
  FormOutlined,
  DatabaseOutlined,
  ProfileOutlined,
  KeyOutlined,
  ScanOutlined,
  BookOutlined,
  TranslationOutlined,
  FileTextOutlined,
} from "@ant-design/icons-vue";
import { useRouter } from "vue-router";
import { message } from "ant-design-vue";

export default defineComponent({
  components: {
    GlobalOutlined,
    ApiOutlined,
    ReadOutlined,
    FireOutlined,
    GroupOutlined,
    WifiOutlined,
    CalendarOutlined,
    PlayCircleOutlined,
    PlusOutlined,
    MinusOutlined,
    StarOutlined,
    InfoCircleOutlined,
    FormOutlined,
    DatabaseOutlined,
    ProfileOutlined,
    KeyOutlined,
    ScanOutlined,
    BookOutlined,
    TranslationOutlined,
    FileTextOutlined,
  },
  setup() {
    const router = useRouter();
    
    const plugins = ref([
      {
        key: "2",
        icon: "FormOutlined",
        label: "随手记",
        path: "/note",
        description: "快速记录想法和待办事项，简单便捷",
      },
      {
        key: "3",
        icon: "DatabaseOutlined",
        label: "文件中转",
        path: "/file",
        description: "临时文件存储和分享，支持多种格式",
      },
      {
        key: "5",
        icon: "ProfileOutlined",
        label: "笔记",
        path: "/blog",
        description: "编写和管理个人笔记，支持富文本编辑",
      },
      {
        key: "18",
        icon: "KeyOutlined",
        label: "密码管理",
        path: "/pwdmemo",
        description: "安全存储和管理您的账号密码",
      },
      {
        key: "15",
        icon: "GlobalOutlined",
        label: "监控网站状态",
        path: "/monitoring",
        description: "实时监控您关注的网站状态，及时发现故障",
      },
      {
        key: "17",
        icon: "ApiOutlined",
        label: "数据抓取",
        path: "/fetch",
        description: "抓取网页数据，支持多种数据格式导出",
      },
      {
        key: "20",
        icon: "ReadOutlined",
        label: "TXT电子书",
        path: "/txtreader",
        description: "在线阅读TXT格式电子书，支持书签和进度记忆",
      },
      {
        key: "19",
        icon: "FireOutlined",
        label: "热榜",
        path: "/hot",
        description: "聚合各大平台热点资讯，掌握实时热点",
      },
      {
        key: "21",
        icon: "GroupOutlined",
        label: "单词卡",
        path: "/flashcard",
        description: "管理个人单词卡，支持单词收藏和记忆",
      },
      {
        key: "6",
        icon: "WifiOutlined",
        label: "RSS阅读器",
        path: "/rss",
        description: "订阅您喜欢的博客和新闻源，一站式阅读",
      },
      {
        key: "16",
        icon: "CalendarOutlined",
        label: "日历",
        path: "/calendar",
        description: "个人日历管理，支持日程提醒和事件记录",
      },
      {
        key: "23",
        icon: "ScanOutlined",
        label: "OCR识别",
        path: "/ocr",
        description: "图片文字识别，支持多种语言，快速提取图片中的文字内容",
      },
      {
        key: "24",
        icon: "BookOutlined",
        label: "成语词典",
        path: "/idiom",
        description: "输入关键词查询成语词典，快速获取成语解释和详细信息",
      },
      {
        key: "25",
        icon: "TranslationOutlined",
        label: "英汉词典",
        path: "/ecdict",
        description: "输入英文单词查询释义，支持分页浏览",
      },
      {
        key: "26",
        icon: "FileTextOutlined",
        label: "汉英词典",
        path: "/cedict",
        description: "输入中文查询释义，支持分页浏览，支持分页浏览",
      },
    ]);

    const NAV_ORDER_STORAGE_KEY = "nav_order_config";

    // 响应式状态，用于跟踪已添加到导航的插件keys
    const addedPluginKeys = ref(new Set());

    const getNavConfig = () => {
      try {
        const saved = localStorage.getItem(NAV_ORDER_STORAGE_KEY);
        return saved ? JSON.parse(saved) : null;
      } catch {
        return null;
      }
    };

    const saveNavConfig = (config) => {
      try {
        localStorage.setItem(NAV_ORDER_STORAGE_KEY, JSON.stringify(config));
        // 触发自定义事件通知App.vue更新菜单
        window.dispatchEvent(new Event("navConfigChanged"));
      } catch (e) {
        console.warn("保存导航配置失败", e);
      }
    };

    // 更新响应式状态
    const updateAddedPluginKeys = () => {
      const config = getNavConfig();
      if (!config) {
        addedPluginKeys.value = new Set();
        return;
      }
      const allNavItems = [...(config.main || []), ...(config.more || [])];
      addedPluginKeys.value = new Set(allNavItems.map((item) => item.key));
    };

    const isInNav = (key) => {
      return addedPluginKeys.value.has(key);
    };

    const addToNav = (plugin) => {
      let config = getNavConfig();
      if (!config) {
        config = {
          main: [
            { key: "1", icon: "StarOutlined", label: "书签", path: "/home" },
            { key: "22", icon: "AppstoreOutlined", label: "插件广场", path: "/plugins" },
          ],
          more: [
            { key: "8", icon: null, label: "管理目录", path: "/manage", showIf: null },
            { key: "9", icon: null, label: "个人设置", path: "/profile", showIf: "useremail" },
            { key: "10", icon: null, label: "清理七牛云无用图片", path: "/clear", showIf: "never" },
            { key: "11", icon: null, label: "导入书签", path: "/upload", showIf: null },
            { key: "12", icon: null, label: "导出书签至本地", path: "/export", showIf: null },
            { key: "13", icon: null, label: "发送书签至邮箱", path: "/email", showIf: "useremail" },
            { key: "14", icon: null, label: "退出", path: "/logout", showIf: null },
          ],
        };
      }

      const newItem = {
        key: plugin.key,
        icon: plugin.icon,
        label: plugin.label,
        path: plugin.path,
      };

      const allKeys = new Set([
        ...config.main.map((item) => item.key),
        ...config.more.map((item) => item.key),
      ]);

      if (!allKeys.has(plugin.key)) {
        config.main.push(newItem);
        saveNavConfig(config);
        updateAddedPluginKeys(); // 更新响应式状态
        message.success(`已将「${plugin.label}」添加到常用菜单`);
      }
    };

    const removeFromNav = (key) => {
      const config = getNavConfig();
      if (!config) return;

      const plugin = plugins.value.find((p) => p.key === key);
      if (!plugin) return;

      config.main = config.main.filter((item) => item.key !== key);
      config.more = config.more.filter((item) => item.key !== key);
      saveNavConfig(config);
      updateAddedPluginKeys(); // 更新响应式状态
      message.success(`已从常用菜单移除「${plugin.label}」`);
    };

    const navigateTo = (path) => {
      router.push(path);
    };

    const iconMap = {
      GlobalOutlined,
      ApiOutlined,
      ReadOutlined,
      FireOutlined,
      GroupOutlined,
      WifiOutlined,
      CalendarOutlined,
      FormOutlined,
      DatabaseOutlined,
      ProfileOutlined,
      KeyOutlined,
      ScanOutlined,
      BookOutlined,
      TranslationOutlined,
      FileTextOutlined,
    };

    const getIcon = (iconName) => {
      return iconMap[iconName] || GlobalOutlined;
    };

    onMounted(() => {
      updateAddedPluginKeys(); // 初始化时加载状态
    });

    return {
      plugins,
      isInNav,
      addToNav,
      removeFromNav,
      navigateTo,
      getIcon,
    };
  },
});
</script>

<style scoped>
.plugin-market {
  padding: 24px;
  min-height: 100vh;
  background: #f5f5f5;
}

.market-header {
  text-align: center;
  margin-bottom: 40px;
  color: #1f2937;
}

.market-header h1 {
  font-size: 32px;
  margin-bottom: 8px;
  font-weight: 600;
}

.market-header p {
  font-size: 16px;
  color: #6b7280;
}

.plugin-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

.plugin-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
}

.plugin-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.plugin-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  font-size: 28px;
  color: white;
}

.plugin-card h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #1f2937;
}

.plugin-card p {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 16px;
  line-height: 1.6;
}

.plugin-actions {
  display: flex;
  gap: 8px;
}

.plugin-actions button {
  flex: 1;
}

.favorite-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  background: #fef3c7;
  color: #d97706;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 12px;
}

.favorite-badge svg {
  font-size: 12px;
}

.tips {
  text-align: center;
  margin-top: 40px;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.tips svg {
  font-size: 16px;
}

.tips span {
  font-size: 14px;
}
</style>
