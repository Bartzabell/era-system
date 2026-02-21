<script setup>
import { ref, computed, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { PhTrash, PhListMagnifyingGlass, PhMagnifyingGlass } from "@phosphor-icons/vue";
import CustomSelect from './CustomSelect.vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Object, // Changed from Array to Object to match Laravel pagination
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  showSearch: {
    type: Boolean,
    default: true
  },
  showPerPage: {
    type: Boolean,
    default: false
  },
  perPageOptions: {
    type: Array,
    default: () => [
      { value: 5, label: '5' },
      { value: 10, label: '10' },
      { value: 25, label: '25' },
      { value: 50, label: '50' },
      { value: 100, label: '100' }
    ]
  }
})

const navigateToPage = (url) => {
  if (!url) return;

  // If using Inertia.js
  if (window.Inertia) {
    window.Inertia.visit(url);
  } else {
    // Otherwise use standard navigation
    window.location.href = url;
  }
};

const emit = defineEmits(['update:filters'])

const sorting = ref([])
const globalFilter = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || props.data.per_page || 10)

// Watch for changes in global filter and emit back to parent
watch(globalFilter, (newValue) => {
  emit('update:filters', {
    ...props.filters,
    search: newValue
  })
})

// Watch for changes in per page and emit back to parent
watch(perPage, (newValue) => {
  emit('update:filters', {
    ...props.filters,
    per_page: newValue,
    page: 1 // Reset to first page when changing per page
  })
})

const table = useVueTable({
  get data() { return props.data.data }, // Use .data to access the actual array
  get columns() { return props.columns },
  state: {
    get sorting() { return sorting.value },
    get globalFilter() { return globalFilter.value }
  },
  onSortingChange: updaterOrValue => {
    sorting.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(sorting.value)
      : updaterOrValue
  },
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  // getPaginationRowModel: getPaginationRowModel(),
})
</script>

<template>
  <div class="space-y-4">
    <!-- Global Search -->
    <div class="flex flex-col gap-1 pt-1 sm:flex-row sm:items-end sm:justify-end">
      <div v-if="showPerPage" class="flex items-center gap-2">
        <div class="text-gray-400 text-xs">Per page:</div>
        <CustomSelect label="" name="per_page" v-model="perPage" :options="perPageOptions" class="w-max" />
      </div>
      <div v-if="showSearch" class="flex items-center pt-4">
        <PhMagnifyingGlass class="relative text-gray-400 transform -translate-y-1/2 left-8 top-3" :size="24" />
        <Input class="w-full text-right bg-white border border-black" placeholder=". . ." :model-value="globalFilter"
          @update:model-value="globalFilter = $event" />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden shadow-lg rounded-lg  bg-white">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead v-for="header in headerGroup.headers" :key="header.id"
              :class="header.column.getCanSort() ? 'cursor-pointer text-left select-none' : ''"
              @click="header.column.getCanSort() ? header.column.toggleSorting() : null">
              <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <template v-if="table.getRowModel().rows.length">
            <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
              <TableCell class="py-1" v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div class="flex flex-col items-center justify-between px-2 lg:flex-row">
      <div class="flex items-center space-x-2">
        <nav class="flex w-full gap-2 -space-x-px bg-gray-300lg:inline-flex" aria-label="Pagination">
          <template v-for="page in data.links" :key="page.label">

            <Button v-if="page.active" size="sm"
              :class="'z-10 text-xs h-auto border hover:!bg-orange-600/50 border-black bg-orange-600'">
              <span v-html="page.label"></span>
            </Button>
            <!-- Clickable page with URL -->
            <Link v-else-if="page.url" :href="page.url" method="get" preserve-state class="inline-block">
            <Button size="sm" :class="'text-black bg-gray-200 border border-black text-xs hover:!bg-orange-600/50'">
              <span v-html="page.label"></span>
            </Button>
            </Link>
            <!-- Disabled page (no URL, like "...") -->
            <Button v-else size="sm"
              :class="'opacity-50 bg-white border border-black hover:!bg-orange-600/80 text-black text-xs'">
              <span v-html="page.label"></span>
            </Button>
          </template>
        </nav>
      </div>
      <div class="flex text-sm text-muted-foreground">
        Showing {{ data.from }} to {{ data.to }} of {{ data.total }} entries
      </div>
    </div>
  </div>
</template>
