<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Megaphone, FileCheckCorner, ChartColumnStacked, Users, CalendarRange, Squircle } from 'lucide-vue-next';
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
const userRole = computed(() => (page.props.auth as any)?.user?.role ?? '');

const isAdmin = computed(() => userRole.value === 'administrator');
const isAssistantAdmin = computed(() => userRole.value === 'assistant admin');
const isResponder = computed(() => userRole.value === 'responder');

type NavItemWithRoles = NavItem & {
    admin?: boolean;
    assistantAdmin?: boolean;
    responder?: boolean;
};

interface NavGroup {
    label: string;
    admin?: boolean;
    assistantAdmin?: boolean;
    responder?: boolean;
    items: NavItemWithRoles[];
}

const navGroups: NavGroup[] = [
    {
        label: 'Dashboard',
        admin: true,
        assistantAdmin: true,
        responder: true,
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: ChartColumnStacked,
                admin: true,
                assistantAdmin: true,
                responder: true,
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
                admin: true,
                assistantAdmin: true,
                responder: true,
            },
            {
                title: 'Dispatch',
                href: '/dispatch',
                icon: PhAmbulance,
                admin: true,
                assistantAdmin: true,
            },
            {
                title: 'Monthly Report',
                href: '/monthly-report',
                icon: CalendarRange,
                admin: true,
            },
        ],
    },
    {
        label: 'Admin',
        admin: true,
        items: [
            {
                title: 'Alerts Config',
                href: '/announcement-alert',
                icon: Megaphone,
                admin: true,
            },
            {
                title: 'Users',
                href: '/users',
                icon: Users,
                admin: true,
            },
            {
                title: 'Login UI Settings',
                href: '/login-settings',
                icon: Squircle,
                admin: true,
            },
        ],
    },
];

const visibleNavGroups = computed(() =>
    navGroups
        .filter(group => {
            if (group.admin && isAdmin.value) return true;
            if (group.assistantAdmin && isAssistantAdmin.value) return true;
            if (group.responder && isResponder.value) return true;
            return !group.admin && !group.assistantAdmin && !group.responder;
        })
        .map(group => ({
            ...group,
            items: group.items.filter(item => {
                if (item.admin && isAdmin.value) return true;
                if (item.assistantAdmin && isAssistantAdmin.value) return true;
                if (item.responder && isResponder.value) return true;
                return !item.admin && !item.assistantAdmin && !item.responder;
            }),
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
