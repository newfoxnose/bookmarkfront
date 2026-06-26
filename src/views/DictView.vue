<template>
  <div class="dict-container">
    <a-card :bordered="false">
      <div class="search-section">
        <a-input-search
          v-model:value="keyword"
          :placeholder="config.placeholder"
          :enter-button="config.buttonText"
          size="large"
          :loading="loading"
          @search="search"
          style="width: 500px"
        />
      </div>

      <div v-if="results.length > 0" class="result-section">
        <a-divider>{{ config.resultTitle }} (共 {{ pagination.total }} 条)</a-divider>
        <div class="result-list">
          <div
            v-for="(item, index) in results"
            :key="index"
            class="result-item"
            v-html="item.display"
          ></div>
        </div>

        <div class="pagination-section">
          <a-pagination
            :current="pagination.page"
            :page-size="pagination.pageSize"
            :total="pagination.total"
            @change="handlePageChange"
            show-quick-jumper
            :show-total="(total) => `共 ${total} 条`"
          />
        </div>
      </div>

      <div v-else-if="!loading && hasSearched && results.length === 0" class="empty-section">
        <a-empty :description="config.emptyText" />
      </div>

      <a-alert
        v-if="error"
        :message="error"
        type="error"
        show-icon
        style="margin-top: 16px"
      />
    </a-card>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { message } from 'ant-design-vue';

const route = useRoute();

const apiBaseUrl = 'https://express.o-oo.net.cn';
const apiUrl = `${apiBaseUrl}/api/chaxun`;
const TOKEN_KEY = 'dict_token';

const dictConfig = {
  idiom: {
    dictionary: '成语词典',
    placeholder: '请输入成语关键词进行搜索',
    buttonText: '搜索',
    resultTitle: '搜索结果',
    emptyText: '未找到相关成语',
    notFoundMessage: '未找到相关成语',
    searchError: '搜索失败'
  },
  ecdict: {
    dictionary: '英汉词典',
    placeholder: '请输入英文单词进行查询',
    buttonText: '查询',
    resultTitle: '查询结果',
    emptyText: '未找到相关单词',
    notFoundMessage: '未找到相关单词',
    searchError: '查询失败'
  },
  cedict: {
    dictionary: '汉英词典',
    placeholder: '请输入中文进行查询',
    buttonText: '查询',
    resultTitle: '查询结果',
    emptyText: '未找到相关单词',
    notFoundMessage: '未找到相关单词',
    searchError: '查询失败'
  }
};

const currentType = computed(() => {
  return route.params.type || route.name;
});

const config = computed(() => {
  return dictConfig[currentType.value] || dictConfig.idiom;
});

const keyword = ref('');
const loading = ref(false);
const error = ref('');
const results = ref([]);
const hasSearched = ref(false);
const pagination = ref({
  page: 1,
  pageSize: 15,
  total: 0,
  totalPages: 0
});

const token = ref('');
const lastKeyword = ref('');

const getToken = async () => {
  try {
    const response = await fetch(`${apiBaseUrl}/auth/token`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: 'gm.ws' })
    });

    const data = await response.json();

    if (data.access_token) {
      token.value = data.access_token;
      localStorage.setItem(TOKEN_KEY, token.value);
    } else {
      error.value = '获取 Token 失败';
    }
  } catch (err) {
    error.value = '获取 Token 失败: ' + err.message;
  }
};

const search = async () => {
  if (!keyword.value.trim()) {
    message.warning('请输入查询关键词');
    return;
  }

  if (keyword.value.trim() !== lastKeyword.value) {
    pagination.value.page = 1;
    lastKeyword.value = keyword.value.trim();
  }

  if (!token.value) {
    await getToken();
    if (!token.value) return;
  }

  hasSearched.value = true;
  loading.value = true;
  error.value = '';
  results.value = [];

  try {
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token.value}`
      },
      body: JSON.stringify({
        keyword: keyword.value.trim(),
        dictionary: config.value.dictionary,
        page: pagination.value.page,
        pageSize: pagination.value.pageSize
      })
    });

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem(TOKEN_KEY);
        await getToken();
        if (!token.value) return;
        return search();
      }
      throw new Error(data.msg || config.value.searchError);
    }

    if (data.success !== 'ok') {
      throw new Error(data.msg || config.value.searchError);
    }

    if (data.result && data.result.data) {
      results.value = data.result.data.filter(item => item && item.display).map(item => ({
        ...item,
        display: item.display || ''
      }));
      pagination.value = {
        page: data.result.pagination?.page || 1,
        pageSize: data.result.pagination?.pageSize || 15,
        total: data.result.pagination?.total || 0,
        totalPages: data.result.pagination?.totalPages || 0
      };
    } else {
      results.value = [];
      pagination.value.total = 0;
    }

    if (results.value.length === 0) {
      message.info(config.value.notFoundMessage);
    }

  } catch (err) {
    error.value = err.message;
    message.error(config.value.searchError + ': ' + err.message);
  } finally {
    loading.value = false;
  }
};

const loadToken = () => {
  const savedToken = localStorage.getItem(TOKEN_KEY);
  if (savedToken) {
    token.value = savedToken;
  }
};

const handlePageChange = (page) => {
  pagination.value.page = page;
  search();
};

const resetState = () => {
  keyword.value = '';
  results.value = [];
  hasSearched.value = false;
  pagination.value = {
    page: 1,
    pageSize: 15,
    total: 0,
    totalPages: 0
  };
  lastKeyword.value = '';
  error.value = '';
};

watch(() => route.name, () => {
  loadToken();
  resetState();
});

onMounted(() => {
  loadToken();
});
</script>

<style scoped>
.dict-container {
  padding: 24px;
  max-width: 900px;
  margin: 0 auto;
}

.search-section {
  margin-bottom: 24px;
  display: flex;
  justify-content: center;
}

.result-section {
  margin-top: 24px;
}

.result-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.result-item {
  padding: 16px;
  background: #fafafa;
  border-radius: 8px;
  border: 1px solid #f0f0f0;
  transition: all 0.3s;
}

.result-item:hover {
  background: #fff;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.result-item :deep(p) {
  margin: 6px 0;
  line-height: 1.6;
}

.result-item :deep(p:first-child) {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 8px;
}

.pagination-section {
  margin-top: 24px;
  display: flex;
  justify-content: center;
}

.empty-section {
  margin-top: 48px;
}
</style>