<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Megaphone, FileCheckCorner, ChartColumnStacked, Users } from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();

const userPermissions = computed<string[]>(() =>
    (page.props.auth as any)?.user?.permissions?.map((p: { slug: string }) => p.slug) ?? []
);

const can = (...perms: string[]) =>
    perms.some(p => userPermissions.value.includes(p));

const allNavItems: (NavItem & { permissions?: string[] })[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: ChartColumnStacked,
        permissions: ['admin_access', 'responder_access'],
    },
    {
        title: 'Reports',
        href: '/incident-report',
        icon: FileCheckCorner,
    },
    {
        title: 'Alerts Config',
        href: '/announcement-alert',
        icon: Megaphone,
        permissions: ['admin_access'],
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        permissions: ['admin_access'],
    },
];

const mainNavItems = computed<NavItem[]>(() =>
    allNavItems.filter(item =>
        !item.permissions || can(...item.permissions)
    )
);

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
