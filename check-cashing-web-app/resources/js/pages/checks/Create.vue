<script setup lang="ts">
/// <reference types="@types/google.maps" />
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea'
import { FormLabel } from '@/components/ui/form'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import InputError from '@/components/InputError.vue';
import { type CheckForm } from '@/types';
import { ref, onMounted } from 'vue';
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add Customer',
        href: '/customers',
    },
];

// Customer form parameters
const form = useForm<CheckForm>({
    company_name: '',
    company_address_street: '',
    company_address_city: '',
    company_address_state: '',
    company_address_zip: '',
    account_number: '',
    routing_number: '',
    type: undefined,
    cashing_status: undefined,
    notes: undefined,
});

// Submit form action
const submit = () => {
    form.post(route('checks.store'));
};

// Import Google Maps API and make connection
(g=>{
    //@ts-ignore
    var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>
        h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);
        e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));(a.nonce=m.querySelector("script[nonce]") as HTMLScriptElement | null)?.nonce||"";
        m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))
    })
    ({
        key: "",
        v: "",
});

// Google Autocomplete API suggestions
let suggestionsList;
// User address search query
let addressQuery;;
// Unique request id
let newestRequestId = 0;
// Show suggestions based on user behavior
const showSuggestions = ref<boolean>(false); 

// Initial request for suggestions
const request = {
    input: '',
    locationRestriction: { west: -91, north: 39, east: -89, south: 38 },
    origin: { lat: 38.552489559792825, lng: -90.29070638952501 },
    includedPrimaryTypes: ['street_address', 'apartment_building', 'apartment_complex', 'condominium_complex', 'housing_complex'],
    language: 'en-US',
    region: 'us',
};

// Initialize Google Autocomplete API
async function init() {
    await google.maps.importLibrary("places");
    suggestionsList = document.getElementById('suggestions_list');
    addressQuery = document.getElementById('company_address_street');
    refreshToken(request);
}

// Debounce function
function debounceAsync(fn, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}

// Make autocomplete request in a debounced manner
const debouncedMakeAutocompleteRequest = debounceAsync(makeAutocompleteRequest, 1000)

// Make autocomplete request
async function makeAutocompleteRequest(inputEvent) {
    // Reset elements and exit if an empty string is received.
    if (inputEvent.target.value == '') {
        suggestionsList.replaceChildren();
        return;
    }
    // Add the latest char sequence to the request.
    request.input = inputEvent.target.value;
    // To avoid race conditions, store the request ID and compare after the request.
    const requestId = ++newestRequestId;
    // Fetch autocomplete suggestions and show them in a list.
    const { suggestions } = await google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request);
    // If the request has been superseded by a newer request, do not render the output.
    if (requestId !== newestRequestId) {
        return;
    }

    // Clear the list first.
    suggestionsList.replaceChildren();

    // If there are suggestions, show them.
    showSuggestions.value = suggestions.length > 0;

    for (const suggestion of suggestions) {
        const placePrediction = suggestion.placePrediction;
        // Create a link for the place, add an event handler to fetch the place.
        const a = document.createElement('a');
        a.addEventListener('click', () => {
            onPlaceSelected(placePrediction.toPlace());
        });
        a.innerText = placePrediction.text.toString();
        a.classList.add('relative', 'flex', 'w-full', 'cursor-default', 'select-none', 'items-center', 'rounded-sm', 'py-1.5', 'pl-8', 'pr-2', 'text-sm', 
                        'outline-none', 'focus:bg-accent', 'focus:text-accent-foreground', 'data-[disabled]:pointer-events-none', 'data-[disabled]:opacity-50', 
                        'hover:bg-accent', 'hover:text-accent-foreground', 'hover:cursor-pointer');
        const li = document.createElement('li');
        li.appendChild(a);
        suggestionsList.appendChild(li);
    }
}
// Event handler for clicking on a suggested place.
async function onPlaceSelected(place) {
    await place.fetchFields({
        fields: ['formattedAddress'],
    });
    const addressComponents = place.formattedAddress.split(', ');
    const stateZip = addressComponents[2].split(' ');

    form.company_address_street = addressComponents[0];
    form.company_address_city = addressComponents[1];
    form.company_address_state = stateZip[0];
    form.company_address_zip = stateZip[1];

    suggestionsList.innerHTML = '';
    showSuggestions.value = false;
    refreshToken(request);
}
// Helper function to refresh the session token.
function refreshToken(request) {
    // Create a new session token and add it to the request.
    request.sessionToken = new google.maps.places.AutocompleteSessionToken();
}

// Hide address search results when user leaves the input.
const hideAddressSearchResults = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 500);
};

// On mount, initialize the autocomplete API.
onMounted(() => {
    init();
});

</script>

