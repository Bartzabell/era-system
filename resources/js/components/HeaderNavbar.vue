<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const page = usePage();
const user = page.props.auth?.user;

const permissions = computed(() =>
    (user?.permissions || []).map((p: any) => p.slug)
);

const isAdmin = computed(() => permissions.value.includes('admin_access'));
const isResponder = computed(() => permissions.value.includes('responder_access'));
const isCitizen = computed(() => permissions.value.includes('citizen_access'));

const handleLogout = () => router.post('/logout');

const goToIncidentReport = () => router.get('incident-report');

const goToMainPage = () => {
    if (isAdmin.value || isResponder.value) {
        router.get('dashboard');
    } else {
        router.get('citizen-page');
    }
};
</script>

<template>
    <header class="bg-white border-b border-gray-200 shadow-sm m-2 rounded-lg">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Operation Center</h1>
            </div>
            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    @click="goToMainPage"
                    class="flex items-center gap-2 shadow-none border-none px-2"
                >
                    {{ (isAdmin || isResponder) ? 'Dashboard' : 'Citizen Page' }}
                </Button>
                <Button
                    variant="outline"
                    @click="goToIncidentReport"
                    class="flex items-center gap-2 shadow-none border-none px-2"
                >
                    Incident Report
                </Button>
                <span class="text-base font-medium text-gray-700">{{ user?.username }}</span>
                <Button
                    variant="ghost"
                    size="icon"
                    @click="handleLogout"
                    class="h-9 hover:bg-gray-100"
                    title="Logout"
                >
                    <LogOut class="h-4 text-gray-600" />
                </Button>
            </div>
        </div>
    </header>
</template>
