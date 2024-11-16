<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class reportcontroller extends Controller


{
    //
    public function index(){

        return view("report.index");
    }

    public function generateReport(Request $request) {
        $deviceType = $request->device_type;
        $timeRange = $request->time_range;

        // Mengatur interval waktu berdasarkan pilihan pengguna
        switch($timeRange) {
            case '1 Hari':
                $interval = '1 DAY';
                break;
            case '1 Minggu':
                $interval = '1 WEEK';
                break;
            case '1 Bulan':
                $interval = '1 MONTH';
                break;
            case '3 Bulan':
                $interval = '3 MONTH';
                break;
            case '6 Bulan':
                $interval = '6 MONTH';
                break;
            default:
                $interval = '1 WEEK';
        }

        $tableName = '';
        $selectColumns = '';

        if ($deviceType == 'hydroponik') {
            $tableName = 'hydroponik_sensor_datas';
            $selectColumns = '
                AVG(ph_air) as avg_ph,
                AVG(tds) as avg_tds,
                AVG(laju_air) as avg_laju,
                AVG(volume_air) as avg_volume_air,
                AVG(suhu_air) as avg_suhu_air,
                AVG(panel_temp) as avg_panel';
        } elseif ($deviceType == 'aeroponik') {
            $tableName = 'aeroponik_sensor_datas';
            $selectColumns = '
                ROUND(AVG(suhu_air),2) as avg_suhu,
                AVG(ph_air) as avg_ph,
                AVG(tds) as avg_tds,
                AVG(kelembaban_udara) as avg_kelembaban,
                AVG(volume_air) as avg_volume_air,
                AVG(panel_temp) as avg_panel';
        } elseif ($deviceType == 'greenhouse') {
            $tableName = 'greenhouse_sensor_datas';
            $selectColumns = '
                AVG(suhu_avg) as avg_temperature,
                AVG(kelembaban_avg) as avg_humidity,
                AVG(co2) as avg_co2,
                AVG(intensitas) as avg_light_intensity,
                AVG(konsumsi_air) as avg_water_consumption,
                AVG(panel_temp) as avg_panel';
        }

        if (empty($tableName) || empty($selectColumns)) {
            toast('Data '.$deviceType.' Tidak Ditemukan', 'error');
            return redirect()->back();
        }

        $data = DB::table($tableName)
            ->select(DB::raw("$selectColumns, DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_block"))
            ->whereRaw("created_at >= NOW() - INTERVAL $interval")
            ->groupBy('hour_block')
            ->orderBy('hour_block', 'ASC')
            ->get();

        if ($data->isEmpty()) {
            toast('Data '.$deviceType.' Waktu '.$timeRange.' Tidak Ditemukan', 'error');
            return redirect()->back();
        }

        // Mengirimkan data ke view jika diperlukan
        return view('report.table', compact('data','deviceType'));
    }

}
