<?php

use App\Models\Instrument;
use App\Models\Position;
use App\Services\OptionStrategyClassifier;

test('it identifies vertical option spread names from their strikes and sides', function (
    string $putCall,
    float $lowerQuantity,
    float $higherQuantity,
    string $expected
) {
    $positions = collect([
        makeOptionPosition(1, 100, $putCall, $lowerQuantity),
        makeOptionPosition(2, 105, $putCall, $higherQuantity),
    ]);

    expect((new OptionStrategyClassifier())->classify($positions))->toBe($expected);
})->with([
    'bull call spread' => ['C', 1, -1, 'Bull Call Spread'],
    'bear call spread' => ['C', -1, 1, 'Bear Call Spread'],
    'bull put spread' => ['P', 1, -1, 'Bull Put Spread'],
    'bear put spread' => ['P', -1, 1, 'Bear Put Spread'],
]);

function makeOptionPosition(int $instrumentId, float $strike, string $putCall, float $quantity): Position
{
    $position = new Position([
        'instrument_id' => $instrumentId,
        'quantity' => $quantity,
    ]);
    $position->setRelation('instrument', new Instrument([
        'symbol' => 'SPXW',
        'underlying_symbol' => 'SPXW',
        'asset_type' => 'OPT',
        'expiry' => '2026-10-02',
        'strike' => $strike,
        'put_call' => $putCall,
    ]));

    return $position;
}
