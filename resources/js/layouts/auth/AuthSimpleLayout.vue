<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { PhFirstAid } from '@phosphor-icons/vue'

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center gap-2 font-medium">
                        <div class="rounded-md bg-red-500">
                            <PhFirstAid :size="30" weight="fill" class="text-red-500 bg-white rounded-full m-1"/>
                        </div>
                        <span class="text-xl font-bold leading-none">
                            GEARS<br/>
                            <span class="text-sm leading-none font-medium">Emergency Response System</span>
                        </span>
                    </div>

                    <Link
                        :href="home()"
                        class="flex items-center gap-2 font-medium"
                    >
                        <span class="text-xl font-medium">{{ title }}</span>
                    </Link>

                    <div class="space-y-2 text-center">
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
