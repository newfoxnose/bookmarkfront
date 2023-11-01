<template>
  <h3 style="margin-top:15px;">导入书签</h3>
  <div class="clearfix">
    <a-upload :file-list="fileList" :before-upload="beforeUpload" :customRequest="noUse" :disabled="fileList.length >= 1"  @remove="handleRemove">
      <a-button>
        <upload-outlined></upload-outlined>
        选择书签文件
      </a-button>
    </a-upload>
    <p>{{ selected_filename }}</p>
    <a-button type="primary" :disabled="fileList.length !== 0" :loading="uploading" style="margin-top: 16px"
      @click="Upload">
      {{ uploading ? 'Uploading' : '上传' }}
    </a-button>
  </div>
  <div id="bm" style="display:none"></div>
</template>
<script>
import { UploadOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { defineComponent, ref } from 'vue';
export default defineComponent({
  components: {
    UploadOutlined,
  },
  setup() {
    const fileList = ref([]);
    const uploading = ref(false);
    const handleRemove = file => {
      const index = fileList.value.indexOf(file);
      const newFileList = fileList.value.slice();
      newFileList.splice(index, 1);
      fileList.value = newFileList;
    };
    return {
      fileList,
      uploading,
      handleRemove,
    };
  },
  data() {
    return {
      json_string: [],
      selected_filename:''
    }
  },
  methods: {
    beforeUpload(file) {
      this.json_string = convertbookmark(file);
      this.selected_filename=file.name;
    },
    noUse() {
      //这个函数没什么用，只是为了避免自动调用默认上传接口
    },
    Upload() {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      for (var i = 0; i < this.json_string.length; i++) {
        params.append("json_string[]", this.json_string[i]);
      }
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      const { data: res } = this.$http.post('/ajax/import_ajax', params)
        .then(res => {
          console.log(res.data);
          // obj.success ? obj.success(res) : null
          message.info(res.data.msg);
          if (res.data.data != null) {
            window.location.href = "/user"
          }
        })
        .catch(error => {
          // obj.error ? obj.error(error) : null;
          console.log(error);
        })
    },
  }
});



//要使用jquery，必须修改vite.config.js并把下面两句加上
import jQuery from "jquery";
Object.assign(window, { $: jQuery, jQuery });
//jquery结束



function convertbookmark(file) {
  var reader = new FileReader();
  var return_arr = [];
  reader.readAsText(file);
  reader.onload = () => {
    $("#bm").html(reader.result)
    var dl = $("#bm dl").first();
    console.log("dt长度为：" + dl.children("dt").length);
    //var dt = dl.children("dt").eq(0);
    //var obj = foo(dt);
    var arr1 = [];
    var obj1 = {};
    for (var i = 0; i < dl.children("dt").length; i++) {
      // 遍历下一级dt标签
      var tmp1 = foo(dl.children("dt").eq(i));
      // 将返回的对象push至子文件数组
      arr1.push(tmp1);
    }
    // 创建文件夹与子文件数组的键值对
    obj1["根"] = arr1;
    // 将对象转化为json字符串，添加额外参数使json格式更易阅读
    var s = JSON.stringify(obj1, null, 4);

    var iCount;
    var iMaxChars = 20000;
    //$("#textarea_div").empty();
    iCount = parseInt(s.length / iMaxChars) + 1;

    for (var i = 1; i <= iCount; i++) {
      return_arr[i - 1] = s.substring((i - 1) * iMaxChars, i * iMaxChars);
      //$("#textarea_div").append("<textarea class='hide' name='json_string[]'>" + s.substring((i - 1) * iMaxChars, i * iMaxChars) + "</textarea>");
    }

    // 将json字符串写入json文件
    //fs.writeFileSync('output.json', s);
    function foo(dt) {
      // h3标签为文件夹名称
      var h3 = dt.children("h3");
      if (h3.length == 0) {
        // a标签为网址
        var a = dt.children("a");
        // 返回该书签的名称和网址组成的对象
        return a.length > 0 ? { "name": a.text(), "href": a.attr('href'), "icon_uri": a.attr('icon_uri'), "tag": a.attr('tag'), "private": a.attr('private'), "icon": a.attr('icon') } : null;
      }
      var h3_text = h3.text();
      var arr = [];
      var obj = {};
      // 获取下一级dt标签集合
      var dl = dt.children("dl");
      var dtArr = dl.children("dt");
      for (var i = 0; i < dtArr.length; i++) {
        // 遍历下一级dt标签
        var tmp = foo(dtArr.eq(i));
        // 将返回的对象push至子文件数组
        arr.push(tmp);
      }
      // 创建文件夹与子文件数组的键值对
      obj[h3_text] = arr;
      // 返回该对象
      return obj;
    }
  }
  return return_arr;
}

</script>
