<template>
  <a-form :model="formState" name="basic" :label-col="{ span: 8 }" :wrapper-col="{ span: 16 }" autocomplete="off"
    @finish="getList" @finishFailed="onFinishFailed">

    <a-form-item label="邮箱" name="email" :rules="[{ type: 'email', required: true, message: '请输入有效邮箱地址' }]">
      <a-input v-model:value="formState.email" />
    </a-form-item>

    <a-form-item label="密码" name="password" :rules="[{ required: true, message: '请输入密码' }]">
      <a-input-password v-model:value="formState.password" />
    </a-form-item>

    <a-form-item name="记住" :wrapper-col="{ offset: 8, span: 16 }">
      <a-checkbox v-model:checked="formState.remember">记住我</a-checkbox>
    </a-form-item>

    <a-form-item :wrapper-col="{ offset: 8, span: 16 }">
      <a-button type="primary" html-type="submit">登入</a-button>
    </a-form-item>
  </a-form>
</template>
<script>
import { defineComponent, reactive } from 'vue';
if ($cookies.get('login')=="yes"){
  window.location.href ="/user"
}
export default defineComponent({
  setup() {
    const formState = reactive({     //熟悉下这里的数据写法
      email: '',
      password: '',
      remember: true,
    });
    const onFinish = values => {
      //getList(values)
      console.log('Success:', values);
    };
    const onFinishFailed = errorInfo => {
      console.log('Failed:', errorInfo);
    };
    return {
      formState,
      onFinish,
      onFinishFailed,
    };
  },
  methods: {
    getList(values) {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("email", values.email);
      params.append("password", values.password);
      const { data: res } = this.$http.post('/index/login', params)
        .then(res => {
          // obj.success ? obj.success(res) : null
          if (res.data.msg == "登入成功") {
            $cookies.set('teacher_id',res.data.data.teacher_id,"720h")  
            $cookies.set('login',res.data.data.login,"720h")  
            $cookies.set('level',res.data.data.level,"720h")  
            window.location.href ="/user"
          }
          console.log(res.data);
        })
        .catch(error => {
          // obj.error ? obj.error(error) : null;
          console.log(error);
        })
    },
  },

});
</script>