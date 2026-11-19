<?php

declare(strict_types=1);

use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Liberu\Accounting\FinancialStatementsFilament\FinancialStatementsFilamentPlugin;
use Liberu\Accounting\FinancialStatementsFilament\FinancialStatementsFilamentServiceProvider;

it('registers and configures accounting-financial-statements-filament filament resources', function (): void {
    $provider = $this->app->register(FinancialStatementsFilamentServiceProvider::class, force: true);
    if (method_exists($provider, 'boot')) { $provider->boot(); }
    expect($provider)->toBeInstanceOf(FinancialStatementsFilamentServiceProvider::class);
    $plugin = FinancialStatementsFilamentPlugin::make();
    $panel = Panel::make()->id('test-accounting-financial-statements-filament');
    $plugin->register($panel);
    $plugin->boot($panel);
    expect($plugin->getId())->toBeString()->not->toBeEmpty();
});

it('covers FinancialStatements page mount and view data', function (): void {
    $page = new \Liberu\Accounting\FinancialStatementsFilament\Pages\FinancialStatements;
    expect(fn () => $page->mount(1))->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);

    $this->actingAs(\Liberu\Accounting\Erp\Models\User::factory()->create());
    $entity = \Liberu\Accounting\Core\Models\LegalEntity::query()->create(['name' => 'FSF Ent', 'currency_code' => 'USD', 'accounting_basis' => 'accrual']);
    $book = \Liberu\Accounting\Core\Models\Book::query()->create(['legal_entity_id' => $entity->getKey(), 'name' => 'FSF Book', 'code' => 'FSFB', 'accounting_basis' => 'accrual', 'is_active' => true]);

    $page->mount((int) $book->getKey());
    $data = Closure::bind(fn () => $this->getViewData(), $page, $page::class)();
    expect($data)->toHaveKey('statement');
});
