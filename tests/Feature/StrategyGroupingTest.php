<?php

use App\Models\Instrument;
use App\Models\Position;
use App\Models\TradeImportLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->artisan('migrate:fresh', ['--force' => true])->assertExitCode(0);
    $this->withoutVite();
    config(['app.key' => 'base64:MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY=']);
});

test('csv imports group fills by their brokerage order id', function () {
    $user = User::factory()->create();
    $headers = [
        'Symbol',
        'AssetClass',
        'UnderlyingSymbol',
        'CurrencyPrimary',
        'Expiry',
        'Strike',
        'Put/Call',
        'Multiplier',
        'TradeID',
        'DateTime',
        'Buy/Sell',
        'Quantity',
        'TradePrice',
        'IBCommission',
        'BrokerageOrderID',
    ];
    $rows = [
        ['SPXW261002C07750000', 'OPT', 'SPXW', 'USD', '2026-10-02', '7750', 'C', '100', 'exec-1', '2026-10-01 10:00:00', 'BUY', '1', '10', '1', 'combo-42'],
        ['SPXW261002C07760000', 'OPT', 'SPXW', 'USD', '2026-10-02', '7760', 'C', '100', 'exec-2', '2026-10-01 10:00:00', 'SELL', '-1', '5', '1', 'combo-42'],
        ['SPY', 'STK', 'SPY', 'USD', '', '', '', '1', 'exec-3', '2026-10-01 10:01:00', 'BUY', '10', '500', '0.5', 'stock-order'],
        ['SPY', 'STK', 'SPY', 'USD', '', '', '', '1', 'exec-4', '2026-10-01 10:02:00', 'SELL', '-10', '501', '0.5', 'stock-order'],
    ];

    $makeCsv = function (array $columns, array $records): string {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $columns, ',', '"', '');
        foreach ($records as $record) {
            fputcsv($stream, $record, ',', '"', '');
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    };

    $this->actingAs($user)->post(route('trades.store'), [
        'broker' => 'interactive_broker',
        'csv_file' => UploadedFile::fake()->createWithContent(
            'trades.csv',
            $makeCsv(array_slice($headers, 0, -1), array_map(fn($row) => array_slice($row, 0, -1), $rows))
        ),
    ])->assertRedirect(route('trades'));

    $this->assertDatabaseCount('fills', 4);
    $this->assertDatabaseMissing('fills', ['brokerage_order_id' => 'combo-42']);

    $this->post(route('trades.store'), [
        'broker' => 'interactive_broker',
        'csv_file' => UploadedFile::fake()->createWithContent(
            'trades.csv',
            $makeCsv($headers, $rows)
        ),
    ])->assertRedirect(route('trades'));

    $this->assertDatabaseCount('fills', 4);
    $this->assertDatabaseHas('fills', ['exec_id' => 'exec-1', 'brokerage_order_id' => 'combo-42']);
    $this->assertDatabaseCount('positions', 3);
    $this->assertDatabaseHas('positions', ['brokerage_order_id' => 'combo-42']);

    $this->assertDatabaseHas('trade_import_logs', [
        'user_id' => $user->id,
        'source' => 'csv',
        'status' => 'success',
        'file_name' => 'trades.csv',
    ]);

    $this->get(route('trades.strategy', 'combo-42'))
        ->assertOk()
        ->assertSee('combo-42')
        ->assertSee('SPXW261002C07750000')
        ->assertSee('SPXW261002C07760000');

    $this->get(route('trades'))
        ->assertOk()
        ->assertSee('Bull Call Spread')
        ->assertSee('Close time')
        ->assertSee('Position')
        ->assertSee('SPREAD')
        ->assertSee('OPT')
        ->assertSee('CALL')
        ->assertDontSee('OPT · CALL')
        ->assertSee('1 contract')
        ->assertSee('+1 7750 Call')
        ->assertSee('-1 7760 Call')
        ->assertSee('10:01:00')
        ->assertSee('10:02:00')
        ->assertSee('10 shares')
        ->assertSee('STK');

    $this->get(route('trades') . '?put_call=STRATEGY')
        ->assertOk()
        ->assertSee('Bull Call Spread')
        ->assertDontSee('10 shares');

    $this->get(route('trades') . '?put_call=CALL')
        ->assertOk()
        ->assertSee('Bull Call Spread');

    $this->get(route('trade-import-logs.index'))
        ->assertOk()
        ->assertSee('trades.csv')
        ->assertSee('Successful');
});

test('invalid CSV upload displays and records a user-visible error', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('trades.store'), ['broker' => 'interactive_broker'])
        ->assertRedirect()
        ->assertSessionHasErrors('csv_file')
        ->assertSessionHas('error');

    $this->assertDatabaseHas('trade_import_logs', [
        'user_id' => $user->id,
        'source' => 'csv',
        'status' => 'error',
    ]);

    $this->get(route('trade-import-logs.index', ['status' => 'error']))
        ->assertOk()
        ->assertSee('The csv file field is required.');
});

test('import log page only shows the signed-in user logs', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    TradeImportLog::create([
        'user_id' => $user->id,
        'source' => 'csv',
        'status' => 'error',
        'message' => 'My import failed.',
    ]);
    TradeImportLog::create([
        'user_id' => $otherUser->id,
        'source' => 'csv',
        'status' => 'error',
        'message' => 'Another user import failed.',
    ]);

    $this->actingAs($user)
        ->get(route('trade-import-logs.index'))
        ->assertOk()
        ->assertSee('My import failed.')
        ->assertDontSee('Another user import failed.');
});

test('dashboard analytics count shared order positions as one strategy', function () {
    $user = User::factory()->create();

    foreach ([
        ['SPXW261002C07750000', 100],
        ['SPXW261002C07760000', -50],
    ] as [$symbol, $pnl]) {
        $instrument = Instrument::create([
            'user_id' => $user->id,
            'symbol' => $symbol,
            'underlying_symbol' => 'SPXW',
            'asset_type' => 'OPT',
            'expiry' => '2026-10-02',
            'strike' => 7750,
            'put_call' => 'C',
            'multiplier' => 100,
            'currency' => 'USD',
        ]);

        Position::create([
            'instrument_id' => $instrument->id,
            'brokerage_order_id' => 'combo-42',
            'open_datetime' => '2026-10-01 10:00:00',
            'close_datetime' => '2026-10-01 11:00:00',
            'quantity' => 1,
            'cost_basis' => 1000,
            'realized_pnl' => $pnl,
        ]);
    }

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('totalTrades', 1)
        ->assertViewHas('winningTrades', 1)
        ->assertViewHas('losingTrades', 0);
});
