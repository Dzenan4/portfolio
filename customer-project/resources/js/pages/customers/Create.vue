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
import InputError from '@/components/InputError.vue';
import { type CustomerForm } from '@/types';
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
const form = useForm<CustomerForm>({
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

// Submit form action
const submit = () => {
    form.post(route('customers.store'));
};

// Drivers license and self picture preview
const driversLicensePicturePreview = ref<string | null>(null);
const selfPicturePreview = ref<string | null>(null);

// Handle drivers license picture input
const handleDriversLicensePictureInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.dl_picture_link = file;
        driversLicensePicturePreview.value = URL.createObjectURL(file);
    }
}

// Handle self picture input
const handleSelfPictureInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.self_picture_link = file;
        selfPicturePreview.value = URL.createObjectURL(file);
    }
}

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
    addressQuery = document.getElementById('address_street');
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

    form.address_street = addressComponents[0];
    form.address_city = addressComponents[1];
    form.address_state = stateZip[0];
    form.address_zip = stateZip[1];

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
    <Head title="Add Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 items-center">
            <form @submit.prevent="submit" class="flex flex-col gap-6 w-full max-w-6xl overflow-auto">
                <Card class="min-w-full">
                    <CardHeader>
                    <CardTitle>Add Customer</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6">
                            <div class="flex gap-6">
                                <!-- Customer Name Data -->
                                <div class="grid gap-2 w-3/5">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Name</strong></h1> 
                                    </div>
                                    <div class="flex gap-6 w-full">
                                        <!-- First Name -->
                                        <div class="grid gap-2 w-2/5">
                                            <Label for="first_name">First Name</Label>
                                            <Input
                                                id="first_name"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="text"
                                                v-model="form.first_name"
                                                placeholder="First name..."
                                            />
                                            <div v-if="form.errors.first_name || form.errors.last_name" class="h-5">
                                                <InputError :message="form.errors.first_name" />
                                            </div>
                                        </div>

                                        <!-- Middle Initial -->
                                        <div class="grid gap-2">
                                            <Label for="middle_initial">M.I.</Label>
                                            <Input
                                                id="middle_initial"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="1"
                                                autocomplete="text"
                                                v-model="form.middle_initial"
                                                placeholder="Middle initial..."
                                            />
                                            <div v-if="form.errors.first_name || form.errors.last_name" class="h-5">
                                                <InputError :message="form.errors.middle_initial" />
                                            </div>                                        
                                        </div>

                                        <!-- Last Name -->
                                        <div class="grid gap-2 w-2/5">
                                            <Label for="last_name">Last Name</Label>
                                            <Input
                                                id="last_name"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="text"
                                                v-model="form.last_name"
                                                placeholder="Last name..."
                                            />
                                            <div v-if="form.errors.first_name || form.errors.last_name" class="h-5">
                                                <InputError :message="form.errors.last_name" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Contact Information -->
                                <div class="grid gap-2 w-1/5">
                                    <!-- Phone Number -->
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Contact</strong></h1> 
                                    </div>
                                    <div class="flex gap-6">
                                        <div class="grid gap-2 w-full">
                                            <Label for="phone_number">Phone Number</Label>
                                                <Input
                                                    id="phone_number"
                                                    type="text"
                                                    autofocus
                                                    :tabindex="1"
                                                    maxlength="10"
                                                    autocomplete="text"
                                                    v-model="form.phone_number"
                                                    placeholder="Phone Number..."
                                                />
                                                <div v-if="form.errors.first_name || form.errors.last_name" class="h-5">
                                                    <InputError :message="form.errors.phone_number" />
                                                </div>
                                        </div> 
                                    </div>
                                </div>

                                <!-- Customer Date of Birth -->
                                <div class="grid gap-2 w-1/5">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Date of Birth</strong></h1> 
                                    </div>
                                    <div class="flex gap-6">
                                        <div class="grid gap-2 w-full">
                                            <!-- Date of Birth -->
                                            <Label for="dob">Date of Birth</Label>
                                            <Input
                                                id="dob"
                                                type="date"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="8"
                                                autocomplete="text"
                                                v-model="form.dob"
                                                placeholder="Date of Birth..."
                                            />
                                            <div v-if="form.errors.first_name || form.errors.last_name" class="h-5">
                                                <InputError :message="form.errors.dob" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <!-- Customer Address Information -->
                            <div class="flex gap-6">
                                <div class="grid gap-2 w-3/5">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Address</strong></h1> 
                                    </div>
                                    <div class="flex gap-6 w-full">
                                        <!-- Street Address -->
                                        <div class="grid gap-2 w-1/2 relative">
                                            <Label for="address_street">Street</Label>
                                            <Input
                                                id="address_street"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="text"
                                                @focus="showSuggestions = true"
                                                @blur="hideAddressSearchResults"
                                                v-model="form.address_street"
                                                placeholder="Street..."
                                                @input="debouncedMakeAutocompleteRequest"
                                            />
                                            <ul 
                                                v-show="showSuggestions" 
                                                id="suggestions_list" 
                                                class="top-full absolute z-50 max-h-96 min-w-32 overflow-hidden rounded-md border bg-popover text-popover-foreground 
                                                    shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 
                                                    data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 
                                                    data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',position === 'popper'&& 'data-[side=bottom]:translate-y-1 
                                                    data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',props.class,)">
                                            </ul>
                                            <div v-if="form.errors.address_street" class="h-5">
                                                <InputError message="The customer's address is required." />
                                            </div>
                                        </div>

                                         <!--  -->
                                        <div class="grid gap-2 w-1/6">
                                            <Label for="address_city">City</Label>
                                            <Input
                                                id="address_city"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="text"
                                                v-model="form.address_city"
                                                placeholder="City..."
                                            />
                                            <div v-if="form.errors.address_street" class="h-5">
                                            </div>
                                        </div>

                                        <!-- State -->
                                        <div class="grid gap-2 w-1/6">
                                            <Label for="address_state">State</Label>
                                            <Input
                                                id="address_state"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="2"
                                                autocomplete="text"
                                                v-model="form.address_state"
                                                placeholder="State..."
                                            />
                                            <div v-if="form.errors.address_street" class="h-5">
                                                <!-- <InputError :message="form.errors.address_state" /> -->
                                            </div>                                        
                                        </div>

                                        <!-- Zip Code-->
                                        <div class="grid gap-2 w-1/6">
                                            <Label for="address_zip">Zip</Label>
                                            <Input
                                                id="address_zip"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="5"
                                                autocomplete="text"
                                                v-model="form.address_zip"
                                                placeholder="Zip..."
                                            />
                                            <div v-if="form.errors.address_street" class="h-5">
                                                <!-- <InputError :message="form.errors.address_zip" /> -->
                                            </div>
                                        </div> 
                                    </div>
                                </div>

                                <!-- Customer Driver's License -->
                                <div class="grid gap-2 w-1/5">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Driver's License</strong></h1> 
                                    </div>
                                    <div class="flex gap-6">
                                        <!-- Driver's License Number -->
                                        <div class="grid gap-2 w-full">
                                            <Label for="dl_number">D.L. Number</Label>
                                            <Input
                                                id="dl_number"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="20"
                                                autocomplete="text"
                                                v-model="form.dl_number"
                                                placeholder="D.L. Number..."
                                            />
                                            <div v-if="form.errors.address_street || form.errors.dl_number" class="h-5">
                                                <InputError :message="form.errors.dl_number" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-2 w-1/5">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500"><br></strong></h1> 
                                    </div>
                                    <div class="flex gap-6">
                                        <!-- Driver's License State -->
                                        <div class="grid gap-2 w-full">
                                            <Label for="dl_state">D.L. State</Label>
                                            <Input
                                                id="dl_state"
                                                type="text"
                                                autofocus
                                                :tabindex="1"
                                                maxlength="2"
                                                autocomplete="text"
                                                v-model="form.dl_state"
                                                placeholder="D.L. State..."
                                            />
                                            <div v-if="form.errors.address_street || form.errors.dl_state" class="h-5">
                                                <InputError :message="form.errors.dl_state" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Driver's License -->
                            <div class="grid gap-6">
                                <!-- Customer Picture + Driver's License Picture -->
                                <div class="flex gap-6">
                                    <div class="grid gap-2 w-1/2">
                                        <div>
                                            <h1 class="text-xl font-medium"><strong class="text-gray-500">Driver's License Picture</strong></h1> 
                                        </div>
                                        <div class="flex gap-6">
                                            <!-- Driver's License Picture -->
                                            <div class="grid gap-2 w-full">
                                                <Label for="dl_picture_link">D.L. Picture</Label>
                                                <Input
                                                    id="dl_picture_link"
                                                    type="file"
                                                    autofocus
                                                    :tabindex="2"
                                                    @change="handleDriversLicensePictureInput"
                                                    class="text-muted-foreground"
                                                />
                                                <div v-if="driversLicensePicturePreview || form.errors.dl_picture_link || selfPicturePreview || form.errors.self_picture_link" class="h-24">
                                                    <img v-if="driversLicensePicturePreview" :src="driversLicensePicturePreview" alt="" class="h-24 w-18" />
                                                    <InputError :message="form.errors.dl_picture_link" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Picture -->
                                    <div class="grid gap-2 w-1/2">
                                        <div>
                                            <h1 class="text-xl font-medium"><strong class="text-gray-500">Customer Picture</strong></h1> 
                                        </div>
                                        <div class="flex gap-6">
                                            <div class="grid gap-2 w-full">
                                                <Label for="self_picture_link">Customer Picture</Label>
                                                <Input
                                                    id="self_picture_link"
                                                    type="file"
                                                    autofocus
                                                    :tabindex="2"
                                                    @change="handleSelfPictureInput"
                                                    class="text-muted-foreground"
                                                />
                                                <div v-if="selfPicturePreview || form.errors.self_picture_link || driversLicensePicturePreview || form.errors.dl_picture_link" class="h-24">
                                                    <img v-if="selfPicturePreview" :src="selfPicturePreview" alt="" class="h-24 w-18" />
                                                    <InputError :message="form.errors.self_picture_link" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Additional Information -->
                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <div>
                                        <h1 class="text-xl font-medium"><strong class="text-gray-500">Additional Information</strong></h1> 
                                    </div>
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
                                                v-model="form.notes"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button type="submit" class="mt-4 w-full bg-[#038245] hover:bg-[#026234] text-white" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Add Customer
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
