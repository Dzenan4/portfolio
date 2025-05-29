<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import axios from 'axios';
import { ref } from 'vue';
import { List, User } from 'lucide-vue-next';
import Autoplay from 'embla-carousel-autoplay';
import { Card, CardTitle, CardContent } from '@/components/ui/card';
import { Carousel, CarouselContent, CarouselItem } from '@/components/ui/carousel';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';
import { UserPlus } from 'lucide-vue-next';
import { Banknote, HandCoins } from 'lucide-vue-next';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const plugin = Autoplay({
    delay: 5000,
    stopOnMouseEnter: true,
    stopOnInteraction: false,
})

function financialInfo(type, payout, earnings) {
    this.type = type;
    this.payout = payout;
    this.earnings = earnings;
}

const recentTransactions = ref([]);
const financialInfoData = ref([]);

function addCustomer() {
    router.visit(route('customers.create'));
}

function addCheck() {
    router.visit(route('checks.create'));
}

function checkPayment() {
    router.visit(route('transactions.index'));
}

const fetchDashboardData = async () => {
    try {
        const [
            dailyEarningsData,
            weeklyEarningsData, 
            monthlyEarningsData, 
            yearlyEarningsData, 
            dailyPayoutData,
            weeklyPayoutData, 
            monthlyPayoutData, 
            yearlyPayoutData,
            recentTransactionsData
        ] = await Promise.all([
            axios.get('/dashboard/getDailyEarnings'), // Earnings information for day, week, month, and year
            axios.get('/dashboard/getWeeklyEarnings'),
            axios.get('/dashboard/getMonthlyEarnings'),
            axios.get('/dashboard/getYearlyEarnings'),
            axios.get('/dashboard/getDailyPayout'), // Payout information for day, week, month, and year
            axios.get('/dashboard/getWeeklyPayout'),
            axios.get('/dashboard/getMonthlyPayout'),
            axios.get('/dashboard/getYearlyPayout'),
            axios.get('/dashboard/getRecentTransactions'),
        ]);
    

        financialInfoData.value = [
            new financialInfo('Daily', dailyPayoutData.data, dailyEarningsData.data),
            new financialInfo('Weekly', weeklyPayoutData.data, weeklyEarningsData.data),
            new financialInfo('Monthly', monthlyPayoutData.data, monthlyEarningsData.data),
            new financialInfo('Yearly', yearlyPayoutData.data, yearlyEarningsData.data),
        ];

        recentTransactions.value = recentTransactionsData.data;
        console.log(recentTransactions.value);
    } catch (error) {
        console.error(error);
    }
};

fetchDashboardData();

</script>

<template>
    <Head title="Dashboard"></Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full w-full rounded-xl p-6 justify-center">
            <div class="max-w-sm w-full justify-center">
                <Carousel 
                    class="relative w-full max-w-sm justify-center" 
                    :plugins="[plugin]" 
                    @mouseenter="plugin.stop()" 
                    @mouseleave="[plugin.reset(), plugin.play()];"
                >
                    <CarouselContent>
                        <CarouselItem v-for="info in financialInfoData" :key="info.type">
                            <Card class="h-full w-5/6">
                                <CardContent class="grid items-center gap-2">
                                    <h1 class="mt-5 text-3xl font-bold text-center mb-5">{{ info.type }} Revenue</h1>
                                    <hr class="border-[#038245] border-2">
                                    <div class="grid items-center gap-2 mt-5 mb-5">
                                        <span class="text-2xl text-center font-bold">Total Payout:</span>
                                        <span class="text-4xl text-center">${{ info.payout }}</span>
                                    </div>
                                    <hr class="border-[#038245] border-2">
                                    <div class="grid items-center gap-2 mt-5 mb-5">
                                        <span class="text-2xl text-center font-bold">Total Earnings:</span>
                                        <span class="text-4xl text-center">${{ info.earnings }}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        </CarouselItem>
                    </CarouselContent>
                </Carousel>

                <Button @click="checkPayment" class="mt-5 mb-2 w-5/6 bg-[#038245] text-white hover:bg-[#026234]">
                    <HandCoins class="w-4 h-4 mr-2"></HandCoins>
                    Check Payment
                </Button>

                <Button @click="addCustomer" class="mt-2 mb-2 w-5/6 bg-[#038245] text-white hover:bg-[#026234]">
                    <UserPlus class="size-24 mr-2"></UserPlus>
                    Add Customer
                </Button>

                <Button @click="addCheck" class="mt-2 mb-2 w-5/6 bg-[#038245] text-white hover:bg-[#026234]">
                    <Banknote class="w-4 h-4 mr-2"></Banknote>
                    Add Check
                </Button>

            </div>
            <div>
                <Card class="h-full w-full min-w-[1000px]">
                    <CardTitle class="text-3xl text-center mt-5 mb-5">Recent Transactions</CardTitle>
                    <CardContent>
                        <Table class="text-sm">
                            <TableHeader class="text-md">
                            <TableRow>
                                <TableHead class="min-w-[115px] w-[115px] text-left">Date</TableHead>
                                <TableHead class="text-center">Customer</TableHead>
                                <TableHead class="text-center">Check</TableHead>
                                <TableHead class="min-w-[90px] max-w-[95px] text-right">Check Amount</TableHead>
                            </TableRow>
                            </TableHeader>
                            <TableBody>
                            <TableRow v-for="transaction in recentTransactions" :key="transaction.id">
                                <TableCell class="text-left">{{ transaction.date }}</TableCell> 
                                <TableCell class="text-center">{{ transaction.customer.first_name }} {{ transaction.customer.last_name }} </TableCell>
                                <TableCell class="text-center">{{ transaction.check.company_name }} </TableCell>
                                <TableCell class="text-right">${{ transaction.check_amount }} </TableCell>
                            </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
