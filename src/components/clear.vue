<script>
import { message } from 'ant-design-vue';
export default ({
  mounted() {

    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("token", $cookies.get('token'));
    params.append("timestamp",new Date().getTime());

    const { data: res } = this.$http.post('/ajax/qiniu_clear_ajax', params)
      .then(res => {
        console.log(res.data);
        // obj.success ? obj.success(res) : null
        message.info("导出成功");
      })
      .catch(error => {
        // obj.error ? obj.error(error) : null;
        console.log(error);
      })
  }
});
</script>
<template>
  <a-result title="清理七牛云上的无用图片" sub-title="请耐心等待">
  </a-result>
</template>