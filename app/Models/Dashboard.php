<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnimalHealthRecord;
use App\Models\ProductionRecord;
use Illuminate\Support\Facades\DB;

class Dashboard extends Model
{
    use HasFactory;

   //livestock health chart
    public static function liveStockHealth()
    {
        // Fetch all health records
        $healthRecords = AnimalHealthRecord::all();
    
        // Initialize arrays to store data for the chart
        $timestamps = [];
        $statusCounts = [];
    
        // Iterate through health records and count occurrences
        foreach ($healthRecords as $record) {
            $date = $record->date;
    
            // Add the date to timestamps if not already present
            if (!in_array($date, $timestamps)) {
                $timestamps[] = $date;
            }
    
            // Initialize count for the status on the date
            if (!isset($statusCounts[$date])) {
                $statusCounts[$date] = [];
            }
    
            // Increment the count for the status on the date
            if (!isset($statusCounts[$date][$record->status])) {
                $statusCounts[$date][$record->status] = 0;
            }
    
            $statusCounts[$date][$record->status]++;
        }
    
        // Sort timestamps in ascending order
        sort($timestamps);
    
        // Prepare data for Chart.js
        $datasets = [];
        $uniqueStatuses = array_unique($healthRecords->pluck('status')->toArray());
    
        foreach ($uniqueStatuses as $status) {
            $data = [];
    
            foreach ($timestamps as $date) {
                $count = isset($statusCounts[$date][$status]) ? $statusCounts[$date][$status] : 0;
                $data[] = $count;
            }
    
            $datasets[] = [
                'label' => $status,
                'data' => $data,
                'fill' => false,
            ];
        }
    
        return view('livestock_health_chart', [
            'timestamps' => $timestamps,
            'datasets' => $datasets,
        ]);
    }
    
    //livestock breed chart
    public static function livestockBreed() 
    {
        $data = DB::table('locations')
            ->join('farms', 'locations.id', '=', 'farms.location_id')
            ->join('breed_farm', 'farms.id', '=', 'breed_farm.farm_id')
            ->join('breeds', 'breed_farm.breed_id', '=', 'breeds.id')
            ->select('locations.name as location_name', 'breeds.name as breed_name', DB::raw('COUNT(*) as breed_count'))
            ->groupBy('locations.name', 'breeds.name')
            ->get();
    
        return view('livestock_breed_chart', compact('data'));
    }

    //production metrics chart

    public static function productionMetrics()
    {
        $data = DB::table('production_records')
        ->join('farms', 'production_records.farm_id', '=', 'farms.id')
        ->join('breeds', 'production_records.breed_id', '=', 'breeds.id')
        ->select( 'breeds.name as breed_name', 'farms.name as farm_name', 'production_records.created_at as date', 'production_records.daily_weight_gain as weight')
        ->groupBy( 'breeds.name', 'farms.name', 'production_records.created_at', 'production_records.daily_weight_gain')
        ->get();
        
        return view('production_metrics_chart', compact('data'));
    }

    

    
}
