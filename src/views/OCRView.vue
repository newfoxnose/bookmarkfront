<template>
  <div class="ocr-container">
    <a-card title="OCR图片文字识别" :bordered="false">
      <!-- 图片上传 -->
      <div class="upload-section">
        <a-upload
          :before-upload="beforeUpload"
          :show-upload-list="false"
          accept="image/*"
        >
          <a-button type="primary" :icon="h(UploadOutlined)">
            选择图片
          </a-button>
        </a-upload>

        <!-- 图片预览 -->
        <div v-if="imagePreview" class="image-preview">
          <img :src="imagePreview" alt="预览" />
        </div>
      </div>

      <!-- 语言选择 -->
      <div class="language-section">
        <span>识别语言：</span>
        <a-select
          v-model:value="selectedLanguage"
          style="width: 200px"
        >
          <a-select-option value="ara">阿拉伯语</a-select-option>
          <a-select-option value="bul">保加利亚语</a-select-option>
          <a-select-option value="chs">中文(简体)</a-select-option>
          <a-select-option value="cht">中文(繁体)</a-select-option>
          <a-select-option value="hrv">克罗地亚语</a-select-option>
          <a-select-option value="cze">捷语语</a-select-option>
          <a-select-option value="dan">丹麦语</a-select-option>
          <a-select-option value="dut">荷兰语</a-select-option>
          <a-select-option value="eng">英语</a-select-option>
          <a-select-option value="fin">芬兰语</a-select-option>
          <a-select-option value="fre">法语</a-select-option>
          <a-select-option value="ger">德语</a-select-option>
          <a-select-option value="gre">希腊语</a-select-option>
          <a-select-option value="hun">匈牙利语</a-select-option>
          <a-select-option value="kor">韩语</a-select-option>
          <a-select-option value="ita">意大利语</a-select-option>
          <a-select-option value="jpn">日语</a-select-option>
          <a-select-option value="pol">波兰语</a-select-option>
          <a-select-option value="por">葡萄牙语</a-select-option>
          <a-select-option value="rus">俄语</a-select-option>
          <a-select-option value="slv">斯洛伐克语</a-select-option>
          <a-select-option value="spa">西班牙语</a-select-option>
          <a-select-option value="swe">瑞典语</a-select-option>
          <a-select-option value="tha">泰语</a-select-option>
          <a-select-option value="tur">土耳其语</a-select-option>
          <a-select-option value="ukr">乌克兰语</a-select-option>
          <a-select-option value="vnm">越南语</a-select-option>
        </a-select>
      </div>

      <!-- 识别按钮 -->
      <div class="action-section">
        <a-button
          type="primary"
          :loading="loading"
          :disabled="!imageBase64"
          @click="performOCR"
          size="large"
        >
          {{ loading ? '识别中...' : '开始识别' }}
        </a-button>
      </div>

      <!-- 结果显示 -->
      <div v-if="result" class="result-section">
        <a-divider>识别结果</a-divider>
        <a-textarea
          v-model:value="result.text"
          :rows="10"
          readonly
          placeholder="识别结果将显示在这里"
        />
        <div class="result-actions">
          <a-button @click="copyResult" :icon="h(CopyOutlined)">
            复制结果
          </a-button>
          <a-button @click="clearResult" :icon="h(DeleteOutlined)">
            清空
          </a-button>
        </div>
      </div>

      <!-- 错误提示 -->
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
import { ref, h } from 'vue';
import { message } from 'ant-design-vue';
import { UploadOutlined, CopyOutlined, DeleteOutlined } from '@ant-design/icons-vue';

// API 配置
const apiBaseUrl = 'https://express.o-oo.net.cn';

// 图片相关
const imageFile = ref(null);
const imageBase64 = ref('');
const imagePreview = ref('');

// 语言选择
const selectedLanguage = ref('eng');

// 状态
const loading = ref(false);
const result = ref(null);
const error = ref('');

// Token
const token = ref('');

// 获取 JWT Token
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
      localStorage.setItem('ocr_token', token.value);
    } else {
      error.value = '获取 Token 失败';
    }
  } catch (err) {
    error.value = '获取 Token 失败: ' + err.message;
  }
};

// 文件上传前验证
const beforeUpload = (file) => {
  // 验证文件类型
  if (!file.type.startsWith('image/')) {
    message.error('请选择图片文件');
    return false;
  }

  // 验证文件大小（限制5MB）
  if (file.size > 5 * 1024 * 1024) {
    message.error('图片大小不能超过5MB');
    return false;
  }

  imageFile.value = file;
  error.value = '';
  result.value = null;

  // 转换为 Base64
  convertToBase64(file);

  return false; // 阻止自动上传
};

// 文件转 Base64
const convertToBase64 = (file) => {
  const reader = new FileReader();

  reader.onload = (e) => {
    imageBase64.value = e.target.result;
    imagePreview.value = e.target.result;
  };

  reader.onerror = () => {
    message.error('图片读取失败');
  };

  reader.readAsDataURL(file);
};

// 执行 OCR 识别
const performOCR = async () => {
  if (!imageBase64.value) {
    message.error('请先选择图片');
    return;
  }

  // 检查 token 是否存在
  if (!token.value) {
    await getToken();
    if (!token.value) return;
  }

  loading.value = true;
  error.value = '';
  result.value = null;

  try {
    const response = await fetch(`${apiBaseUrl}/api/ocr`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token.value}`
      },
      body: JSON.stringify({
        image: imageBase64.value,
        language: selectedLanguage.value
      })
    });

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('ocr_token');
        await getToken();
        return performOCR();
      }
      const errorMsg = data.message ? `${data.error}: ${data.message.join(', ')}` : (data.error || '识别失败');
      throw new Error(errorMsg);
    }

    if (!data.success || data.isErrorredOnProcessing) {
      throw new Error(data.error || '识别失败');
    }

    result.value = data;
    message.success('识别成功！');

  } catch (err) {
    error.value = err.message;
    message.error('识别失败: ' + err.message);
  } finally {
    loading.value = false;
  }
};

// 复制结果
const copyResult = () => {
  if (result.value && result.value.text) {
    navigator.clipboard.writeText(result.value.text).then(() => {
      message.success('已复制到剪贴板');
    }).catch(() => {
      message.error('复制失败');
    });
  }
};

// 清空结果
const clearResult = () => {
  result.value = null;
  imageBase64.value = '';
  imagePreview.value = '';
  imageFile.value = null;
  error.value = '';
};

// 初始化时从 localStorage 获取 token
const savedToken = localStorage.getItem('ocr_token');
if (savedToken) {
  token.value = savedToken;
}
</script>

<style scoped>
.ocr-container {
  padding: 24px;
  max-width: 800px;
  margin: 0 auto;
}

.upload-section {
  margin-bottom: 24px;
}

.image-preview {
  margin-top: 16px;
  text-align: center;
}

.image-preview img {
  max-width: 100%;
  max-height: 400px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 8px;
  background: #fafafa;
}

.language-section {
  margin-bottom: 24px;
  display: flex;
  align-items: center;
}

.language-section span {
  margin-right: 8px;
  font-weight: 500;
}

.action-section {
  margin-bottom: 24px;
}

.result-section {
  margin-top: 24px;
}

.result-actions {
  margin-top: 16px;
  display: flex;
  gap: 8px;
}
</style>