<template>
    <Head title="Add Check" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 items-center">
            <form @submit.prevent="submit" class="flex flex-col gap-6 w-full max-w-2xl overflow-auto">
                <Card class="min-w-full">
                    <CardHeader>
                    <CardTitle>Add Check</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6">
                            <div class="flex gap-6">
                                <!-- Check Data -->
                                <div class="grid gap-2 w-full">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Check Information</strong></h1> 
                                    </div>
                                    <div class="flex gap-6 w-full">
                                        <!-- Company Name -->
                                        <div class="grid gap-4 w-full">
                                            <div class="grid gap-2 w-full">
                                                <Label for="company_name">Company Name</Label>
                                                <Input
                                                    id="company_name"
                                                    type="text"
                                                    autofocus
                                                    :tabindex="1"
                                                    autocomplete="text"
                                                    v-model="form.company_name"
                                                    placeholder="Company Name..."
                                                />
                                                <div v-if="form.errors.company_name || form.errors.account_number || form.errors.routing_number" class="h-5">
                                                    <InputError :message="form.errors.company_name" />
                                                </div>
                                            </div>

                                            <div class="flex gap-6 w-full">
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
                                                        v-model="form.account_number"
                                                        placeholder="Driver's License Number..."
                                                    />
                                                    <div v-if="form.errors.company_name || form.errors.account_number || form.errors.routing_number" class="h-5">
                                                        <InputError :message="form.errors.account_number" />
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
                                                        v-model="form.routing_number"
                                                        placeholder="Driver's License State..."
                                                    />
                                                    <div v-if="form.errors.company_name || form.errors.account_number || form.errors.routing_number" class="h-5">
                                                        <InputError :message="form.errors.routing_number" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid gap-4">
                                            <div class="grid gap-2">
                                                <Label>Type</Label>
                                                <Select 
                                                    id="type" 
                                                    v-model="form.type"
                                                >
                                                    <SelectTrigger class="w-[180px]">
                                                        <SelectValue placeholder="Select check type" />
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
                                            <div v-if="form.errors.company_name || form.errors.account_number || form.errors.routing_number" class="h-5">
                                                <InputError :message="form.errors.routing_number" />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label>Status</Label>
                                                <Select 
                                                    id="cashing_status"
                                                    v-model="form.cashing_status"
                                                >
                                                    <SelectTrigger class="w-[180px]">
                                                        <SelectValue placeholder="Select cashing status" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Status</SelectLabel>
                                                            <SelectItem value="positive">Positive</SelectItem>
                                                            <SelectItem value="unverified">Unverified</SelectItem>
                                                            <SelectItem value="negative">Negative</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div v-if="form.errors.company_name || form.errors.account_number || form.errors.routing_number" class="h-5">
                                                <InputError :message="form.errors.routing_number" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Check Routing Information -->
                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Company Address</strong></h1> 
                                    </div>
                                    <div class="flex gap-6">
                                        <div class="grid gap-2 w-1/2 relative">
                                            <Label for="company_address_street">Street</Label>
                                            <Input
                                                id="company_address_street"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="text"
                                                @focus="showSuggestions = true"
                                                @blur="hideAddressSearchResults"
                                                v-model="form.company_address_street"
                                                placeholder="Street..."
                                                @input="debouncedMakeAutocompleteRequest"
                                            />
                                            <ul 
                                                v-show="showSuggestions" 
                                                id="suggestions_list" 
                                                class=" top-full absolute z-50 max-h-96 min-w-32 overflow-hidden rounded-md bg-popover text-popover-foreground 
                                                    shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 
                                                    data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 
                                                    data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',position === 'popper'&& 'data-[side=bottom]:translate-y-1 
                                                    data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',props.class,)">
                                            </ul>
                                            <div v-if ="form.errors.company_address_street || form.errors.company_address_city || form.errors.company_address_state || form.errors.company_address_zip" class="h-5">
                                                <InputError :message="form.errors.company_address_street" />
                                            </div>
                                        </div>

                                         <!-- City -->
                                        <div class="grid gap-2 w-1/6">
                                            <Label for="company_address_city">City</Label>
                                            <Input
                                                id="company_address_city"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="text"
                                                v-model="form.company_address_city"
                                                placeholder="City..."
                                            />
                                            <div v-if="form.errors.company_address_street || form.errors.company_address_city || form.errors.company_address_state || form.errors.company_address_zip" class="h-5">
                                                <InputError :message="form.errors.company_address_city" />
                                            </div>
                                        </div>

                                        <!-- State -->
                                        <div class="grid gap-2 w-1/6">
                                            <Label for="company_address_state">State</Label>
                                            <Input
                                                id="company_address_state"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="2"
                                                autocomplete="text"
                                                v-model="form.company_address_state"
                                                placeholder="State..."
                                            />
                                            <div v-if="form.errors.company_address_street || form.errors.company_address_city || form.errors.company_address_state || form.errors.company_address_zip" class="h-5">
                                                <InputError :message="form.errors.company_address_state" />
                                            </div>                                        
                                        </div>

                                        <!-- Zip Code-->
                                        <div class="grid gap-2 w-1/6">
                                            <Label for="company_address_zip">Zip</Label>
                                            <Input
                                                id="company_address_zip"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="5"
                                                autocomplete="text"
                                                v-model="form.company_address_zip"
                                                placeholder="Zip..."
                                            />
                                            <div v-if="form.errors.company_address_street || form.errors.company_address_city || form.errors.company_address_state || form.errors.company_address_zip" class="h-5">
                                                <InputError :message="form.errors.company_address_zip" />
                                            </div>
                                        </div> 
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <div class="grid gap-2 w-full">
                                        <div>
                                            <h1 class="text-xl font-medium"><strong class="text-gray-500">Additional Information</strong></h1> 
                                        </div>
                                        <div class="grid gap-6">
                                            <!-- Check Notes -->
                                            <div class="grid gap-2">
                                                <Label for="notes">Notes</Label>
                                                <Textarea 
                                                    id="notes"
                                                    class="resize-none h-32" 
                                                    placeholder="Notes..."
                                                    v-model="form.notes"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button type="submit" class="mt-4 w-full bg-[#038245] hover:bg-[#026234] text-white" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Add Check
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
