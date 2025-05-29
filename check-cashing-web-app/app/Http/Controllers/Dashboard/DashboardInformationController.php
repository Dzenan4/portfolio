<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Check;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardInformationController extends Controller
{
    // Accessing payout information for day, month, week, and year
    public function getDailyPayout() {
        $dailyPayout = Transaction::where('date', Carbon::today())->sum('payout_amount');
        return round($dailyPayout, 2);
    }

    public function getWeeklyPayout() {
        $startOfWeek = Carbon::today()->startOfWeek();
        $weeklyPayout = Transaction::where('date', '>=', $startOfWeek)->sum('payout_amount');
        return round($weeklyPayout, 2);
    }

    public function getMonthlyPayout() {
        $startOfMonth = Carbon::today()->startOfMonth();
        $monthlyPayout = Transaction::where('date', '>=', $startOfMonth)->sum('payout_amount');
        return round($monthlyPayout, 2);
    }
    
    public function getYearlyPayout() {
        $startOfYear = Carbon::today()->startOfYear();
        $yearlyPayout = Transaction::where('date', '>=', $startOfYear)->sum('payout_amount');
        return round($yearlyPayout, 2);
    }

    public function getDailyEarnings() {
        $dailyEarnings = Transaction::where('date', Carbon::today())->sum('charge_amount');
        return round($dailyEarnings, 2);
    }

    public function getWeeklyEarnings() {
        $startOfWeek = Carbon::today()->startOfWeek();
        $weeklyEarnings = Transaction::where('date', '>=', $startOfWeek)->sum('charge_amount');
        return round($weeklyEarnings, 2);
    }

    public function getMonthlyEarnings() {
        $startOfMonth = Carbon::today()->startOfMonth();
        $monthlyEarnings = Transaction::where('date', '>=', $startOfMonth)->sum('charge_amount');
        return round($monthlyEarnings, 2);
    }

    public function getYearlyEarnings() {
        $startOfYear = Carbon::today()->startOfYear();
        $yearlyEarnings = Transaction::where('date', '>=', $startOfYear)->sum('charge_amount');
        return round($yearlyEarnings, 2);
    }

    public function getRecentTransactions() {
        $recentTransactions = Transaction::with(['customer:id,first_name,last_name', 'check:id,company_name'])->orderBy('date', 'desc')->take(20)->get();

        return $recentTransactions;
    }
}
