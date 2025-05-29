<script setup lang="ts">
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs'
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { LoaderCircle } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { BadgeAlert } from 'lucide-vue-next';
import { Customer, CustomerForm, Check, CheckForm, TransactionForm } from '@/types'
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { Table, TableBody, TableHeader, TableHead, TableRow, TableCell } from '@/components/ui/table';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: '/transactions',
    },
];

const recentTransactions = ref([]);

// Customer form parameters
const customerForm = useForm<CustomerForm>({
    first_name: '',
    middle_initial: '',
    last_name: '',
    address_street: '',
    address_city: '',
    address_state: '',
    address_zip: '',
    phone_number: '',
    dl_number: '',
    dl_state: '',
    dob: null as Date | null,
    dl_picture_link: null as File | null,
    self_picture_link: null as File | null,
    notes: '',
});

const checkForm = useForm<CheckForm>({
    company_name: '',
    company_address_street: '',
    company_address_city: '',
    company_address_state: '',
    company_address_zip: '',
    account_number: '',
    routing_number: '',
    type: '',
    cashing_status: '',
    notes: '',
});

const transactionForm = useForm<TransactionForm>({
    customer_id: null,
    check_id: null,
    date: undefined,
    check_amount: null,
    check_number: null,
    payout_amount: '0.00',
    charge_amount: null,
    charge_percentage: '2.00',
    check_picture_link: null as File | null,
});

const props = defineProps<{ 
    customers: Customer[]
    checks: Check[]
}>();

const customersList = ref([...props.customers]);
const checksList = ref([...props.checks]);

watch(() => checkForm.type, (type) => {
    transactionForm.charge_percentage = type == 'insurance' ? '3.00' : '2.00';
});

watch(() => transactionForm.check_amount, (amount) => {

    if (Number.isNaN(parseFloat(amount)) || amount == null) {
        transactionForm.charge_amount = "0.00";
        transactionForm.payout_amount = "0.00";
        return;
    }

    let chargeAmount = (parseFloat(amount) * (parseFloat(transactionForm.charge_percentage) / 100)).toFixed(2);
    let payoutAmount = (parseFloat(amount) - parseFloat(chargeAmount)).toFixed(2);

    if (!chargeAmount.includes('.')) {
        chargeAmount += '.00';
    }

    if (!payoutAmount.includes('.')) {
        payoutAmount += '.00';
    }

    transactionForm.charge_amount = parseFloat(chargeAmount).toFixed(2);
    transactionForm.payout_amount = parseFloat(payoutAmount).toFixed(2);
});

watch(() => checkForm.cashing_status, (status) => {
    let checkCard = document.getElementById('cashing_status_alert');
    if (status == 'positive') {
        checkCard.classList.remove('bg-yellow-600');
        checkCard.classList.remove('bg-red-600');
        checkCard.classList.add('bg-green-600');
    } else if (status == 'negative') {
        checkCard.classList.remove('bg-green-600');
        checkCard.classList.remove('bg-yellow-600');
        checkCard.classList.add('bg-red-600');
    } else if (status = 'unverified') {
        checkCard.classList.remove('bg-green-600');
        checkCard.classList.remove('bg-red-600');
        checkCard.classList.add('bg-yellow-600');
    } else {
        checkCard.classList.remove('border-green-600');
        checkCard.classList.remove('border-red-600');
        checkCard.classList.remove('border-yellow-600');    }
});

watch(() => transactionForm.charge_percentage, (amount) => {
    if (Number.isNaN(parseFloat(amount)) || amount == null) {
        transactionForm.charge_amount = "0.00";
        transactionForm.payout_amount = "0.00";
        return;
    }

    let chargeAmount = (parseFloat(transactionForm.check_amount) * (parseFloat(amount) / 100)).toFixed(2);
    let payoutAmount = (parseFloat(transactionForm.check_amount) - parseFloat(chargeAmount)).toFixed(2);

    if (!chargeAmount.includes('.')) {
        chargeAmount += '.00';
    }

    if (!payoutAmount.includes('.')) {
        payoutAmount += '.00';
    }

    transactionForm.charge_amount = parseFloat(chargeAmount).toFixed(2);
    transactionForm.payout_amount = parseFloat(payoutAmount).toFixed(2);
})
interface Flash {
    success?: null;
    error?: null
}

const checkPicturePreview = ref<string | null>(null);

