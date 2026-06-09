<script setup>
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command'
import { Check, ChevronsUpDown, X } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = defineProps({
    // Data source
    items: {
        type: Array,
        required: true
    },
    // Existing value for edit mode
    existingValue: {
        type: [Number, String, Object, Array],
        default: null
    },
    // Label field to display in dropdown and search
    labelField: {
        type: String,
        default: 'name'
    },
    descriptionField: {
        type: String,
        default: null
    },
    secondDescriptionField: {
        type: String,
        default: null
    },
    nameDescriptionField: {
        type: String,
        default: null
    },
    // Search fields
    searchFields: {
        type: Array,
        default: () => ['name', 'item_code', 'id', 'city_municipality_description', 'province_description']
    },
    // Placeholder text
    placeholder: {
        type: String,
        default: '- '
    },
    // Enable multiple select (default to false)
    multiple: {
        type: Boolean,
        default: false
    },
    // New props for readonly and disabled states
    readonly: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['change'])

// State management
const open = ref(false)
const searchValue = ref('')
const selectedItem = ref(null)
const selectedItems = ref([])

// Function to format datetime
const formatDateTime = (value) => {
    if (!value) return value

    try {
        // If format is always YYYY-MM-DD HH:mm:ss.SSS
        const [datePart, timePart] = value.split(' ')
        const [year, month, day] = datePart.split('-')
        const [hours, minutes] = timePart.split(':')

        const hourNum = parseInt(hours)
        const ampm = hourNum >= 12 ? 'pm' : 'am'
        const displayHour = hourNum % 12 || 12

        return `${month}/${day}/${year} ${displayHour}:${minutes}${ampm}`
    } catch {
        return value
    }
}

// Watch for existing value changes
watch(() => props.existingValue, (newValue) => {
    if (props.multiple) {
        // Multiple selection logic
        if (newValue && Array.isArray(newValue)) {
            // Find matching items for existing values
            const matchedItems = newValue.map(value =>
                props.items.find(item =>
                    item.id === value ||  // Direct ID match
                    item.id === Number(value) ||  // Number conversion
                    item[props.labelField] === value  // Label field match
                )
            ).filter(item => item !== undefined)

            selectedItems.value = matchedItems
        } else {
            selectedItems.value = []
        }
    } else {
        // Single selection logic (original implementation)
        if (newValue) {
            // Try matching on different possible fields
            const matchedItem = props.items.find(item =>
                item.id === newValue ||  // First try direct ID match
                item.id === Number(newValue) ||  // Try number conversion
                item[props.labelField] === newValue  // Then try label field match
            )
            selectedItem.value = matchedItem || null
        } else {
            selectedItem.value = null
        }
    }
}, { immediate: true })

// Compute filterable items with search text
const filterableItems = computed(() => {
    return props.items.map(item => {
        // Create a searchable text combining search fields
        const searchText = props.searchFields
            .map(field => item[field])
            .filter(value => value !== undefined && value !== null)
            .join(' ')
            .toLowerCase()

        return {
            ...item,
            searchText
        }
    })
})

// Filter items based on search input
const filteredItems = computed(() => {
    const search = searchValue.value.toLowerCase()
    return filterableItems.value.filter(item =>
        item.searchText.includes(search) &&
        // Exclude already selected items in multiple mode
        (!props.multiple || !selectedItems.value.some(selected => selected.id === item.id))
    )
})

// Select an item
const selectItem = (item) => {
    if (props.readonly || props.disabled) return

    if (props.multiple) {
        // Multiple selection logic
        const existingIndex = selectedItems.value.findIndex(selected => selected.id === item.id)
        if (existingIndex > -1) {
            // Remove if already selected
            selectedItems.value.splice(existingIndex, 1)
        } else {
            // Add if not selected
            selectedItems.value.push(item)
        }
        // Emit selected items
        emit('change', selectedItems.value)
    } else {
        // Single selection logic
        selectedItem.value = item

        // Emit the entire item for flexible handling
        emit('change', item)

        // Close the popover
        open.value = false
    }

    // Reset search
    searchValue.value = ''
}

// Remove a specific item in multiple mode
const removeItem = (itemToRemove) => {
    if (props.readonly || props.disabled) return

    selectedItems.value = selectedItems.value.filter(item => item.id !== itemToRemove.id)
    emit('change', selectedItems.value)
}

// Determine display text
const displayText = computed(() => {
    if (props.multiple) {
        // Multiple selection display
        if (selectedItems.value.length === 0) {
            return props.placeholder
        }

        return selectedItems.value.length === 1
            ? selectedItems.value[0][props.labelField]
            : `${selectedItems.value.length} selected`
    } else {
        // Single selection display (original implementation)
        return selectedItem.value
            ? selectedItem.value[props.labelField]
            : props.placeholder
    }
})
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="outline" role="combobox" :aria-expanded="open"
                class="justify-between w-full bg-white border border-black hover:bg-gray-50" :class="{
                    'opacity-100 cursor-not-allowed': readonly || disabled,
                    'border-gray-800 bg-gray-50': readonly || disabled
                }">
                <div class="flex items-center text-[10px] 2xl:text-lg gap-2 overflow-hidden">
                    <template v-if="!multiple">
                        <span :class="[
                            'truncate text-[10px] 2xl:text-lg',
                            (!selectedItem && !multiple) || (multiple && selectedItems.length === 0) ? 'text-gray-400 italic opacity-90' : 'text-black',
                            (readonly || disabled) ? 'text-amber-500 font-black' : ''
                        ]">
                            {{ displayText || '- ' }}
                        </span>
                        <button v-if="!readonly && !disabled" type="button"
                            class="ml-1 hover:bg-gray-200 text-[10px] 2xl:text-lg rounded-full p-0.5"
                            @click.stop="removeItem(item)">
                            <X class="w-3 h-3" />
                        </button>
                    </template>
                    <template v-if="multiple && selectedItems.length > 0">
                        <div v-for="item in selectedItems.slice(0, 5)" :key="item.id"
                            class="flex items-center px-1 text-[10px] 2xl:text-sm bg-gray-100 rounded-sm">
                            {{ item[labelField] }} {{ formatDateTime(item[descriptionField]) }}
                            <button v-if="!readonly && !disabled" type="button"
                                class="ml-1 hover:bg-gray-200 text-[10px] 2xl:text-lg rounded-full p-0.5"
                                @click.stop="removeItem(item)">
                                <X class="w-3 h-3" />
                            </button>
                        </div>
                        <span v-if="selectedItems.length > 2" class="text-[10px] 2xl:text-sm text-gray-500">
                            +{{ selectedItems.length - 2 }}
                        </span>
                    </template>
                </div>
                <ChevronsUpDown class="w-4 h-2 ml-2 opacity-100 shrink-0"
                    :class="{ 'text-gray-50': readonly || disabled }" />
            </Button>
        </PopoverTrigger>
        <PopoverContent v-if="!readonly && !disabled" class="w-full text-black p-0 border border-gray-400">
            <Command>
                <CommandInput v-model="searchValue" class="text-right text-sm" :placeholder="`Search`" />
                <CommandList>
                    <CommandEmpty>No data found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem class="font-medium" v-for="item in filteredItems" :key="item.id" :value="item"
                            @select="() => selectItem(item)">

                            <div class="flex text-[10px] 2xl:text-base flex-col">
                                {{ item[labelField] }}<br><span class="text-[10px] 2xl:text-sm opacity-70">
                                    {{ item[nameDescriptionField] }}<template v-if="item[descriptionField]"> : {{
                                        formatDateTime(item[descriptionField]) }}</template>
                                    <template v-if="item[secondDescriptionField]"> ( {{
                                        formatDateTime(item[secondDescriptionField]) }} )</template>
                                </span>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
