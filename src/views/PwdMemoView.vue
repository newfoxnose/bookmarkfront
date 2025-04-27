<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
 </div>
  <h3 class="content-title">密码管理系统</h3>

  <div class="form-container" style="margin: 10px;">
    <a-form layout="vertical">
      <a-row :gutter="[16, 16]">
        <a-col :xs="24" :sm="24" :md="24" :lg="2" style="display: flex; align-items: center;">
          新增密码
        </a-col>
        <a-col :xs="24" :sm="12" :md="12" :lg="4">
          <a-form-item label="名称*">
            <a-input v-model:value="newName" placeholder="名称（必填）" />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :sm="12" :md="12" :lg="5">
          <a-form-item label="网址">
            <a-input v-model:value="newUrl" placeholder="网址（非必填）" />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :sm="12" :md="12" :lg="4">
          <a-form-item label="登录名">
            <a-input v-model:value="newUsername" placeholder="登录名（非必填）" />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :sm="12" :md="12" :lg="3">
          <a-form-item label="密码*">
            <a-input-password v-model:value="pwd" placeholder="密码（必填）" />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :sm="12" :md="12" :lg="3">
          <a-form-item label="密钥*">
            <a-input-password v-model:value="encrypt_key" placeholder="密钥（必填）" />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="3">
          <a-form-item>
            <a-button type="primary" @click="insertMonitoringUrl" :loading="iconLoading" style="width: 120px; margin-top: 30px;">提交</a-button>
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>
  </div>

  <div class="table-container">
    <a-table 
      :columns="columns" 
      :data-source="fileitems" 
      :pagination="false"
      :scroll="{ x: 'max-content' }"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'site_name'">
          <template v-if="record.site_url && /^https?:\/\/.+/.test(record.site_url)">
            <a :href="record.site_url" target="_blank">{{ record.site_name }}</a>
          </template>
          <template v-else>
            {{ record.site_name }}
          </template>
        </template>
        <template v-if="column.key === 'decrypt'">
          <a-space>
            <a-input-password v-model:value="record.decrypt_key" placeholder="输入密钥" style="width: 100px" />
            <a-button type="primary" @click="decryptPassword(record)" :loading="iconLoading">
              查看密码
            </a-button>
          </a-space>
        </template>
        <template v-if="column.key === 'action'">
          <a-space>
            <a-button type="primary" danger  @click="deleteMonitoringUrl(record)" :loading="iconLoading">
              删除
            </a-button>
          </a-space>
        </template>
      </template>
    </a-table>
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

.form-container {
  padding: 15px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.table-container {
  padding: 15px;
  border-radius: 4px;
}

.content-title {
  margin: 0 0 20px 0;
  padding-top: 20px;
}

@media screen and (max-width: 768px) {
  .form-container {
    padding: 10px;
  }
  
  .table-container {
    padding: 10px;
  }
  
  .content-title {
    padding: 0 10px;
  }
}
</style>
<script>
import { message, Modal } from 'ant-design-vue';
import { EditOutlined, DeleteOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';
import CryptoJS from 'crypto-js';

export default {
  components: {
    EditOutlined,
    DeleteOutlined,
    EyeOutlined
  },
  setup() {
    $cookies.set('selectedkey', '18', "720h")
    $cookies.set('openkey', '')
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const iconLoading = ref(false);

    const { proxy } = getCurrentInstance()

    const fileitems = ref([])
    const newUrl = ref('')
    const newName = ref('');
    const newUsername = ref('');
    const pwd = ref('');
    const encrypt_key = ref('');

    const columns = ref([
      {
        title: 'ID',
        dataIndex: 'id',
        key: 'id',
        width: 80,
      },
      {
        title: ' 名称',
        dataIndex: 'site_name',
        key: 'site_name',
        width: 200,
      },
      {
        title: '用户名',
        dataIndex: 'username',
        key: 'username',
        width: 200,
      },
      {
        title: '加密密码',
        dataIndex: 'encrypted_string',
        key: 'encrypted_string',
        width: 300,
        ellipsis: true,
        customRender: ({ text }) => {
          return {
            props: {
              style: { wordBreak: 'break-all', whiteSpace: 'normal' }
            },
            children: text
          }
        }
      },
      {
        title: '解密',
        key: 'decrypt',
        fixed: 'right',
        align: 'center',
        width: 100,
      },
      {
        title: '操作',
        key: 'action',
        fixed: 'right',
        align: 'center'
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

    // 加密函数
    const encryptPassword = (password, key) => {
      try {
        return CryptoJS.AES.encrypt(password, key).toString();
      } catch (error) {
        console.error('加密失败:', error);
        return null;
      }
    };

    // 解密函数
    const decryptPassword = (record) => {
      if (!record.decrypt_key) {
        message.warning('请输入密钥');
        return;
      }

      try {
        const bytes = CryptoJS.AES.decrypt(record.encrypted_string, record.decrypt_key);
        const decrypted = bytes.toString(CryptoJS.enc.Utf8);
        
        if (decrypted) {
          Modal.info({
            title: '密码信息',
            content: `${record.site_name} 的密码是: ${decrypted}`,
          });
        } else {
          message.error('密钥错误或密码格式不正确');
        }
      } catch (error) {
        message.error('解密失败，请检查密钥是否正确');
      }
    };

    const insertMonitoringUrl = () => {
      if (!newName.value) {
        message.warning('请输入名称');
        return;
      }
      if (!pwd.value) {
        message.warning('请输入密码');
        return;
      }
      if (!encrypt_key.value) {
        message.warning('请输入密钥');
        return;
      }

      const encrypted = encryptPassword(pwd.value, encrypt_key.value);
      if (!encrypted) {
        message.error('加密失败，请重试');
        return;
      }
      
      iconLoading.value = true;
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      params.append("site_name", newName.value);
      params.append("username", newUsername.value);
      params.append("site_url", newUrl.value);
      params.append("encrypted_string", encrypted);
      
      proxy.$http.post('/ajax/insert_pwdmemo_ajax/', params)
        .then(res => {
          if (res.data.code=='200') {
            message.success(res.data.msg);
            // 清空输入框
            newName.value = '';
            newUsername.value = '';
            newUrl.value = '';
            pwd.value = '';
            encrypt_key.value = '';
            // 强制清空密码输入框的DOM值
            document.querySelectorAll('input[type="password"]').forEach(input => {
              input.value = '';
            });
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

    const loadMonitoringList = () => {
      let params = new URLSearchParams();
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post('/ajax/list_pwdmemo_ajax/', params).then(res => {
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
          
          proxy.$http.post('/ajax/delete_pwdmemo_ajax/', params)
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

    return {
      fileitems,
      visible,
      defaultPercent,
      loadingdone,
      iconLoading,
      columns,
      handleUrlChange,
      deleteMonitoringUrl,
      newUrl,
      newName,
      newUsername,
      pwd,
      encrypt_key,
      decryptPassword,
      insertMonitoringUrl
    };
  },
}
</script>
