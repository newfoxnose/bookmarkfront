<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
 </div>
 <h3 class="content-title">喝咖啡</h3>

  <div class="hot-list-container">

    
    <div class="platform-grid">
      
      <div v-for="platform in platforms" :key="platform.key" class="platform-card">
        <div class="platform-header">
          <div class="header-left">
            <span class="platform-icon" :class="platform.key">{{ getPlatformIcon(platform.key) }}</span>
            <h4>{{ platform.name }}</h4>
            <span class="update-time" v-if="platform.items.length">（{{ Math.floor(Math.random() * 60) }}分钟前）</span>
          </div>
          <a-button type="link" class="refresh-btn" @click="refreshPlatform(platform.key)" :loading="platform.loading">
            <template #icon><sync-outlined /></template>
          </a-button>
        </div>
        <div class="hot-list">
          <div v-for="(item, index) in platform.items" :key="index" class="hot-item">
            <div class="rank" :class="{ 'top-rank': index < 3 }">{{ index + 1 }}</div>
            <div class="content">
              <a :href="item.url" target="_blank" class="title">{{ item.title }}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}

.hot-list-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}


.platform-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin: 0 auto;
  max-width: 1200px;
  width: 100%;
  background: #eee;
  padding: 20px;
  border-radius: 8px;
}


.platform-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(18, 18, 18, 0.1);
  overflow: hidden;
  transition: all 0.3s;
  position: relative;
  min-width: 0;
  width: 100%;
  padding-bottom: 10px;
  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    
    .platform-header .refresh-btn {
      opacity: 1;
    }
  }
}
/* 深色主题下的样式 */
.content-dark-theme .platform-grid {
  background-color: #1a1a1a !important;
  color: #e0e0e0;
  border-color: #333;
}
.content-dark-theme .platform-card {
  background-color: #2a2a2a !important;
  color: #e0e0e0;
  border: 1px solid #3a3a3a;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}
.content-dark-theme .platform-header {
  background-color: #2a2a2a !important;
  color: #e0e0e0;
  border-bottom: 1px solid #3a3a3a;
}
.content-dark-theme .platform-header h4 {
  color: #fff;
}
.platform-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  border-bottom: 1px solid #f0f0f0;
  background: #fff;

  .refresh-btn {
    opacity: 0;
    transition: opacity 0.3s;
  }
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.platform-icon {
  width: 20px;
  height: 20px;
  line-height: 20px;
  text-align: center;
  border-radius: 4px;
  color: #fff;
  font-size: 12px;
  font-weight: bold;

  &.zhihu {
    background: #056de8;
  }
  &.bilibili {
    background: #fb7299;
  }
  &.weibo {
    background: #ff8200;
  }
  &.douyin {
    background: #000000;
  }
  &.baidu {
    background: #4e6ef2;
  }
  &.toutiao {
    background: #ff2442;
  }
  &.tencent {
    background: #2c2c2c;
  }
  &.sougou {
    background: #f94d1b;
  }
  &.juejin {
    background: #1e80ff;
  }
  &.hupu {
    background: #c01d2f;
  }
  &.csdn {
    background: #fc5531;
  }
}

.platform-header h4 {
  margin: 0;
  font-size: 14px;
  color: #121212;
  font-weight: 600;
}

.update-time {
  color: #8590a6;
  font-size: 12px;
}

.hot-list {
  padding: 2px 0;
  max-height: 320px;
  overflow: auto;
  scrollbar-width: none; /* 隐藏Firefox滚动条 */
  /* 悬停时不影响列表宽度 */
  padding-right: 8px;
  &:hover {
    padding-right: 0;
  }
  /* 隐藏WebKit浏览器滚动条 */
  &::-webkit-scrollbar {
    width: 0;
    background: transparent;
  }

  /* 悬停时显示Firefox滚动条 */
  &:hover {
    scrollbar-width: auto;
  }

  /* 悬停时显示WebKit滚动条并设置样式 */
  &:hover::-webkit-scrollbar {
    width: 8px;
  }

  &:hover::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  &:hover::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
  }

  &:hover::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
}

.hot-item {
  display: flex;
  align-items: center;
  padding: 6px 12px;
  transition: background-color 0.3s;

  &:hover {
    background-color: #f7f8fa;
  }
}

.rank {
  width: 20px;
  height: 20px;
  line-height: 20px;
  text-align: center;
  font-size: 13px;
  font-weight: 500;
  color: #8590a6;
  margin-right: 8px;
  flex-shrink: 0;
}

.top-rank {
  color: #ff5a5a;
  font-weight: 600;
}

.content {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-width: 0;
  gap: 8px;
}

.title {
  color: #121212;
  font-size: 16px;
  text-decoration: none;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
  line-height: 1.5;
  flex: 1;
  word-break: break-all;

  &:hover {
    color: #175199;
  }
}

