<script>
import { message } from 'ant-design-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';
import md5 from 'js-md5';
export default defineComponent({
  setup() {
    $cookies.set('selectedkey','9',"720h") 
    $cookies.set('openkey','sub1',"720h") 
    const iconLoading = ref(false);
    const { proxy } = getCurrentInstance()
    const formState = ref([])
    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/get_profile_ajax/', params).then(res => {
        if (res.data.code=='401'){      //不在登陆状态或为test用户
      window.location.href ="/";
    }
        formState.value=res.data.data;
      });
    })
    const onFinish = values => {
      iconLoading.value = true;
      //console.log('提交数据Success:', values);
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      for (var x in values){
        if (values[x]==null){
          values[x]='';
        }
        if (x=="current_pwd"||x=="pwd"||x=="pwd_repeat"){
          params.append(x, md5(values[x]));
        }
        else{
          params.append(x, values[x]);
        }
      }
      proxy.$http.post('/ajax/update_profile_ajax/', params).then(res => {
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
      iconLoading
    };
  },
  data() {
    return {
      theme_list: ["bs1.css","bs2.css","bs3.css","bs4.css","bs5.css","bs6.css","bs7.css","bs8.css","bs9.css","bs10.css","bs11.css","bs12.css","bs13.css","bs14.css","bs15.css","bs16.css","bs17.css","bs18.css","bs19.css","bs20.css","bs21.css","bs22.css","bs23.css","bs24.css","bs25.css"]
    }
  },
});
</script>
<template>
  <div style="margin-top:15px;"></div>
  <a-form :model="formState" name="basic" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }" autocomplete="off"
    @finish="onFinish" @finishFailed="onFinishFailed">
    <a-form-item label="昵称" name="name" :rules="[{ required: true, message: '昵称不能为空' }]">
      <a-input v-model:value="formState.name" disabled/>
    </a-form-item>

    <a-form-item label="邮箱" name="email" :rules="[{ required: true, message: '邮箱不能为空' }]">
      <a-input v-model:value="formState.email" disabled/>
    </a-form-item>
    <a-form-item label="SLOGAN" name="slogan" style="display:none">
      <a-input v-model:value="formState.slogan" />
    </a-form-item>
    <a-form-item label="七牛域名" name="qiniu_domain" :rules="[{ required: false }]" style="display:none">
      <a-input v-model:value="formState.qiniu_domain" suffix="以//开头，结尾不带/" />
      <span class="font-color-by-theme">推荐专门新注册一个七牛账号使用，以免泄密，有10G免费空间和每月10G免费http流量。</span>
    </a-form-item>
    <a-form-item label="七牛ACCESSKEY" name="qiniu_accesskey" :rules="[{ required: false }]" style="display:none">
      <a-input v-model:value="formState.qiniu_accesskey" />
    </a-form-item>
    <a-form-item label="七牛SECRETKEY" name="qiniu_secretkey" :rules="[{ required: false }]" style="display:none">
      <a-input v-model:value="formState.qiniu_secretkey" />
    </a-form-item>
    <a-form-item label="七牛BUCKET" name="qiniu_bucket" :rules="[{ required: false }]" style="display:none">
      <a-input v-model:value="formState.qiniu_bucket" />
    </a-form-item>
    <a-form-item label="个人网站主题" name="theme" :rules="[{ required: false }]" style="display:none">
      <a-radio-group v-model:value="formState.theme" size="large" button-style="solid" class="radio-check">
        <a-radio-button v-for="item in theme_list" :value="item" class="theme_thumbnail"><img :src="'images/'+ item +'.png'" /></a-radio-button>
      </a-radio-group>
</a-form-item>
<a-form-item label="新密码（不修改请留空）" name="pwd" :rules="[{ required:false }]">
      <a-input-password v-model:value="formState.pwd"  />
    </a-form-item>
    <a-form-item label="重复新密码（不修改请留空）" name="pwd_repeat" :rules="[{ required:false }]">
      <a-input-password v-model:value="formState.pwd_repeat"  />
    </a-form-item>
    <a-form-item label="现密码" name="current_pwd" :rules="[{ required:true, message: '现密码不能为空' }]">
      <a-input-password v-model:value="formState.current_pwd" />
    </a-form-item>

    <a-form-item :wrapper-col="{ offset: 6, span: 16}">
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