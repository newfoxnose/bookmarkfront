<template>
  <!-- 页面锁定遮罩：使用 Teleport 挂载到 body，避免父级布局影响 -->
  <Teleport to="body">
    <div
      v-if="locked"
      class="page-lock-overlay"
      :class="themeClass"
      @click="handleOverlayClick"
    >
    <!-- 壁纸层：使用渐变背景模拟壁纸效果 -->
    <div class="wallpaper-layer"></div>
    <!-- 中央内容区：指针表盘 + 日期时间 -->
    <div class="center-content">
      <!-- 指针表盘：玻璃拟态 + 动效 -->
      <div class="clock-face">
        <svg viewBox="0 0 200 200" class="clock-svg">
          <defs>
            <!-- 表盘渐变描边 -->
            <linearGradient id="clockBorder" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color: rgba(255,255,255,0.6)" />
              <stop offset="100%" style="stop-color: rgba(255,255,255,0.2)" />
            </linearGradient>
            <!-- 表盘内发光 -->
            <filter id="clockGlow">
              <feGaussianBlur stdDeviation="2" result="blur" />
              <feMerge>
                <feMergeNode in="blur" />
                <feMergeNode in="SourceGraphic" />
              </feMerge>
            </filter>
          </defs>
          <!-- 外圈光晕 -->
          <circle cx="100" cy="100" r="96" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="4" />
          <!-- 主表盘圈 -->
          <circle cx="100" cy="100" r="92" fill="rgba(255,255,255,0.08)" stroke="url(#clockBorder)" stroke-width="2" filter="url(#clockGlow)" />
          <!-- 12 个刻度：整点加粗 -->
          <g v-for="i in 12" :key="i">
            <line
              :class="{ 'tick-major': i % 3 === 0 }"
              :x1="100 + 78 * Math.cos((i * 30 - 90) * Math.PI / 180)"
              :y1="100 + 78 * Math.sin((i * 30 - 90) * Math.PI / 180)"
              :x2="100 + 90 * Math.cos((i * 30 - 90) * Math.PI / 180)"
              :y2="100 + 90 * Math.sin((i * 30 - 90) * Math.PI / 180)"
            />
          </g>
          <!-- 时针：带过渡 -->
          <g class="hand hour-hand" :style="{ transform: `rotate(${hourDeg}deg)` }">
            <line x1="100" y1="100" x2="100" y2="62" stroke="#fff" stroke-width="5" stroke-linecap="round" />
          </g>
          <!-- 分针：带过渡 -->
          <g class="hand minute-hand" :style="{ transform: `rotate(${minuteDeg}deg)` }">
            <line x1="100" y1="100" x2="100" y2="42" stroke="#fff" stroke-width="3" stroke-linecap="round" />
          </g>
          <!-- 秒针：带过渡，红色 -->
          <g class="hand second-hand" :style="{ transform: `rotate(${secondDeg}deg)` }">
            <line x1="100" y1="100" x2="100" y2="28" stroke="#ff6b6b" stroke-width="2" stroke-linecap="round" />
          </g>
          <!-- 中心圆点 -->
          <circle cx="100" cy="100" r="8" fill="#fff" class="center-dot" />
        </svg>
      </div>
      <!-- 日期时间 -->
      <div class="datetime-display">
        <p class="date-text">{{ displayDate }}</p>
        <p class="time-text">{{ displayTime }}</p>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script>
import { defineComponent, ref, onMounted, onUnmounted } from 'vue';

