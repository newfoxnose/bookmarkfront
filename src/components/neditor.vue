<template>
    <div>
        <div :id="this.id"></div>
    </div>
</template>

<script>

import '../../public/neditor/neditor.config.js'
import '../../public/neditor/neditor.all.min.js'
//import '../../public/neditor/i18n/zh-cn/zh-cn.js'
import '../../public/neditor/neditor.parse.min.js'



    export default {
        name: 'editor',
        props: ['id','r_content'],
        data() {
            return {
                ue: '', //ueditor实例
                content: '', //编辑器内容
            }
        },
        methods: {
            //初始化编辑器
            initEditor() {
                this.ue = UE.getEditor(this.id, {
                    initialFrameWidth: '100%',
                    initialFrameHeight: '350',
                    scaleEnabled: true,
                })
                //编辑器准备就绪后会触发该事件
                this.ue.addListener('ready',()=>{
                    //设置可以编辑
                    this.ue.setEnabled()
                    this.ue.setContent(this.r_content)
                })
                //编辑器内容修改时
                this.selectionchange()
            },
            
            //编辑器内容修改时
            selectionchange() {
                this.ue.addListener('selectionchange', () => {
                    this.content = this.ue.getContent()
                })
            },

        },

        activated() {
            //初始化编辑器
            this.initEditor()
        },
        deactivated() {
            //销毁编辑器实例，使用textarea代替
            this.ue.destroy()
            //重置编辑器，可用来做多个tab使用同一个编辑器实例
            //this.ue.reset()
            //如果要使用同一个实例,请注释destroy()方法
        },

        computed:{
            // 利用计算属性返回prop里传过来的内容
            revecive:function(){
                return this.r_content
            }
        },

        watch:{
            // !!! 这里需要注意，需要一个watch来实时更新编辑器的内容
            revecive:function(){
                this.ue.setContent(this.r_content)
            }
        }

    }
</script>

<style>

</style>