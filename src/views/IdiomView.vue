<template>
  <div class="idiom-container">
    <a-card title="成语词典" :bordered="false">
      <div class="search-section">
        <a-input-search
          v-model:value="keyword"
          placeholder="请输入成语关键词进行搜索"
          enter-button="搜索"
          size="large"
          :loading="loading"
          @search="searchIdiom"
          style="width: 500px"
        />
      </div>

      <div v-if="results.length > 0" class="result-section">
        <a-divider>搜索结果 (共 {{ pagination.total }} 条)</a-divider>
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
            show-size-changer
            show-quick-jumper
            :show-total="(total) => `共 ${total} 条`"
          />
        </div>
      </div>

      <div v-else-if="!loading && keyword" class="empty-section">
        <a-empty description="未找到相关成语" />
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
import { ref } from 'vue';
import { message } from 'ant-design-vue';

const apiBaseUrl = 'https://express.o-oo.net.cn';
const apiUrl = `${apiBaseUrl}/api/chaxun`;

const keyword = ref('');
const loading = ref(false);
const error = ref('');
const results = ref([]);
const pagination = ref({
  page: 1,
  pageSize: 20,
  total: 0,
  totalPages: 0
});

const token = ref('');

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
      localStorage.setItem('idiom_token', token.value);
    } else {
      error.value = '获取 Token 失败';
    }
  } catch (err) {
    error.value = '获取 Token 失败: ' + err.message;
  }
};

const searchIdiom = async () => {
  if (!keyword.value.trim()) {
    message.warning('请输入搜索关键词');
    return;
  }

  if (!token.value) {
    await getToken();
    if (!token.value) return;
  }

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
        dictionary: '成语词典'
      })
    });

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('idiom_token');
        await getToken();
        if (!token.value) return;
        return searchIdiom();
      }
      throw new Error(data.msg || '搜索失败');
    }

    if (data.success !== 'ok') {
      throw new Error(data.msg || '搜索失败');
    }

    if (data.result && data.result.data) {
      results.value = data.result.data.filter(item => item && item.display).map(item => ({
        ...item,
        display: item.display || ''
      }));
      pagination.value = {
        page: data.result.pagination?.page || 1,
        pageSize: data.result.pagination?.pageSize || 20,
        total: data.result.pagination?.total || 0,
        totalPages: data.result.pagination?.totalPages || 0
      };
    } else {
      results.value = [];
      pagination.value.total = 0;
    }

    if (results.value.length === 0) {
      message.info('未找到相关成语');
    }

  } catch (err) {
    error.value = err.message;
    message.error('搜索失败: ' + err.message);
  } finally {
    loading.value = false;
  }
};

const savedToken = localStorage.getItem('idiom_token');
if (savedToken) {
  token.value = savedToken;
}

const handlePageChange = (page, pageSize) => {
  pagination.value.page = page;
  if (pageSize) {
    pagination.value.pageSize = pageSize;
  }
  searchIdiom();
};
</script>

<style scoped>
.idiom-container {
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