/**
 * 页面锁定遮罩组件
 * - 锁定后全屏显示壁纸样式，展示实时日期时间
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
    const displayDate = ref('');
    const displayTime = ref('');
    const hourDeg = ref(0);
    const minuteDeg = ref(0);
    const secondDeg = ref(0);
    /** 挂载时的初始秒数（含毫秒），用于累积角度，避免秒针过 12 时回跳闪烁 */
    let mountSeconds = 0;

    /** 格式化并更新日期时间及表盘指针角度 */
    const updateDateTime = () => {
      const now = new Date();
      displayDate.value = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日`;
      displayTime.value = now.toLocaleTimeString('zh-CN', {
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      });
      // 指针角度：0 度为 12 点方向，顺时针
      minuteDeg.value = (now.getMinutes() / 60) * 360 + (now.getSeconds() / 60) * 6;
      hourDeg.value = ((now.getHours() % 12) / 12) * 360 + (now.getMinutes() / 60) * 30;
    };

    let timer = null;
    let smoothTimer = null;
    onMounted(() => {
      const now = new Date();
      mountSeconds = now.getSeconds() + now.getMilliseconds() / 1000;
      updateDateTime();
      timer = setInterval(updateDateTime, 1000);
      // 每 50ms 更新秒针：使用累积秒数，角度单调递增，避免过 12 时回跳
      smoothTimer = setInterval(() => {
        const elapsed = (Date.now() - (performance.timing?.navigationStart ?? Date.now())) / 1000;
        const totalSeconds = mountSeconds + (Date.now() / 1000 - Math.floor(Date.now() / 60000) * 60 - (now.getMinutes() * 60 + now.getSeconds() + now.getMilliseconds() / 1000));
        // 简化：用挂载时刻 + 经过的毫秒数
        const elapsedMs = Date.now() - (window.__clockMountTime ?? Date.now());
        if (!window.__clockMountTime) window.__clockMountTime = Date.now();
        secondDeg.value = (mountSeconds + elapsedMs / 1000) * 6;
      }, 50);
    });
    onUnmounted(() => {
      if (timer) clearInterval(timer);
      if (smoothTimer) clearInterval(smoothTimer);
    });

    const handleOverlayClick = () => {
      emit('unlock');
    };
    return { handleOverlayClick, displayDate, displayTime, hourDeg, minuteDeg, secondDeg };
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

/* 中央内容区：表盘 + 日期时间，置于壁纸之上，入场动画 */
.center-content {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 32px;
  z-index: 2;
  pointer-events: none;
  animation: content-fade-in 0.6s ease-out forwards;
}

@keyframes content-fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.center-content * {
  pointer-events: auto;
}

/* 指针表盘：玻璃拟态 + 入场动效 */
.clock-face {
  width: 220px;
  height: 220px;
  flex-shrink: 0;
  padding: 10px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 50%;
  backdrop-filter: blur(12px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), inset 0 0 0 1px rgba(255, 255, 255, 0.1);
  animation: clock-scale-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.clock-svg {
  width: 100%;
  height: 100%;
  display: block;
}

/* 刻度线：普通与整点区分 */
.clock-svg line {
  stroke: rgba(255, 255, 255, 0.5);
  stroke-width: 2;
  transition: stroke 0.2s;
}

.clock-svg line.tick-major {
  stroke: rgba(255, 255, 255, 0.85);
  stroke-width: 3;
}

/* 指针：以表盘中心为旋转原点，带平滑过渡 */
.clock-svg .hand {
  transform-origin: 100px 100px;
  transition: transform 0.1s cubic-bezier(0.4, 0, 0.2, 1);
}

.clock-svg .second-hand {
  transition: transform 0.05s linear;
}

.clock-svg .center-dot {
  filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.5));
}

@keyframes clock-scale-in {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* 壁纸层：使用渐变营造壁纸效果 */
.wallpaper-layer {
  position: absolute;
  inset: 0;
  /* ?????????????? */
  background: linear-gradient(
    135deg,
    #3b82f6 0%,
    #0ea5e9 33%,
    #06b6d4 66%,
    #22d3ee 100%
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

/* 日期时间：在表盘下方，无底色，低对比度 */
.datetime-display {
  text-align: center;
  color: rgba(255, 255, 255, 0.75);
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
  padding: 24px 48px;
}

.datetime-display .date-text {
  margin: 0;
  font-size: 16px;
  font-weight: 300;
  letter-spacing: 1px;
}

.datetime-display .time-text {
  margin: 10px 0 0 0;
  font-size: 40px;
  font-weight: 300;
  letter-spacing: 4px;
  font-variant-numeric: tabular-nums;
}
</style>
