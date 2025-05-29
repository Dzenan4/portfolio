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
import { UserPlus, Search } from 'lucide-vue-next';
import { Customer } from '@/types'
import { Input } from '@/components/ui/input';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { EllipsisVertical } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customers',
        href: '/customers',
    },
];

const props = defineProps<{ customers: Customer[] }>();

const customersList = ref([...props.customers]);

function addCustomer() {
    router.visit(route('customers.create'));
}

watch(() => props.customers, (updatedCustomers) => {
    customersList.value = [...updatedCustomers];
});

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

const customerSearchQuery = (event: Event) => {
    if ((event.target as HTMLInputElement).value == '') {
        customersList.value = props.customers;
    } else {
        const query = (event.target as HTMLInputElement).value;
        customersList.value = props.customers.filter(customer => { 
            return customer.last_name?.toLowerCase().includes(query.toLowerCase()) 
            || customer.first_name?.toLowerCase().includes(query.toLowerCase())
            || customer.middle_initial?.toLowerCase().includes(query.toLowerCase())
            || customer.address_street?.toLowerCase().includes(query.toLowerCase())
            || customer.address_city?.toLowerCase().includes(query.toLowerCase())
            || customer.address_state?.toLowerCase().includes(query.toLowerCase())
            || customer.address_zip?.toLowerCase().includes(query.toLowerCase())
            || customer.phone_number?.includes(query.toLowerCase())
            || customer.dl_number?.toLowerCase().includes(query.toLowerCase());
        });
    }
}

</script>

<template>
    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between rounded-xl ">

                <div class="relative flex w-full max-w-sm items-center">
                    <Input 
                        id="search" 
                        type="text" 
                        placeholder="Search Customers..." 
                        class="pl-10"
                        @input="customerSearchQuery"
                    />
                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                        <Search class="size-6 text-muted-foreground" />
                    </span>
                </div>

                <Button @click="addCustomer" class="mt-2 mb-2 bg-[#038245] text-white hover:bg-[#026234] md:min-h-min max-w-[200px] text-md">
                    <UserPlus class="w-4 h-4"></UserPlus>
                    Add Customer
                </Button>
            </div>
            
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <Table>
                    <TableHeader>
                    <TableRow>
                        <TableHead>Customer</TableHead>
                        <TableHead class="text-center">Address</TableHead>
                        <TableHead class="text-center">Phone Number</TableHead>
                        <TableHead class="text-center">DL Number</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                    </TableHeader>
                    <TableBody>
                    <TableRow v-for="customer in customersList" :key="customer.id">
                        <TableCell>{{ customer.first_name }} {{ customer.last_name }} </TableCell>
                        <TableCell class="text-center">{{ customer.address_street }}, {{ customer.address_city }}, {{ customer.address_state }} {{ customer.address_zip }}</TableCell>
                        <TableCell class="text-center"> ({{ customer.phone_number.slice(0, 3) }}) {{ customer.phone_number.slice(3, 6) }}-{{ customer.phone_number.slice(6, 10) }}</TableCell>
                        <TableCell class="text-center">{{ customer.dl_number }}</TableCell>
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
                                    <Link :href="route('customers.edit', customer.id)">Edit</Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem>
                                    <Link :href="route('customers.destroy', customer.id)" method="delete" as="button">Delete</Link>
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
