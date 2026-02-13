<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { LogOut, FileText } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const page = usePage();
const user = page.props.auth?.user;

// Check if user has admin or responder access
const hasFullAccess = computed(() => {
    const permissions = user?.permissions || [];
    return permissions.includes('admin_access') || permissions.includes('responder_access');
});

const handleLogout = () => {
    router.post('/logout');
};

const goToIncidentReport = () => {
    router.get('incident-report');
};

const goToMainPage = () => {
    if (hasFullAccess.value) {
        router.get('dashboard');
    } else {
        router.get('citizen-page');
    }
};
</script>

<template>
    <header class="bg-white border-b border-gray-200 shadow-sm m-2 rounded-lg">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Left: Operation Center -->
            <div class="flex items-center">
                <h1 class="text-xl font-semibold text-gray-900">Operation Center</h1>
            </div>
            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    @click="goToMainPage"
                    class="flex items-center gap-2 shadow-none border-none px-2"
                >
                    {{ hasFullAccess ? 'Dashboard' : 'Citizen Page' }}
                </Button>
                <Button
                    variant="outline"
                    @click="goToIncidentReport"
                    class="flex items-center gap-2 shadow-none border-none px-2"
                >
                    Incident Report
                </Button>
                <span class="text-sm font-medium text-gray-700">{{ user?.username }}</span>
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