// Handle check picture input
const handleCheckPictureInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        transactionForm.check_picture_link = file;
        checkPicturePreview.value = URL.createObjectURL(file);
    }
}

onMounted(() => {
    watch(() => usePage<{ flash: Flash }>().props.flash, 
    (flash: Flash) => {
        if (flash.success) {
            toast.success(flash.success);
            flash.success = null;
        }
    }, { immediate: true });
    customerSuggestionsList = document.getElementById('customer_suggestions_list');
    checkSuggestionsList = document.getElementById('check_suggestions_list');
});

// Customer search suggestions
let customerSuggestionsList;
// Show suggestions based on user behavior
const showCustomerSuggestions = ref<boolean>(false); 

// Make customer search request
async function customerSearchRequest(inputEvent) {
    // Reset elements and exit if an empty string is received.
    if (inputEvent.target.value == '') {
        customerSuggestionsList.replaceChildren();
        return;
    }

    // Clear the list first.
    customerSuggestionsList?.replaceChildren();

    // If there are suggestions, show them.
    showCustomerSuggestions.value = customersList.value.length > 0;

    const filteredCustomers = customersList.value.filter((customer) => {
        return customer.last_name?.toLowerCase().includes(inputEvent.target.value.toLowerCase()) 
        || customer.first_name?.toLowerCase().includes(inputEvent.target.value.toLowerCase())
        || customer.address_street?.toLowerCase().includes(inputEvent.target.value.toLowerCase())
        || customer.address_city?.toLowerCase().includes(inputEvent.target.value.toLowerCase())
        || customer.phone_number?.includes(inputEvent.target.value.toLowerCase())
        || customer.dl_number?.toLowerCase().includes(inputEvent.target.value.toLowerCase());
    })

    for (const customer of filteredCustomers) {
        // Create a link for the place, add an event handler to fetch the place.
        const a = document.createElement('a');
        a.addEventListener('click', () => {
            onCustomerSelected(customer);
        });
        a.innerText = customer.first_name + ' ' + customer.last_name;
        a.classList.add('relative', 'flex', 'w-full', 'cursor-default', 'select-none', 'items-center', 'rounded-sm', 'py-1.5', 'pl-3', 'pr-2', 'text-sm', 
                        'outline-none', 'focus:bg-accent', 'focus:text-accent-foreground', 'data-[disabled]:pointer-events-none', 'data-[disabled]:opacity-50', 
                        'hover:bg-accent', 'hover:text-accent-foreground', 'hover:cursor-pointer');
        const li = document.createElement('li');
        li.appendChild(a);
        customerSuggestionsList.appendChild(li);
    }
}

// Check search suggestions
let checkSuggestionsList;
// Show suggestions based on user behavior
const showCheckSuggestions = ref<boolean>(false); 

// Make customer search request
async function checkSearchRequest(inputEvent) {
    // Reset elements and exit if an empty string is received.
    if (inputEvent.target.value == '') {
        checkSuggestionsList.replaceChildren();
        return;
    }

    // Clear the list first.
    checkSuggestionsList?.replaceChildren();

    // If there are suggestions, show them.
    showCheckSuggestions.value = checksList.value.length > 0;

    const filteredChecks = checksList.value.filter((check) => {
        return check.company_name?.toLowerCase().includes(inputEvent.target.value.toLowerCase()) 
        || check.company_address_street?.toLowerCase().includes(inputEvent.target.value.toLowerCase())
        || check.account_number?.includes(inputEvent.target.value.toLowerCase())
        || check.routing_number?.includes(inputEvent.target.value.toLowerCase());
    })

    for (const check of filteredChecks) {
        // Create a link for the place, add an event handler to fetch the place.
        const a = document.createElement('a');
        a.addEventListener('click', () => {
            onCheckSelected(check);
        });
        a.innerText = check.company_name;
        a.classList.add('relative', 'flex', 'w-full', 'cursor-default', 'select-none', 'items-center', 'rounded-sm', 'py-1.5', 'pl-3', 'pr-2', 'text-sm', 
                        'outline-none', 'focus:bg-accent', 'focus:text-accent-foreground', 'data-[disabled]:pointer-events-none', 'data-[disabled]:opacity-50', 
                        'hover:bg-accent', 'hover:text-accent-foreground', 'hover:cursor-pointer');
        const li = document.createElement('li');
        li.appendChild(a);
        checkSuggestionsList.appendChild(li);
    }
}

