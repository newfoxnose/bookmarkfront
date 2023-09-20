<script>
import { message } from 'ant-design-vue';
export default ({
  mounted() {

    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("teacher_id", $cookies.get('teacher_id'));
    params.append("login", $cookies.get('login'));
    params.append("level", $cookies.get('level'));

    this.$http.post('/ajax/send_email_ajax/', params).then(res => {
      console.log(res.data.data);
      message.info(res.data.msg);
    }).catch(error => {
      // obj.error ? obj.error(error) : null;
      console.log(error);
    });
  }
});
</script>
<template></template>