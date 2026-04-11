<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Megaphone, FileCheckCorner, ChartColumnStacked, Users, CalendarRange } from 'lucide-vue-next';
import { PhAmbulance } from '@phosphor-icons/vue'
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

type NavItemWithPermissions = NavItem & { permissions?: string[] };

interface NavGroup {
    label: string;
    permissions?: string[];
    items: NavItemWithPermissions[];
}

const navGroups: NavGroup[] = [
    {
        label: 'Dashboard',
        permissions: ['admin_access', 'responder_access'],
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: ChartColumnStacked,
                permissions: ['admin_access', 'responder_access'],
            },
        ],
    },
    {
        label: 'Incident Module',
        items: [
            {
                title: 'Incident Reports',
                href: '/incident-report',
                icon: FileCheckCorner,
            },
            {
                title: 'Dispatch',
                href: '/dispatch',
                icon: PhAmbulance,
            },
            {
                title: 'Monthly Report',
                href: '/monthly-report',
                icon: CalendarRange,

            },
        ],
    },
    {
        label: 'Admin',
        permissions: ['admin_access'],
        items: [
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
        ],
    },
];

const visibleNavGroups = computed(() =>
    navGroups
        .filter(group =>
            !group.permissions || can(...group.permissions)
        )
        .map(group => ({
            ...group,
            items: group.items.filter(item =>
                !item.permissions || can(...item.permissions)
            ),
        }))
        .filter(group => group.items.length > 0)
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
            <NavMain
                v-for="group in visibleNavGroups"
                :key="group.label"
                :label="group.label"
                :items="group.items"
            />
        </SidebarContent>
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
