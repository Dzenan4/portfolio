<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-vue-next';

const searchQuery = ref('');

const emit = defineEmits(['update']);

const search = debounce(() => {
  router.get('/transactions', { search: searchQuery.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onSuccess: () => {
      emit('update', searchQuery.value);
    },
  });
}, 300);

watch(searchQuery, () => {
  search();
});
</script>

<template>
  <div class="relative w-full max-w-sm items-center">
    <Input
      id="search"
      type="text"
      v-model="searchQuery"
      placeholder="Search customers..."
      class="pl-10 h-full"
    />
    <span class="absolute start-0 inset-y-2 flex items-center justify-center px-2 bottom-2">
      <Search class="size-6 text-muted-foreground" />
    </span>
  </div>
</template>
