<?php

namespace App\Services;

use Illuminate\Support\Collection;

class OptionStrategyClassifier
{
    public function classify(Collection $positions): string
    {
        $legs = $positions
            ->filter(fn($position) => $position->instrument->asset_type === 'OPT')
            ->groupBy('instrument_id')
            ->map(function (Collection $positions) {
                $instrument = $positions->first()->instrument;

                return [
                    'symbol' => $instrument->underlying_symbol ?: $instrument->symbol,
                    'expiry' => $instrument->expiry?->format('Y-m-d'),
                    'strike' => (float) $instrument->strike,
                    'put_call' => $instrument->put_call,
                    'quantity' => $positions->sum(fn($position) => (float) $position->quantity),
                ];
            })
            ->values();

        if ($legs->count() < 2) {
            return 'Multi-leg Option Strategy';
        }

        if ($legs->count() === 2) {
            $first = $legs[0];
            $second = $legs[1];

            if ($first['put_call'] === $second['put_call']
                && $first['symbol'] === $second['symbol']
                && $first['expiry'] === $second['expiry']
                && $first['strike'] !== $second['strike']) {
                $lower = $first['strike'] < $second['strike'] ? $first : $second;
                $higher = $lower === $first ? $second : $first;
                $lowerIsLong = $lower['quantity'] > 0;
                $higherIsLong = $higher['quantity'] > 0;

                if ($lowerIsLong !== $higherIsLong) {
                    if ($lower['put_call'] === 'P') {
                        return $lowerIsLong ? 'Bull Put Spread' : 'Bear Put Spread';
                    }

                    return $lowerIsLong ? 'Bull Call Spread' : 'Bear Call Spread';
                }
            }

            if ($first['symbol'] === $second['symbol']
                && $first['put_call'] === $second['put_call']
                && $first['strike'] === $second['strike']
                && $first['expiry'] !== $second['expiry']) {
                return 'Calendar Spread';
            }

            if ($first['symbol'] === $second['symbol']
                && $first['expiry'] === $second['expiry']
                && $first['put_call'] !== $second['put_call']
                && ($first['quantity'] > 0) === ($second['quantity'] > 0)) {
                $isLong = $first['quantity'] > 0;
                $type = $first['strike'] === $second['strike'] ? 'Straddle' : 'Strangle';

                return ($isLong ? 'Long ' : 'Short ') . $type;
            }
        }

        if ($legs->count() === 3
            && $legs->pluck('put_call')->unique()->count() === 1
            && $legs->pluck('expiry')->unique()->count() === 1
            && $legs->pluck('symbol')->unique()->count() === 1) {
            $ordered = $legs->sortBy('strike')->values();
            if ($ordered->pluck('strike')->unique()->count() === 3
                && $ordered[0]['quantity'] * $ordered[1]['quantity'] < 0
                && $ordered[0]['quantity'] * $ordered[2]['quantity'] > 0) {
                return 'Butterfly Spread';
            }
        }

        if ($legs->count() === 4
            && $legs->pluck('put_call')->filter(fn($type) => $type === 'P')->count() === 2
            && $legs->pluck('put_call')->filter(fn($type) => $type === 'C')->count() === 2
            && $legs->pluck('expiry')->unique()->count() === 1) {
            $puts = $legs->where('put_call', 'P')->sortBy('strike')->values();
            $calls = $legs->where('put_call', 'C')->sortBy('strike')->values();
            $sameUnderlying = $legs->pluck('symbol')->unique()->count() === 1;
            $shortPutStrike = $puts[1]['strike'];
            $shortCallStrike = $calls[0]['strike'];
            $longWings = $puts[0]['quantity'] > 0
                && $puts[1]['quantity'] < 0
                && $calls[0]['quantity'] < 0
                && $calls[1]['quantity'] > 0;

            if ($sameUnderlying
                && $longWings
                && $shortPutStrike === $shortCallStrike) {
                return 'Iron Butterfly';
            }

            if ($sameUnderlying
                && $longWings
                && $shortPutStrike < $shortCallStrike) {
                return 'Iron Condor';
            }
        }

        return 'Multi-leg Option Strategy';
    }
}
