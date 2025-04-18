<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>
  <h3 class="content-title">数据抓取</h3>

  <div class="input-section">
    <a-form layout="vertical">
      <a-form-item label="新增抓取网址">
        <a-input v-model:value="newUrl" placeholder="请输入要抓取数据的网址" />
      </a-form-item>
      <a-form-item label="Bearer">
        <a-textarea v-model:value="newBearer" placeholder='bearer值，不包含bearer字样以及空格' :rows="3" />
      </a-form-item>
      <a-form-item label="表单数据">
        <a-textarea v-model:value="newPostData" placeholder='表单数据' :rows="3" />
      </a-form-item>
      <a-form-item label="格式化显示规则">
        <a-textarea v-model:value="newFormatting" placeholder='格式如下:{ "pairs": {     "显示名称1": "数据路径1","显示名称2": "数据路径2"}, "tables":["表格1数据路径","表格2数据路径"]}' :rows="3" />
      </a-form-item>
      <a-form-item>
        <a-button type="primary" @click="insertFetchUrl" :loading="iconLoading" block>提交</a-button>
      </a-form-item>
    </a-form>
  </div>

  <a-table :columns="columns" :data-source="fileitems" :pagination="false" rowKey="id" v-model:expandedRowKeys="expandedRowKeys" :scroll="{ x: true }">
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'url'">
        <a-input v-model:value="record.url" @change="handleUrlChange(record)" />
      </template>
      <template v-if="column.key === 'bearer'">
        <a-textarea v-model:value="record.bearer" @change="handlePostDataChange(record)" :rows="3" />
      </template>
      <template v-if="column.key === 'formatting'">
        <a-textarea v-model:value="record.formatting" @change="handleFormattingChange(record)" :rows="3" />
      </template>
      <template v-if="column.key === 'post_data'">
        <a-textarea v-model:value="record.post_data" @change="handlePostDataChange(record)" :rows="3" />
      </template>
      <template v-if="column.key === 'action'">
        <a-space direction="vertical" align="center" style="width: 100%">
          <a-button type="primary" @click="fetchUrlContent(record)" :loading="iconLoading" style="width: 80px">
            <template #icon><download-outlined /></template>
            抓取
          </a-button>
          <a-button type="primary" @click="updateFetchUrl(record)" :loading="iconLoading" style="width: 80px">
            <template #icon><edit-outlined /></template>
            修改
          </a-button>
          <a-button type="primary" danger @click="deleteFetchUrl(record)" :loading="iconLoading" style="width: 80px">
            <template #icon><delete-outlined /></template>
            删除
          </a-button>
        </a-space>
      </template>
    </template>
    <template #expandedRowRender="{ record }">
      <div v-if="record.fetch_data">
        <pre
          style="background: #f5f5f5; padding: 8px; border-radius: 4px; max-height: 200px; overflow: auto; margin: 0;">
          {{ JSON.stringify(record.fetch_data, null, 2) }}
        </pre>
        <div v-if="record.formatting && record.formatted_data">
          <a-table 
            v-if="getPairsData(record).length > 0"
            :columns="[
              { title: '字段', dataIndex: 'key', key: 'key', width: 200 },
              { title: '值', dataIndex: 'value', key: 'value', width: 300 }
            ]"
            :dataSource="getPairsData(record)"
            :pagination="false"
            size="small"
            style="margin-top: 10px"
          />
          <template v-for="(tablePath, index) in getTablePaths(record)" :key="index">
            <a-table 
              v-if="getTableData(record, tablePath).length > 0"
              :columns="formatDataAndColumns(getTableData(record, tablePath)[0]).columns"
              :dataSource="getTableData(record, tablePath)"
              :pagination="false"
              size="small"
              style="margin-top: 10px"
            />
          </template>
        </div>
      </div>
    </template>
  </a-table>

</template>
<style scoped>
.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}

.input-section {
  padding: 15px;
  max-width: 800px;
  margin: 0 auto;
}

.content-title {
  text-align: center;
  margin: 20px 0;
}

@media screen and (max-width: 768px) {
  .input-section {
    padding: 10px;
  }
  
  :deep(.ant-table) {
    width: 100%;
    overflow-x: auto;
  }
  
  :deep(.ant-table-cell) {
    white-space: nowrap;
  }
}
</style>
<script>
import { message, Modal } from 'ant-design-vue';

import { EditOutlined, DeleteOutlined, DownloadOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref, h } from 'vue';