// Customer and check to store into transactions
const customerId = ref(null);
const checkId = ref(null);

// Event handler for clicking on a suggested place.
async function onCustomerSelected(customer) {
    customerForm.first_name = customer.first_name;
    customerForm.middle_initial = customer.middle_initial;
    customerForm.last_name = customer.last_name;
    customerForm.address_street = customer.address_street;
    customerForm.address_city = customer.address_city;
    customerForm.address_state = customer.address_state;
    customerForm.address_zip = customer.address_zip;
    customerForm.phone_number = customer.phone_number;
    customerForm.dl_number = customer.dl_number;
    customerForm.dl_state = customer.dl_state;
    customerForm.dob = customer.dob;
    customerForm.dl_picture_link = customer.dl_picture_link;
    customerForm.self_picture_link = customer.self_picture_link;
    customerForm.notes = customer.notes;
    customerId.value = customer.id;
    customerSuggestionsList.innerHTML = '';
    showCustomerSuggestions.value = false;

    getRecentCustomerTransactions(customer.id);
}

// Event handler for clicking on a suggested place.
async function onCheckSelected(check) {
    checkForm.company_name = check.company_name;
    checkForm.account_number = check.account_number;
    checkForm.routing_number = check.routing_number;
    checkForm.type = check.type;
    checkForm.cashing_status = check.cashing_status;
    checkForm.notes = check.notes;
    checkId.value = check.id;
    customerSuggestionsList.innerHTML = '';
    showCustomerSuggestions.value = false;
}

async function getRecentCustomerTransactions(customerId) {
    try {
        const response = await axios.get('/transactions/getRecentTransactions', {
            params: { 
                'customer_id': customerId 
            }
        })
    
        recentTransactions.value = response.data;
        console.log(recentTransactions.value);
    } catch (error) {
        console.log(error);
    }
}

// Hide address search results when user leaves the input.
const hideCustomerSearchResults = () => {
    setTimeout(() => {
        showCustomerSuggestions.value = false;
    }, 200);
};

// Hide address search results when user leaves the input.
const hideCheckSearchResults = () => {
    setTimeout(() => {
        showCheckSuggestions.value = false;
    }, 200);
};

// Submit form action
const submit = () => {
    transactionForm.customer_id = customerId.value;
    transactionForm.check_id = checkId.value;
    transactionForm.post(route('transactions.store'));
};

