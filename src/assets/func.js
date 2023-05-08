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
};