export default {
  components: {
    EditOutlined,
    DeleteOutlined,
    DownloadOutlined
  },
  setup() {
    $cookies.set('selectedkey', '16', "720h")
    $cookies.set('openkey', '')
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const iconLoading = ref(false);

    const { proxy } = getCurrentInstance()

    const fileitems = ref([])
    const newUrl = ref('')
    const newFormatting = ref('')
    const newPostData = ref('')
    const newBearer = ref('')

    const columns = ref([
      {
        title: 'ID',
        dataIndex: 'id',
        key: 'id',
        width: 80,
      },
      {
        title: '网址',
        dataIndex: 'url',
        key: 'url',
        width: 300,
      },
      {
        title: 'Bearer',
        dataIndex: 'bearer',
        key: 'bearer',
        width: 300,
      },
      {
        title: '表单数据',
        dataIndex: 'post_data',
        key: 'post_data',
        width: 300,
      },
      {
        title: '格式化显示规则',
        dataIndex: 'formatting',
        key: 'formatting',
        width: 300,
      },
      {
        title: '操作',
        key: 'action',
        fixed: 'right',
        width: 250,
      }
    ]);

    const formatDataAndColumns = (data) => {
      if (!data || typeof data !== 'object') return { dataSource: [], columns: [] };

      // 获取所有键作为列
      const keys = Object.keys(data);
      const columns = keys.map(key => ({
        title: key,
        dataIndex: key,
        key: key,
        width: 200,
        customRender: ({ text }) => {
          if (!text) return '';
          
          // 如果是字符串或数字，直接显示
          if (typeof text === 'string' || typeof text === 'number') {
            return text;
          }
          
          // 如果是对象，递归处理
          if (typeof text === 'object' && !Array.isArray(text)) {
            const { dataSource, columns } = formatDataAndColumns(text);
            return h('div', { style: 'padding: 8px;' }, [
              h('a-table', {
                columns: columns,
                dataSource: dataSource,
                pagination: false,
                size: 'small'
              })
            ]);
          }
          
          // 如果是数组，将数组内容作为表格显示
          if (Array.isArray(text)) {
            if (text.length === 0) return '[]';
            
            // 获取数组第一个元素的键作为列
            const firstItem = text[0];
            if (typeof firstItem !== 'object') {
              return text.join(', ');
            }
            
            const arrayColumns = Object.keys(firstItem).map(subKey => ({
              title: subKey,
              dataIndex: subKey,
              key: subKey,
              width: 200,
              customRender: ({ text: subText }) => {
                if (typeof subText === 'object') {
                  const { dataSource, columns } = formatDataAndColumns(subText);
                  return h('div', { style: 'padding: 8px;' }, [
                    h('a-table', {
                      columns: columns,
                      dataSource: dataSource,
                      pagination: false,
                      size: 'small'
                    })
                  ]);
                }
                return subText;
              }
            }));

            return h('div', { style: 'padding: 8px;' }, [
              h('a-table', {
                columns: arrayColumns,
                dataSource: text.map((item, index) => ({ key: index, ...item })),
                pagination: false,
                size: 'small'
              })
            ]);
          }
          
          return JSON.stringify(text);
        }
      }));

      // 创建数据源
      const dataSource = [{
        key: 'root',
        ...data
      }];

      return { dataSource, columns };
    };

    const fetchColumns = ref([]);

    const visible = ref(false);

    const expandedRowKeys = ref([]);


    const insertFetchUrl = () => {
      if (!newUrl.value) {
        message.warning('请输入要监控的网址');
        return;
      }

      iconLoading.value = true;
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("url", newUrl.value);
      params.append("formatting", newFormatting.value);
      params.append("post_data", newPostData.value);
      params.append("bearer", newBearer.value);

      proxy.$http.post('/ajax/insert_fetch_url_ajax/', params)
        .then(res => {
          if (res.data.code == '200') {
            message.success(res.data.msg);
            // 清空输入框
            newUrl.value = '';
            newFormatting.value = '';
            newPostData.value = '';
            newBearer.value = '';
          } else {
            message.error(res.data.msg);
          }
          fileitems.value = res.data.data.history;
        })
        .catch(error => {
          message.error('添加失败，请重试');
          console.error(error);
        })
        .finally(() => {
          iconLoading.value = false;
        });
    };

    const loadFetchList = () => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post('/ajax/list_fetch_url_ajax/', params).then(res => {
        if (res.data.code == '401') {
          window.location.href = "/";
        }
        // 确保每条记录都有fetch_data字段
        fileitems.value = res.data.data.history.map(item => ({
          ...item,
          fetch_data: item.fetch_data || null
        }));
      });
    };

    onMounted(() => {
      const interval = setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random() * 7 + 2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100)
      loadFetchList();
      defaultPercent.value = 100;
      loadingdone.value = true;
    })

    const handleUrlChange = (record) => {
      console.log('URL changed:', record);
    };

    const handleFormattingChange = (record) => {
      console.log('Formatting changed:', record);
    };

    const handlePostDataChange = (record) => {
      console.log('Post data changed:', record);
    };

    const handleBearerChange = (record) => {
      console.log('Bearer changed:', record);
    };

    const deleteFetchUrl = (record) => {
      Modal.confirm({
        title: "确认删除该项目吗？",
        content: "点击确定删除且无法找回，点击取消则放弃删除",
        onOk() {
          iconLoading.value = true;
          let params = new URLSearchParams();
          params.append("token", $cookies.get('token'));
          params.append("timestamp", new Date().getTime());
          params.append("id", record.id);

          proxy.$http.post('/ajax/delete_fetch_url_ajax/', params)
            .then(res => {
              if (res.data.msg) {
                message.success(res.data.msg);
                // 从本地数据中移除该记录
                fileitems.value = fileitems.value.filter(item => item.id !== record.id);
              }
            })
            .catch(error => {
              message.error('删除失败，请重试');
              console.error(error);
            })
            .finally(() => {
              iconLoading.value = false;
            });
        },
        onCancel() {},
      });
    };

    const updateFetchUrl = (record) => {
      iconLoading.value = true;
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("id", record.id);
      params.append("url", record.url);
      params.append("formatting", record.formatting);
      params.append("post_data", record.post_data);
      params.append("bearer", record.bearer);

      proxy.$http.post('/ajax/update_fetch_url_ajax/', params)
        .then(res => {
          if (res.data.msg) {
            message.success(res.data.msg);
            // 更新本地数据
            const index = fileitems.value.findIndex(item => item.id === record.id);
            if (index !== -1) {
              fileitems.value[index] = { 
                ...fileitems.value[index], 
                url: record.url, 
                formatting: record.formatting,
                post_data: record.post_data,
                bearer: record.bearer
              };
            }
          }
        })
        .catch(error => {
          message.error('更新失败，请重试');
          console.error(error);
        })
        .finally(() => {
          iconLoading.value = false;
        });
    };

    const fetchUrlContent = (record) => {
      iconLoading.value = true;
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      
      let requestPromise;
      const config = {};
      
      // 如果有bearer，添加Authorization请求头
      if (record.bearer) {
        config.headers = {
          'Authorization': `Bearer ${record.bearer}`
        };
      }
      
      if (record.post_data) {
        // 如果有post_data，使用POST请求
        try {
          const postData = JSON.parse(record.post_data);
          requestPromise = proxy.$http.post(record.url, postData, config);
        } catch (e) {
          message.error('表单数据格式错误，请检查JSON格式');
          iconLoading.value = false;
          return;
        }
      } else {
        // 如果没有post_data，使用GET请求
        requestPromise = proxy.$http.get(record.url, config);
      }
      
      requestPromise
        .then(res => {
          // 更新记录的fetch_data
          const index = fileitems.value.findIndex(item => item.id === record.id);
          if (index !== -1) {
            let formattedData = {};
            const originalData = res.data;
            
            // 如果有formatting规则，则按照规则处理数据
            if (record.formatting) {
              try {
                const formatting = JSON.parse(record.formatting);
                
                // 处理pairs
                if (formatting.pairs) {
                  Object.entries(formatting.pairs).forEach(([key, path]) => {
                    const value = path.split('.').reduce((obj, key) => obj?.[key], originalData);
                    formattedData[key] = value !== undefined ? value : '';
                  });
                }
                
                // 处理tables
                if (formatting.tables) {
                  formatting.tables.forEach(path => {
                    const data = path.split('.').reduce((obj, key) => obj?.[key], originalData);
                    if (Array.isArray(data)) {
                      formattedData[path] = data;
                    }
                  });
                }
              } catch (e) {
                console.error('解析格式化规则失败:', e);
                formattedData = originalData;
              }
            } else {
              formattedData = originalData;
            }
            
            fileitems.value[index] = { 
              ...fileitems.value[index], 
              fetch_data: originalData,
              formatted_data: formattedData
            };
            
            // 自动展开当前行
            if (!expandedRowKeys.value.includes(record.id.toString())) {
              expandedRowKeys.value = [record.id.toString()];
            }
            message.success('抓取成功');
          }
        })
        .catch(error => {
          message.error('抓取失败，请重试');
          console.error(error);
        })
        .finally(() => {
          iconLoading.value = false;
        });
    };

    const getPairsData = (record) => {
      try {
        const formatting = JSON.parse(record.formatting);
        if (!formatting.pairs) return [];
        
        return Object.entries(formatting.pairs).map(([key, path]) => {
          const value = record.formatted_data[key];
          return {
            key,
            value: value !== undefined ? value : ''
          };
        });
      } catch (e) {
        console.error('解析格式化规则失败:', e);
        return [];
      }
    };

    const getTablePaths = (record) => {
      try {
        const formatting = JSON.parse(record.formatting);
        return formatting.tables || [];
      } catch (e) {
        console.error('解析格式化规则失败:', e);
        return [];
      }
    };

    const getTableData = (record, path) => {
      try {
        const data = record.formatted_data[path];
        return Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('获取表格数据失败:', e);
        return [];
      }
    };

    return {
      fileitems,
      visible,
      defaultPercent,
      loadingdone,
      iconLoading,
      columns,
      fetchColumns,
      handleUrlChange,
      handleFormattingChange,
      handlePostDataChange,
      handleBearerChange,
      updateFetchUrl,
      deleteFetchUrl,
      newUrl,
      newFormatting,
      newPostData,
      newBearer,
      insertFetchUrl,
      expandedRowKeys,
      fetchUrlContent,
      formatDataAndColumns,
      getPairsData,
      getTablePaths,
      getTableData
    };
  },
}
</script>