</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 items-center">
            <Tabs default-value="new_transaction" class="w-full">
                <TabsList class=" grid w-full grid-cols-2">
                    <TabsTrigger value="new_transaction">New Transaction</TabsTrigger>
                    <TabsTrigger value="transaction_history">Transaction History</TabsTrigger>
                </TabsList>
                <TabsContent value="new_transaction" class="grid gap-4 justify-center mt-10">
                    <div class="flex gap-6 w-full">
                        <div class="grid gap-6 max-w-full w-1/3">
                            <div class="grid gap-4 w-full w-min-lg">
                                <form @submit.prevent="submit" class="flex flex-col gap-6 w-full max-w-md overflow-hidden flex-wrap">
                                    <Card class="min-w-full">
                                        <CardHeader>
                                            <CardTitle>
                                                Customer Information
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent class="flex flex-col gap-6">
                                            <div class="grid gap-4">
                                                <div class="flex gap-6">
                                                    <!-- Customer Name Data -->
                                                    <div class="grid gap-2 w-full">
                                                        <div class="flex gap-3 w-full">
                                                            <!-- First Name -->
                                                            <div class="grid gap-2 w-5/12 relative">
                                                                <Label for="first_name">First Name</Label>
                                                                <Input
                                                                    id="first_name"
                                                                    type="text"
                                                                    autofocus
                                                                    @focus="showCustomerSuggestions = true"
                                                                    @blur="hideCustomerSearchResults"
                                                                    :tabindex="1"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.first_name"
                                                                    placeholder="First name..."
                                                                    @input="customerSearchRequest"
                                                                />
                                                                <ul 
                                                                    v-show="showCustomerSuggestions" 
                                                                    id="customer_suggestions_list" 
                                                                    class="list-none top-full absolute z-50 max-h-96 min-w-32 w-full overflow-hidden rounded-md border bg-popover text-popover-foreground 
                                                                        shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 
                                                                        data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 
                                                                        data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',position === 'popper'&& 'data-[side=bottom]:translate-y-1 
                                                                        data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',props.class,)">
                                                                </ul>
                                                                <div v-if="customerForm.errors.first_name || customerForm.errors.last_name" class="h-5">
                                                                    <InputError :message="customerForm.errors.first_name" />
                                                                </div>
                                                            </div>

                                                            <!-- Middle Initial -->
                                                            <div class="grid gap-2 w-2/12">
                                                                <Label for="middle_initial">M.I.</Label>
                                                                <Input
                                                                    id="middle_initial"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    maxlength="1"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.middle_initial"
                                                                    placeholder="Middle initial..."
                                                                />
                                                                <div v-if="customerForm.errors.first_name || customerForm.errors.last_name" class="h-5">
                                                                    <InputError :message="customerForm.errors.middle_initial" />
                                                                </div>                                        
                                                            </div>

                                                            <!-- Last Name -->
                                                            <div class="grid gap-2 w-5/12">
                                                                <Label for="last_name">Last Name</Label>
                                                                <Input
                                                                    id="last_name"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.last_name"
                                                                    placeholder="Last name..."
                                                                />
                                                                <div v-if="customerForm.errors.first_name || customerForm.errors.last_name" class="h-5">
                                                                    <InputError :message="customerForm.errors.last_name" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex gap-6">
                                                    <!-- Customer Name Data -->
                                                    <div class="grid gap-2 w-full">
                                                        <div class="flex gap-3 w-full">
                                                            <!-- First Name -->
                                                            <div class="grid gap-2 w-full relative">
                                                                <Label for="dl_number">D.L. Number</Label>
                                                                <Input
                                                                    id="dl_number"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.dl_number"
                                                                    placeholder="D.L. Number..."
                                                                />
                                                                <div v-if="customerForm.errors.dl_number || customerForm.errors.dob" class="h-5">
                                                                    <InputError :message="customerForm.errors.dl_number" />
                                                                </div>
                                                            </div>

                                                            <!-- Last Name -->
                                                            <div class="grid gap-2 w-full">
                                                                <Label for="dob">Date of Birth</Label>
                                                                <Input
                                                                    id="dob"
                                                                    type="date"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.dob"
                                                                />
                                                                <div v-if="customerForm.errors.dl_number || customerForm.errors.dob" class="h-5">
                                                                    <InputError :message="customerForm.errors.dob" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex gap-6">
                                                    <!-- Customer Name Data -->
                                                    <div class="grid gap-2 w-full">
                                                        <div class="flex gap-3 w-full">
                                                            <!-- First Name -->
                                                            <div class="grid gap-2 relative">
                                                                <Label for="phone_number">Phone Number</Label>
                                                                <Input
                                                                    id="phone_number"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    maxlength="10"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.phone_number"
                                                                    placeholder="Phone Number..."
                                                                />
                                                                <div v-if="customerForm.errors.phone_number || customerForm.errors.address_street" class="h-5">
                                                                    <InputError :message="customerForm.errors.phone_number" />
                                                                </div>
                                                            </div>

                                                            <!-- First Name -->
                                                            <div class="grid gap-2 relative">
                                                                <Label for="address_street">Address</Label>
                                                                <Input
                                                                    id="address_street"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    autocomplete="text"
                                                                    v-model="customerForm.address_street"
                                                                    placeholder="Address..."
                                                                />
                                                                <div v-if="customerForm.errors.phone_number || customerForm.errors.address_street" class="h-5">
                                                                    <InputError :message="customerForm.errors.address_street" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <!-- Customer Additional Information -->
                                                <div class="grid gap-6">
                                                    <div class="grid gap-2">
                                                        <div class="flex gap-6">
                                                            <!-- Customer Notes -->
                                                            <div class="grid gap-2 w-full">
                                                                <Label for="notes">Notes</Label>
                                                                <Textarea 
                                                                    id="notes"
                                                                    class="resize-none"
                                                                    :tabindex="2"
                                                                    autofocus
                                                                    placeholder="Notes..."
                                                                    v-model="customerForm.notes"
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                        <CardFooter>
                                            <Button type="submit" class="mt-4 w-full bg-[#038245] hover:bg-[#026234] text-white" :tabindex="4" :disabled="transactionForm.processing">
                                                <LoaderCircle v-if="transactionForm.processing" class="h-4 w-4 animate-spin" />
                                                View Customer Information
                                            </Button>                                        
                                        </CardFooter>
                                    </Card>
                                </form>
                            </div>
                        </div>

                        <div class="grid gap-6 max-w-full w-1/3">
                            <form @submit.prevent="submit" class="flex flex-col gap-6 w-full max-w-md overflow-auto">
                                <Card id="check_card">
                                    <CardHeader>
                                        <CardTitle>
                                            Check Information
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent class="flex flex-col gap-6">
                                        <div class="grid gap-4">
                                            <!-- Customer Name Data -->
                                            <div class="flex gap-6">
                                                <div class="grid gap-2 w-full">
                                                    <!-- First Name -->
                                                    <div class="grid gap-4 w-full relative">
                                                        <div class="grid gap-2 relative">
                                                            <Label for="company_name">Company Name</Label>
                                                            <Input
                                                                id="company_name"
                                                                type="text"
                                                                autofocus
                                                                @focus="showCheckSuggestions = true"
                                                                @blur="hideCheckSearchResults"
                                                                :tabindex="1"
                                                                autocomplete="text"
                                                                v-model="checkForm.company_name"
                                                                placeholder="Company..."
                                                                @input="checkSearchRequest"
                                                            />
                                                            <ul 
                                                                v-show="showCheckSuggestions" 
                                                                id="check_suggestions_list" 
                                                                class="top-full absolute z-50 max-h-96 min-w-32 w-full overflow-hidden rounded-md border bg-popover text-popover-foreground 
                                                                    shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 
                                                                    data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 
                                                                    data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',position === 'popper'&& 'data-[side=bottom]:translate-y-1 
                                                                    data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',props.class,)">
                                                            </ul>
                                                            <div v-if="checkForm.errors.company_name || checkForm.errors.account_number || checkForm.errors.routing_number" class="h-5">
                                                                <InputError :message="checkForm.errors.company_name" />
                                                            </div>
                                                        </div>

                                                        <div class="flex gap-3 w-full">
                                                            <!-- Account Number -->
                                                            <div class="grid gap-2 w-1/2">
                                                                <Label for="account_number">Account Number</Label>
                                                                <Input
                                                                    id="account_number"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    maxlength="12"
                                                                    autocomplete="text"
                                                                    v-model="checkForm.account_number"
                                                                    placeholder="Account Number..."
                                                                />
                                                                <div v-if="checkForm.errors.company_name || checkForm.errors.account_number || checkForm.errors.routing_number" class="h-5">
                                                                    <InputError :message="checkForm.errors.account_number" />
                                                                </div>
                                                            </div>

                                                            <!-- Routing Number -->
                                                            <div class="grid gap-2 w-1/2">
                                                                <Label for="routing_number">Routing Number</Label>
                                                                <Input
                                                                    id="routing_number"
                                                                    type="text"
                                                                    autofocus
                                                                    :tabindex="1"
                                                                    maxlength="9"
                                                                    autocomplete="text"
                                                                    v-model="checkForm.routing_number"
                                                                    placeholder="Routing Number..."
                                                                />
                                                                <div v-if="checkForm.errors.company_name || checkForm.errors.account_number || checkForm.errors.routing_number" class="h-5">
                                                                    <InputError :message="checkForm.errors.routing_number" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="flex gap-3 w-full">
                                                            <div class="grid gap-2 w-full">
                                                                <Label>Type</Label>
                                                                <Select 
                                                                    id="type" 
                                                                    v-model="checkForm.type""
                                                                >
                                                                    <SelectTrigger class="w-full">
                                                                        <SelectValue placeholder="Select check type"/>
                                                                    </SelectTrigger>
                                                                    <SelectContent>
                                                                        <SelectGroup>
                                                                            <SelectLabel>Type</SelectLabel>
                                                                            <SelectItem value="business">Business</SelectItem>
                                                                            <SelectItem value="government">Government</SelectItem>
                                                                            <SelectItem value="insurance">Insurance</SelectItem>
                                                                            <SelectItem value="personal">Personal</SelectItem>
                                                                            <SelectItem value="cashiers">Cashiers</SelectItem>
                                                                            <SelectItem value="other">Other</SelectItem>
                                                                        </SelectGroup>
                                                                    </SelectContent>
                                                                </Select>
                                                            </div>
                                                            <div v-if="checkForm.errors.company_name || checkForm.errors.account_number || checkForm.errors.routing_number" class="h-5">
                                                                <InputError :message="checkForm.errors.routing_number" />
                                                            </div>

                                                            <div class="grid gap-2 w-full">
                                                                <Label>Status</Label>
                                                                <Select 
                                                                    id="cashing_status"
                                                                    v-model="checkForm.cashing_status"
                                                                >
                                                                    <SelectTrigger class="w-full">
                                                                        <SelectValue placeholder="Select cashing status" />
                                                                    </SelectTrigger>
                                                                    <SelectContent>
                                                                        <SelectGroup>
                                                                            <SelectLabel>Status</SelectLabel>
                                                                            <SelectItem value="positive">Positive</SelectItem>
                                                                            <SelectItem value="unverfied">Unverified</SelectItem>
                                                                            <SelectItem value="negative">Negative</SelectItem>
                                                                        </SelectGroup>
                                                                    </SelectContent>
                                                                </Select>
                                                            </div>
                                                            <div v-if="checkForm.errors.company_name || checkForm.errors.account_number || checkForm.errors.routing_number" class="h-5">
                                                                <InputError :message="checkForm.errors.routing_number" />
                                                            </div>
                                                        </div>

                                                        <!-- Customer Additional Information -->
                                                        <div class="grid gap-6">
                                                            <div class="grid gap-2">
                                                                <div class="flex gap-6">
                                                                    <!-- Customer Notes -->
                                                                    <div class="grid gap-2 w-full">
                                                                        <Label for="notes">Notes</Label>
                                                                        <Textarea 
                                                                            id="notes"
                                                                            class="resize-none"
                                                                            :tabindex="2"
                                                                            autofocus
                                                                            placeholder="Notes..."
                                                                            v-model="customerForm.notes"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                    <CardFooter>
                                            <Button type="submit" class="mt-4 w-full bg-[#038245] hover:bg-[#026234] text-white" :tabindex="4" :disabled="transactionForm.processing">
                                                <LoaderCircle v-if="transactionForm.processing" class="h-4 w-4 animate-spin" />
                                                View Check Information
                                            </Button>                                        
                                        </CardFooter>
                                </Card>
                            </form>
                            <div class="max-w-md w-full" v-show="checkForm.cashing_status">
                                <Alert id="cashing_status_alert" class="max-w-md w-full">
                                    <BadgeAlert class="h-4 w-4"/>
                                    <AlertTitle>Heads Up!</AlertTitle>
                                    <AlertDescription v-if="checkForm.cashing_status === 'negative'" class="max-w-md w-full">
                                        This check has been reported as a negative check.
                                    </AlertDescription>
                                    <AlertDescription v-if="checkForm.cashing_status === 'positive'">
                                        This check has been verified as a positive check.
                                    </AlertDescription>
                                    <AlertDescription v-if="checkForm.cashing_status === 'unverified'">
                                        This check has not been verified yet.
                                    </AlertDescription>
                                    </Alert>
                            </div>
                        </div>


                        <div class="grid gap-6 max-w-md w-full">
                            <form @submit.prevent="submit" class="flex flex-col gap-6 w-full max-w-md overflow-auto">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Transaction Details</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="grid gap-4 w-full">
                                            <div class="grid gap-2 w-full">
                                                <div class="flex gap-6">
                                                    <!-- Driver's License Picture -->
                                                    <div class="grid gap-2 w-full">
                                                        <Label for="dl_picture_link">Check Picture</Label>
                                                        <Input
                                                            id="dl_picture_link"
                                                            type="file"
                                                            autofocus
                                                            :tabindex="2"
                                                            @change="handleCheckPictureInput"
                                                            class="text-muted-foreground"
                                                        />
                                                        <div v-if="!checkPicturePreview" class="h-14">

                                                        </div>
                                                        <div v-if="checkPicturePreview || transactionForm.errors.check_picture_link" class="h-24">
                                                            <img v-if="checkPicturePreview" :src="checkPicturePreview" alt="" class="h-24 w-18" />
                                                            <InputError :message="transactionForm.errors.check_picture_link" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-6 w-full">
                                                <div class="grid gap-2 w-full">
                                                    <!-- Account Number -->
                                                    <div class="flex gap-2 w-full">
                                                        <div class="grid gap-2 w-full">
                                                            <Label for="check_number">Check Number</Label>
                                                            <Input
                                                                id="check_number"
                                                                type="text"
                                                                autofocus
                                                                :tabindex="1"
                                                                maxlength="12"
                                                                autocomplete="text"
                                                                v-model="transactionForm.check_number"
                                                                placeholder="Check Number..."
                                                            />
                                                            <div v-if="transactionForm.errors.check_number || transactionForm.errors.check_amount" class="h-8">
                                                                <InputError message="A check number is required." />
                                                            </div>
                                                        </div>

                                                        <!-- Routing Number -->
                                                        <div class="grid gap-2 w-full">
                                                            <Label for="check_amount">Check Amount</Label>
                                                            <Input
                                                                id="check_amount"
                                                                type="text"
                                                                autofocus
                                                                :tabindex="1"
                                                                maxlength="8"
                                                                autocomplete="text"
                                                                v-model="transactionForm.check_amount"
                                                                placeholder="Check Amount..."
                                                            />
                                                            <div v-if="transactionForm.errors.check_number || transactionForm.errors.check_amount" class="h-8">
                                                                <InputError message="A check amount is required." />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-2 w-full">
                                                <!-- Account Number -->
                                                <div class="grid gap-2 w-1/2">
                                                    <Label for="charge_percentage">Charge Percentage</Label>
                                                    <Input
                                                        id="charge_percentage"
                                                        type="text"
                                                        autofocus
                                                        :tabindex="1"
                                                        maxlength="5"
                                                        autocomplete="text"
                                                        v-model="transactionForm.charge_percentage"
                                                        placeholder="Charge Percentage..."
                                                    />
                                                    <div v-if="transactionForm.errors.check_number || transactionForm.errors.check_amount" class="h-8">
                                                        <InputError :message="transactionForm.errors.charge_percentage" />
                                                    </div>
                                                </div>

                                                <!-- Routing Number -->
                                                <div class="grid gap-2 w-1/2">
                                                    <Label for="charge_amount">Charge Amount</Label>
                                                    <Input
                                                        id="charge_amount"
                                                        type="text"
                                                        autofocus
                                                        :tabindex="1"
                                                        maxlength="8"
                                                        v-model="transactionForm.charge_amount"
                                                        placeholder="Charge Amount..."
                                                    />
                                                    <div v-if="transactionForm.errors.check_number || transactionForm.errors.check_amount" class="h-8">
                                                        <InputError :message="transactionForm.errors.charge_amount" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-6 w-full">
                                                <!-- Account Number -->
                                                <div class="flex gap-2 w-full items-center">
                                                    <div class="w-1/2">
                                                        <Label class="w-1/2 text-2xl font-bold">Payout Amount:</Label>
                                                    </div>
                                                    <div class="w-1/2">
                                                        <h1 id="payout_amount" class="text-4xl font-bold text-center"> ${{ transactionForm.payout_amount }} </h1>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                    <CardFooter>
                                        <Button type="submit" class="mt-4 w-full bg-[#038245] hover:bg-[#026234] text-white" :tabindex="4" :disabled="transactionForm.processing">
                                            <LoaderCircle v-if="transactionForm.processing" class="h-4 w-4 animate-spin" />
                                            Complete Transaction
                                        </Button>
                                    </CardFooter>
                                </Card>
                            </form>
                        </div>
                    </div>
                </TabsContent>
                <TabsContent value="transaction_history">
                    <div class="w-1/2">
                        <Card>
                            <CardHeader class="text-center">
                                <CardTitle v-if="customerForm.first_name !== ''"> {{ customerForm.first_name }} {{ customerForm.last_name }}'s Transaction History</CardTitle>
                                <CardTitle v-else>Transaction History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Table class="text-sm">
                                    <TableHeader class="text-md">
                                    <TableRow>
                                        <TableHead class="min-w-[115px] w-[115px] text-left">Date</TableHead>
                                        <TableHead class="text-center">Company Name</TableHead>
                                        <TableHead class="text-right">Check Amount</TableHead>
                                    </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                    <TableRow v-for="transaction in recentTransactions" :key="transaction.id">
                                        <TableCell class="text-left">{{ transaction.date }}</TableCell> 
                                        <TableCell class="text-center">{{ transaction.check.company_name }} </TableCell>
                                        <TableCell class="text-right">${{ transaction.check_amount }} </TableCell>
                                    </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
