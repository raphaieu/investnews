<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MarketSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_date',
        'open_time',
        'close_time',
        'market_status',
        'reason',
        'description',
        'timezone',
        'is_dst',
        'is_manual',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'open_time'     => 'datetime:H:i:s',
        'close_time'    => 'datetime:H:i:s',
        'is_dst'        => 'boolean',
        'is_manual'     => 'boolean',
    ];

    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('schedule_date', '>=', Carbon::today());
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('market_status', 'open');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('market_status', 'closed');
    }

    public static function isMarketOpen(): bool
    {
        $now = Carbon::now('America/Sao_Paulo');
        $schedule = self::where('schedule_date', $now->toDateString())->first();

        if (!$schedule) {
            return self::isDefaultMarketOpen($now);
        }

        if ($schedule->market_status === 'closed') {
            return false;
        }

        $current = $now->toTimeString();
        return $current >= $schedule->open_time->format('H:i:s')
            && $current <= $schedule->close_time->format('H:i:s');
    }

    public static function willBeOpenAt(Carbon $datetime): bool
    {
        $schedule = self::where('schedule_date', $datetime->toDateString())->first();

        if (!$schedule) {
            return self::isDefaultMarketOpen($datetime);
        }

        if ($schedule->market_status === 'closed') {
            return false;
        }

        $time = $datetime->toTimeString();
        return $time >= $schedule->open_time->format('H:i:s')
            && $time <= $schedule->close_time->format('H:i:s');
    }

    private static function isDefaultMarketOpen(Carbon $datetime): bool
    {
        if ($datetime->isWeekend()) {
            return false;
        }

        $hour = $datetime->hour;
        return $hour >= 9 && $hour < 18;
    }

    public static function addHoliday(Carbon $date, string $description = null): void
    {
        self::updateOrCreate(
            ['schedule_date' => $date],
            [
                'open_time'     => '00:00:00',
                'close_time'    => '00:00:00',
                'market_status' => 'closed',
                'reason'        => 'feriado',
                'description'   => $description ?? 'Feriado',
                'timezone'      => 'America/Sao_Paulo',
                'is_dst'        => $date->isDST(),
                'is_manual'     => true,
            ]
        );
    }

    public static function addHalfDay(Carbon $date, string $closeTime = '13:00:00', string $description = null): void
    {
        self::updateOrCreate(
            ['schedule_date' => $date],
            [
                'open_time'     => '09:00:00',
                'close_time'    => $closeTime,
                'market_status' => 'half_day',
                'reason'        => 'meio_expediente',
                'description'   => $description ?? 'Meio expediente',
                'timezone'      => 'America/Sao_Paulo',
                'is_dst'        => $date->isDST(),
                'is_manual'     => true,
            ]
        );
    }
}
