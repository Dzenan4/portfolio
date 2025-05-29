<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Toaster } from "@/components/ui/sonner";
import { onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import { UserPlus, Search, Ellipsis } from 'lucide-vue-next';
import { Check } from '@/types'
import { Input } from '@/components/ui/input';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Banknote } from 'lucide-vue-next';
import { EllipsisVertical } from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { router } from '@inertiajs/vue3';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Checks',
        href: '/checks',
    },
];

const props = defineProps<{ checks: Check[] }>();

const checksList = ref([...props.checks]);

watch(() => props.checks, (updatedChecks) => {
    checksList.value = [...updatedChecks];
});

function addCheck() {
    router.visit(route('checks.create'));
}

interface Flash {
    success?: null;
    error?: null
}

onMounted(() => {
    watch(() => usePage<{ flash: Flash }>().props.flash, 
    (flash: Flash) => {
        if (flash.success) {
            toast.success(flash.success);
            flash.success = null;
        }
    }, { immediate: true });
});

const checkSearchQuery = (event: Event) => {
    if ((event.target as HTMLInputElement).value == '') {
        checksList.value = props.checks;
    } else {
        const query = (event.target as HTMLInputElement).value;
        checksList.value = props.checks.filter(check => { 
            return check.company_name?.toLowerCase().includes(query.toLowerCase())
            || check.company_address_street?.toLowerCase().includes(query.toLowerCase())
            || check.company_address_city?.toLowerCase().includes(query.toLowerCase())
            || check.company_address_state?.toLowerCase().includes(query.toLowerCase())
            || check.company_address_zip?.includes(query.toLowerCase())
            || check.account_number?.includes(query.toLowerCase())
            || check.routing_number?.includes(query.toLowerCase());
        });
    }
}

const formattedAddress = (check: Check) => {
    return check.company_address_street ? `${ check.company_address_street }, ${ check.company_address_city }, ${ check.company_address_state } ${ check.company_address_zip }` : '';
}
</script>

<template>
    <Head title="Checks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between rounded-xl ">

                <div class="relative flex w-full max-w-sm items-center justify-center">
                    <Input 
                        id="search" 
                        type="text" 
                        placeholder="Search Checks..." 
                        class="pl-10"
                        @input="checkSearchQuery"
                    />
                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                        <Search class="size-6 text-muted-foreground" />
                    </span>
                </div>

                <Button @click="addCheck" class="mt-2 mb-2 bg-[#038245] text-white hover:bg-[#026234] md:min-h-min max-w-[200px] text-md">
                    <Banknote class="w-4 h-4 mr-2"></Banknote>
                    Add Check
                </Button>
            </div>
            
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <Table>
                    <TableHeader>
                    <TableRow>
                        <TableHead>Company</TableHead>
                        <TableHead class="text-center">Address</TableHead>
                        <TableHead class="text-center">Account Number - Routing Number</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                    </TableHeader>
                    <TableBody>
                    <TableRow v-for="check in checksList" :key="check.id">
                        <TableCell>{{ check.company_name }} </TableCell>
                        <TableCell class="text-center"> {{ formattedAddress(check) }}</TableCell>
                        <TableCell class="text-center">{{ check.account_number }} - {{ check.routing_number }}</TableCell>
                        <TableCell class="flex space-x-2">
                            <DropdownMenu>
                                <DropdownMenuTrigger>
                                    <Button variant="ghost">
                                        <EllipsisVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                <DropdownMenuLabel>Check Actions</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem>
                                    <Link :href="route('checks.edit', check.id)">Edit</Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem>
                                    <Link :href="route('checks.destroy', check.id)" method="delete" as="button">Delete</Link>
                                </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
