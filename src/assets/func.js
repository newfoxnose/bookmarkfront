export default {
  http() {
    alert("http");
  },
  https() {
    showDrawer;
  },
  formatterSizeUnit(size) {
    if (size) {
      var result = parseFloat(size);
      if (result < 1024) {
        return result.toFixed(0) + "B";
      } else if (result < 1024 * 1024) {
        return (result / 1024).toFixed(1) + "KB";
      } else if (result < 1024 * 1024 * 1024) {
        return (result / (1024 * 1024)).toFixed(1) + "MB";
      } else {
        return (result / (1024 * 1024 * 1024)).toFixed(1) + "GB";
      }
    }
  },
  urlsafe_b64encode(string) {
    string = string.replace(/\+/g, "-");
    string = string.replace(/\//g, "_");
    string = string.replace(/\=/g, "");
    return string;
  },
  isValidUrl(url) {
    const regex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
    return regex.test(url);
  },
  timeFormat(timeStamp) {
    timeStamp = String(timeStamp).slice(0, 13);
    timeStamp = Number(timeStamp);
    const obj = timeStamp ? new Date(timeStamp) : new Date();
    var res = {
      y: obj.getFullYear(),
      m: obj.getMonth() + 1,
      d: obj.getDate(),
      h: obj.getHours(),
      i: obj.getMinutes(),
      s: obj.getSeconds(),
    };
    for (var x in res) {
      res[x] = res[x] < 10 ? "0" + res[x] : res[x];
    }
    return (
      res.y +
      "-" +
      res.m +
      "-" +
      res.d +
      " " +
      res.h +
      ":" +
      res.i +
      ":" +
      res.s
    );
  },
  getVarType(val) {
    var type = typeof val;
    // object需要使用Object.prototype.toString.call判断
    if (type === "object") {
      var typeStr = Object.prototype.toString.call(val);
      // 解析[object String]
      typeStr = typeStr.split(" ")[1];
      type = typeStr.substring(0, typeStr.length - 1);
    }
    return type;
  },
  insert_item(arr, folder_path, item,action) {
    if (folder_path.length == 1) {
      if (action=="delete"){
        for (var x=0;x<arr.root_bookmarks.length;x++){
          if (arr.root_bookmarks[x]['id']==item.id){
            arr.root_bookmarks.splice(x,1);
          }
        }
      }
      else if (action=="newfolder"){
        arr.folder.unshift(item);
      }
      else{
        arr.root_bookmarks.unshift(item);
      }
      return arr;
    } else if (folder_path.length == 2) {
      for (var i = 0; i < arr.folder.length; i++) {
        if (arr.folder[i]["id"] == folder_path[1]) {
          if (action=="delete"){
            for (var x=0;x<arr.folder[i]["bookmarks"].length;x++){
              if (arr.folder[i]["bookmarks"][x]['id']==item.id){
                arr.folder[i]["bookmarks"].splice(x,1);
              }
            }
          }
          else if (action=="newfolder"){
            arr.folder[i]['subfolder'].unshift(item);
          }
          else{
            arr.folder[i]["bookmarks"].unshift(item);
          }
          return arr;
        }
      }
    } else if (folder_path.length == 3) {
      for (var i = 0; i < arr.folder.length; i++) {
        if (arr.folder[i]["id"] == folder_path[1]) {
          for (var j = 0; j < arr.folder[i]['subfolder'].length; j++){
            if (arr.folder[i]['subfolder'][j]["id"] == folder_path[2]) {
              if (action=="delete"){
                for (var x=0;x<arr.folder[i]['subfolder'][j]["bookmarks"].length;x++){
                  if (arr.folder[i]['subfolder'][j]["bookmarks"][x]['id']==item.id){
                    arr.folder[i]['subfolder'][j]["bookmarks"].splice(x,1);
                  }
                }
              }
              else if (action=="newfolder"){
                arr.folder[i]['subfolder'][j]['subfolder'].unshift(item);
              }
              else{
                arr.folder[i]['subfolder'][j]["bookmarks"].unshift(item);
              }
              return arr;
            }
          }
        }
      }
    } else if (folder_path.length == 4) {
      for (var i = 0; i < arr.folder.length; i++) {
        if (arr.folder[i]["id"] == folder_path[1]) {
          for (var j = 0; j < arr.folder[i]['subfolder'].length; j++){
            if (arr.folder[i]['subfolder'][j]["id"] == folder_path[2]) {
              for (var k = 0; k < arr.folder[i]['subfolder'][j]['subfolder'].length; k++){
                if (arr.folder[i]['subfolder'][j]['subfolder'][k]["id"] == folder_path[3]) {
                  if (action=="delete"){
                    for (var x=0;x<arr.folder[i]['subfolder'][j]['subfolder'][k]["bookmarks"].length;x++){
                      if (arr.folder[i]['subfolder'][j]['subfolder'][k]["bookmarks"][x]['id']==item.id){
                        arr.folder[i]['subfolder'][j]['subfolder'][k]["bookmarks"].splice(x,1);
                      }
                    }
                  }
                  else if (action=="newfolder"){
                    arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'].unshift(item);
                  }
                  else{
                    arr.folder[i]['subfolder'][j]['subfolder'][k]["bookmarks"].unshift(item);
                  }
                  return arr;
                }
              }
            }
          }
        }
      }
    } else if (folder_path.length == 5) {
      for (var i = 0; i < arr.folder.length; i++) {
        if (arr.folder[i]["id"] == folder_path[1]) {
          for (var j = 0; j < arr.folder[i]['subfolder'].length; j++){
            if (arr.folder[i]['subfolder'][j]["id"] == folder_path[2]) {
              for (var k = 0; k < arr.folder[i]['subfolder'][j]['subfolder'].length; k++){
                if (arr.folder[i]['subfolder'][j]['subfolder'][k]["id"] == folder_path[3]) {
                  for (var l = 0; l < arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'].length; l++){
                    if (arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]["id"] == folder_path[4]) {
                      if (action=="delete"){
                        for (var x=0;x<arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]["bookmarks"].length;x++){
                          if (arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]["bookmarks"][x]['id']==item.id){
                            arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]["bookmarks"].splice(x,1);
                          }
                        }
                      }
                      else if (action=="newfolder"){
                        arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'].unshift(item);
                      }
                      else{
                        arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]["bookmarks"].unshift(item);
                      }
                      return arr;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }else if (folder_path.length == 6) {
      for (var i = 0; i < arr.folder.length; i++) {
        if (arr.folder[i]["id"] == folder_path[1]) {
          for (var j = 0; j < arr.folder[i]['subfolder'].length; j++){
            if (arr.folder[i]['subfolder'][j]["id"] == folder_path[2]) {
              for (var k = 0; k < arr.folder[i]['subfolder'][j]['subfolder'].length; k++){
                if (arr.folder[i]['subfolder'][j]['subfolder'][k]["id"] == folder_path[3]) {
                  for (var l = 0; l < arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'].length; l++){
                    if (arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]["id"] == folder_path[4]) {
                      for (var m = 0; m < arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'].length; m++){
                        if (arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'][m]["id"] == folder_path[5]) {
                          if (action=="delete"){
                            for (var x=0;x<arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'][m]["bookmarks"].length;x++){
                              if (arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'][m]["bookmarks"][x]['id']==item.id){
                                arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'][m]["bookmarks"].splice(x,1);
                              }
                            }
                          }
                          else if (action=="newfolder"){    //到了这里就不应该允许新建目录了
                            arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'][m]['subfolder'].unshift(item);
                          }
                          else{
                            arr.folder[i]['subfolder'][j]['subfolder'][k]['subfolder'][l]['subfolder'][m]["bookmarks"].unshift(item);
                          }
                          return arr;
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }else {
      //根目录-一级-二级-三级-四级-五级-到第六级了，不想写了
    }
  },
};
