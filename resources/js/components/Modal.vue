<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  maxWidth: {
    type: String,
    default: '2xl',
  },
  closeable: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

watch(() => props.show, () => {
  if (props.show) {
    document.body.style.overflow = 'hidden';
    showSlot.value = true;
    // Use show() instead of showModal() to allow other overlays on top
    if (dialog.value) {
      dialog.value.show();
    }
  } else {
    document.body.style.overflow = null;
    setTimeout(() => {
      if (dialog.value) {
        dialog.value.close();
      }
      showSlot.value = false;
    }, 200);
  }
});

const close = () => {
  if (props.closeable) {
    emit('close');
  }
};

const closeOnEscape = (e) => {
  if (e.key === 'Escape') {
    e.preventDefault();

    if (props.show) {
      close();
    }
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
  return {
    'sm': 'sm:max-w-sm',
    'md': 'sm:max-w-md',
    'lg': 'sm:max-w-lg',
    'xl': 'sm:max-w-xl',
    '2xl': 'sm:max-w-[100vw] max-w-[90vw]',
  }[props.maxWidth];
});
</script>

<template>
  <dialog class="w-auto min-h-full m-0 overflow-y-auto bg-transparent backdrop:bg-transparent" ref="dialog">
    <div class="fixed inset-0 flex bg-black/50 items-center justify-center px-4 py-6 overflow-y-auto sm:px-0"
      style="z-index: 40;" scroll-region>

      <!-- Modal Content -->
      <transition enter-active-class="duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        enter-to-class="translate-y-0 opacity-100 sm:scale-100" leave-active-class="duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100 sm:scale-100"
        leave-to-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95">
        <div v-show="show"
          class="w-full md:w-auto mb-6 p-2 overflow-hidden transition-all transform bg-white rounded-xl  shadow-xl dark:bg-gray-800 sm:mx-auto max-h-[85vh] flex flex-col"
          :class="maxWidthClass">

          <!-- Scrollable Content Area -->
          <div class="flex-1 overflow-y-auto">
            <slot v-if="showSlot" />
          </div>

          <!-- Optional Footer -->
        </div>
      </transition>
    </div>
  </dialog>
</template>
