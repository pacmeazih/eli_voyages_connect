// vite.config.js
import { defineConfig } from "file:///C:/Users/pacme/OneDrive/Bureau/ELI-VOYAGES%20SARL%20U/Clients%20platform/code%20src%20eli-voyages-connect/eli_voyages_connect/node_modules/vite/dist/node/index.js";
import laravel from "file:///C:/Users/pacme/OneDrive/Bureau/ELI-VOYAGES%20SARL%20U/Clients%20platform/code%20src%20eli-voyages-connect/eli_voyages_connect/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///C:/Users/pacme/OneDrive/Bureau/ELI-VOYAGES%20SARL%20U/Clients%20platform/code%20src%20eli-voyages-connect/eli_voyages_connect/node_modules/@vitejs/plugin-vue/dist/index.mjs";
var vite_config_default = defineConfig(({ command, mode }) => ({
  plugins: [
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true
    })
  ],
  resolve: {
    alias: {
      "@": "/resources/js"
    }
  },
  server: {
    host: "127.0.0.1",
    port: 5173,
    hmr: { host: "localhost" }
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ["vue", "@inertiajs/vue3", "@headlessui/vue", "@heroicons/vue"],
          utils: ["axios", "ziggy-js"]
        }
      }
    },
    chunkSizeWarningLimit: 1e3,
    sourcemap: mode !== "production"
  },
  define: {
    __APP_VERSION__: JSON.stringify(process.env.npm_package_version || "1.0.0")
  }
}));
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJjOlxcXFxVc2Vyc1xcXFxwYWNtZVxcXFxPbmVEcml2ZVxcXFxCdXJlYXVcXFxcRUxJLVZPWUFHRVMgU0FSTCBVXFxcXENsaWVudHMgcGxhdGZvcm1cXFxcY29kZSBzcmMgZWxpLXZveWFnZXMtY29ubmVjdFxcXFxlbGlfdm95YWdlc19jb25uZWN0XCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJjOlxcXFxVc2Vyc1xcXFxwYWNtZVxcXFxPbmVEcml2ZVxcXFxCdXJlYXVcXFxcRUxJLVZPWUFHRVMgU0FSTCBVXFxcXENsaWVudHMgcGxhdGZvcm1cXFxcY29kZSBzcmMgZWxpLXZveWFnZXMtY29ubmVjdFxcXFxlbGlfdm95YWdlc19jb25uZWN0XFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9jOi9Vc2Vycy9wYWNtZS9PbmVEcml2ZS9CdXJlYXUvRUxJLVZPWUFHRVMlMjBTQVJMJTIwVS9DbGllbnRzJTIwcGxhdGZvcm0vY29kZSUyMHNyYyUyMGVsaS12b3lhZ2VzLWNvbm5lY3QvZWxpX3ZveWFnZXNfY29ubmVjdC92aXRlLmNvbmZpZy5qc1wiO2ltcG9ydCB7IGRlZmluZUNvbmZpZyB9IGZyb20gJ3ZpdGUnO1xuaW1wb3J0IGxhcmF2ZWwgZnJvbSAnbGFyYXZlbC12aXRlLXBsdWdpbic7XG5pbXBvcnQgdnVlIGZyb20gJ0B2aXRlanMvcGx1Z2luLXZ1ZSc7XG5cbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZygoeyBjb21tYW5kLCBtb2RlIH0pID0+ICh7XG4gIHBsdWdpbnM6IFtcbiAgICB2dWUoe1xuICAgICAgdGVtcGxhdGU6IHtcbiAgICAgICAgdHJhbnNmb3JtQXNzZXRVcmxzOiB7XG4gICAgICAgICAgYmFzZTogbnVsbCxcbiAgICAgICAgICBpbmNsdWRlQWJzb2x1dGU6IGZhbHNlLFxuICAgICAgICB9LFxuICAgICAgfSxcbiAgICB9KSxcbiAgICBsYXJhdmVsKHtcbiAgICAgIGlucHV0OiBbJ3Jlc291cmNlcy9jc3MvYXBwLmNzcycsICdyZXNvdXJjZXMvanMvYXBwLmpzJ10sXG4gICAgICByZWZyZXNoOiB0cnVlLFxuICAgIH0pLFxuICBdLFxuICByZXNvbHZlOiB7XG4gICAgYWxpYXM6IHtcbiAgICAgICdAJzogJy9yZXNvdXJjZXMvanMnLFxuICAgIH0sXG4gIH0sXG4gIHNlcnZlcjoge1xuICAgIGhvc3Q6ICcxMjcuMC4wLjEnLFxuICAgIHBvcnQ6IDUxNzMsXG4gICAgaG1yOiB7IGhvc3Q6ICdsb2NhbGhvc3QnIH0sXG4gIH0sXG4gIGJ1aWxkOiB7XG4gICAgcm9sbHVwT3B0aW9uczoge1xuICAgICAgb3V0cHV0OiB7XG4gICAgICAgIG1hbnVhbENodW5rczoge1xuICAgICAgICAgIHZlbmRvcjogWyd2dWUnLCAnQGluZXJ0aWFqcy92dWUzJywgJ0BoZWFkbGVzc3VpL3Z1ZScsICdAaGVyb2ljb25zL3Z1ZSddLFxuICAgICAgICAgIHV0aWxzOiBbJ2F4aW9zJywgJ3ppZ2d5LWpzJ10sXG4gICAgICAgIH0sXG4gICAgICB9LFxuICAgIH0sXG4gICAgY2h1bmtTaXplV2FybmluZ0xpbWl0OiAxMDAwLFxuICAgIHNvdXJjZW1hcDogbW9kZSAhPT0gJ3Byb2R1Y3Rpb24nLFxuICB9LFxuICBkZWZpbmU6IHtcbiAgICBfX0FQUF9WRVJTSU9OX186IEpTT04uc3RyaW5naWZ5KHByb2Nlc3MuZW52Lm5wbV9wYWNrYWdlX3ZlcnNpb24gfHwgJzEuMC4wJyksXG4gIH0sXG59KSk7XG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQXVpQixTQUFTLG9CQUFvQjtBQUNwa0IsT0FBTyxhQUFhO0FBQ3BCLE9BQU8sU0FBUztBQUVoQixJQUFPLHNCQUFRLGFBQWEsQ0FBQyxFQUFFLFNBQVMsS0FBSyxPQUFPO0FBQUEsRUFDbEQsU0FBUztBQUFBLElBQ1AsSUFBSTtBQUFBLE1BQ0YsVUFBVTtBQUFBLFFBQ1Isb0JBQW9CO0FBQUEsVUFDbEIsTUFBTTtBQUFBLFVBQ04saUJBQWlCO0FBQUEsUUFDbkI7QUFBQSxNQUNGO0FBQUEsSUFDRixDQUFDO0FBQUEsSUFDRCxRQUFRO0FBQUEsTUFDTixPQUFPLENBQUMseUJBQXlCLHFCQUFxQjtBQUFBLE1BQ3RELFNBQVM7QUFBQSxJQUNYLENBQUM7QUFBQSxFQUNIO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDUCxPQUFPO0FBQUEsTUFDTCxLQUFLO0FBQUEsSUFDUDtBQUFBLEVBQ0Y7QUFBQSxFQUNBLFFBQVE7QUFBQSxJQUNOLE1BQU07QUFBQSxJQUNOLE1BQU07QUFBQSxJQUNOLEtBQUssRUFBRSxNQUFNLFlBQVk7QUFBQSxFQUMzQjtBQUFBLEVBQ0EsT0FBTztBQUFBLElBQ0wsZUFBZTtBQUFBLE1BQ2IsUUFBUTtBQUFBLFFBQ04sY0FBYztBQUFBLFVBQ1osUUFBUSxDQUFDLE9BQU8sbUJBQW1CLG1CQUFtQixnQkFBZ0I7QUFBQSxVQUN0RSxPQUFPLENBQUMsU0FBUyxVQUFVO0FBQUEsUUFDN0I7QUFBQSxNQUNGO0FBQUEsSUFDRjtBQUFBLElBQ0EsdUJBQXVCO0FBQUEsSUFDdkIsV0FBVyxTQUFTO0FBQUEsRUFDdEI7QUFBQSxFQUNBLFFBQVE7QUFBQSxJQUNOLGlCQUFpQixLQUFLLFVBQVUsUUFBUSxJQUFJLHVCQUF1QixPQUFPO0FBQUEsRUFDNUU7QUFDRixFQUFFOyIsCiAgIm5hbWVzIjogW10KfQo=
