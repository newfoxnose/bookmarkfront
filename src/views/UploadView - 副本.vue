<template>
  <h2>导入书签</h2>
  <div style="height:200px;">
    <form action="http://bookmark.com/ajax/import_ajax" enctype="multipart/form-data" method="post" accept-charset="utf-8">
      <input type="file" name="file" size="20" id="fileInput" />
      <input type="hidden" name="teacher_id" :value="$cookies.get('teacher_id')">
      <input type="hidden" name="login" :value="$cookies.get('login')">
      <input type="hidden" name="level" :value="$cookies.get('level')">
      <input type="hidden" name="onlywork" value="" id="onlywork">
      <div class="hide" id="textarea_div"></div>
      <button type="submit" name="submit" value="submit" class="btn btn-default">提交</button>
      （文件大时速度会比较慢，请耐心等待）
    </form>
  </div>
  <div id="bm" style="display:none"></div>
</template>
<script>
import { message } from 'ant-design-vue';
export default ({
  data() {
    return {
      teacher_id: $cookies.get('teacher_id'),
      father_id: '',
      folder_id: '',
      folder_data: [{
        key: '',
        name: '',
        lv: '',
        value: '',
        disabled: false,
      }],
      data: [],
      columns: [{
        title: 'lv',
        dataIndex: 'lv'
      }, {
        title: '目录名称',
        dataIndex: 'name'
      }, {
        title: '书签数量',
        dataIndex: 'amount'
      }, {
        title: '操作'
      }],
      columns_addnew: [{
        title: '添加目录'
      }, {
        title: '目录名称'
      }, {
        title: '父目录',
      }, {
        title: '操作'
      }],
      data_addnew: [{}],   //这里至少要有一条空记录
      new_folder_name: '',
      new_father_id: '根目录',
    }
  },
});
//console.log($)

//要用jquery，必须修改vite.config.js并把下面两句加上
import jQuery from "jquery";
Object.assign(window, { $: jQuery, jQuery });
//jquery结束


$(document).ready(function () {
  document.getElementById("fileInput")
    .addEventListener("change", function selectedFileChanged() {
      if (this.files.length === 0) {
        alert("请选择文件！");
        return;
      }
      var reader = new FileReader();
      reader.onload = function fileReadCompleted() {
        $("#bm").html(reader.result)
        var dl = $("#bm dl").first();
        var onlywork = $("meta[name=onlywork]");
        if (onlywork.length == 1) {
          $("#onlywork").val("onlywork");
        }
        console.log(onlywork.length);
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
        $("#textarea_div").empty();
        iCount = parseInt(s.length / iMaxChars) + 1;
        for (var i = 1; i <= iCount; i++) {
          $("#textarea_div").append("<textarea class='hide' name='json_string[]'>" + s.substring((i - 1) * iMaxChars, i * iMaxChars) + "</textarea>");
        }

        console.log(s);
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
      };
      reader.readAsText(this.files[0]);      //这句必须有
    });
});

</script>
