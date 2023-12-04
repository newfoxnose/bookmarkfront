<template>
  <a-result title="重定向中......" sub-title="请稍等.......">
  </a-result>
</template>
<script>
import { message } from 'ant-design-vue';
import { defineComponent } from 'vue';

export default defineComponent({
  mounted() {
    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
    const { data: res } = this.$http.post('/ajax/domain_ajax', params)
      .then(res => {
        if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/login";
    }
        // obj.success ? obj.success(res) : null
        if (res.data.msg == "请求成功") {
          console.log(res.data.return_domain);
          window.location.href =res.data.return_domain;
        }
        else {
          message.info("请求失败");
        }
        //console.log(res.data);
      })
      .catch(error => {
        // obj.error ? obj.error(error) : null;
        console.log(error);
      })

  }
});
</script>