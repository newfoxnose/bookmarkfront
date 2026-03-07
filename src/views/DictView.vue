<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>

  <!--这个文件不能用自动格式化，否则:data={token:qiniu_token,key:file_key}这部分会异常-->
  <div>
    <a-upload-dragger v-model:fileList="fileList" name="file" :multiple="false" :action=upload_url
      :data={token:aaa,timestamp:bbb,key:file_key} @change=" handleChange " :before-upload=" handleBeforeUpload "
      @drop=" handleDrop ">
      <p class="ant-upload-drag-icon">
        <inbox-outlined></inbox-outlined>
      </p>
      <p class="ant-upload-text">点击或拖放excel文件到此处进行上传</p>
      <p class="ant-upload-hint">
        每次只能上传一个文件，仅支持xls、xlsx格式，且文件大小不能超过5M。
      </p>
    </a-upload-dragger>
  </div>
  <br>
  <a-row :gutter="[16, 16]">
    <a-col v-for=" item  in  fileitems " :key="item.id" :xs="24" :sm="24" :md="12" :lg="12">
      <div class="dict-item">
        <div class="dict-item-header">
          <span class="dict-item-name">{{ item.dict_name }}</span>
          <a class="dict-item-delete" @click="deletefile(item.id)">删除</a>
        </div>
        <div class="dict-item-meta">记录数 {{ item.words_amount }} · {{ item.createtime }}</div>
      <!-- 词典下方展示随机单词卡片：仅 body 区域点击可切换隐藏/换一个 -->
      <div v-if="randomWordsMap[item.id]?.word?.[0]" class="word-card">
        <div class="word-card-header">
          <a class="word-card-hide-btn" @click.stop="hideWord(item.id)">不再出现该单词</a>
          <div class="word-card-actions" @click.stop>
            <a-tooltip title="复制">
              <span class="word-card-btn" @click="copyWord(item.id)"><copy-outlined /></span>
            </a-tooltip>
            <a-tooltip title="朗读">
              <span class="word-card-btn" @click="speakWord(item.id)"><sound-outlined /></span>
            </a-tooltip>
            <a-tooltip :title="hiddenType[item.id] ? '显示' : '已全部显示'">
              <span class="word-card-btn" @click="revealContent(item.id)">
                <eye-outlined v-if="!hiddenType[item.id]" />
                <eye-invisible-outlined v-else />
              </span>
            </a-tooltip>
            <a-tooltip title="换一个">
              <span class="word-card-btn" @click="fetchRandomWord(item.id)"><reload-outlined /></span>
            </a-tooltip>
          </div>
        </div>
        <div class="word-card-body word-card-clickable" @click="handleCardClick(item.id)">
          <!-- 单词区：hiddenType 为 'word' 时隐藏 -->
          <div class="word-card-main">
            <span v-show="hiddenType[item.id] !== 'word'" class="word-card-text">{{ randomWordsMap[item.id].word[0].word }}</span>
            <span v-show="hiddenType[item.id] !== 'word' && randomWordsMap[item.id].word[0].ipa" class="word-card-ipa">{{ randomWordsMap[item.id].word[0].ipa }}</span>
            <div v-if="hiddenType[item.id] === 'word'" class="word-card-hint">点击显示单词</div>
          </div>
          <!-- 释义区：hiddenType 为 'meaning' 时隐藏 -->
          <div v-show="hiddenType[item.id] !== 'meaning'" class="word-card-meaning">
            {{ randomWordsMap[item.id].word[0].meaning || '-' }}
          </div>
          <div v-if="hiddenType[item.id] === 'meaning'" class="word-card-hint">点击显示释义</div>
        </div>
      </div>
      <div v-else-if="randomWordLoading[item.id]" class="word-card word-card-loading">
        <a-spin size="small" /> 加载中...
      </div>
      </div>
    </a-col>
  </a-row>
