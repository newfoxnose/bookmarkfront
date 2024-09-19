<script>
import { message } from 'ant-design-vue';
export default ({
  setup(){
    $cookies.set('selectedkey','13',"720h") 
    $cookies.set('openkey','sub1',"720h") 
  },
  mounted() {

    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
    this.$http.post('/ajax/send_email_ajax/', params).then(res => {
      console.log(res.data.data);
      if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/";
    }
      message.info(res.data.msg);
    }).catch(error => {
      // obj.error ? obj.error(error) : null;
      console.log(error);
    });
  }
});
</script>
<template>
  <a-result title="发送至邮箱" sub-title="请在提示成功5分钟后到邮箱查看，也有可能在垃圾邮件里">
  </a-result>
</template>