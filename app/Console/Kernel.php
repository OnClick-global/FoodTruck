<?php

namespace App\Console;

use App\Console\Commands\MonthelyReport;
use App\Models\MonthlyReport;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        MonthelyReport::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $resturants = Restaurant::select('id', 'Profit_Ratio')->get();
        foreach ($resturants as $key => $resturant) {
            $totalAmount = Order::where('restaurant_id', $resturant['id'])->sum('order_amount');
            $monthly_percentage = $resturant['Profit_Ratio'];
            $compuny_amount = $totalAmount * (100 / $monthly_percentage);
            $restaurant_amount = $totalAmount - $compuny_amount;

            $data['restaurant_id'] = $resturant->id ;
            $data['monthly_percentage'] = $monthly_percentage;
            $data['Total_amount'] = $totalAmount ;
            $data['client_amount'] =  $restaurant_amount ;
            $data['Company_amount'] = $compuny_amount ;
            $data['withdraw_status'] = '0';
            MonthlyReport::create($data);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