</template>
<style scoped>
/* 词典列表项容器 */
.dict-item {
  padding: 16px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
  position: relative;
}
.dict-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, #3b82f6 0%, #60a5fa 100%);
  border-radius: 4px 0 0 4px;
}
.dict-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  border-color: #cbd5e1;
}
.dict-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
.dict-item-name {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.dict-item-delete {
  flex-shrink: 0;
  font-size: 13px;
  color: #94a3b8;
  transition: color 0.2s ease;
}
.dict-item-delete:hover {
  color: #ef4444;
}
.dict-item-meta {
  margin-top: 4px;
  font-size: 12px;
  color: #94a3b8;
}

/* 单词卡片样式 */
.word-card {
  margin-top: 12px;
  max-width: 100%;
  padding: 16px;
  border-radius: 12px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.2s ease;
}
.word-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.word-card-clickable {
  cursor: pointer;
}
.word-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e8f0;
}
.word-card-hide-btn {
  font-size: 12px;
  color: #94a3b8;
  cursor: pointer;
}
.word-card-hide-btn:hover {
  color: #1890ff;
}
.word-card-actions {
  display: flex;
  gap: 4px;
}
.word-card-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}
.word-card-btn:hover {
  color: #1890ff;
  background: rgba(24, 144, 255, 0.08);
}
.word-card-body {
  padding: 12px 2px 0;
  text-align: center;
}
.word-card-main {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 10px;
  margin-bottom: 8px;
}
.word-card-text {
  font-size: 32px;
  font-weight: 600;
  color: #1e293b;
  letter-spacing: 0.5px;
}
.word-card-ipa {
  font-size: 14px;
  color: #94a3b8;
  font-style: italic;
}
.word-card-meaning {
  font-size: 14px;
  line-height: 1.6;
  color: #475569;
  white-space: pre-wrap;
  text-align: center;
}
.word-card-hint {
  font-size: 13px;
  color: #94a3b8;
  cursor: pointer;
  padding: 8px 0;
  user-select: none;
}
.word-card-hint:hover {
  color: #1890ff;
}
.word-card-loading {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
}

.ext {
  text-align: center;
  display: inline-block;
  width: 40px;
  margin-right: 3px;
  color: coral;
  font-weight: bold;
  border-style: solid;
  border-width: thin;
  border-color: crimson;
  padding: 2px;
  margin: 3px;
}

.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}
</style>
<script>
import { message } from 'ant-design-vue';
import { InboxOutlined, CopyOutlined, SoundOutlined, EyeOutlined, EyeInvisibleOutlined, ReloadOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, ref } from 'vue';

