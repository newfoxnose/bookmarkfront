<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
 </div>
  <h3 class="content-title">监控网站状态</h3>

  <div class="input-section">
    <a-row :gutter="[16, 16]">
      <a-col :xs="24" :sm="24" :md="4">
        <div class="input-label">新增监控网址</div>
      </a-col>
      <a-col :xs="24" :sm="24" :md="16">
        <a-input v-model:value="newUrl" placeholder="请输入要监控的网址" />
      </a-col>
      <a-col :xs="24" :sm="24" :md="4">
        <a-button type="primary" @click="insertMonitoringUrl" :loading="iconLoading" block>提交</a-button>
      </a-col>
    </a-row>
  </div>


    <a-table 
      :columns="columns" 
      :data-source="fileitems" 
      :pagination="false"
      :scroll="{ x: true }"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'url'">
          <a-input v-model:value="record.url" @change="handleUrlChange(record)" />
        </template>
        <template v-if="column.key === 'action'">
          <a-space>
            <a-button type="primary" @click="openUrl(record.url)">
              <template #icon><link-outlined /></template>
              访问
            </a-button>
            <a-button type="primary" @click="updateMonitoringUrl(record)" :loading="iconLoading">
              <template #icon><edit-outlined /></template>
              修改
            </a-button>
            <a-button type="primary" danger @click="deleteMonitoringUrl(record)" :loading="iconLoading">
              <template #icon><delete-outlined /></template>
              删除
            </a-button>
          </a-space>
        </template>
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

.content-title {
  margin: 16px;
  text-align: center;
}


.input-section {
  padding: 15px;
  max-width: 800px;
  margin: 0 auto;
}

.input-label {
  line-height: 32px;
  text-align: center;
}



@media screen and (max-width: 768px) {
  .input-section {
    padding: 10px;
  }

  .ant-table {
    font-size: 12px;
  }
  
  .ant-btn {
    padding: 0 8px;
    font-size: 12px;
  }
}
</style>
<script>
import { message, Modal } from 'ant-design-vue';

import { EditOutlined, DeleteOutlined, LinkOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';

export default {
  components: {
    EditOutlined,
    DeleteOutlined,
    LinkOutlined
  },
  setup() {
    $cookies.set('selectedkey', '15', "720h")
    $cookies.set('openkey', '')
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const iconLoading = ref(false);

    const { proxy } = getCurrentInstance()

    const fileitems = ref([])
    const newUrl = ref('')

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
        title: '状态码',
        dataIndex: 'httpcode',
        key: 'httpcode',
        width: 100,
      },
      {
        title: '结果',
        dataIndex: 'monitoring_result',
        key: 'monitoring_result',
        width: 150,
      },
      {
        title: '检查时间',
        dataIndex: 'updatetime',
        key: 'updatetime',
        width: 180,
      },
      {
        title: '操作',
        key: 'action',
        fixed: 'right',
        width: 150,
      }
    ]);

    const visible = ref(false);
    const showDrawer = (record) => {
      drawer.value.feed_id = record.id;
      drawer.value.feed_name = record.feed_name;
      drawer.value.feed_url = record.url;
      drawer.value.is_private = record.is_private === 1;
      visible.value = true;
    };
    const onClose = () => {
      iconLoading.value = false;
      visible.value = false;
    };
    const getUrl = () => {
      if (proxy.$func.isValidUrl(formState.value.feed_url)==true){
        iconLoading.value = true;
      message.info('自动获取网页标题中，请稍等');
          let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
          params.append("url", formState.value.feed_url);
          params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
          const { data: res } = proxy.$http.post('/ajax/url_title', params)
            .then(res => {
              console.log(res.data);
              // obj.success ? obj.success(res) : null
              if (res.data.msg == "请求成功") {
                message.info("成功获取源名称");
                formState.value.feed_name = res.data.data.title
              }
              else {
                message.info("未获取到源名称，请手动输入");
              }
            })
            .catch(error => {
              // obj.error ? obj.error(error) : null;
              console.log(error);
            })
      iconLoading.value = false;
      }
      else{
        message.info('网址无效');
      }
    };
    const insertMonitoringUrl = () => {
      if (!newUrl.value) {
        message.warning('请输入要监控的网址');
        return;
      }
      
      iconLoading.value = true;
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("url", newUrl.value);
      
      proxy.$http.post('/ajax/insert_monitoring_url_ajax/', params)
        .then(res => {
          if (res.data.code=='200') {
            message.success(res.data.msg);
            // 清空输入框
            newUrl.value = '';
          }else{
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

    const loadMonitoringList = () => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post('/ajax/list_monitoring_url_ajax/', params).then(res => {
        if (res.data.code == '401') {
          window.location.href = "/";
        }
        fileitems.value = res.data.data.history;
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
      loadMonitoringList();
      defaultPercent.value = 100;
      loadingdone.value = true;
    })
    const deletefeed = (id) => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("feed_id", id);
      proxy.$http.post('/ajax/delete_feed_ajax/', params).then(res => {
        fileitems.value = res.data.data.feed
      });
      visible.value = false;
      iconLoading.value = false
    };
    const onFinish = values => {
      iconLoading.value = true;
      console.log('提交数据Success:', values);
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("feed_id", values.feed_id);
      params.append("feed_name", values.feed_name);
      params.append("feed_url", values.feed_url);
      if (values.is_private == true) {
          params.append("is_private", 1);
        }
        else {
          params.append("is_private", 0);
        }
      proxy.$http.post('/ajax/save_feed_ajax/', params).then(res => {
        if (res.data.msg!=''){
          iconLoading.value = false;
          message.success(res.data.msg);
          if (res.data.msg=='添加成功'){
            fileitems.value.unshift(res.data.data);
          }
          else{
            
          }
        }
      });
    };
    const onFinishFailed = errorInfo => {
      //message.success(`失败.`);
      console.log('Failed:', errorInfo);
    };
    const handleUrlChange = (record) => {
      // 这里可以添加URL修改后的处理逻辑
      console.log('URL changed:', record);
    };

    const deleteMonitoringUrl = (record) => {
      Modal.confirm({
        title: "确认删除该项目吗？",
        content: "点击确定删除且无法找回，点击取消则放弃删除",
        onOk() {
          iconLoading.value = true;
          let params = new URLSearchParams();
          params.append("token", $cookies.get('token'));
          params.append("timestamp", new Date().getTime());
          params.append("id", record.id);
          
          proxy.$http.post('/ajax/delete_monitoring_url_ajax/', params)
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

    const updateMonitoringUrl = (record) => {
      iconLoading.value = true;
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("id", record.id);
      params.append("url", record.url);
      
      proxy.$http.post('/ajax/update_monitoring_url_ajax/', params)
        .then(res => {
          if (res.data.msg) {
            message.success(res.data.msg);
            // 更新本地数据
            const index = fileitems.value.findIndex(item => item.id === record.id);
            if (index !== -1) {
              fileitems.value[index] = { ...fileitems.value[index], url: record.url };
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

    const openUrl = (url) => {
      window.open(url, '_blank');
    };

    return {
      fileitems,
      visible,
      defaultPercent,
      loadingdone,
      iconLoading,
      columns,
      handleUrlChange,
      updateMonitoringUrl,
      deleteMonitoringUrl,
      newUrl,
      insertMonitoringUrl,
      openUrl
    };
  },
}
</script>
