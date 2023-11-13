<script>
import { message } from 'ant-design-vue';
export default ({
  mounted() {

    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());

    const { data: res } = this.$http.post('/ajax/export_ajax', params)
      .then(res => {
        let blob = new Blob([res.data]);
        let url = window.URL.createObjectURL(blob); // 创建 url 并指向 blob
        let a = document.createElement('a');
        a.href = url;
        var date = new Date();
        a.download = '导出书签' + date.toLocaleDateString() + '.html';
        a.click();
        window.URL.revokeObjectURL(url); // 释放该 url
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
  <a-result title="导出到本地" sub-title="请注意查看下载列表内容">
  </a-result>
</template>