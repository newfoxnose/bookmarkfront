export default {
  http() {
    alert("http")
  },
  https() {
    showDrawer
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
}
}