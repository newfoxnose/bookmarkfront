<template>
  <div class="saying-container">
    <a-card :bordered="false">
      <div class="search-section">
        <a-select
          v-model:value="selectedCategory"
          style="width: 150px; margin-right: 8px"
          size="large"
        >
          <a-select-option value="谜语">谜语</a-select-option>
          <a-select-option value="歇后语">歇后语</a-select-option>
          <a-select-option value="脑筋急转弯">脑筋急转弯</a-select-option>
        </a-select>
        <a-button
          type="primary"
          size="large"
          :loading="loading"
          @click="refresh"
        >
          <template #icon>
            <reload-outlined />
          </template>
          刷新
        </a-button>
      </div>

      <div v-if="saying" class="result-section">
        <a-divider>{{ selectedCategory }} (点击卡片查看答案，点击刷新换一条)</a-divider>
        <div class="saying-card" @click="showAnswer = true">
          <div class="saying-question">
            <a-tag color="blue">{{ selectedCategory }}</a-tag>
            <div v-html="saying.question"></div>
          </div>
          <div v-if="showAnswer" class="saying-answer">
            <a-tag color="green">答案</a-tag>
            <div v-html="saying.answer"></div>
          </div>
          <div v-else class="hint">
            <a-tag color="orange">点击查看答案</a-tag>
          </div>
        </div>
      </div>

      <div v-else-if="!loading" class="empty-section">
        <a-empty description="点击刷新获取内容" />
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
import { ref, onMounted, watch } from 'vue';
import { ReloadOutlined } from '@ant-design/icons-vue';

const apiBaseUrl = 'https://express.o-oo.net.cn';
const apiUrl = `${apiBaseUrl}/api/saying`;
const TOKEN_KEY = 'dict_token';

const selectedCategory = ref('谜语');
const loading = ref(false);
const error = ref('');
const saying = ref(null);
const showAnswer = ref(false);
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
      localStorage.setItem(TOKEN_KEY, token.value);
    } else {
      error.value = '获取 Token 失败';
    }
  } catch (err) {
    error.value = '获取 Token 失败: ' + err.message;
  }
};

const loadToken = () => {
  const savedToken = localStorage.getItem(TOKEN_KEY);
  if (savedToken) {
    token.value = savedToken;
  }
};

const refresh = async () => {
  loading.value = true;
  error.value = '';
  saying.value = null;
  showAnswer.value = false;

  if (!token.value) {
    await getToken();
    if (!token.value) {
      loading.value = false;
      return;
    }
  }

  try {
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token.value}`
      },
      body: JSON.stringify({
        category: selectedCategory.value
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.msg || '获取失败');
    }

    if (data.success !== 'ok') {
      throw new Error(data.msg || '获取失败');
    }

    saying.value = data.result;

  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

watch(selectedCategory, () => {
  refresh();
});

onMounted(() => {
  loadToken();
  refresh();
});
</script>

<style scoped>
.saying-container {
  padding: 24px;
  max-width: 700px;
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

.saying-card {
  padding: 24px;
  background: #fafafa;
  border-radius: 8px;
  border: 1px solid #f0f0f0;
}

.saying-question {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px dashed #e8e8e8;
}

.saying-question div,
.saying-answer div {
  margin: 12px 0 0 0;
  font-size: 18px;
  line-height: 1.8;
}

.hint {
  margin-top: 12px;
  text-align: center;
  cursor: pointer;
}
</style>
