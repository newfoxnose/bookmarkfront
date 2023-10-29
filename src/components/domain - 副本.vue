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
    params.append("teacher_id", $cookies.get('teacher_id'));
    params.append("level", $cookies.get('level'));
    const { data: res } = this.$http.post('/ajax/note_ajax', params)
      .then(res => {
        // obj.success ? obj.success(res) : null
        if (res.data.msg == "请求成功") {
          console.log(res.data.data.domain);
          //window.location.href = this.$remoteDomain+"/u"+res.data.data.cookie_teacher_id;
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