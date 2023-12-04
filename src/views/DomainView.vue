<script>

import { message } from 'ant-design-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';
import md5 from 'js-md5';
export default defineComponent({
  setup() {
    const testuser = ref(false);
    const iconLoading = ref(false);
    const { proxy } = getCurrentInstance()
    const formState = ref([])
    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/get_profile_ajax/', params).then(res => {
        if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/login";
    }
        formState.value=res.data.data;
        if (res.data.data.email=='test@test.com'){
          testuser.value=true;
        }
      });
    })
    const onFinish = values => {
      iconLoading.value = true;
      //console.log('提交数据Success:', values);
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      params.append("current_pwd", md5(values.current_pwd));
      params.append("domain", values.domain);
      proxy.$http.post('/ajax/update_domain_ajax/', params).then(res => {
        formState.value=res.data.data;
        if (res.data.msg!=''){
          iconLoading.value = false;
          if (res.data.msg!='修改成功!'){
            message.error(res.data.msg);
          }
          else{
            message.success(res.data.msg);
          }
        }
      });
    };
    const onFinishFailed = errorInfo => {
      //message.success(`失败.`);
      console.log('Failed:', errorInfo);
    };
    return {
      formState,
      onFinish,
      onFinishFailed,
      iconLoading,
      testuser
    };
  }
});
</script>
<template>
  <div style="margin-top:15px;"></div>
  <a-form :model="formState" name="basic" :label-col="{ span: 6 }" :wrapper-col="{ span: 18 }" autocomplete="off"
    @finish="onFinish" @finishFailed="onFinishFailed">
    <a-form-item label="域名" name="domain">
      <a-input v-model:value="formState.domain" :disabled="testuser" suffix="开头不带协议，结尾不带/"/>
      <a-typography-paragraph>请为此域名添加一条cname记录指向cname.gm.ws</a-typography-paragraph>
    </a-form-item> 
    <a-form-item label="现密码" name="current_pwd" :rules="[{ required:true, message: '现密码不能为空' }]">
      <a-input-password v-model:value="formState.current_pwd" />
    </a-form-item>

    <a-form-item :wrapper-col="{ offset: 6, span: 18 }">
      <a-button type="primary" html-type="submit" :loading="iconLoading">提交</a-button>
    </a-form-item>
  </a-form>
</template>

<style scoped>
.theme_thumbnail{
  margin:5px;
  width:130px;
  height:82px;
  padding:3px;
}
.theme_thumbnail img{
  width:100%;
  border-style:solid;
  border-width:thin;
  border-color:white;
  filter: brightness(0.7);
}
.ant-radio-button-wrapper-checked {
  border-color:rgb(81, 19, 214) !important;
  background-color:rgb(81, 19, 214) !important;
}
.ant-radio-button-wrapper-checked img {
  filter: brightness(1.2);
}
</style>