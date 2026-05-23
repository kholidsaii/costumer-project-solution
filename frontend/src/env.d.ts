// src/env.d.ts (atau src/vite-env.d.ts)
declare module '*.css';
declare module '*.vue' {
  import type { DefineComponent } from 'vue';
  const component: DefineComponent<{}, {}, any>;
  export default component;
}