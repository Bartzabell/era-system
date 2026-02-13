<script setup lang="ts" generic="T extends Record<string, any>">
import { computed, ref } from 'vue';
import { ChevronUp, ChevronDown, ChevronsUpDown, ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    render?: (value: any, row: T) => string;
}

interface Props {
    columns: Column[];
    data: T[];
    perPage?: number;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    perPage: 10,
    loading: false
});

const emit = defineEmits<{
    rowClick: [row: T];
}>();

const currentPage = ref(1);
const sortField = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

// Sorting logic
const sortedData = computed(() => {
    if (!sortField.value) return props.data;

    return [...props.data].sort((a, b) => {
        const aVal = a[sortField.value!];
        const bVal = b[sortField.value!];

        if (aVal === null || aVal === undefined) return 1;
        if (bVal === null || bVal === undefined) return -1;

        let comparison = 0;
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            comparison = aVal.localeCompare(bVal);
        } else if (typeof aVal === 'number' && typeof bVal === 'number') {
            comparison = aVal - bVal;
        } else {
            comparison = String(aVal).localeCompare(String(bVal));
        }

        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
});

// Pagination logic
const totalPages = computed(() => Math.ceil(sortedData.value.length / props.perPage));

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * props.perPage;
    const end = start + props.perPage;
    return sortedData.value.slice(start, end);
});

const pageNumbers = computed(() => {
    const pages: (number | string)[] = [];
    const maxVisible = 5;

    if (totalPages.value <= maxVisible) {
        for (let i = 1; i <= totalPages.value; i++) {
            pages.push(i);
        }
    } else {
        if (currentPage.value <= 3) {
            for (let i = 1; i <= 4; i++) pages.push(i);
            pages.push('...');
            pages.push(totalPages.value);
        } else if (currentPage.value >= totalPages.value - 2) {
            pages.push(1);
            pages.push('...');
            for (let i = totalPages.value - 3; i <= totalPages.value; i++) pages.push(i);
        } else {
            pages.push(1);
            pages.push('...');
            pages.push(currentPage.value - 1);
            pages.push(currentPage.value);
            pages.push(currentPage.value + 1);
            pages.push('...');
            pages.push(totalPages.value);
        }
    }

    return pages;
});

const handleSort = (column: Column) => {
    if (!column.sortable) return;

    if (sortField.value === column.key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = column.key;
        sortDirection.value = 'asc';
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const getCellValue = (row: T, column: Column) => {
    const value = row[column.key];
    return column.render ? column.render(value, row) : value;
};
</script>

<template>
    <div class="w-full">
        <!-- Table -->
        <div class="relative overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            scope="col"
                            class="px-6 py-3"
                            :class="{ 'cursor-pointer select-none hover:bg-gray-100': column.sortable }"
                            @click="handleSort(column)"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <component
                                    v-if="column.sortable"
                                    :is="sortField === column.key
                                        ? (sortDirection === 'asc' ? ChevronUp : ChevronDown)
                                        : ChevronsUpDown"
                                    class="w-4 h-4"
                                    :class="sortField === column.key ? 'text-orange-600' : 'text-gray-400'"
                                />
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading" class="border-b">
                        <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-5 h-5 border-2 border-orange-600 border-t-transparent rounded-full animate-spin"></div>
                                <span>Loading...</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-else-if="paginatedData.length === 0" class="border-b">
                        <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
                            No data available
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="(row, index) in paginatedData"
                        :key="index"
                        class="bg-white border-b hover:bg-gray-50 cursor-pointer"
                        @click="emit('rowClick', row)"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ getCellValue(row, column) }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && paginatedData.length > 0" class="flex items-center justify-between mt-4">
            <div class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                to
                <span class="font-medium">{{ Math.min(currentPage * perPage, data.length) }}</span>
                of
                <span class="font-medium">{{ data.length }}</span>
                results
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>

                <button
                    v-for="(page, index) in pageNumbers"
                    :key="index"
                    @click="typeof page === 'number' ? goToPage(page) : null"
                    :disabled="page === '...'"
                    class="px-3 py-2 text-sm font-medium rounded-lg"
                    :class="[
                        page === currentPage
                            ? 'text-white bg-orange-600'
                            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50',
                        page === '...' ? 'cursor-default hover:bg-white' : ''
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
