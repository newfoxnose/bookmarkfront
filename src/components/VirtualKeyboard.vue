<template>
  <div v-if="visible" class="virtual-keyboard-overlay" @click="handleOverlayClick">
    <div class="virtual-keyboard" @click.stop>
      <div class="keyboard-header">
        <span class="keyboard-title">输入私有内容口令</span>
        <button class="close-btn" @click="closeKeyboard">×</button>
      </div>
      
      <div class="keyboard-display">
        <input 
          ref="displayInput"
          v-model="inputValue" 
          type="password" 
          class="display-input"
          readonly
          placeholder="请输入口令"
        />
        <button class="clear-btn" @click="clearInput" v-if="inputValue">清空</button>
      </div>
      
      <div class="keyboard-grid">
        <button 
          v-for="number in numbers" 
          :key="number"
          class="keyboard-key number-key"
          @click="addNumber(number)"
        >
          {{ number }}
        </button>
        <button class="keyboard-key action-key" @click="deleteLast">删除</button>
        <button class="keyboard-key action-key" @click="clearInput">清空</button>
        <button class="keyboard-key action-key confirm-key" @click="confirmInput">确认</button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, watch } from 'vue'

export default defineComponent({
  name: 'VirtualKeyboard',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    modelValue: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue', 'confirm', 'close'],
  setup(props, { emit }) {
    const inputValue = ref(props.modelValue)
    const displayInput = ref(null)
    
    // 数字键盘布局
    const numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0']
    
    // 监听外部值变化
    watch(() => props.modelValue, (newValue) => {
      inputValue.value = newValue
    })
    
    // 添加数字
    const addNumber = (number) => {
      inputValue.value += number
      emit('update:modelValue', inputValue.value)
    }
    
    // 删除最后一个字符
    const deleteLast = () => {
      if (inputValue.value.length > 0) {
        inputValue.value = inputValue.value.slice(0, -1)
        emit('update:modelValue', inputValue.value)
      }
    }
    
    // 清空输入
    const clearInput = () => {
      inputValue.value = ''
      emit('update:modelValue', inputValue.value)
    }
    
    // 确认输入
    const confirmInput = () => {
      emit('confirm', inputValue.value)
      closeKeyboard()
    }
    
    // 关闭键盘
    const closeKeyboard = () => {
      emit('close')
    }
    
    // 点击遮罩层关闭键盘
    const handleOverlayClick = () => {
      closeKeyboard()
    }
    
    return {
      inputValue,
      displayInput,
      numbers,
      addNumber,
      deleteLast,
      clearInput,
      confirmInput,
      closeKeyboard,
      handleOverlayClick
    }
  }
})
</script>

<style scoped>
/* 遮罩层 */
.virtual-keyboard-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* 键盘容器 */
.virtual-keyboard {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  padding: 20px;
  min-width: 320px;
  max-width: 400px;
  width: 90%;
}

/* 键盘头部 */
.keyboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e8e8e8;
}

.keyboard-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #999;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background-color: #f5f5f5;
}

/* 显示区域 */
.keyboard-display {
  margin-bottom: 20px;
  position: relative;
}

.display-input {
  width: 100%;
  height: 50px;
  border: 2px solid #d9d9d9;
  border-radius: 8px;
  padding: 0 15px;
  font-size: 18px;
  text-align: center;
  letter-spacing: 2px;
  background-color: #fafafa;
  outline: none;
  transition: border-color 0.2s;
}

.display-input:focus {
  border-color: #1890ff;
}

.clear-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #1890ff;
  cursor: pointer;
  font-size: 14px;
  padding: 5px 10px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.clear-btn:hover {
  background-color: #e6f7ff;
}

/* 键盘网格 */
.keyboard-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

/* 键盘按键 */
.keyboard-key {
  height: 50px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  background: #fff;
  font-size: 18px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
}

.keyboard-key:hover {
  background-color: #f5f5f5;
  border-color: #40a9ff;
}

.keyboard-key:active {
  transform: scale(0.95);
  background-color: #e6f7ff;
}

/* 数字键样式 */
.number-key {
  color: #333;
}

/* 功能键样式 */
.action-key {
  color: #666;
  font-size: 16px;
}

/* 确认键特殊样式 */
.confirm-key {
  background-color: #1890ff;
  color: #fff;
  border-color: #1890ff;
}

.confirm-key:hover {
  background-color: #40a9ff;
  border-color: #40a9ff;
}

/* 响应式设计 */
@media (max-width: 480px) {
  .virtual-keyboard {
    margin: 20px;
    min-width: auto;
    width: calc(100% - 40px);
  }
  
  .keyboard-key {
    height: 45px;
    font-size: 16px;
  }
  
  .display-input {
    height: 45px;
    font-size: 16px;
  }
}
</style>
