<template>
  <!-- 页面锁定遮罩：显示壁纸，点击任意位置可唤起解锁 -->
  <div
    v-if="locked"
    class="page-lock-overlay"
    :class="themeClass"
    @click="handleOverlayClick"
  >
    <!-- 壁纸层：使用渐变背景模拟壁纸效果 -->
    <div class="wallpaper-layer"></div>
    <!-- 解锁提示 -->
    <div class="unlock-hint">
      <p>页面已锁定</p>
      <p class="hint-sub">点击任意位置输入私有口令解锁</p>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

/**
 * 页面锁定遮罩组件
 * - 锁定后全屏显示壁纸样式
 * - 点击唤起解锁（由父组件控制 VirtualKeyboard 显示）
 */
export default defineComponent({
  name: 'PageLockOverlay',
  props: {
    /** 是否处于锁定状态 */
    locked: {
      type: Boolean,
      default: false,
    },
    /** 主题类名，用于适配深色/浅色壁纸 */
    themeClass: {
      type: String,
      default: '',
    },
  },
  emits: ['unlock'],
  setup(props, { emit }) {
    const handleOverlayClick = () => {
      emit('unlock');
    };
    return { handleOverlayClick };
  },
});
</script>

<style scoped>
.page-lock-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 9998;
  cursor: pointer;
}

/* 壁纸层：使用渐变营造壁纸效果 */
.wallpaper-layer {
  position: absolute;
  inset: 0;
  /* 浅色主题壁纸：柔和蓝紫渐变 */
  background: linear-gradient(
    135deg,
    #667eea 0%,
    #764ba2 25%,
    #f093fb 50%,
    #4facfe 75%,
    #00f2fe 100%
  );
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
}

/* 深色主题壁纸 */
.page-lock-overlay.content-dark-theme .wallpaper-layer {
  background: linear-gradient(
    135deg,
    #0f0c29 0%,
    #302b63 25%,
    #24243e 50%,
    #2c3e50 75%,
    #3498db 100%
  );
}

@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* 解锁提示文字 */
.unlock-hint {
  position: absolute;
  bottom: 15%;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  color: rgba(255, 255, 255, 0.9);
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.unlock-hint p {
  margin: 0;
  font-size: 20px;
  font-weight: 500;
}

.hint-sub {
  margin-top: 8px !important;
  font-size: 14px !important;
  opacity: 0.85;
}
</style>