export default {
  components: {
    InboxOutlined,
    CopyOutlined,
    SoundOutlined,
    EyeOutlined,
    EyeInvisibleOutlined,
    ReloadOutlined
  },
  setup() {
    $cookies.set('selectedkey','21',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);

    const { proxy } = getCurrentInstance()
    const aaa=ref($cookies.get('token'));
    const bbb=ref();
    const file_key = ref('')
    const fileitems = ref([])
    const upload_url = ref(proxy.$remoteDomain+"/ajax/import_dict_ajax")
    // 每个词典的随机单词缓存，key为词典id
    const randomWordsMap = ref({})
    // 随机单词加载状态
    const randomWordLoading = ref({})
    // 默认随机隐藏单词或释义，key为词典id，值为 'word'|'meaning'|null（null=已全部显示）
    const hiddenType = ref({})

    onMounted(() => {
      // 触发 Chrome 预加载 TTS 语音列表，避免首次朗读失败
      if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
      }
      const interval=setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/list_dict_ajax/', params).then(res => {
        if (res.data.code=='401'){
          window.location.href ="/";
        }
        if (res.data.code=="200"){
          fileitems.value = res.data.data.documents
          fetchAllRandomWords();
        }
        else{
          message.error(res.data.msg);
        }
        defaultPercent.value = 100;
        loadingdone.value = true
      });
    });

    const handleBeforeUpload = (file) => {
      bbb.value=new Date().getTime();
      file_key.value = "dict/"+file.name;
    }

    /**
     * 刷新词典列表，从服务端获取最新数据
     */
    const refreshDictList = () => {
      const params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post('/ajax/list_dict_ajax/', params).then(res => {
        if (res.data.code === '401') {
          window.location.href = "/";
        }
        if (res.data.code === "200") {
          fileitems.value = res.data.data.documents;
          fetchAllRandomWords();
        } else {
          message.error(res.data.msg);
        }
      });
    };

    const handleChange = info => {
      const status = info.file.status;
      if (status === 'done') {
        if (info.file.response.code == "200") {
          message.success(`${info.file.name} 上传成功.`);
          refreshDictList();
        }
        else {
          message.error(info.file.response.msg);
        }
      } else if (status === 'error') {
        message.error(`${info.file.name} 上传失败.`);
      }
    };

    const deletefile = (id) => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("id", id);
      proxy.$http.post('/ajax/delete_dict_ajax/', params).then(res => {
        fileitems.value = res.data.data.documents
        // 删除后移除该词典的随机单词及显隐状态缓存
        const newMap = { ...randomWordsMap.value };
        delete newMap[id];
        randomWordsMap.value = newMap;
        const newHidden = { ...hiddenType.value };
        delete newHidden[id];
        hiddenType.value = newHidden;
      });
    };

    /**
     * 获取指定词典的随机单词
     * @param {string|number} dictId - 词典id
     */
    const fetchRandomWord = (dictId) => {
      randomWordLoading.value = { ...randomWordLoading.value, [dictId]: true };
      const params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("dict_id", dictId);
      proxy.$http.post('/ajax/random_word_ajax/', params).then(res => {
        randomWordLoading.value = { ...randomWordLoading.value, [dictId]: false };
        // data.word 为单词对象数组，code 可能为数字或字符串
        if ((res.data.code === 200 || res.data.code === '200') && res.data.data?.word?.length) {
          randomWordsMap.value = { ...randomWordsMap.value, [dictId]: res.data.data };
          // 随机隐藏单词或释义二者之一
          hiddenType.value = { ...hiddenType.value, [dictId]: Math.random() < 0.5 ? 'word' : 'meaning' };
        }
      }).catch(() => {
        randomWordLoading.value = { ...randomWordLoading.value, [dictId]: false };
      });
    };

    /**
     * 点击显示图标后，展示被隐藏的单词或释义
     */
    const revealContent = (dictId) => {
      if (hiddenType.value[dictId]) {
        hiddenType.value = { ...hiddenType.value, [dictId]: null };
      }
    };

    /**
     * 卡片点击：有隐藏项时显示，全部显示时换一个
     */
    const handleCardClick = (dictId) => {
      if (hiddenType.value[dictId]) {
        revealContent(dictId);
      } else {
        fetchRandomWord(dictId);
      }
    };

    /**
     * 复制单词及释义到剪贴板
     */
    const copyWord = (dictId) => {
      const data = randomWordsMap.value[dictId]?.word?.[0];
      if (!data) return;
      const text = `${data.word}${data.ipa ? ' ' + data.ipa : ''}\n${data.meaning || ''}`;
      navigator.clipboard?.writeText(text).then(() => {
        message.success('已复制到剪贴板');
      }).catch(() => message.error('复制失败'));
    };

    /**
     * 朗读单词，使用浏览器 TTS
     * Chrome：需在用户点击后调用；先 cancel 再 speak 可避免卡住
     */
    const speakWord = (dictId) => {
      const word = randomWordsMap.value[dictId]?.word?.[0]?.word;
      if (!word) return;
      if (!('speechSynthesis' in window)) {
        message.warning('当前浏览器不支持语音朗读');
        return;
      }
      window.speechSynthesis.cancel();
      const utterance = new SpeechSynthesisUtterance(word);
      utterance.lang = 'en-US';
      utterance.rate = 1;
      utterance.volume = 1;
      const voices = window.speechSynthesis.getVoices();
      const enVoice = voices.find(v => v.lang.startsWith('en'));
      if (enVoice) utterance.voice = enVoice;
      window.speechSynthesis.speak(utterance);
    };

    /**
     * 不再显示当前单词：标记隐藏后获取新单词
     */
    const hideWord = (dictId) => {
      const wordId = randomWordsMap.value[dictId]?.word?.[0]?.id;
      if (!wordId) return;
      randomWordLoading.value = { ...randomWordLoading.value, [dictId]: true };
      const params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("hidden", "1");
      params.append("word_id", wordId);
      params.append("dict_id", dictId);
      proxy.$http.post('/ajax/update_word_status_ajax/', params).then(res => {
        randomWordLoading.value = { ...randomWordLoading.value, [dictId]: false };
        if ((res.data.code === 200 || res.data.code === '200') && res.data.data?.word?.length) {
          randomWordsMap.value = { ...randomWordsMap.value, [dictId]: res.data.data };
          hiddenType.value = { ...hiddenType.value, [dictId]: Math.random() < 0.5 ? 'word' : 'meaning' };
        }
      }).catch(() => {
        randomWordLoading.value = { ...randomWordLoading.value, [dictId]: false };
      });
    };

    /**
     * 为所有词典获取随机单词
     */
    const fetchAllRandomWords = () => {
      fileitems.value.forEach(item => {
        if (item.words_amount > 0) {
          fetchRandomWord(item.id);
        }
      });
    };

    return {
      aaa,
      bbb,
      upload_url,
      file_key,
      fileitems,
      randomWordsMap,
      randomWordLoading,
      hiddenType,
      fetchRandomWord,
      revealContent,
      handleCardClick,
      hideWord,
      copyWord,
      speakWord,
      handleBeforeUpload,
      handleChange,
      deletefile,
      fileList: ref([]),
      handleDrop: e => {
        console.log(e);
      },
      defaultPercent,
      loadingdone
    };
  },
}
</script>
