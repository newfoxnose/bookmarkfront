import { fileURLToPath, URL } from "node:url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import inject from "@rollup/plugin-inject";

// Plugins，vuetify用的插件，unplugin-vue-components用来自动引入组件，unplugin-fonts用来自动引入字体文件
import Components from 'unplugin-vue-components/vite'
import Vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import ViteFonts from 'unplugin-fonts/vite'


// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    inject({
      $: "jquery",
      jQuery: 'jquery',
    }),
    vue({
      template: { transformAssetUrls }   // 处理vuetify的路径
    }),
    Vuetify(),
    Components(),
    ViteFonts({
      google: {
        families: [{
          name: 'Roboto',
          styles: 'wght@100;300;400;500;700;900',
        }],
      },
    }),
  ],
  define: { 'process.env': {} },  //vuetify用到的
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
    extensions: [
      '.js',
      '.json',
      '.jsx',
      '.mjs',
      '.ts',
      '.tsx',
      '.vue',
    ],
  },
  build: {
    rollupOptions: {
      //external: ['scrollReveal']
    }
  },
});