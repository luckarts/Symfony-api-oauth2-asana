<script setup lang="ts">
const colorMode = useColorMode()
const { t } = useI18n()

const isDark = computed(() => colorMode.value === 'dark')

function toggleTheme() {
  colorMode.preference = isDark.value ? 'light' : 'dark'
}
</script>

<template>
  <div class="min-h-screen bg-panel flex flex-col items-center justify-center px-4">
    <div class="flex-1 flex items-center justify-center w-full">
      <slot />
    </div>
    <footer class="w-full py-6 flex flex-col items-center gap-3 text-sm text-gray-400">
      <Button @click="toggleTheme">
        <template #leading>
          <UIcon
            :name="isDark ? 'i-heroicons-sun' : 'i-heroicons-moon'"
            class="size-4 shrink-0 text-gray-500"
          />
        </template>
        {{ isDark ? t('user.lightMode') : t('user.darkMode') }}
      </Button>
      <p>©2026 Horizon UI. All Rights Reserved.</p>
     
    </footer>
  </div>
</template>