.heat {
  color: #8590a6;
  font-size: 12px;
  white-space: nowrap;
}

/* 深色主题下的样式 */
.content-dark-theme .hot-item {
  &:hover {
    background-color: #3a3a3a;
  }
}

.content-dark-theme .title {
  color: #e0e0e0;
  
  &:hover {
    color: #1890ff;
  }
}

.content-dark-theme .rank {
  color: #888;
}

.content-dark-theme .top-rank {
  color: #ff4d4f;
}

.content-dark-theme .update-time {
  color: #888;
}

.content-dark-theme .platform-icon {
  &.zhihu {
    background: #056de8;
  }
  &.bilibili {
    background: #fb7299;
  }
  &.weibo {
    background: #ff8200;
  }
  &.douyin {
    background: #000000;
  }
  &.baidu {
    background: #4e6ef2;
  }
  &.toutiao {
    background: #ff2442;
  }
  &.tencent {
    background: #2c2c2c;
  }
  &.sougou {
    background: #f94d1b;
  }
  &.juejin {
    background: #1e80ff;
  }
  &.hupu {
    background: #c01d2f;
  }
  &.csdn {
    background: #fc5531;
  }
}
</style>

<script>
import { message } from 'ant-design-vue';
import { SyncOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';

export default {
  components: {
    SyncOutlined,
  },
  setup() {
    $cookies.set('selectedkey','19',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()

    const platforms = ref([
     
      { 
        key: 'zhihu', 
        name: '知乎热榜',
        items: [],
        loading: false
      },
      { 
        key: 'hupu', 
        name: '虎扑步行街',
        items: [],
        loading: false
      },
      { 
        key: 'bilibili', 
        name: 'B站',
        items: [],
        loading: false
      },
      { 
        key: 'juejin', 
        name: '掘金',
        items: [],
        loading: false
      },
      { 
        key: 'toutiao', 
        name: '今日头条',
        items: [],
        loading: false
      },
      { 
        key: 'tencent', 
        name: '腾讯新闻',
        items: [],
        loading: false
      },
      { 
        key: 'weibo', 
        name: '微博热搜',
        items: [],
        loading: false
      },
      { 
        key: 'sougou', 
        name: '搜狗热榜',
        items: [],
        loading: false
      },
      { 
        key: 'douyin', 
        name: '抖音',
        items: [],
        loading: false
      },
      { 
        key: 'csdn', 
        name: 'CSDN热榜',
        items: [],
        loading: false
      },
      { 
        key: 'baidu', 
        name: '百度热搜',
        items: [],
        loading: false
      }
    ]);

    const fetchBilibiliData = async () => {
      const platform = platforms.value.find(p => p.key === 'bilibili');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/bilibili_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.short_link_v2 || `https://www.bilibili.com/video/${item.bvid}`,
            author: item.owner.name,
            rank: index + 1
          }));
        } else {
          message.error('获取B站热榜数据失败');
        }
      } catch (error) {
        console.error('获取B站数据异常:', error);
        message.error('获取B站热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchZhihuData = async () => {
      const platform = platforms.value.find(p => p.key === 'zhihu');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/zhihu_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            author: item.author,
            excerpt: item.excerpt,
            rank: index + 1
          }));
        } else {
          message.error('获取知乎热榜数据失败');
        }
      } catch (error) {
        console.error('获取知乎数据异常:', error);
        message.error('获取知乎热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchWeiboData = async () => {
      const platform = platforms.value.find(p => p.key === 'weibo');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
          params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/weibo_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            heat: item.heat,
            rank: index + 1
          }));
        } else {
          message.error('获取微博热搜数据失败');
        }
      } catch (error) {
        console.error('获取微博数据异常:', error);
        message.error('获取微博热搜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchToutiaoData = async () => {
      const platform = platforms.value.find(p => p.key === 'toutiao');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/toutiao_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            heat: item.heat,
            rank: index + 1
          }));
        } else {
          message.error('获取头条热榜数据失败');
        }
      } catch (error) {
        console.error('获取头条数据异常:', error);
        message.error('获取头条热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchTencentData = async () => {
      const platform = platforms.value.find(p => p.key === 'tencent');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/tencent_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            author: item.author,
            heat: item.heat,
            cover: item.cover,
            rank: index + 1
          }));
        } else {
          message.error('获取腾讯新闻热榜数据失败');
        }
      } catch (error) {
        console.error('获取腾讯新闻数据异常:', error);
        message.error('获取腾讯新闻热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchSougouData = async () => {
      const platform = platforms.value.find(p => p.key === 'sougou');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
      params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/sougou_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            heat: item.heat,
            rank: index + 1
          }));
        } else {
          message.error('获取搜狗热榜数据失败');
        }
      } catch (error) {
        console.error('获取搜狗热榜数据异常:', error);
        message.error('获取搜狗热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchJuejinData = async () => {
      const platform = platforms.value.find(p => p.key === 'juejin');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/juejin_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            author: item.author,
            author_avatar: item.author_avatar,
            heat: item.heat,
            rank: index + 1
          }));
        } else {
          message.error('获取掘金热榜数据失败');
        }
      } catch (error) {
        console.error('获取掘金热榜数据异常:', error);
        message.error('获取掘金热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchHupuData = async () => {
      const platform = platforms.value.find(p => p.key === 'hupu');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/hupu_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            heat: `${item.likes} | ${item.heat}`,
            rank: index + 1
          }));
        } else {
          message.error('获取虎扑步行街数据失败');
        }
      } catch (error) {
        console.error('获取虎扑步行街数据异常:', error);
        message.error('获取虎扑步行街数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchDouyinData = async () => {
      const platform = platforms.value.find(p => p.key === 'douyin');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/douyin_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            heat: item.heat,
            rank: index + 1
          }));
        } else {
          message.error('获取抖音热榜数据失败');
        }
      } catch (error) {
        console.error('获取抖音热榜数据异常:', error);
        message.error('获取抖音热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchCsdnData = async () => {
      const platform = platforms.value.find(p => p.key === 'csdn');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/csdn_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            heat: item.heat,
            author: item.author,
            author_avatar: item.author_avatar,
            rank: index + 1
          }));
        } else {
          message.error('获取CSDN热榜数据失败');
        }
      } catch (error) {
        console.error('获取CSDN热榜数据异常:', error);
        message.error('获取CSDN热榜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const fetchBaiduData = async () => {
      const platform = platforms.value.find(p => p.key === 'baidu');
      if (!platform) return;
      
      platform.loading = true;
      try {
        let params = new URLSearchParams();
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get('token'));
        const { data: res } = await proxy.$http.post('/ajax/baidu_hot_ajax/', params);
        
        if (res.code === 0 && res.data && res.data.list) {
          platform.items = res.data.list.map((item, index) => ({
            title: item.title,
            url: item.url,
            img: item.img,
            content: item.content,
            heat: item.heat,
            rank: index + 1
          }));
        } else {
          message.error('获取百度热搜数据失败');
        }
      } catch (error) {
        console.error('获取百度热搜数据异常:', error);
        message.error('获取百度热搜数据异常，请稍后重试');
      } finally {
        platform.loading = false;
      }
    };

    const refreshPlatform = async (platformKey) => {
      const platform = platforms.value.find(p => p.key === platformKey);
      if (!platform) return;
      
      if (platform.loading) {
        message.info('数据正在加载中...');
        return;
      }

      switch (platformKey) {
        case 'bilibili':
          await fetchBilibiliData();
          break;
        case 'zhihu':
          await fetchZhihuData();
          break;
        case 'weibo':
          await fetchWeiboData();
          break;
        case 'toutiao':
          await fetchToutiaoData();
          break;
        case 'tencent':
          await fetchTencentData();
          break;
        case 'sougou':
          await fetchSougouData();
          break;
        case 'juejin':
          await fetchJuejinData();
          break;
        case 'hupu':
          await fetchHupuData();
          break;
        case 'douyin':
          await fetchDouyinData();
          break;
        case 'csdn':
          await fetchCsdnData();
          break;
        case 'baidu':
          await fetchBaiduData();
          break;
        default:
          message.info('该平台数据获取功能开发中...');
      }
    };

    const getPlatformIcon = (key) => {
      switch (key) {
        case 'zhihu':
          return '知';
        case 'bilibili':
          return 'B';
        case 'weibo':
          return '微';
        case 'douyin':
          return '抖';
        case 'baidu':
          return '百';
        case 'toutiao':
          return '头';
        case 'tencent':
          return '腾';
        case 'sougou':
          return '搜';
        case 'juejin':
          return '掘';
        case 'hupu':
          return '虎';
        case 'csdn':
          return 'C';
        default:
          return '';
      }
    };

    onMounted(async () => {
      const interval = setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 100)
      
      // 初始化加载数据
      await Promise.all([
        fetchBilibiliData(),
        fetchZhihuData(),
        fetchWeiboData(),
        fetchToutiaoData(),
        fetchTencentData(),
        fetchSougouData(),
        fetchJuejinData(),
        fetchHupuData(),
        fetchDouyinData(),
        fetchCsdnData(),
        fetchBaiduData()
      ]);
      
      defaultPercent.value = 100;
      loadingdone.value = true;
    });

    return {
      defaultPercent,
      loadingdone,
      platforms,
      refreshPlatform,
      getPlatformIcon
    };
  },
}
</script>

