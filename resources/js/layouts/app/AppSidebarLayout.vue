<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import HeaderNavbar from '@/components/HeaderNavbar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar" class=" flex flex-col w-full">
        <!-- <AppSidebar /> -->
         <HeaderNavbar />
        <AppContent variant="sidebar" class="overflow-x-hidden rounded-lg shadow-lg">
            <!-- <AppSidebarHeader :breadcrumbs="breadcrumbs" /> -->
            <slot />
        </AppContent>
    </AppShell>
</template>
